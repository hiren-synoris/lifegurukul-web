<?php

namespace App\Console\Commands;

use App\Mail\EarnCoinMail;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Learner;
use App\Models\UserCoin;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CourseFinishedEarnCoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'course-finished-earn-coin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give coin to learner on completion of course';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $currentTime = Carbon::now()->subHours(1);
        \Log::info("start schedule for course-finished-earn-coin");
        $data = UserCourseProgress::select('id', 'learner_id', 'chapter_id', 'updated_at')
            ->where('is_completed', '1')
            ->where('updated_at', '>=', $currentTime)
            ->get();

        // dd($data);

        $new_data = [];
        foreach ($data as $record) {
            $get_chapter = Chapter::select('course_id')
                ->where('id', $record->chapter_id)
                ->first();
            if ($get_chapter) {
                $new_data[$record->learner_id][$get_chapter->course_id] = 1;
            }
        }

        foreach ($new_data as $learner_id => $learner_arr) {
            foreach ($learner_arr as $course_id => $course) {

                $total_chapter_ids = Chapter::where('course_id', $course_id)
                    ->where("parent_id", "!=", 0)
                    ->where("asset_type", "!=", 7)
                    ->where("asset_type", "!=", 4)
                    ->pluck('id')
                    ->toArray();

                $count = UserCourseProgress::whereIn('chapter_id', $total_chapter_ids)
                    ->where('learner_id', $learner_id)
                    ->where('is_completed', 1)
                    ->count();

                if ($count == count($total_chapter_ids)) {
                    $existingCoin = UserCoin::where("type", 7)->where("learner_id", $learner_id)->where("course_id", $course_id)->get();

                    if ($existingCoin->count() == 0) {
                        \Log::info("complete course log 1");
                        $courseFinished = Course::find($get_chapter->course_id);
                        if (isset($courseFinished->course_finished) && $courseFinished->course_finished != "" && $courseFinished->course_finished != "0") {

                            UserCoin::create([
                                'learner_id' => $learner_id,
                                'coins' => $courseFinished->course_finished,
                                'type' => 7,
                                'course_id' => $get_chapter->course_id,
                                // 'comment' => "Congratulations, You have got $courseFinished->course_finished coins to successfully. completed to' . $courseFinished->title . ' certain time",
                                'comment' => "You have earned $courseFinished->course_finished success coins due to you have completed $courseFinished->title course",
                            ]);

                            \Log::info("done course-finished-earn-coin");

                            $subject = "Congratulations! Success Coins Allocated to Your Account!";
                            // $content = "Congratulations, You have got $courseFinished->course_finished coins to successfully.";
                            $content = "you have completed $courseFinished->title course";

                            $plus = UserCoin::where("learner_id", $learner_id)
                                ->where(function ($query) {
                                    $query->where("type", "!=", 2);
                                    $query->Where("type", "!=", "4");
                                })->sum("coins");

                            $deduct = UserCoin::where("learner_id", $learner_id)
                                ->where(function ($query) {
                                    $query->where("type", "=", 2)
                                        ->orWhere("type", "=", 4);
                                })->sum("coins");

                            $total = $plus - $deduct;
                            //$user_coin = UserCoin::where("learner_id", $learner_id)->where("type", 7)->first();

                            $learner = Learner::where("id", $learner_id)->first();

                            $inst = new EarnCoinMail($content, $subject, $learner, $courseFinished->course_finished, $total);
                            if ($learner) {
                                Mail::to($learner->email)->send($inst);
                            }
                        }

                    } else {
                        \Log::info("not complete course log 1");
                    }

                    /* when completed in specific time */
                    \Log::info("Start when completed in specific time ");
                    $course = Course::where('id', $course_id)->with("userCourseExpectedRelationship")->firstOrFail();

                    if ($course->course_finished_day != null && $course->course_finished_day != "0" && $course->days != null) {

                        $package_course = CoursePackage::where("course_id", $course_id)->first();

                        if ($package_course) {
                            $user_course = UserCourse::where("course_id", $package_course->package_id)->first();
                            $course_days = isset($user_course->created_at) ? $user_course->created_at : "";
                        } else {

                            $course_days = isset($course->userCourseExpectedRelationship[0]->created_at) ? $course->userCourseExpectedRelationship[0]->created_at : "";
                        }

                        $currentDate = Carbon::now();
                        $courseDate = Carbon::parse($course_days);

                        if ($courseDate->diffInDays($currentDate) + 1 <= 2) {
                            $chapter_watch_whole = Chapter::where("course_id", $course->id)
                                ->where("parent_id", "!=", 0)
                                ->where("asset_type", "!=", 7)
                                ->where("asset_type", "!=", 4)
                                ->pluck('id');

                            $usercousreprogress = UserCourseProgress::whereIn("chapter_id", $chapter_watch_whole)->where("learner_id", $learner_id)->where("is_completed", 1)->get();

                            if ($usercousreprogress->count() == $chapter_watch_whole->count()) {

                                if ($usercousreprogress) {
                                    $check_user = UserCoin::where("type", 8)->where("learner_id", $learner_id)->where("course_id", $course->id)->get();

                                    if ($check_user->count() == 0) {

                                        $user_coin = UserCoin::create([
                                            "learner_id" => $learner_id,
                                            "course_id" => $course->id,
                                            "coins" => $course->course_finished_day,
                                            "type" => 8,
                                            // "comment" => "Congratulations, You have got $course->course_finished_day coins to successfully. completed to $course->title course",
                                            // "comment" => "Completed Course $course->title in certain time, earned you $course->course_finished_day coins.",
                                            "comment" => "You have earned $course->course_finished_day success coins due to you have completed $course->title course within $course->days days",
                                        ]);
                                        \Log::info("done in specific time");

                                        // $plus = UserCoin::where("type","!=",2)->where("type","!=",2)->sum("coins");
                                        // $deduct = UserCoin::where("type",2)->Orwhere("type",4)->sum("coins");

                                        $plus = UserCoin::where("learner_id", $learner_id)
                                            ->where(function ($query) {
                                                $query->where("type", "!=", 2);
                                                $query->Where("type", "!=", "4");
                                            })->sum("coins");

                                        // $deduct = UserCoin::where("type",2)->where("type",4)->sum("coins");
                                        $deduct = UserCoin::where("learner_id", $learner_id)
                                            ->where(function ($query) {
                                                $query->where("type", "=", 2)
                                                    ->orWhere("type", "=", 4);
                                            })->sum("coins");

                                        $total = $plus - $deduct;

                                        $subject = "Congratulations! Success Coins Allocated to Your Account!";
                                        // $content = "Congratulations, You have got $course->course_finished_day coins to successfully.";
                                        //$content = "Completed Course $course->title in certain time, earned you $course->course_finished_day coins.";
                                        $content = "you have completed $course->title course within $course->days days";
                                        $learner = Learner::where("id", $learner_id)->first();

                                        $inst = new EarnCoinMail($content, $subject, $learner, $course->course_finished_day, $total);
                                        if ($learner) {
                                            Mail::to($learner->email)->send($inst);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return Command::SUCCESS;
    }
}
