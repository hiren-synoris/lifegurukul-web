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

class PackageReport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    // $rand = rand(0,500000);

    public function __construct($request)
    {
        $this->request = $request;

    }

    public function view(): View
    {

        $data = UserCourse::with(['learner',"countryName"])
        ->where('course_id', $this->request['course_id'])
        ->orderBy('id', 'desc');
        if($this->request['ids']) {
            $data->whereIn("id",explode(",",$this->request['ids']));
        }
        $data = $data->get()
        ->unique('learner_id', 'course_id');


        return view("admin.packages.package_report", compact("data"));
    }
}
