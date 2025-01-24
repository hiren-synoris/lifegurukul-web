<?php

namespace App\Exports;

use App\Models\Chapter;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchaseExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        // dd( $this->request['filter_data']);
    }

    public function view(): View
    {

        $query = UserCourse::
        //     with(['course.chapters' => function ($query) {
        //     $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id')->where('asset_type', '!=', 4)->where('asset_type', '!=', 7);
        // }])
       // with("userChpater")

        select("*")->with("getCoupon:code,id")->with(["userChpater"=>function($q){
            $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
        }])->orderBy("id", "desc");

        // if ($this->request['filter_data']['signup_date'] && !empty($this->request-['filter_data']['signup_dat'])) {
        //     if ($this->request['filter_data']['signup_date'] && !empty($this->request->filter_data['signup_date'])) {
        //         $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $this->request['filter_data']['signup_date'])->format('Y-m-d') . "%");
        //     }
        // }
        // dd($query->limit(10)->get());

        if($this->request['ids']) {
            $query->whereIn("id",explode(",",$this->request['ids']));
        }

        $all_date =$this->request['filter_data']['signup_date'];

            $all_date_arr = explode(' - ', $all_date);

            $start_date = "";
            if(isset($all_date_arr[0]) && $all_date_arr[0] != '') {
                $start_date = date('Y-m-01');
                $start = str_replace('/', '-', $all_date_arr[0]);
                $start_date = date("Y-m-d", strtotime($start));
            }
            $end_date = "";
            if(isset($all_date_arr[1]) && $all_date_arr[1] != '') {
                $end_date = date('Y-m-d');
                $end = str_replace('/', '-', $all_date_arr[1]);
                $end_date = date("Y-m-d", strtotime($end));
            }

            if($this->request['filter_data']['price']){
                $query->whereBetween('price',[ $this->request['filter_data']['price'], $this->request['filter_data']['priceTo']]);
            }


            $query->when($start_date && $end_date,
                function ($orders) use ($start_date,$end_date) {
                    $orders->whereDate('user_courses.created_at', '>=', $start_date);
                    $orders->whereDate('user_courses.created_at', '<=', $end_date);
            });



            if ($this->request['filter_data']['course'] && !empty($this->request['filter_data']['course'])) {
                // $query->with(["userCourses", function ($q) {
                    $query->where('course_id', $this->request['filter_data']['course']);
                // }]);
            }
            if ($this->request['filter_data']['instructor'] && !empty($this->request['filter_data']['instructor'])) {
                $req = $this->request;
                $query->whereHas("course", function ($q) use ($req) {
                    $q->where('courses.instructor_id', $req['filter_data']['instructor']);
                });
            }

            if ($this->request['filter_data']['email'] && !empty($this->request['filter_data']['email'])) {
                $query->where('email', 'like', "%".$this->request['filter_data']['email']."%");
            }



            if ($this->request['filter_data']['mobile'] && !empty($this->request['filter_data']['mobile'])) {
                $query->where('mobile', 'like' , "%".$this->request['filter_data']['mobile']."%");

            }

            if ($this->request['filter_data']['payment_status'] && !empty($this->request['filter_data']['payment_status'])) {
                $query->where('order_status', $this->request['filter_data']['payment_status']);
            }

            if ($this->request['filter_data']['gender'] && !empty($this->request['filter_data']['gender'])) {
                $req = $this->request;
                $query->whereHas("learner", function ($q) use ($req) {
                    $q->where('gender', $req['filter_data']['gender']);
                });
            }

            if ($this->request['filter_data']['age'] && !empty($this->request['filter_data']['age'])) {
                $req = $this->request;
                $birthdate = now()->subYears($req['filter_data']['age']);
                $query->whereHas("learner", function ($q) use ($birthdate) {
                    $q->where('d_o_b', '<=', $birthdate);
                });

            }

            if ($this->request['filter_data']['city'] && !empty($this->request['filter_data']['city'])) {
                $req = $this->request;
                $query->whereHas("learner", function ($q) use ($req) {
                    $q->where('city_id', $req['filter_data']['city']);
                });
            }


            if ($this->request['filter_data']['state'] && !empty($this->request['filter_data']['state'])) {
                // $query->where('learners.state_id', 'like', $this->request['filter_data']['state']);
                $req = $this->request;
                $query->whereHas("learner", function ($q) use ($req) {
                    $q->where('state_id', $req['filter_data']['state']);
                });
            }
            if ($this->request['filter_data']['country'] && !empty($this->request['filter_data']['country'])) {
                // $query->where('learners.country_id', $this->request['filter_data']['country']);
                $req = $this->request;
                $query->whereHas("learner", function ($q) use ($req) {
                    $q->where('country_id', $req['filter_data']['country']);
                });
            }

            $data = $query->get();

            // $data->each(function ($value) {

            //     $sum = 0;
            //     $value->course?->getChapters?->filter(function ($v) use ($value,&$sum) {
            //         $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')
            //             ->where('asset_type', '!=', 4)
            //             ->where('asset_type', '!=', 7)
            //             ->where('parent_id', $v->id)->count();
            //             $sum += $v->chpter_count;
            //         });
            //         // $value->course?->getChapters?->filter(function ($val) use (&$sum) {
            //     //     return $sum += $val->chpter_count;
            //     // });
            //     $value->tot_chapterCount = $sum;
            // });

            $data->each(function ($value) {
                if (!empty($value->course) && $value->course->chapters->isNotEmpty()) {
                    
                    $value->userChpater->filter(function($fl) use ($value){
                        return $value->new_chapterIds = $fl->chapterIds;
                    });
                    $user_progress = UserCourseProgress::whereIn("chapter_id",explode(",",$value->new_chapterIds))->where("learner_id",$value->learner_id)->where("is_completed",1)->count();
                    // dd($value->user_chpater_count);
                    $value->totalProgress = round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $value->new_chapterIds))) : "0";
                } else {
                    $value->totalProgress = '0';
                }
            });
    
            // $data->map(function ($value, $key) {
            //     if (!empty($value->course?->chapters?->first()) && !empty($value->course?->chapters?->first())) {
            //         $chapterIds = explode(",", $value->course?->chapters?->first()->chapterIds);
            //         $totalDuration = count($chapterIds);
    
            //         $totalCompletedChapter = UserCourseProgress::whereIn('chapter_id', $chapterIds)
            //             ->where('learner_id', $value->learner_id)
            //             ->where('is_completed', 1)->count();
            //             return round($totalCompletedChapter) != 0 ? round(($totalCompletedChapter * 100) / count($chapterIds)) . "%" : "0%";
            //             // ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
            //             // ->where('chapters.asset_type', '!=', 4)
            //             // ->where('chapters.asset_type', '!=', 7)
            //             // ->pluck('watched_times')
            //             // ->first();
    
            //         // $value->totalCompletedDuration = round($totalCompletedChapter);
            //         // $value->totalProgress = ($totalCompletedChapter > 0 && $totalDuration > 0)
            //         // ? round(($totalCompletedChapter * 100) / $value->tot_chapterCount )
            //         // : 0;
            //         // return $value;

            //         // $row->userChpater->filter(function($fl) use ($row){
            //         //     return $row->new_chapterIds = $fl->chapterIds;
            //         // });
            //         // $user_progress = UserCourseProgress::whereIn("chapter_id",explode(",",$row->new_chapterIds))->where("learner_id",$row->learner_id)->where("is_completed",1)->count();
            //         // dd($row->user_chpater_count);
            //         // return round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $row->new_chapterIds))) . "%" : "0%";
    
            //     }
            // });

            $user_sales = "";

            $coin_price = config()->has('settings.completedprofile') ? config('settings.completedprofile') : 0;

            return view('admin.reports.export_purchase', compact("data", "user_sales", "coin_price"));
    }
}
