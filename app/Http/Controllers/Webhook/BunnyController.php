<?php

namespace App\Http\Controllers\Webhook;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\NewPostUpload;
use Illuminate\Support\Facades\Notification;

class BunnyController extends Controller
{
    public function videoStatus(Request $request)
    {
        // Following status we are getting from bunny webhook
        // 3 - Finished: The video encoding has finished and the video is fully available
        // 4 - Resolution finished: The encoder has finished processing one of the resolutions. The first request also signals that the video is now playable.
        Log::info("Bunny request: ".json_encode($request));
        if(isset($request) && ($request->Status == 3 || $request->Status == 4)){
            Log::info("Bunny webhook sent status 3 or 4: ".$request->getContent());

            $post = Post::where('video_id',$request->VideoGuid)
            ->where('library_id',$request->VideoLibraryId)
            ->where('status', '!=', config('constant.status.active_value'))
            ->first();

            if($post){
                deleteFile($post->getRawOriginal('media_url'));
                
                $post->update([
                    'status'    => 'active',
                    'media_url' => NULL
                ]);

                //Send notificaiton to all followers
                Notification::send($post->user->follower, new NewPostUpload("Checkout new video uploaded by - ".$post->user->full_name, $post));
            }

            return true;
        }else{
            Log::info("Bunny webhook sent other status: ".$request->getContent());
            return false;
        }
    }
}
