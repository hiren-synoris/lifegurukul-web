<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\LearnerLog;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;

class UserLogExport implements FromView
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

        $all_date = $this->request['date_range'];
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

        $data = LearnerLog::with("getLerner:id,name", "getCourse:id,title", "getChapter:id,title")->orderbyDesc("id");
        if ($this->request['date_range']) {
            $data->whereDate('created_at', '>=', $start_date);
            $data->whereDate('created_at', '<=', $end_date);
        }

        $data = $data->get();

        return view("admin.learners.userActivityLog", compact("data"));
    }
}
