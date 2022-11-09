<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class UpdatePostLatLon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:lat-lon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update post latitude and longitude based on taluka id';

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
        Post::whereNull('latitude')
            ->orWhereNull('longitude')
            ->with('taluka')
            ->chunkById(100, function($posts) {
                foreach($posts as $post) {
                    $taluka = $post->taluka;
                    if($taluka && $taluka->latitude && $taluka->longitude){
                        $post->latitude = $taluka->latitude;
                        $post->longitude = $taluka->longitude;
                        $post->save();                        
                    }
                }
            });
    }
}
