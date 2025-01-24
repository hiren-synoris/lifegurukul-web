<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\DropdownOption;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $categories = DropdownOption::whereHas('dropdown', function($query){
        //                                 $query->where('slug','course_category');
        //                             })
        //                             ->select('id','image','name')
        //                             ->withCount('courses')
        //                             ->where('status',true)
        //                             ->get();
        $deviceType = Course::COURSE_WEBSITE;
        $categories = DropdownOption::getDropdownCategories('course_category')
            ->select('dropdown_options.id', 'dropdown_options.image', 'dropdown_options.name', 'dropdown_options.home', DB::raw('COUNT(c.id) as total_course'))
            // ->with(['courses']['courses.courseCategory'])
            // ->with(['courses' => function ($query) {
            //     $query->leftJoin('course_plans as cp','cp.id','=','default_web_price');
            //     $query->where('courses.status', '1');
            //     $query->where('cp.status','1');
            //     // $query->whereNotNull('courses.default_web_price');
            //     // $query->whereNull('courses.deleted_at');
            //     // $query->groupBy('courses.id');
            //     return $query;
            // }])
            ->leftJoin('course_categories as cc','cc.category_id','=','dropdown_options.id')
            ->leftJoin('courses as c','c.id','cc.course_id')
            ->leftJoin('course_plans as cp','cp.id','=','c.default_web_price')
            ->whereNotNull('c.default_web_price')
            ->whereNull('c.deleted_at')
            ->where('c.status','1')
            ->where('cp.status','1')
            ->where('dropdown_options.status', true)
            ->where('dropdown_options.home', true)
            ->groupBy('dropdown_options.id')
            ->whereRaw("find_in_set($deviceType , course_platform)")
            ->orderBy("id","desc")
            ->get();
            // $categories = $categories->filter(function ($value, $key) {
            //     if(count($value->plans) > 0){
            //         return true;
            //     }
            // });

        if(view()->exists('front.categories.list')){
            return view('front.categories.list', compact('categories'));
        } abort(404);
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }
}
