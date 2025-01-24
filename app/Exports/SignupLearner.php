<?php

namespace App\Exports;

use App\Models\Chapter;
use App\Models\Learner;
use App\Models\CoursePackage;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;

class SignupLearner implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {


        $query = Learner::with('userLastLogin')->withCount('getDevice');

        if ($this->request['ids']) {
            $query->whereIn("id", explode(",", $this->request['ids']));
        }


        if ($this->request['filter_data']['email'] && !empty($this->request['filter_data']['email'])) {
            $query->where('email', 'like', "%" . $this->request['filter_data']['email'] . "%");
        }



        if ($this->request['filter_data']['mobile'] && !empty($this->request['filter_data']['mobile'])) {
            $query->where('mobile', 'like', "%" . $this->request['filter_data']['mobile'] . "%");
        }


        if ($this->request['filter_data']['signup_date'] && !empty($this->request['filter_data']['signup_date'])) {

            $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $this->request['filter_data']['signup_date'])->format('Y-m-d') . "%");
        }

        if ($this->request['filter_data']['device_count'] && !empty($this->request['filter_data']['device_count'])) {
            $deviceCount = $this->request['filter_data']['device_count'];
            $query->withCount(["getDevice" => function ($q) {
                $q->select(DB::raw('count(*)'));
            }])->having('get_device_count', $deviceCount);

        }

        if ($this->request['filter_data']['course'] && !empty($this->request['filter_data']['course'])) {
            // $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
            // $query->where('uc.course_id', $this->request['filter_data']['course']);

            $course_id = $this->request['filter_data']['course'];

            $course_package = CoursePackage::whereIn("course_id", $course_id)->first();

            $course_packages = CoursePackage::whereIn("course_id", $course_id)
                ->orWhere("course_id", optional($course_package)->package_id)
                ->pluck('package_id');
            $course_ids = $course_packages->merge($course_id)->unique();

            $query->whereHas('user_courses', function ($q) use ($course_ids) {
                $q->whereIn('course_id', $course_ids);
            });
        }

        if ($this->request['filter_data']['city'] && !empty($this->request['filter_data']['city'])) {
            $query->where('learners.city_id', 'like', $this->request['filter_data']['city']);
        }
        if ($this->request['filter_data']['state'] && !empty($this->request['filter_data']['state'])) {
            $query->where('learners.state_id', 'like', $this->request['filter_data']['state']);
        }
        if ($this->request['filter_data']['country'] && !empty($this->request['filter_data']['country'])) {
            $query->where('learners.country_id', 'like', $this->request['filter_data']['country']);
        }

        if ($this->request['filter_data']['age'] && !empty($this->request['filter_data']['age']) || $this->request['filter_data']['ageTo'] && !empty($this->request['filter_data']['ageTo'])) {
            if ($this->request['filter_data']['age'] && !empty($this->request['filter_data']['age'])) {
                $birthdate1 = now()->subYears($this->request['filter_data']['age']);
                $query->where('d_o_b', '<=', $birthdate1);
            }

            if ($this->request['filter_data']['ageTo'] && !empty($this->request['filter_data']['ageTo'])) {
                $birthdate2 = now()->subYears($this->request['filter_data']['ageTo']);
                $query->where('d_o_b', '>=', $birthdate2);
            }
        }
        if ($this->request['filter_data']['gender'] && !empty($this->request['filter_data']['gender'])) {
            if ($this->request['filter_data']['gender'] == "1") {
                $query->where('learners.gender', 1);
            }
            if ($this->request['filter_data']['gender'] == "2") {
                $query->where('learners.gender', 2);
            }
            if ($this->request['filter_data']['gender'] == "3") {
                $query->where('learners.gender', 3);
            }
        }

        if ($this->request['filter_data']['day_filters'] && !empty($this->request['filter_data']['day_filters'])) {
            if($this->request['filter_data']['day_filters'] == 'daterange' && $this->request['filter_data']["daterange"] && $this->request['filter_data']["daterange"] != ''){
                $all_date_arr = explode(' - ', $this->request['filter_data']["daterange"]);

                $start_date = "";
                if(isset($all_date_arr[0]) && $all_date_arr[0] != '') {
                    $start = str_replace('/', '-', $all_date_arr[0]);
                    $start_date = date("Y-m-d", strtotime($start));
                }
                $end_date = "";
                if(isset($all_date_arr[1]) && $all_date_arr[1] != '') {
                    $end = str_replace('/', '-', $all_date_arr[1]);
                    $end_date = date("Y-m-d", strtotime($end));
                }

                $query->whereDate('learners.created_at', '>=', $start_date);
                $query->whereDate('learners.created_at', '<=', $end_date);
            }
            else{
                $now = Carbon::now();
                $daysAgo = $now->subDays($this->request['filter_data']['day_filters']);
                $query->where('learners.created_at', '>=', $daysAgo->format('Y-m-d'));
            }
        }

        $data = $query->orderBy("learners.id","desc")->get();
    
        return view('admin.reports.export_user_signup', compact("data"));
    }
}
