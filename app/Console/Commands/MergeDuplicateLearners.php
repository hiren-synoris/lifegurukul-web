<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use Illuminate\Console\Command;
use App\Models\Learner;
use App\Models\Notification;
use App\Models\UserCourse;
use App\Models\RatingReview;
use App\Models\Wishlist;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeDuplicateLearners extends Command
{
    // Command signature and description
    protected $signature = 'learners:merge-duplicates';
    protected $description = 'Merge duplicate learners with the same mobile number and country code';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Find duplicates by mobile and country_id
        $duplicates = DB::table('learners')
    ->select(
            DB::raw('GROUP_CONCAT(id) as ids'),  // Concatenate the ids of the duplicate records
            'country_id',
            'mobile',
            DB::raw('COUNT(*) as mobile_count')  // Count the number of duplicates
        )
        ->groupBy('country_id', 'mobile')       // Group by country and mobile
        ->having('mobile_count', '=', 2)        // Filter for duplicates (mobile count greater than 1)
        ->orderBy('id', 'ASC')                  // Order by ID in ascending order
        // ->limit(10)
        ->get()->toArray();
        //dd(count($duplicates)); // 8500 records
        $learners_record = [];
        if(!empty($duplicates)) {
            foreach ($duplicates as $duplicate) {
                $one_record_learners_ids = explode(",", $duplicate->ids);
    
                if(!empty($one_record_learners_ids) && count($one_record_learners_ids) > 1) {
                    foreach ($one_record_learners_ids as $learner_id) {
                        //$is_courses_added = UserCourse::select(['id', 'course_id'])->where('learner_id', $learner_id)->pluck('id', 'course_id')
                        //->toArray();
                        $is_courses_added = UserCourse::select(['id', 'course_id'])->where('learner_id', $learner_id)->get()->toArray();
                        // dd($is_courses_added);
                        if(!empty($is_courses_added)) {
                            // $learners_record[$duplicate->mobile][$learner_id] = ['total_course' => count($is_courses_added), 'courses' => implode(', ', $is_courses_added)];
                            $learners_record[$duplicate->mobile][$learner_id] = count($is_courses_added);
                        } else {
                            $learners_record[$duplicate->mobile][$learner_id] = 0; 
                        }
                        //dd($learners_record);
                    }
                }
            }
    
        }
        dd(json_encode($learners_record));
        if (!empty($learners_record)) {
            foreach ($learners_record as $mobile => $courses) {
                // Get the keys (learners' IDs)
                $courseIds = array_keys($courses);
                // dd($courseIds);
                if (count($courseIds) == 2) {
                    // dd($learners_record[0]['total_course']);
                    // if($learners_record[0]['total_course']) {

                    // }
                    // Get the first two learner IDs
                    $firstLearnerId = $courseIds[0];
                    $secondLearnerId = $courseIds[1];
        
                    // If both learners have courses, merge them into the last one and delete the first learner
                    DB::table('user_courses')
                        ->where('learner_id', $firstLearnerId)
                        ->update(['learner_id' => $secondLearnerId]);
    
                    RatingReview::where('learner_id', $firstLearnerId)->forceDelete();
                    Wishlist::where('learner_id', $firstLearnerId)->forceDelete();
                    UserCourse::where('learner_id', $firstLearnerId)->forceDelete();
                    UserCourseProgress::where('learner_id', $firstLearnerId)->forceDelete();
                    Notification::where('learnerId', $firstLearnerId)->forceDelete();
                    DeviceToken::where('learner_id', $firstLearnerId)->forceDelete();
    
                    // Delete the first learner from the database
                    Learner::where('id', $firstLearnerId)->forceDelete();
                    Log::info('Duplicate Learners Deleted id - '.$firstLearnerId);
                    // Remove the first learner from the array
                    // unset($learners_record[$mobile][$firstLearnerId]);
                    // break;
                }
            }
        }
        // dd(($learners_record));
        // dd(json_encode($learners_record));
        // // remove the double entry duplicate
        // if(!empty($learners_record)) {
        //     foreach($learners_record as $mobile=>$val) {
        //         if(!empty($val) && count($val) == 2) {
        //             foreach($val as $l_id => $c_count) {
        //                 if($c_count == 0) { // if no any course then delete that learners
        //                     $learner = Learner::where('id', $l_id)->firstOrFail();
        //                     RatingReview::where('learner_id', $l_id)->forceDelete();
        //                     Wishlist::where('learner_id', $l_id)->forceDelete();
        //                     UserCourse::where('learner_id', $l_id)->forceDelete();
        //                     UserCourseProgress::where('learner_id', $l_id)->forceDelete();
        //                     Notification::where('learnerId', $l_id)->forceDelete();
        //                     DeviceToken::where('learner_id', $l_id)->forceDelete();
        //                     $learner->forceDelete();
        //                     unset($learners_record[$mobile]);
        //                     continue;
        //                 }
        //             }
        //         }

        //     }
        // }
        
        // dd(json_encode($learners_record));
        // Log::info('Duplicate Learners Found', ['duplicates' => json_encode($learners_record)]);
        $this->info('All duplicates have been merged.');
        return Command::SUCCESS;
    }
}