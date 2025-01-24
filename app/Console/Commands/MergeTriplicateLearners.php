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

class MergeTriplicateLearners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'learners:merge-triplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge triplicate learners with the same mobile number and country code';

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
        ->having('mobile_count', '=', 3)        // Filter for duplicates (mobile count greater than 1)
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
        // dd(($learners_record));
        dd(json_encode($learners_record));
        //Log::info('Duplicate Learners Found', ['duplicates' => json_encode($learners_record)]);
        // remove the triple entry duplicate
        if(!empty($learners_record)) {
            foreach ($learners_record as $mobile => $courses) {
                // Get the keys (learners)
                $courseIds = array_keys($courses);
            
                if (count($courseIds) == 3) {
                    // Get the first two learners to remove
                    $firstLearnerId = $courseIds[0];
                    $secondLearnerId = $courseIds[1];
            
                    // Get the last learner to assign the courses
                    $lastLearnerId = end($courseIds);
            
                    // Update user_courses table, assigning courses of the first two learners to the last learner
                    DB::table('user_courses')
                        ->whereIn('learner_id', [$firstLearnerId, $secondLearnerId])
                        ->update(['learner_id' => $lastLearnerId]);
            
                    RatingReview::whereIn('learner_id', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    Wishlist::whereIn('learner_id', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    UserCourse::whereIn('learner_id', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    UserCourseProgress::whereIn('learner_id', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    Notification::whereIn('learnerId', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    DeviceToken::whereIn('learner_id', [$firstLearnerId, $secondLearnerId])->forceDelete();

                    // Delete the first two learners from the database
                    Learner::whereIn('id', [$firstLearnerId, $secondLearnerId])->forceDelete();
                    Log::info('Triplicate Learners Deleted ids - '.$firstLearnerId .' - '.$secondLearnerId);
                    // Remove the first two learners from the array
                    // unset($learners_record[$mobile][$firstLearnerId], $learners_record[$mobile][$secondLearnerId]);

                    // break;
                }
            }
        }
        
        $this->info('All triplicate have been merged.');
        return Command::SUCCESS;
    }
}
