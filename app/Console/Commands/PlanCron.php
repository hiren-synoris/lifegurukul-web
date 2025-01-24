<?php

namespace App\Console\Commands;

use App\Models\Log;
use App\Models\Course;
use App\Models\CoursePlan;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PlanCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // $course = Course::where("default_web_price",'')->where("default_iphone_price",'')->where("default_android_price",'')->get();

        $affected =CoursePlan::
            where('status', 1)
            // ->where('course_limit', 1 )
            // ->where('is_fixed_date', 1)
            ->with("course")->whereDate('access_value', '<', Carbon::now())->get();

        foreach($affected as $val) {
            CoursePlan::where("id",$val->id)->update(["status"=>"0"]);
            $data = $val->course->where("id",$val->course->id)->first();
            $data->default_web_price = NULL;
            $data->default_iphone_price = NULL;
            $data->default_android_price = NULL;
            $data->save();

            $course_plan = CoursePlan::where("course_id",$val->course->id)->where("status",1)->whereHas("course",function($q){
                $q->where("default_web_price",null)->where("default_iphone_price",null)->where("default_android_price",null);
            })
            ->orderBydesc("id")->get();

            foreach($course_plan as $plan) {

                Course::where("id",$plan->course_id)->update([
                    "default_web_price"=>$plan->id,
                    "default_iphone_price"=>$plan->id,
                    "default_android_price"=>$plan->id,
                ]);
            }

        }

        // $course_plan =CoursePlan::where('status', 1)
        // ->whereHas("course",function($q){
        //     $q->where("default_web_price",null)->where("default_iphone_price",null)->where("default_android_price",null);
        // })
        // ->orderBydesc("id")->get();



        info("Cron has disabled $affected total plans");
        return Command::SUCCESS;
    }
}
