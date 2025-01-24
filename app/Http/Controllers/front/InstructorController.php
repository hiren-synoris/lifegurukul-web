<?php

namespace App\Http\Controllers\front;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Instructors;
use App\Models\User;
use App\Models\Wishlist;

class InstructorController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wishlists = [];
        // $instructor = User::with('instructure')->find($id);
        $deviceType = Course::COURSE_WEBSITE;
        $instructor = User::with('instructure')->whereNull('deleted_at')->findOrFail($id);
        $instructorCourse = Course::select('courses.*','cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')

                        //->whereIn('course_platform',[Course::COURSE_ALL,Course::COURSE_WEBSITE])
                        ->whereRaw("find_in_set($deviceType , course_platform)")
                        ->with(['instructor' => function ($query) {
                            $query->select('name', 'email','id','profile_picture');
                        },'categories','wishlists','rating_reviews' => function($query){
                            $query->where('is_approve', true);
                        },'plans'=>function($query){
                            return $query->where('status',1);
                        }])
                        ->with('instructor.instructure')
                        ->withCount(['chapters' => function ($query) {
                            $query->where('parent_id',0);
                        },
                        'rating_reviews as total_review',
                        'packages'])
                        ->withAvg('rating_reviews as AverageRating', 'rating')
                        ->leftJoin('course_plans as cp','cp.id','=','courses.default_web_price')
                        ->where('courses.status','1')
                        ->where('cp.status','1')
                        ->whereNull('courses.deleted_at')
                        ->where('instructor_id',$id)
                        ->where('courses.status',1)
                        ->latest()
                        ->get();

                        $instructorCourse = $instructorCourse->filter(function ($value, $key) {
                            if(count($value->plans) <= 0){
                                return false;
                            }
                            else{
                                return true;
                            }
                        });
                        $totalCount = count($instructorCourse);

                        $instructorCourse = cpaginate($instructorCourse);
                        // dd($instructorCourse);
        $wishlists = [];
        if(isLearnerLoggedIn()){
            $wishlists = Wishlist::where('learner_id', authLearnerID())->whereNull('deleted_at')->get();
        }
        return view('front.instructors.detail', compact('instructor','wishlists','instructorCourse','totalCount'));
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
