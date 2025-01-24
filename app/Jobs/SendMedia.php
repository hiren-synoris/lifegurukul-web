<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\UserCourse;
use App\Models\Course;
use App\Exports\ExportMedia;
use Illuminate\Bus\Queueable;
use App\Exports\CompleteReport;
use App\Models\DownloadExcelAdmin;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendMedia implements ShouldQueue
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
    public function __construct($data,$params,$type, $scope, $subject,$content,$status,$user_id)
    {
        $this->data = $data;
        $this->params = $params;
        $this->type = $type;
        $this->scope = $scope;
        $this->subject = $subject;
        $this->content = $content;
        $this->status = $status;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if($this->status=="true") {
            $file_name = 'media_report_'. time().'.xlsx';
            //$filenewname = str_replace(' ', '_', $file_name);
            Excel::store(new ExportMedia($this->data), $file_name,'report');

            DownloadExcelAdmin::create([
                "user_id"=>$this->user_id,
                "excel_file"=>$file_name,
            ]);
            send_notification($this->params,$this->type, $this->scope, $this->subject,$this->content,$file_name);
        }
    }
}
