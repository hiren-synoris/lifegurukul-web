<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Chapter;
use App\Models\ChapterInfo;
use App\Models\UserCoin;
use App\Models\Course;
use App\Models\Learner;
use App\Mail\EarnCoinMail;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\Mail;

class VideoFinishedEarnCoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video-finished-earn-coin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give coin to learner on completion of video';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentTime = Carbon::now()->subMinutes(10);
        // \Log::info("start for video finished");
        $data = UserCourseProgress::select('id', 'learner_id', 'chapter_id')
            ->where('is_completed', '1')
            ->where('updated_at', '>=', $currentTime)
            ->get();

        if ($data->isNotEmpty()) {
            foreach ($data as $record) {

                $get_chapter = ChapterInfo::select('title', 'whole_video_coin')
                    ->where('chapter_id', $record->chapter_id)
                    ->first();

                if ($get_chapter->whole_video_coin != "" && $get_chapter->whole_video_coin != "0") {
                    $cdata = UserCoin::select('id')
                        ->where('type', '5')
                        ->where('learner_id', $record->learner_id)
                        ->where('chapter_id', $record->chapter_id)
                        ->get();

                    if ($cdata->isEmpty()) {
                        // dd($cdata);

                        $course_data = Chapter::select('course_id')
                            ->where('id', $record->chapter_id)
                            ->first();

                        if (isset($course_data->course_id) && $course_data->course_id != "") {

                            UserCoin::create([
                                'learner_id' => $record->learner_id,
                                'coins' => $get_chapter->whole_video_coin,
                                'type' => 5,
                                'course_id' => $course_data->course_id,
                                'chapter_id' => $record->chapter_id,
                                // 'comment' => 'After watching the video thoroughly, you have earned those coins',
                                'comment' => "You have earned $get_chapter->whole_video_coin success coin due to you have watched $get_chapter->title video completely",
                            ]);

                            // \Log::info("done earn coin successafully for video complated");

                            $subject = "Congratulations! Success Coins Allocated to Your Account!";
                            // $content = "Congratulations, You have got $get_chapter->whole_video_coin coins to successfully.";
                            $content = "you have watched $get_chapter->title video completely.";

                            $plus = UserCoin::where("learner_id", $record->learner_id)
                                ->where(function ($query) {
                                    $query->where("type", "!=", 2);
                                    $query->Where("type", "!=", "4");
                                })->sum("coins");

                            $deduct = UserCoin::where("learner_id", $record->learner_id)
                                ->where(function ($query) {
                                    $query->where("type", "=", 2)
                                        ->orWhere("type", "=", 4);
                                })->sum("coins");

                            $total = $plus - $deduct;
                            //$user_coin = UserCoin::where("learner_id", $record->learner_id)->where("type", 5)->first();

                            $learner = Learner::where("id", $record->learner_id)->first();
                            $inst = new EarnCoinMail($content, $subject, $learner, $get_chapter->whole_video_coin, $total);
                            if ($learner) {
                                Mail::to($learner->email)->send($inst);
                            }
                        }
                    }
                }
            }
        }
        return Command::SUCCESS;
    }
}
