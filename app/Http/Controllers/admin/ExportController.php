<?php

namespace App\Http\Controllers\admin;

use App\Models\Course;
use App\Jobs\SendLearner;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use App\Exports\Enrollearner;
use App\Exports\CompleteReport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function learnerExport(Request $request) {

        // return Excel::download(new Enrollearner($request), 'learner-report.xlsx');
      // SendLearner::dispatch($request->all())->onQueue('high');
        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Sales Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"sales_report",$user_id);
        // ->onQueue('high');

        return back()->with("msg",'Sales report will be generated shortly. Kindly check your email.');

    }

    public function courseExport(Request $request)
    {


        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Courses Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"course_export",$user_id);
        // ->onQueue('high');

        return back()->with("msg",'Report will be generated shortly. Kindly check your email.');
    }

    public function purchaseExport(Request $request)
    {
        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Purchase vs course complete Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"purchase_report",$user_id);
        // ->onQueue('high');

        return back()->with("msg",'Report will be generated shortly. Kindly check your email.');
    }


    public function loginLearnerExport(Request $request) {

        // return Excel::download(new Enrollearner($request), 'learner-report.xlsx');
      // SendLearner::dispatch($request->all())->onQueue('high');
        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Login learner Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"login-learner",$user_id);
        // ->onQueue('high');

        return back()->with("msg",'Login learner report will be generated shortly. Kindly check your email.');

    }


    public function learnerCompleteReport(Request $request) {


        $course_id = $request->course_id;
        // SendLearner::dispatch($request->all())->onQueue('high');

        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Course Usage Report";
        $content="Hi,".@auth()->user()->name;

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;
        $course_name = UserCourse::where("course_id",$request->course_id)->first();
        if($course_name){
            SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"complete_report",$user_id);
            //->onQueue('high');
            return back()->with("msg",'Usage report will be generated shortly. Kindly check your email.');
        } else {
            return back()->with("msg_error",'Not data found.');

        }


    }

    public function salesPackageLearnerReport(Request $request) {

        $course_id = $request->course_id;

        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Package Report";
        $content="Hi,".@auth()->user()->name;

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;
        $course_name = UserCourse::where("course_id",$request->course_id)->first();
        if($course_name){
            SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"sales_learner_package_report",$user_id);

            return back()->with("msg",'Package purchase report will be generated shortly. Kindly check your email.');
        } else {
            return back()->with("msg_error",'Not data found.');

        }


    }

    public function userSignupExport(Request $request) {
        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="User Signup Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendLearner::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"user-signup",$user_id);

        return back()->with("msg",'User Signup report will be generated shortly. Kindly check your email.');

    }
}
