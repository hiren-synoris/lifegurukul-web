<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\LearnerLog;
use App\Models\UserCourse;
use App\Models\CoursePackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class AllCoureProgressExport implements FromView
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct($request)
    {
        $this->request = $request;

    }
    // $this->request['course_id']

    public function view(): View
    {

        $course_id = $this->request['course_id'];

        $course_package = CoursePackage::where("course_id", $course_id)->whereHas("getPackageBasedCourse")->first();
        $package_course_id = @$course_package->course_id;

        $data = UserCourse::with(['learner', 'course', 'userChpater'])

            ->where('course_id', $course_id)
            ->orWhere("course_id", @$course_package->package_id)
            ->orderBy('id', 'desc')
            ->with(["userChpater" => function ($q) {
                $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])
            ->get()
            ->unique(function ($item) {
                return $item['learner_id'] . $item['course_id'] . $item['plan_id'];
            });

        return view("admin.courses.allCourseProgress", compact("data"));
    }
}
