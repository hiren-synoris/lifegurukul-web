<?php

namespace App\Http\Controllers\front;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\RatingReview;
use App\Models\User;
use App\Models\Learner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Mail\RatingReviewMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class RatingReviewController extends Controller
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        $notification=[];
        $validator = Validator::make($request->all(),[
            'comment' => 'required|filled',
            'course_id' => 'required|filled|exists:courses,id',
            'rating' => 'required|filled'
        ]);
        if($validator->fails()){
            return Redirect::to(URL::previous() . "#reviews")->withErrors($validator)->withInput();
        }
        $data=[
            'comment' => nl2br($request->comment),
            'learner_id' => Auth::guard('learner')->id(),
            'course_id' => $request->course_id,
            'rating' => $request->rating
        ];
        $rating = add_rating_review($data);

        $users = User::role('admin')->select(['id','name','email'])->get();
        $course = Course::findOrFail($request->course_id);

        if (!empty($course)) {
            Course::where('id', $request->course_id)->update(['approval_status' => "1"]);
        }

        $sender = Learner::select(['id', 'name', 'email'])->where('id', Auth::guard('learner')->id())->first();

        $subject = "Review added by " . $sender->name . " for the course: " . $course->title;

        if(isset($users) && !empty($users)){
            foreach($users as $user){
                Mail::to($user->email)->send(new RatingReviewMail($user, $sender, $subject , $rating , $course));
            }
        }

        $course = Course::where("id",$request->course_id)->first()->title;

        // $url = 'https://hooks.zapier.com/hooks/catch/13473230/3vwrv6s/';

        // $url = 'https://hooks.zapier.com/hooks/catch/13473230/2ypk337/';
        // $url = 'https://hooks.zapier.com/hooks/catch/13473230/2ypkc6l/';



        // $zapiarData = [
        //     'name' => Auth::guard('learner')->user()->name,
        //     'comment' => nl2br($request->comment),
        //     'course_name' => $course,
        //     'rating' => $request->rating,
        //     'created_at' => date('Y-m-d H:i:s')
        // ];

        // $zapiarData = [
        //     // 'name' =>"abe ok ",
        //     // 'comment' => "koi nahi",
        //     // 'course_name' => "pluging",
        //     // 'rating' =>4,
        //     // 'created_at' => date('Y-m-d H:i:s')
        // ];


        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($zapiarData));
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //     'Content-Type: application/x-www-form-urlencoded',
        // ));


        // curl_exec($ch);


        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Review added successfully, it will be visible after approval";
        return redirect()->back()->with('notification', $notification);
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
        $reviews = RatingReview::findOrFail($id);
        return $reviews;
        // if (view()->exists('front.course.course_detail')) {

        //     return view('front.course.course_detail', compact('reviews'));
        // }
        // abort(404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $notification=[];
        $rating = RatingReview::where("id",$id)->first();
        if($rating) {
            if($rating->comment != $request->comment || $request->update_rating != $rating->rating) {
                $rating->update([
                    "is_approve" =>0
                ]);
            }
        }

        $data=[
            'comment' => nl2br($request->comment),
            'rating' => $request->update_rating
        ];
        edit_rating_review($data,$id);
        // RatingReview::findOrFail($id)->update($data);
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Review Updated successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification=[];
        RatingReview::findOrFail($id);
        delete_rating_review($id);
        // RatingReview::findOrFail($id)->delete();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Review Deleted successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function load_reviews(Request $request, $id){
        if($request->ajax()){
            if((int)($id) > 0){
                $data=RatingReview::where('id','<',$id)->where('is_approve',true)->latest()->take(5)->with(['learner'])->get();
                return response()->json($data, 200);
            }
            return response()->json(false,200);
        }
    }
}
