<?php

namespace App\Jobs;

use Exception;
use App\Models\Post;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendPostAwsToBunnyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = new Client();

            $url = config('constant.video_api_url').config('constant.video_library_id');
            $postTitle = preg_replace( "/\r|\n/", "", $this->post->title );
            
            //Call Create Video API first and get guid/videoId
            $responseCreateVideo = $client->request('POST', $url.'/videos', [
            'body' => '{"title":"'.$postTitle.'"}',
            'headers' => [
                'Accept' => 'application/json',
                'AccessKey' => config('constant.video_api_key'),
                'Content-Type' => 'application/*+json',
            ],
            ]);

            $createVideo = json_decode($responseCreateVideo->getBody());

            //After create video call upload video API
            if($createVideo && isset($createVideo->guid)){
                Log::info("video Created: ".json_encode($createVideo));

                $responseUploadVideo = $client->request('PUT', $url.'/videos/'.$createVideo->guid, [
                    'headers' => [
                      'Accept' => 'application/json',
                      'AccessKey' => config('constant.video_api_key'),
                    ],
                    'body' => file_get_contents($this->post->media_url),
                  ]);
                  
                  $uploadVideo = json_decode($responseUploadVideo->getBody());

                  //Update Post record with video id and library id
                  if($uploadVideo && $uploadVideo->statusCode == 200){
                    Log::info("video uploaded: ".json_encode($uploadVideo));
                    Post::where('id',$this->post->id)->update(
                        [
                            'video_id'   => $createVideo->guid,
                            'library_id' => config('constant.video_library_id')
                        ]
                    );
                  }
            }

        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
