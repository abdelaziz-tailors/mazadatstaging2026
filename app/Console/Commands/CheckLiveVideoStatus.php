<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckLiveVideoStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livevideo:check';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of live videos';

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
        $videos = LiveVideo::whereNull('status')->get(); // Example query; adjust as per your needs

        foreach ($videos as $video) {
            $updatedAt = Carbon::parse($video->updated_at)->addMinutes(1);
            $currentTime = Carbon::now();

            if ($currentTime->greaterThan($updatedAt)) {
                $video->update([
                    'status'=>'end',
                    'end_at'=>date('Y-m-d H:i:s'),
                ]);
                // Add any other actions you need


                try {
                    $firebase = new FirebaseController();
                    $firebase->removeLive($video->id);
                }
                catch(\Exception $t){}

            }
        }

        $this->info('Live video status check completed.');
    }
}
