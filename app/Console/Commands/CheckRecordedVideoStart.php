<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirebaseController;
use App\Jobs\SendFCMNotification;
use App\Models\LiveVideo;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class CheckRecordedVideoStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordedvideostart:check';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of recorded videos';

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
        $videos = LiveVideo::whereDate('date_start_at', '<=', today())
        ->whereDate('date_end_at', '>=', today())
        ->where(function ($q) {
            $q->where(function ($q2) {
                // الحالة الطبيعية (نفس اليوم)
                $q2->whereTime('time_start_at', '<=', now())
                   ->whereTime('time_end_at', '>=', now());
            })
            ->orWhere(function ($q2) {
                // الحالة اللي بتعدي نص الليل
                $q2->whereTime('time_start_at', '>', DB::raw('time_end_at'))
                   ->where(function ($q3) {
                       $q3->whereTime('time_start_at', '<=', now())
                          ->orWhereTime('time_end_at', '>=', now());
                   });
            });
        })
        ->where('status', '!=', 'end')
        ->get();



        foreach ($videos as $video) {





            if($video->status == 'pending'){
                $tokens_en = User::whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
                $tokens_ar = User::whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
                $notification_record = [
                    'title_en' => 'Auction Started: ' . $video->title,
                    'title_ar' => 'بدأ المزاد: ' . $video->title_ar,
                    'body_en'  => 'Auction "' . $video->title . '" has started on ' . $video->date_start_at . ' at ' . $video->time_start_at,
                    'body_ar'  => 'بدأ المزاد "' . $video->title . '" بتاريخ ' . $video->date_start_at . ' في ' . $video->time_start_at,
                ];
                // Send using job queue
                dispatch(new SendFCMNotification(
                    $tokens_en,
                    $notification_record['title_en'],
                    $notification_record['body_en'],
                ));
                // Send using job queue
                dispatch(new SendFCMNotification(
                    $tokens_ar,
                    $notification_record['title_ar'],
                    $notification_record['body_ar'],
                ));




            }
                $video->update([
                    'status'=>'start',
                    'start_at'=>date('Y-m-d H:i:s'),
                ]);


                try {
                    $firebase = new FirebaseController();
                    $firebase->ChangeLiveStatus($video->id,'start');
                }
                catch(\Exception $t){}




                foreach ($video->video_items as $item) {
                    $item->update([
                        'status'=>'working',
                    ]);

                    try {
                        $firebase = new FirebaseController();
                        $firebase->ChangeLiveItemStatus($item->live_video_id,$item->id,'working');
                    }
                    catch(\Exception $t){}
                }

            }

        $this->info('Live video status check completed.');
    }
}
