<?php

namespace App\Exports;

use App\Models\CoursePackage;
use App\Models\UserCourse;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class Enrollearner implements FromView
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

        // $query = UserCourse::
        //     with(['course.chapters' => function ($query) {
        //     $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id')->where('asset_type', '!=', 4)->where('asset_type', '!=', 7);
        // }])
        // with("userChpater")

        // select("*")->with("getCoupon:code,id","learnerLastLogin");

        // if ($this->request['filter_data']['signup_date'] && !empty($this->request-['filter_data']['signup_dat'])) {
        //     if ($this->request['filter_data']['signup_date'] && !empty($this->request->filter_data['signup_date'])) {
        //         $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $this->request['filter_data']['signup_date'])->format('Y-m-d') . "%");
        //     }
        // }
        // dd($query->limit(10)->get());

        $query = UserCourse::with("learner:name,id,name,country_id,mobile", "course:title,id", "getCoupon:code,id", "learnerLastLogin", "countryName");
        // ->where('course_id', 577)
        // ->whereIn('id', function ($query) {
        //     $query->selectRaw('MAX(id)')
        //         ->from('user_courses')
        //         ->groupBy('course_id',"learner_id");
        // })->get();

        if ($this->request['ids']) {
            $query->whereIn("id", explode(",", $this->request['ids']));
        }

        $all_date = $this->request['filter_data']['signup_date'];

        $all_date_arr = explode(' - ', $all_date);

        $start_date = "";
        if (isset($all_date_arr[0]) && $all_date_arr[0] != '') {
            $start_date = date('Y-m-01');
            $start = str_replace('/', '-', $all_date_arr[0]);
            $start_date = date("Y-m-d", strtotime($start));
        }
        $end_date = "";
        if (isset($all_date_arr[1]) && $all_date_arr[1] != '') {
            $end_date = date('Y-m-d');
            $end = str_replace('/', '-', $all_date_arr[1]);
            $end_date = date("Y-m-d", strtotime($end));
        }

        if ($this->request['filter_data']['price']) {
            $query->whereBetween('price', [$this->request['filter_data']['price'], $this->request['filter_data']['priceTo']]);
        }

        $query->when($start_date && $end_date,
            function ($orders) use ($start_date, $end_date) {
                $orders->whereDate('user_courses.created_at', '>=', $start_date);
                $orders->whereDate('user_courses.created_at', '<=', $end_date);
            });

        if ($this->request['filter_data']['course'] && !empty($this->request['filter_data']['course'])) {
            // $query->with(["userCourses", function ($q) {
            // $query->where('course_id', $this->request['filter_data']['course']);
            // }]);

            $course_package = CoursePackage::whereIn("course_id", $this->request['filter_data']['course'])->first();

            $course_packages = CoursePackage::whereIn("course_id", $this->request['filter_data']['course'])
                ->orWhere("course_id", optional($course_package)->package_id)
                ->pluck('package_id');
            $course_ids = $course_packages->merge($this->request['filter_data']['course'])->unique();
            $query->whereIn('course_id', $course_ids);

        }
        if ($this->request['filter_data']['instructor'] && !empty($this->request['filter_data']['instructor'])) {
            $req = $this->request;
            $query->whereHas("course", function ($q) use ($req) {
                $q->where('courses.instructor_id', $req['filter_data']['instructor']);
            });
        }

        if ($this->request['filter_data']['email'] && !empty($this->request['filter_data']['email'])) {
            $query->where('email', 'like', "%" . $this->request['filter_data']['email'] . "%");
        }

        if ($this->request['filter_data']['mobile'] && !empty($this->request['filter_data']['mobile'])) {
            $query->where('mobile', 'like', "%" . $this->request['filter_data']['mobile'] . "%");

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

        if ($this->request['filter_data']['login_in'] && !empty($$this->request['filter_data']['login_in'])) {
            $query->whereHas("learnerLastLogin", function ($q) use ($request) {
                $q->where('type', 1);
            });
        }

        $data = $query->orderBy("id", "desc")->get();

        $user_sales = "";

        $coin_price = config()->has('settings.completedprofile') ? config('settings.completedprofile') : 0;
        return view('admin.reports.export_learner', compact("data", "user_sales", "coin_price"));

    }

}
