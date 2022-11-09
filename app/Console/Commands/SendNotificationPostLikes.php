<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Console\Command;
use App\Notifications\PostLikesDaily;
use Illuminate\Support\Facades\Notification;

class SendNotificationPostLikes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:likes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification daily for new likes on posts';

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
        $today = Carbon::now()->setTimezone('Asia/Kolkata');

        Post::status()
        ->with('likes' , function($query) use($today) {
            $query->whereDate('post_likes.created_at',$today);
        })
        ->whereHas('likes' , function($query) use($today) {
            $query->whereDate('post_likes.created_at',$today);
        })
        ->chunkById(100, function($posts){

            foreach($posts as $post) {
                
                //notification to creator
                Notification::send($post->user, new PostLikesDaily("You have ".$post->likes->count()." new likes on - ".$post->title, $post));
                
            }
            
        });
        
    }
}
