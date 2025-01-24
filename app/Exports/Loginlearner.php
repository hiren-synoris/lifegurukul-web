<?php

namespace App\Exports;

use App\Models\Chapter;
use App\Models\Learner;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;

class Loginlearner implements FromView
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

            
        $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile', 'learners.country_id', 'learners.d_o_b')->withCount('getDevice');

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
        
        $data = $query->get();  
        
        // dd($data);
        return view('admin.reports.export_login_learner', compact("data"));
    }
}
