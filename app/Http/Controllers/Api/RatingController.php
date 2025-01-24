<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\RatingReview;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $learner = request()->user();

            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'rating' => 'nullable',
                'comment' => 'nullable',
                'id' => 'nullable'
            ]);

            if ($validator->fails()) {
                $data = Helper::apiResonse(0, $validator->messages(), []);
                return response()->json($data, 400);
            }

            $dataToUpdate = [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'updated_by' => $learner->id,
                'is_approve' => 0,

            ];

            // Check if 'id' is provided
            if (!empty($request->id)) {

                RatingReview::where([
                    'id' => $request->id,
                    // 'learner_id' => $learner->id,
                    // 'course_id' => $request->course_id
                ])->update($dataToUpdate);

                $message = "Review updated successfully";
            } else {
                $dataToUpdate['learner_id'] = $learner->id;
                $dataToUpdate['course_id'] = $request->course_id;
                RatingReview::create($dataToUpdate);
                $message = "Review added successfully, it will be visible after approval";

                $course = Course::where("id",$request->course_id)->first()->title;

                // // $url = 'https://hooks.zapier.com/hooks/catch/13473230/3vw1cbd/';
                // $url = 'https://staging.lifegurukul.app/api/zapier-get-Review';


                // $zapiarData = [
                //     'name' => Auth::guard('learner')->user()->name,
                //     'comment' => nl2br($request->comment),
                //     'course_name' => $course,
                //     'rating' => $request->rating,
                //     'created_at' => date('Y-m-d H:i:s')
                // ];



                // $ch = curl_init($url);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($zapiarData));
                // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                //     'Content-Type: application/x-www-form-urlencoded',
                // ));

                // curl_exec($ch);



            }

            $data = Helper::apiResonse(1, $message, []);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            $data = Helper::apiResonse(0, "An error occurred while storing the rating", []);
            return response()->json($data, 500);
        }
    }




    /**d
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
    public function destroy(Request $request)
    {
        $learners = request()->user();
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        try {
            DB::table('rating_reviews')->where('id', $request->id)->delete();
            $data = Helper::apiResonse(1, "Rating deleted successfully", []);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            $data = Helper::apiResonse(0, "An error occurred while deleting the rating", []);
            return response()->json($data, 500);
        }
    }

}
