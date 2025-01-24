<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class PendingCoursesExport implements FromView
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
        $request_all = $this->request;

        $query = Course::with(['user:id,name', "coursePlan", "userCourseCount"])

            ->select('id', 'title', 'instructor_id', 'description', 'created_at', 'deleted_at', 'status', 'image', 'order as order_data', 'type', 'slug', 'is_free', 'banner_image', 'approval_status')
            ->whereNull('deleted_at')
            ->when($request_all["user_id"] && !empty($request_all["user_id"]),
                function ($query) use ($request_all) {
                    return $query->where('instructor_id', $request_all["user_id"]);
                })
            ->when($request_all["status"]!="",
                function ($query) use ($request_all) {
                    return $query->where('status', $request_all["status"]);
                })
            ->withCount(['coursePlan' => function ($query) use ($request_all) {
                $query->where('status', 1);
                $query->where('created_at', '<', Carbon::now());

            }])
            ->with(['userCourseCount' => function ($query) {
                $query->selectRaw('course_id, COUNT(DISTINCT learner_id) as user_course_count')
                    ->groupBy('course_id');
            }]);


        // if (auth()->user()->hasRole(User::INSTRUCTOR)) {
        //     $query = $query->where('instructor_id', auth()->id());
        // } else {
        // }
        // $query = $query;

        // if ($this->request['ids']) {
        //     $query->whereIn("id", explode(",", $this->request['ids']));
        // }
        $data = $query->where("type",1)->orderBy('id', 'desc')->get();

        // dd($data);
        return view('admin.reports.export_course', compact("data"));
    }
}
