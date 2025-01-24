<?php

namespace App\Jobs;

use App\Imports\CourseImport1;
use App\Imports\ImportLearner;
use App\Mail\ImportExcelError;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ImportExcelErrorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $data;

    public $retryAfter = 6000 * 6000;

    public function __construct($request)
    {
        $this->data = $request;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $planId = isset($this->data['plan_id']) && !empty($this->data['plan_id']) ? $this->data['plan_id'] : "";
        $file = $this->data['import_file'];

        if (isset($this->data['course_id'])) {
            $importLearner = new CourseImport1($this->data['course_id'], $planId, $this->data['admin_id']);
            $importLearner->import($file);

        } else {
            $importLearner = new ImportLearner();
            $importLearner->import($file);
        }
        $course_name = @Course::where("id", $this->data['course_id'])->select("title")->first();
        $msg = "";
        if ($course_name) {
            $msg = "Learner import for " . $course_name->title . " course | LifeGurukul";
        } else {
            $msg = "Learner Import " . '|' . "LifeGurukul";
        }

        if ($importLearner->failures()->isNotEmpty()) {
            $data["failures"] = $importLearner->failures();
            $inst = new ImportExcelError($msg, $data["failures"]);
            $to = $this->data['email'];
            if (!empty($inst) && !empty($to)) {
                Mail::to($to)->send($inst);
            }
        } else {
            $data["failures"] = $importLearner->failures();
            $inst = new ImportExcelError($msg, $data["failures"]);
            $to = $this->data['email'];
            if (!empty($inst) && !empty($to)) {
                Mail::to($to)->send($inst);
            }
        }
    }
}
