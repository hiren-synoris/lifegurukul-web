<?php

namespace App\Exports;

use App\Models\Course;
use App\Models\Chapter;
use App\Models\UserCourse;
use App\Models\CoursePackage;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class CompleteReport implements FromView
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
        $courseId = $this->request['course_id'];
        $course_packages = CoursePackage::where("course_id", $courseId)
            ->whereHas("getPackageBasedCourse")->Orwhere("course_id", @$course_package->package_id)
            ->pluck('package_id');
        $course_ids = $course_packages->push($courseId);

        $data = UserCourse::with(['learner', 'course', 'userChpater', "countryName"])
            ->where('course_id', $this->request['course_id'])
            ->orwhereIn("course_id", $course_ids)
            ->with(['course.chapters' => function ($query) {
                $query->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id')->where('asset_type', '!=', 4)->where('asset_type', '!=', 7);
            }])->with("userChpater")
            ->orderBy('id', 'desc');

        if ($this->request['ids']) {
            $data->whereIn("id", explode(",", $this->request['ids']));
        }
        $data = $data->get()
            ->unique('learner_id', 'course_id');

        $data->each(function ($value)use($courseId) {
            if ($value->course->type == 2) {
                $course = Course::where("id",$courseId)->first();
                $sum = 0;
                $course->getChapters?->filter(function ($v) use ($value, &$sum) {
                    $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')
                        ->where('asset_type', '!=', 4)
                        ->where('asset_type', '!=', 7)
                        ->where('parent_id', $v->id)->count();
                    $sum += $v->chpter_count;
                });
                $value->tot_chapterCount = $sum;

            } else {
                $sum = 0;
                $value->course?->getChapters?->filter(function ($v) use ($value, &$sum) {
                    $v->chpter_count = Chapter::whereHas("chapterInfo")->with('media:id,path,videoId')
                        ->where('asset_type', '!=', 4)
                        ->where('asset_type', '!=', 7)
                        ->where('parent_id', $v->id)->count();
                    $sum += $v->chpter_count;
                });

                $value->tot_chapterCount = $sum;
            }

        });
        $data->map(function ($value, $key)use($courseId) {

            if ($value->course->type == 2) {
                $course = Course::where("id",$courseId)->first();

                $chapters = Chapter::select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id')->where('asset_type', '!=', 4)->where('asset_type', '!=', 7);

                if (!empty($chapters?->first()) && !empty($chapters?->first())) {
                    $chapterIds = explode(",", $chapters?->first()->chapterIds);
                    $totalDuration = count($chapterIds);

                    $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')
                        ->whereIn('chapter_id', $chapterIds)
                        ->where('learner_id', $value->learner_id)
                        ->where('is_completed', 1)
                        ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                        ->where('chapters.asset_type', '!=', 4)
                        ->where('chapters.asset_type', '!=', 7)
                        ->pluck('watched_times')
                        ->first();

                    $value->totalCompletedDuration = round($totalCompletedChapter);
                    $value->totalProgress = ($totalCompletedChapter > 0 && $totalDuration > 0)
                    ? round(($totalCompletedChapter * 100) / $value->tot_chapterCount)
                    : 0;
                    return $value;

                }

            } else {
                if (!empty($value->course?->chapters?->first()) && !empty($value->course?->chapters?->first())) {
                    $chapterIds = explode(",", $value->course?->chapters?->first()->chapterIds);
                    $totalDuration = count($chapterIds);

                    $totalCompletedChapter = UserCourseProgress::selectRaw('sum(is_completed) as watched_times')
                        ->whereIn('chapter_id', $chapterIds)
                        ->where('learner_id', $value->learner_id)
                        ->where('is_completed', 1)
                        ->join('chapters', 'user_course_progress.chapter_id', '=', 'chapters.id')
                        ->where('chapters.asset_type', '!=', 4)
                        ->where('chapters.asset_type', '!=', 7)
                        ->pluck('watched_times')
                        ->first();

                    $value->totalCompletedDuration = round($totalCompletedChapter);
                    $value->totalProgress = ($totalCompletedChapter > 0 && $totalDuration > 0)
                    ? round(($totalCompletedChapter * 100) / $value->tot_chapterCount)
                    : 0;
                    return $value;

                }

            }


        });

        return view("admin.courses.complete_report", compact("data","courseId"));
    }
}
