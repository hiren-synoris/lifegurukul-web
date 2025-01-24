<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\UserCourse;
use App\Exports\Enrollearner;
use App\Exports\Loginlearner;
use App\Exports\SignupLearner;
use Illuminate\Bus\Queueable;
use App\Exports\CompleteReport;
use App\Exports\PackageReport;
use App\Exports\PendingCoursesExport;
use App\Exports\PurchaseExport;
use App\Models\DownloadExcelAdmin;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendLearner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $data;
    public $params;
    public $type;
    public $scope;
    public $subject;
    public $content;
    public $status;
    public $user_id;
    public function __construct($data, $params, $type, $scope, $subject, $content, $status, $user_id)
    {
        $this->data = $data;
        $this->params = $params;
        $this->type = $type;
        $this->scope = $scope;
        $this->subject = $subject;
        $this->content = $content;
        $this->status = $status;
        $this->user_id = $user_id;
        // dd($this->data['filter_data']['instructor']);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->status == "sales_report") {

            $file_name = 'sales_report_' . time() . '.xlsx';
            $filenewname = str_replace(' ', '_', $file_name);
            // (new Enrollearner($this->data))->store($filenewname,storage_path('report'));
            Excel::store(new Enrollearner($this->data), $filenewname, 'report');

            DownloadExcelAdmin::create([
                "user_id" => $this->user_id,
                "excel_file" => $filenewname,
            ]);
            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);
        } else if ($this->status == "login-learner") {
            $file_name = 'login_learner_report_' . time() . '.xlsx';
            $filenewname = str_replace(' ', '_', $file_name);
            // (new Enrollearner($this->data))->store($filenewname,storage_path('report'));
            Excel::store(new Loginlearner($this->data), $filenewname, 'report');

            DownloadExcelAdmin::create([
                "user_id" => $this->user_id,
                "excel_file" => $filenewname,
            ]);
            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);
            //send_notification($this->params,$this->type, $this->scope, $this->subject,$this->content,$filenewname);
        }elseif($this->status=="course_export"){
            $file_name = 'Courses report_'. time().'.xlsx';
            $filenewname = str_replace(' ', '_', $file_name);

            Excel::store(new PendingCoursesExport($this->data), $filenewname,'report');

            DownloadExcelAdmin::create([
                "user_id"=>$this->user_id,
                "excel_file"=>$filenewname,
            ]);
            send_notification($this->params,$this->type, $this->scope, $this->subject,$this->content,$filenewname);
        } else if ($this->status == "user-signup") {
            $file_name = 'user_signup_report_' . time() . '.xlsx';
            $filenewname = str_replace(' ', '_', $file_name);
            // (new Enrollearner($this->data))->store($filenewname,storage_path('report'));
            Excel::store(new SignupLearner($this->data), $filenewname, 'report');

            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);

        }elseif($this->status=="purchase_report"){
            $file_name = 'purchase_report_' . time() . '.xlsx';
            $filenewname = str_replace(' ', '_', $file_name);
            // (new Enrollearner($this->data))->store($filenewname,storage_path('report'));
            Excel::store(new PurchaseExport($this->data), $filenewname, 'report');

            DownloadExcelAdmin::create([
                "user_id" => $this->user_id,
                "excel_file" => $filenewname,
            ]);
            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);
        } else if ($this->status=="sales_learner_package_report") {

            $course_name = Course::where("id", $this->data['course_id'])->first()->title;
            $course_name = strtolower($course_name);
            $course_name = str_replace(" ", "_", $course_name);
            $course_name = $course_name.'_'.time();
            $file_name = "$course_name.xlsx";
            $filenewname = str_replace(' ', '_', $file_name);
            // (new CompleteReport($this->data))->store($filenewname);
            Excel::store(new PackageReport($this->data), $filenewname, 'report');
            DownloadExcelAdmin::create([
                "user_id" => $this->user_id,
                "excel_file" => $filenewname,
            ]);
            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);
        }
         else { // course complete usage report
            $course_name = Course::where("id", $this->data['course_id'])->first()->title;
            $course_name = strtolower($course_name);
            $course_name = str_replace(" ", "_", $course_name);
            $course_name = $course_name.'_'.time();
            $file_name = "$course_name.xlsx";
            $filenewname = str_replace(' ', '_', $file_name);
            // (new CompleteReport($this->data))->store($filenewname);
            Excel::store(new CompleteReport($this->data), $filenewname, 'report');
            DownloadExcelAdmin::create([
                "user_id" => $this->user_id,
                "excel_file" => $filenewname,
            ]);
            send_notification($this->params, $this->type, $this->scope, $this->subject, $this->content, $filenewname);
        }
    }
}
