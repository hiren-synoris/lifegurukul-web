<?php

namespace App\Http\Controllers\admin;

use App\Models\Media;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\ChapterInfo;
use Illuminate\Http\Request;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChapterInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Chapter::where('id', $id)->first();
        $courseId= $data->course_id;
        $url = route('courses.builder', ['id' => $courseId]);
        Schema::disableForeignKeyConstraints();
        $page = ChapterInfo::where('chapter_id', $id)->delete();
        $chapter = Chapter::where('id', $id)->delete();
        UserCourseProgress::where('chapter_id', $id)->delete();
        Schema::enableForeignKeyConstraints();
        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Record deleted successfully!'
        ], 200);
    }

}
