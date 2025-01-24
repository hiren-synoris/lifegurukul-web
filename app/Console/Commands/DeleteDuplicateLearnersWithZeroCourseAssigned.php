<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCourse;
use App\Models\Learner;
use App\Models\Notification;
use App\Models\RatingReview;
use App\Models\Wishlist;
use App\Models\UserCourseProgress;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeleteDuplicateLearnersWithZeroCourseAssigned extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-duplicate-learners-with-zero-course-assigned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate learners with the same mobile number and country code and course not assigned';

    /**
     * Execute the console command.
     *
     * @return int
     */
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

        $learners_record = [];
        if(!empty($duplicates)) {
            foreach ($duplicates as $duplicate) {
                $one_record_learners_ids = explode(",", $duplicate->ids);
    
                if(!empty($one_record_learners_ids) && count($one_record_learners_ids) > 1) {
                    foreach ($one_record_learners_ids as $learner_id) {
                        $is_courses_added = UserCourse::where('learner_id', $learner_id)->get()->toArray();
                        if(!empty($is_courses_added)) {
                            $learners_record[$duplicate->mobile][$learner_id] = count($is_courses_added); 
                        } else {
                            $learners_record[$duplicate->mobile][$learner_id] = 0; 
                        }
                        //dd($is_courses_added);
                    }
                }
            }
    
        }
        dd(json_encode($learners_record));
        if(!empty($learners_record)) {
            foreach($learners_record as $mobile=>$val) {
                if(!empty($val)) {
                    foreach($val as $l_id => $c_count) {
                        if($c_count == 0) { // if no any course then delete that learners
                            RatingReview::where('learner_id', $l_id)->forceDelete();
                            Wishlist::where('learner_id', $l_id)->forceDelete();
                            UserCourse::where('learner_id', $l_id)->forceDelete();
                            UserCourseProgress::where('learner_id', $l_id)->forceDelete();
                            Notification::where('learnerId', $l_id)->forceDelete();
                            DeviceToken::where('learner_id', $l_id)->forceDelete();
                            Learner::where('id', $l_id)->forceDelete();
                            Log::info('Duplicate Learners Deleted id - '.$l_id);
                            continue;
                        }
                    }
                }
            }
        }
        $this->info('All null duplicate record has been deleted.');
        return Command::SUCCESS;
    }
}
