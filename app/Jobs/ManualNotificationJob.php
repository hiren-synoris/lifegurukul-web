<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\Learner;
use App\Models\UserCourse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ManualNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $request;
    public $learners;
    public $id;
    public $image_name;
    // public $scope;
    // public $subject;
    // public $content;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$learners,$id,$image_name)
    {
        $this->request = $request;
        $this->learners = $learners;
        $this->id = $id;
        $this->image_name = $image_name;

        // $this->scope = $scope;
        // $this->subject = $subject;
        // $this->content = $content;
        // dd($this->request['platform']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // dd($this->request);
        $scope = [];
        $params = [];
        if(in_array("1",$this->request['platform'])){
            $scope[]="email";

        }
        if(in_array("2",$this->request['platform'])){
            $scope[]="web";
        }
        if(in_array("3",$this->request['platform'])){
            $scope[]="push";
        }
        $subject=$this->request['title'];
        $content=$this->request['content'];
        $course_id = "";
        if(isset($this->request['slug']) && $this->request['slug'] != '') {
            $course = Course::where("slug",$this->request['slug'])->first();
            if($course) {
                $course_id = $course->id;
            }
        }

        // $userCourse = UserCourse::where("course_id",$course_id)->first();
        // dd($this->learners);
        foreach($this->learners as $value){

            $params['email']['to']=$value->email;
                $params['email']['learnerId'] = $value->id;
                $course_purchase_id = '';
                if($course_id != '') {
                    $userCourse = UserCourse::where("course_id",$course_id)->where("learner_id",$value->id)->first();
                    if($userCourse) {
                        $course_purchase_id = $userCourse->id;
                    }
                }
                $learners = Learner::where("id",$value->id)->first()->name;

                $params['common']=[
                    'learner_id' => $value->id,
                    'content' => $this->request['content'],
                    'title' => $this->request['title'],
                    'type'=> 2,
                    "slug"=> isset($this->request['slug']) ? $this->request['slug'] : "",
                    "external_link"=> isset($this->request['external_link']) ? $this->request['external_link'] : "",
                    "redirect_type"=>$this->request['type'],
                    "course_id"=>$course_id,
                    "course_purchase_id"=>$course_purchase_id,
                    "name"=>$learners,
                    "manual_notify_id"=>$this->id,
                    "image_name" => $this->image_name ? env('APP_URL') . Storage::url("public/notification/" . $this->image_name) : ""
                ];
                $params['web']['to'] = [$value->id];
                $params['push']['to'] = $value->id;

                send_notification($params,"manual_notification", $scope, $subject,$content);


        }
        // ManualNotificationJob::dispatch($params,'manual_notification', $scope, $subject,$content);

    }
}
