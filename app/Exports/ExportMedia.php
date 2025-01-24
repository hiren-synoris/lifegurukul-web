<?php

namespace App\Exports;

use App\Models\Media;
use App\Models\UserCourse;
use App\Models\Course;
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

class ExportMedia implements FromView
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
        $query = Media::

        select("*")->with("course");

        if (($this->request['assetTypes'] == "all") && ($this->request['courseFilter'] == "all")) {

            $query = Media::with(['course'])->select('id','videoId','asset_type', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at');

        } else if (($this->request['assetTypes']) || ($this->request['courseFilter'])) {
            $query = Media::with(['course'])->select('media.id','media.videoId','media.asset_type', 'media.title', 'media.file_name', 'media.path', 'media.updated_at', 'media.created_at', 'media.created_by');
            if ($this->request['assetTypes'] != 'all') {
                $query->where('media.asset_type', $this->request['assetTypes']);
            }

            if ($this->request['courseFilter'] != 'all') {
                $query->join('chapters', 'media.id', 'chapters.media_id');
                $query->where('chapters.course_id', $this->request['courseFilter']);
                $query->groupBy('chapters.media_id');
            }

            $query->whereNull('media.deleted_at');

        } else {

            $query = Media::with(['course'])->select('id','videoId','asset_type', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at');
        }
        // dd(auth()->id());
        if ((auth()->user()->roles->first()->name) == "instructor") {
            $query->where("created_by", auth()->id());
        }

        if (($this->request['created_date'] && $this->request['created_date'] != '')){
            
            $all_date_arr = explode(' - ', $this->request['created_date']);

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

            $query->whereDate('media.created_at', '>=', $start_date);
            $query->whereDate('media.created_at', '<=', $end_date);
        }

        $data = $query->get();
        return view('admin.media.export_media', compact("data"));

    }

}
