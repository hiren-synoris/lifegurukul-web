<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use App\Exports\AllCoureProgressExport;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AllCourseProgressJob implements ShouldQueue
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
    public $user_id;
    public function __construct($data,$params,$type, $scope, $subject,$content,$user_id)
    {
        $this->data = $data;
        $this->params = $params;
        $this->type = $type;
        $this->scope = $scope;
        $this->subject = $subject;
        $this->content = $content;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Excel::store(new AllCoureProgressExport($this->data), "LearnerAllCourseProgress.xlsx",'report');

        send_notification($this->params,$this->type, $this->scope, $this->subject,$this->content,"LearnerAllCourseProgress.xlsx");
    }
}
