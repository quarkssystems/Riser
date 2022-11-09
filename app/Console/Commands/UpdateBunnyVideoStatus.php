<?php

namespace App\Console\Commands;

use App\Models\Post;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateBunnyVideoStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bunny:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update video status from processing to active by checking API side from bunny';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Post::where('status',config('constant.status.processing_value'))
            ->whereNotNull('media_url')
            ->whereNotNull('library_id')
            ->whereNotNull('video_id')
            ->chunkById(10, function($posts) {
                foreach($posts as $post) {
                    
                    //API call to check on bunny with video status
                    $client = new Client();
                    $url = config('constant.video_api_url').config('constant.video_library_id');
                    
                    $responseVideo = $client->request('GET', $url.'/videos/'.$post->video_id, [
                        'headers' => [
                          'Accept' => 'application/json',
                          'AccessKey' => config('constant.video_api_key'),
                        ],
                    ]);
                      
                    $videoStatus = json_decode($responseVideo->getBody());

                    //Update Post status if it is available or finished
                    if($videoStatus && ($videoStatus->status == 3 || $videoStatus->status == 4)){
                        $post->media_url = NULL;
                        $post->status = config('constant.status.active_value');
                        $post->update();
                    }else if($videoStatus && $videoStatus->status == 5){
                        $post->media_url = NULL;
                        $post->status = config('constant.status.failed_value');
                        $post->update();
                    }
                }
            });
    }
}
