<?php

namespace App\Console\Commands;

use App\Http\Controllers\FirebaseController;
use App\Jobs\SendFCMNotification;
use App\Models\LiveVideo;
use App\Models\User\User;
use App\Models\VideoComment;
use Illuminate\Console\Command;

class CheckLiveVideoEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livevideoEnd:check';


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
        $now = now();

        // End when the auction window is over: same date/time model as CheckRecordedVideoStart.
        // Normal: past TIMESTAMP(date_end_at, time_end_at). Overnight: past last day or in the
        // daytime gap after time_end_at before time_start_at on date_end_at.
        $videos = LiveVideo::query()
            ->where('status', '!=', 'end')
            ->whereNotNull('date_end_at')
            ->whereNotNull('time_end_at')
            ->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->whereRaw('time_start_at <= time_end_at')
                        ->whereRaw('TIMESTAMP(date_end_at, time_end_at) < ?', [$now]);
                })
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereRaw('time_start_at > time_end_at')
                            ->where(function ($q3) use ($now) {
                                $q3->whereDate('date_end_at', '<', $now)
                                    ->orWhere(function ($q4) use ($now) {
                                        $q4->whereDate('date_end_at', '=', $now)
                                            ->whereRaw('TIME(?) > time_end_at AND TIME(?) < time_start_at', [$now, $now]);
                                    });
                            });
                    });
            })
            ->get();


        foreach ($videos as $video) {
                $video->update([
                    'status'=>'end',
                    'end_at'=>date('Y-m-d H:i:s'),
                ]);


                foreach ($video->video_items as $item) {
                    $item->update([
                        'status'=>'finished',
                        'end_at'=>date('Y-m-d H:i:s'),
                    ]);


                    if($item->user_finished_id==null){


                        $hight_pirce=VideoComment::where('video_id',$item->id)->orderBy('id','desc')->first();

                        if($hight_pirce){
                            $item->update([
                                'finished_price'=>$hight_pirce->comment,
                                'user_finished_id'=>$hight_pirce->user_id,
                            ]);

                            $tokens_en = User::where('id',$item->user_finished_id)->whereNotNull('fcm_token')->where('app_lang', 'en')->pluck('fcm_token')->toArray();
                            $tokens_ar = User::where('id',$item->user_finished_id)->whereNotNull('fcm_token')->where('app_lang', 'ar')->pluck('fcm_token')->toArray();
                                $notification_record = [
                                    'title_en' => 'Auction Ended: ' . $video->title,
                                    'title_ar' => 'انتهى المزاد: ' . $video->title_ar,
                                    'body_en'  => 'Congratulations! You won the auction "' . $video->title . '" on ' . $video->date_end_at . ' at ' . $video->time_end_at,
                                    'body_ar'  => 'تهانينا! لقد فزت بالمزاد "' . $video->title . '" بتاريخ ' . $video->date_end_at . ' في ' . $video->time_end_at,
                                ];
                            // Send using job queue
                            dispatch(new SendFCMNotification(
                                $tokens_en,
                                $notification_record['title_en'],
                                $notification_record['body_en'],
                            ));
                            dispatch(new SendFCMNotification(
                                $tokens_ar,
                                $notification_record['title_ar'],
                                $notification_record['body_ar'],
                            ));


                        }

                    }

                    try {
                        $firebase = new FirebaseController();
                        $firebase->ChangeLiveItemStatus($item->live_video_id,$item->id,'finished');
                    }
                    catch(\Exception $t){}
                }





                try {
                    $firebase = new FirebaseController();
                    $firebase->removeLive($video->id);
                }
                catch(\Exception $t){}

            }

        $this->info('Live video status check completed.');
    }
}
