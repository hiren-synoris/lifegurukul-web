<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\ContactEmail;
use App\Models\Cities;
use App\Models\Contact;
use App\Models\Countries;
use App\Models\Learner;
use App\Models\Notification;
use App\Models\States;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
    public function countryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $query = Countries::whereNull('countries.deleted_at');
        if (isset($request->country_id) && !empty($request->country_id)) {
            $query = $query->where('countries.id', $request->country_id);
        }
        $countryDetailData = $query->orderBy('countries.name')->get(['id', 'code', 'name', 'phonecode', 'flag']);
        $total_cnt = $countryDetailData->count();
        if ($total_cnt > 0) {
            foreach ($countryDetailData as $countryDetail) {
                // echo "<pre>";print_r($blog);die;
                $countryDetail->flag = !empty($countryDetail->flag) ? Helper::getImageUrl($countryDetail->flag) : '';
            }
            $data = Helper::apiResonse(1, "Success", $countryDetailData);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
    public function stateList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $countryMapping = [
            231 => [4, 231, 232, 38], // US // 1 phonecode
            8 => [8, 46, 96, 162], // Antarctica // 672 phonecode
            13 => [13, 45], // Australia // 61  phonecode
            29 => [29, 164, 209], // Bouvet Island // 47  phonecode
            48 => [48, 141], // Comoros / 269  phonecode
            49 => [49, 50], // Congo // 242  phonecode
            71 => [71, 203], // Falkland Islands // 500  phonecode
            78 => [78, 179], // French Southern Territories // 262  phonecode
            107 => [107, 236], // Italy // 39  phonecode
            148 => [148, 242], // Morocco / 212  phonecode
            157 => [157, 174], // New Zealand // 64  phonecode
        ];
        $country_ids = [];
        foreach ($countryMapping as $mappedId => $ids) {
            if (in_array($request->country_id, $ids)) {
                $request->merge(['country_id' => $mappedId]);
                $country_ids =  $ids;
                break;
            }
        }

        $query = States::select('states.id', 'states.name', 'states.country_id')
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->whereNull('states.deleted_at')
            ->whereNull('countries.deleted_at');


        if (isset($country_ids) && !empty($country_ids)) {
            $query = $query->wherein('country_id', $country_ids);
        } else {
            $query = $query->where('country_id', $request->country_id);
        }
        // if(isset($request->state_id) && !empty($request->state_id)){
        //     $query = $query->where('states.id', $request->state_id);
        // }
        $stateDetailData = $query->orderBy('states.name')->get();
        $total_cnt = $stateDetailData->count();
        if ($total_cnt > 0) {
            $data = Helper::apiResonse(1, "Success", $stateDetailData);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
    public function cityList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'nullable|exists:states,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $query = Cities::select('cities.id', 'cities.name', 'cities.state_id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->whereNull('states.deleted_at')
            ->whereNull('cities.deleted_at');
        // if(isset($request->country_id) && !empty($request->country_id)){
        //     $query = $query->where('country_id', $request->country_id);
        // }
        if (isset($request->state_id) && !empty($request->state_id)) {
            $query = $query->where('state_id', $request->state_id);
        }
        $cityDetailData = $query->orderBy('cities.name')->get();
        $total_cnt = $cityDetailData->count();
        if ($total_cnt > 0) {
            $data = Helper::apiResonse(1, "Success", $cityDetailData);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }
    public function getNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'nullable',
            'per_page' => 'nullable',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        // for($i=0;$i<10;$i++) {

        //     $ch = curl_init();

        //         curl_setopt($ch, CURLOPT_URL, 'https://jsonplaceholder.typicode.com/comments');
        //         curl_setopt($ch, CURLOPT_POST, false);
        //         // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //         // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         // curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        //         // curl_setopt($ch, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)

        //         // $response = curl_exec($ch);
        //         // if (curl_errno($ch)) {
        //         //     $error_msg = curl_error($ch);
        //         // }
        //         // $res = json_decode($response);
        //         // dd($res);
        // }

        $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        $learner_id = $request->user_id;

        $notificationData = Notification::select('id', 'title', 'text', 'image', 'created_at', 'isRead', 'courseId', 'type', 'external_link')
            ->with([
                'course' => function ($query) {
                    return $query->select(['id', 'type']);
                }, 'course.userCourse' => function ($query) use ($learner_id) {
                    return $query->where('learner_id', $learner_id);
                },
            ])
            ->where('learnerId', $request->user_id)
            ->orderBy('created_at', 'DESC')
            ->get();
        // dd($notificationData);
        $count = Notification::where('isRead', 0)->where('learnerId', $learner_id)->get();
        $total_unread_count = $count->count();
        $notificationData = cpaginate($notificationData, $per_page, $page_num);
        $collection = $notificationData->getCollection();
        $total_cnt = $notificationData->count();
        // $cnt = $notificationData->count();
        // dd($cnt);
        $notificationData->setCollection($collection);
        if ($total_cnt > 0) {
            // dd($notificationData);
            foreach ($notificationData as $notification_data) {
                $notification_data->created_at_new = $notification_data->created_at->format('Y-m-d H:i:s');
                $notification_data->type = $notification_data->type;
                $notification_data->isCombineCourse = (isset($notification_data->course->type) && $notification_data->course->type == '1') ? false : true;
                $notification_data->isRead = (!empty($notification_data->isRead) && $notification_data->isRead == 1) ? true : false;
                $course_purchase_id = 0;
                if (isset($notification_data['course']['userCourse']['id']) && !empty($notification_data['course']['userCourse']['id'])) {
                    $course_purchase_id = $notification_data['course']['userCourse']['id'];
                }
                $notification_data->course_purchase_id = $course_purchase_id;
                $notification_data->total_unread_count = $total_unread_count;
                $notification_data->created_new_at = \Carbon\Carbon::parse($notification_data->created_at)->format('y-m-d H:i:s');

                // $expireFlag = is_expired($notification_data?->course?->userCourse?->where([["learner_id",auth()->user()->id],["course_id",$notification_data->courseId]])->first()->expire_at ?? '');
                // $notification_data->expire_at = $notification_data?->course?->userCourse?->where([["learner_id",auth()->user()->id],["course_id",$notification_data->courseId]])->first()->expire_at ?? '';

                // $notification_data->is_expire = false;

                // if ($expireFlag == 0) {
                //     $notification_data->is_expire = true;

                // } else if ($expireFlag == 1) {
                //     $notification_data->is_expire = false;

                // } else if ($expireFlag == 2) {
                //     $notification_data->is_expire = false;

                // }

                unset($notification_data->course);
            }

            $data = Helper::apiResonse(1, "Success", $notificationData, true);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }

        // if ((isset($request->learner_email) && !empty($request->learner_email)) || (isset($request->user_id) && !empty($request->user_id))) {
        //     $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
        //     $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5;
        //     $notificationData = Notification::select('id', 'title', 'text', 'image', 'created_at', 'readby_learner_ids')->where('learnerId', $request->user_id)->orWhere('learner_ids', $request->email)->orderBy('id', 'DESC')->get();
        //     $notificationData = cpaginate($notificationData, $per_page, $page_num);
        //     $total_cnt = $notificationData->count();
        //     if ($total_cnt > 0) {
        //         foreach ($notificationData as $notification_data) {
        //             // echo "<pre>";print_r($blog);die;
        //             $notification_data->image = !empty($notification_data->image) ? Helper::getImageUrl($notification_data->image) : '';
        //             $notification_data->isRead = (!empty($notification_data->readby_learner_ids)  && $notification_data->readby_learner_ids == $request->user_id) ? True : False;
        //             unset($notification_data->readby_learner_ids);
        //         }
        //         $data = Helper::apiResonse(1, "Success", $notificationData);
        //         return response()->json($data, 200);
        //     } else {
        //         $data = Helper::apiResonse(0, "No records found.", []);
        //         return response()->json($data, 200);
        //     }
        // } else {
        //     $data = Helper::apiResonse(0, "No records found.", []);
        //     return response()->json($data, 200);
        // }
    }
    public function getNotificationDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        $notification_id = isset($request->notification_id) && !empty($request->notification_id) ? $request->notification_id : '';

        $notificationData = Notification::select('id', 'title', 'text', 'image', 'created_at', 'isRead', 'courseId')
            ->where('learnerId', $request->user_id)
            ->where('id', $notification_id)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!empty($notificationData) && isset($notificationData)) {
            // update Read by here
            $res = $notificationData->update([
                'isRead' => 1,
            ]);

            $notificationData->isRead = ($res) ? true : false;
            unset($notificationData->image);
            $data = Helper::apiResonse(1, "Success", $notificationData);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "No records found.", []);
            return response()->json($data, 200);
        }
    }

    public function updateNotificationRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        if ($request->id == 0) {
            $user = $request->user_id;
            $notify = Notification::where("learnerId", $user)->update(["isRead" => 1]);
            $data = Helper::apiResonse(1, "Successfully updated");
            return response()->json($data, 200);
        }

        if ($request->has('id') && !empty($request->id)) {
            $updateNotification = Notification::select('id', 'isRead')->where('id', $request->id)->first();

            if (isset($updateNotification->id)) {
                $updateQuery = Notification::where('id', $request->id)->update([
                    'isRead' => 1,
                ]);
            }
            $data = Helper::apiResonse(1, "Successfully updated");
            return response()->json($data, 200);
        }
    }
    public function add_contact_us(Request $request)
    {
        // dd($request->all());
        $learner_id = request()->user_id;

        $validator = Validator::make($request->all(), [
            // 'email'=>['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('contact_us', 'email')],
            // 'mobile'=>['required','filled','regex:/^([0-9]*)$/','min:10',Rule::unique('contact_us', 'mobile')],
            'email' => ['required', 'filled', 'regex:/(.+)@(.+)\.(.+)/i'],
            'mobile' => ['required', 'filled', 'regex:/^([0-9]*)$/', 'min:10'],
            'description' => ['required', 'filled'],
            'learner_id' => 'nullable|string',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required', 'filled', 'min:1', 'max:3'],
            'devicetoken' => 'required|filled',
        ]);

        if ($validator_header_data->fails()) {
            $data = Helper::apiResonse(0, $validator_header_data->messages(), []);
            return response()->json($data, 400);
        }
        if (isset($request->learner_id) && !empty($request->learner_id)) {
            $learner_id = $request->user_id;
        } else {
            if ($request->scope == 'learner') {
                $learner_id = $request->user_id;
            } else {
                $learner_id = '';
            }
        }
        try {

            // $currentDateTime = Carbon::now();
            // $countryCode = DB::table("countries")->where("phonecode",$request->countryCode)->first();
            // $contact_check_24hour = Contact::where("mobile", $request->mobile)
            // // ->where("country_id", $countryCode->id)
            // ->where("created_at", '>=', now()->subDay())
            // ->first();

            // if ($contact_check_24hour) {
            //     $data = Helper::apiResonse(1, "Thank you, your query is submitted, team will contact you soon",[]);
            //     return response()->json($data, 200);
            // }

            $contact = Contact::firstOrCreate(
                [
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'description' => $request->description,
                    'name' => $request->name,
                ],
                [
                    'learner_id' => $learner_id ?? '',
                ]
            );
            if ($contact->count() > 0) {
                Mail::to($request->email)->send(new ContactEmail($contact));
            }
            $data = Helper::apiResonse(1, "Thank you, your query is submitted, team will contact you soon", []);
            return response()->json($data, 200);
        } catch (Exception $e) {
            $data = Helper::apiResonse(1, "Some Error occured", []);
            return response()->json($data, 200);
        }
    }

    public function fetchProfile(Request $request)
    {

        $learner_id = $request->user_id;

        $learner_details = Learner::with("newsLetter")->where('id', $learner_id)->first();

        if ($learner_details) {

            $learner_details->profile_pic = !empty($learner_details->profile_pic) ? Helper::getImageUrl($learner_details->profile_pic) : '';
            if (isset($learner_details->newsLetter)) {
                $learner_details->isSubscribeNewsletter = true;
            } else {
                $learner_details->isSubscribeNewsletter = false;
            }
            unset($learner_details->newsLetter);
            $data = Helper::apiResonse(1, "Success", $learner_details);
            return response()->json($data, 200);

        } else {
            $data = Helper::apiResonse(0, "No Records found", []);
            return response()->json($data, 200);
        }
    }
}
