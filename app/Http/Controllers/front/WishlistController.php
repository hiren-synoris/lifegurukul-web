<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index(){
        $courses = Course::select('courses.*','cp.id as planId', 'cp.course_id', 'cp.plan_type', 'cp.plan_name', 'cp.list_price', 'cp.final_payable_price')
        ->join('wishlists', 'wishlists.course_id', '=', 'courses.id')
        ->with(['chapters'=>function($query){
            $query->where('parent_id',0);
        },'categories','instructor','wishlists', 'plans'=>function($query){
            return $query->where('status',1)->orderBy('order','Asc');
        },'packages','instructor.instructure'])
        ->withCount(['chapters' => function ($query) {
            $query->where('parent_id',0);
          },
          'rating_reviews as total_review'])
          ->withAvg('rating_reviews as AverageRating', 'rating')
          ->leftJoin('course_plans as cp','cp.id','=','courses.default_web_price')
          ->where('courses.status','1')
          ->where('cp.status','1')
          ->orderBy("wishlists.id","desc")
          ->where('wishlists.learner_id', Auth::guard('learner')->id())
          ->whereNull('wishlists.deleted_at')->get();
          $courses = cpaginate($courses->filter(function ($value, $key) {
            if(count($value->plans) <= 0){
                return false;
            }
            else{
                return true;
            }
        }));

        $wishlist = Wishlist::where('learner_id', Auth::guard('learner')->id())->whereIn('course_id',$courses->pluck('id'))->count();
        if(view()->exists('front.wishlist.list')){
            return view('front.wishlist.list', compact('courses', 'wishlist'));
        } abort(404);
    }

    public function store(Request $request){
        $notification = [];
        $deleteFlag = 0;
        if (Wishlist::where('learner_id', Auth::guard('learner')->id())->where('course_id', $request->course_id)->exists()) {
            $deleteFlag = 1;
            $wishlist = Wishlist::where('learner_id', Auth::guard('learner')->id())->where('course_id', $request->course_id)->delete();
        } else {
            $deleteFlag = 0;
            Wishlist::Create([
                'learner_id' => Auth::guard('learner')->id(),
                'course_id' => $request->course_id,
                'created_by' => Auth::guard('learner')->id()
            ]);
        }
        $msg = $deleteFlag == 0 ? "added" : "removed";

        return response()->json([
            'status' => 'success',
            'type' => 'sweet-alert',
            'title' => 'Success',
             'msg' => "Wishlist $msg successfully"
        ]);



        // $notification['type'] = "sweet-alert";
        // $notification['status'] = "success";
        // $notification['title'] = "Success";
        // $msg = $deleteFlag == 0 ? "added" : "removed";
        // $notification['msg'] = "Wishlist $msg successfully!";
        // return redirect()->back()->with('notification', $notification);

        // if($request->has('user_id') && $request->has('course_id') && !empty($request->has('user_id')) && !empty($request->has('course_id')) && $request->has('user_id') != "" && $request->has('course_id') != ""){
        //     if($request->has('wishlist_id') && !empty($request->wishlist_id))
        //     {
        //         $y = Wishlist::withTrashed()->where("id",$request->wishlist_id)->get()->toArray();
        //         if(!empty($y)) {

        //             $x=$y->toArray();

        //             if(!empty($x) && count($x) > 0){

        //                 if($request->has('wishlist_active') && $y->trashed() ){
        //                     $y->restore();
        //                     $deleteFlag = 0;
        //                 }
        //                 else{
        //                     Wishlist::destroy($request->wishlist_id);
        //                     $deleteFlag = 1;
        //                 }

        //             }
        //         }
        //     }
        //     else{
        //         Wishlist::Create([
        //             'learner_id' => $request->user_id,
        //             'course_id' => $request->course_id,
        //             'created_by' => Auth::guard('learner')->id()
        //         ]);
        //     }
        // }


        // $notification['type'] = "sweet-alert";
        // $notification['status'] = "success";
        // $notification['title'] = "Success";
        // if($deleteFlag  == 1 ){
        //     $notification['msg'] = "Wishlist remove succesfully";

        // }else{
        //     $notification['msg'] = "Wishlist added succesfully";
        // }
        // return redirect()->back()->with('notification', $notification);
    }
    public function destroy(Request $request, $wishlist){
        $notification = [];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $slider= Wishlist::where('id', $wishlist)->firstOrFail();
            $slider->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Removed from wishlist successfully";

        return redirect()->back()->with('notification', $notification);
    }
}
