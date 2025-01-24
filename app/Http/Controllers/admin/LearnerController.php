<?php

namespace App\Http\Controllers\admin;

use Carbon\Carbon;
use App\Helper\Helper;
use App\Models\Cities;
use App\Models\Course;
use App\Models\States;
use App\Models\Learner;
use App\Models\LoginUrl;
use App\Models\UserCoin;
use App\Models\Wishlist;
use App\Models\Countries;
use Illuminate\Http\File;
use App\Mail\EarnCoinMail;
use App\Models\CoursePlan;
use App\Models\LearnerLog;
use App\Models\UserCourse;
use App\Models\DeviceToken;
use App\Models\PublicForum;
use App\Mail\RedeemCoinMail;
use App\Models\Notification;
use App\Models\RatingReview;
use Illuminate\Http\Request;
use App\Imports\ImportCourse;
use App\Models\CoursePackage;
use App\Exports\ExportLearner;
use App\Imports\ImportLearner;
use App\Imports\NewImportUser;
use App\Jobs\UserLogExpertJob;
use App\Models\DropdownOption;
use Illuminate\Validation\Rule;
use App\Models\PublicForumReply;
use Yajra\DataTables\DataTables;
use App\Jobs\ImportExcelErrorJob;
use App\Imports\ImportOnlyLearner;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LearnerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard()->user();
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!$this->user->can('browse_learners')) {
            abort(403);
        }

        // if ($request->has('mobile_no') && !empty($request->mobile_no)) {
        //     $learner_mobile = $request->mobile_no ? $request->mobile_no : "";
        // }

        $pg_header = 'Learners';
        $countries = Countries::select('id', 'name')->distinct()->get();
        $states = States::select('id', 'name')->distinct()->get();
        // dd($states);
        $cities = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        // dd($cities);
        $courses = Course::select('id', 'title')->distinct()->get();
        if (view()->exists('admin.learners.list')) {
            return view('admin.learners.list', compact('pg_header', 'countries', 'states', 'cities', 'courses'));
        }
        abort(404);
    }

    public function searchCity(Request $request)
    {
        //    dd($request->term);
        $city = Cities::whereRaw("concat(name) like '%" . $request->term . "%' ")->paginate(50);
        // $city = Cities:: where('name', 'LIKE', '%'. $request->search_value.'%')->paginate(5);
        $usersArray = [];
        foreach ($city as $cities) {
            $usersArray[] = array(
                "label" => $cities->name,
                "value" => $cities->id,
            );
        }
        return response()->json($usersArray);
        //    return "ok";
    }
    public function learnerEnrollCourse(Request $request)
    {

        $userCourses = UserCourse::whereNot("course_id",0)->where('learner_id', $request->learner_id)
            ->selectRaw('user_courses.*, DATE_FORMAT(created_at, "%d-%m-%Y") as formatted_created_at, DATE_FORMAT(expire_at, "%d-%m-%Y") as formatted_expire_at')
            ->with([
                "userChpater" => function ($q) {
                    $q->select('course_id', \DB::raw('GROUP_CONCAT(id) as chapterIds'))
                        ->groupBy('course_id');
                },
            ])
            ->where('user_courses.order_status', "!=", 2)
            ->whereIn('id', function ($query) use ($request) {
                $query->selectRaw('MAX(id)')
                    ->from('user_courses')
                    ->where('learner_id', $request->learner_id)
                    ->groupBy('course_id');
            })
            ->get();

        $data = [];

        foreach ($userCourses as $userCourse) {
            $actions = '';
            $url = route("learners.courses.delete", ["id" => $userCourse->id]);
            if ($this->user->can('delete_courses')) {
                // $actions .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                $actions .= '<a class="mx-1 text-danger delete_courses" data-leaner_id=' . $userCourse->learner_id . ' title="Delete" type="button"  data-course_id='. $userCourse->course_id.' data-user_id='. $userCourse->id.'  ><i class="fas fa-trash-alt"></i></a>';
            }

            // Reset learner by Course
            $resetUrl = route("reset.learner", ["courseId" => $userCourse->course_id, "learnerId" => $userCourse->learner_id]);
            // if ($userCourse->course && $userCourse->course->type == 1) {
            //     $actions .= '<a class="mx-1 text-primary" title="Reset" type="button" href="' . $resetUrl . '"><i style="font-size:15px" class="fa">&#xf021;</i></a>';
            // }
            if ($userCourse->course && $userCourse->course->type == 1) {
                $actions .= '<a class="mx-1 text-primary reset_learner" title="Reset"  data-course_id=' . $userCourse->course_id . '  data-leaner_id=' . $userCourse->learner_id .' data-userCourse_id=' . $userCourse->id . ' type="button"><i style="font-size:15px" class="fa">&#xf021;</i></a>';
            }

            $formattedExpireAt = $userCourse->formatted_expire_at ? $userCourse->formatted_expire_at : 'Lifetime';

            // Calculate progress
            if ($userCourse->course && $userCourse->course->type == 2) {
                $progress  = "-";
            } else {
                $progress = '0%'; // Default progress
                $chapterIds = collect($userCourse->userChpater)->pluck('chapterIds')->implode(',');
                if (!empty($chapterIds)) {
                    $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $chapterIds))
                        ->where("learner_id", $userCourse->learner_id)
                        ->where("is_completed", 1)
                        ->count();
                    $total_chapters = count(explode(",", $chapterIds));
                    if ($total_chapters > 0) {
                        $progress = round(($user_progress * 100) / $total_chapters) . "%";
                    }
                }
            }

            // if ($userCourse->userChpater) {
            //     $chapterIds = collect($userCourse->userChpater)->pluck('chapterIds')->implode(',');
            //     $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $chapterIds))
            //         ->where("learner_id", $userCourse->learner_id)
            //         ->where("is_completed", 1)
            //         ->count();
            //     $total_chapters = count(explode(",", $chapterIds));
            //     if ($total_chapters > 0) {
            //         $progress = round(($user_progress * 100) / $total_chapters) . "%";
            //     }
            // }

            $data[] = [
                'course_title' => @$userCourse->course->title,
                'formatted_created_at' => @$userCourse->formatted_created_at,
                'formatted_expire_at' => @$formattedExpireAt,
                'progress' => @$progress,
                'actions' =>@$actions,
                'id' => @$userCourse->id,
            ];
        }

        return response()->json(["userCourse" => $data, "learner_id" => $request->learner_id]);
    }
    public function learnerManualEnrollCourse(Request $request)
    {

        $plan = CoursePlan::where("course_id", $request->course_id)->get();
        $filteredPlans = $plan->reject(function ($q) {
            return $q->is_fixed_date == 1 && $q->access_value <= Carbon::now() || $q->status == 0;
        });

        $course = Course::where("id", $request->course_id)->first()->title;

        return response()->json(["plan" => $filteredPlans, "course" => $course]);
    }

    public function learnerManualPlanAssign(Request $request)
    {

        $plan = CoursePlan::with("course:title,id,type")->where("id", $request->plan_id)->first();
        $learner = Learner::where("id", $request->learner_id)->first();

        $usercourse = UserCourse::create([
            "learner_id" => @$request->learner_id,
            "course_id" => @$plan->course_id,
            'plan_id' => @$plan->id,
            'full_name' => @$learner->name,
            'email' => @$learner->email,
            'order_status' => 4,
            'mobile' => @$learner->mobile,
            'price' => @$plan->final_payable_price,
            'country_id' => @$learner->country_id,
            'state_id' => @$learner->state_id,
            'city_id' => @$learner->city_id,
            'created_by' => auth()->user()->id,
            'expire_at' => get_plan_expire($request->plan_id),
        ]);
        $type = @$plan->course->type == 1 ? "Course" : "Package";
        $subject = @$type . " added";
        $content = @$plan->course->title . " has been purchased successfully.";
        $params['email']['user_course_id'] = @$usercourse->id;
        $params['email']['to'] = @$learner->email;
        $params['web']['to'] = @$learner->id;
        $params['push']['to'] = @$learner->id;
        $params['whatsapp']['message'] = @$plan->course->title;
        $params['common'] = [
            'course_id' => @$plan->course->id,
            'learner_id' => @$learner->id,
            'type' => 3,
        ];
        $type = $type . " enrolled succeessfully";
        // $res = send_notification($params, 'payment', ['email', 'push', 'web', ''], $subject, $content);

        return response()->json(["status" => true, "type" => $type]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_learners')) {
            abort(403);
        }

        $pg_header = 'Add Learner';
        if (view()->exists('admin.learners.add')) {
            $data["states"] = States::select('id', 'name')->distinct()->get();
            $occupations = DropdownOption::getDropdownCategories('occupation')->where('status', "1")->get();
            $marital_status = DropdownOption::getDropdownCategories('marital-status')->where('status', "1")->get();
            $educations = DropdownOption::getDropdownCategories('education')->where('status', "1")->get();
            $your_interests = DropdownOption::getDropdownCategories('your-interests')->where('status', "1")->get();

            return view('admin.learners.add', $data, compact('pg_header', 'occupations', 'marital_status', 'educations', 'your_interests'));
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!$this->user->can('add_learners')) {
            abort(403);
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

        foreach ($countryMapping as $mappedId => $ids) {
            if (in_array($request->country_id, $ids)) {
                $request->merge(['country_id' => $mappedId]);
                break;
            } else {
                $request->merge(['country_id' => $request->country_id]);
            }
        }

        $data = $request->all();

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            // 'mobile' => [
            //     'required', 'regex:/^([0-9]*)$/',
            //     Rule::unique('learners', 'mobile')->whereNull('deleted_at'),
            // ],

            'mobile' => [
                'required',
                Rule::unique('learners')->where(function ($query) use ($request, $data) {
                    return $query->where('country_id', $data['country_id'])
                        ->where('mobile', $request->input('mobile'));
                }),
            ],
            'email' => [
                'required', 'filled', 'regex:/(.+)@(.+)\.(.+)/i', 'email',
                // Rule::unique('learners', 'email')->whereNull('deleted_at'),
            ],
            'profile_pic' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
            'country_id' => 'required',
            'state_id' => 'required',
        ], [

            'country_id.required' => 'The country field is required.',
            'state_id.required' => 'The state field is required.',
            "profile_pic.mimes" => "The profile picture must be a file of type: jpeg, png, jpg, svg.",
            // "profile_pic.max"=>"The profile pic must be less than 500KB.",

        ]);
        try {
            $notification = [];

            DB::beginTransaction();
            $your_interests = '';
            if (!empty($request->input('your_interests'))) {
                $your_interests = $request->input('your_interests');
                $your_interests = implode(',', $your_interests);
            }

            $learner = Learner::create([
                'name' => request()->has('name') ? allowWhiteSpace($request->name) : null,
                'email' => request()->has('email') ? $request->email : null,
                'gender' => request()->has('gender') ? $request->gender : null,
                'mobile' => request()->has('mobile') ? $request->mobile : '',
                'd_o_b' => request()->has('d_o_b') && !empty($request->d_o_b) ? @Carbon::createFromFormat('d/m/Y', $request->d_o_b)->format('Y-m-d') : null,
                'country_id' => $data['country_id'] ? $data['country_id'] : null,
                'state_id' => request()->has('state_id') ? $request->state_id : null,
                'city_id' => request()->has('city_id') ? $request->city_id : null,
                'occupation' => request()->has('occupation') ? $request->occupation : null,
                'marital_status' => request()->has('marital_status') ? $request->marital_status : null,
                'education' => request()->has('education') ? $request->education : null,
                'your_interests' => $your_interests ?? null,
            ]);

            if (request()->hasFile('profile_pic')) {
                $file_path = Storage::putFileAs('profile', $request->profile_pic, $learner->id . '_' . $request->profile_pic->getClientOriginalName());
                $learner->update(['profile_pic' => $file_path]);
            }
            if (!empty($learner)) {
                $params['email']['to'] = $learner->email;
                $params['email']['learnerId'] = $learner->id;
                $params['common'] = [
                    'course_id' => null,
                    'learner_id' => $learner->id,
                    'type' => 1,
                ];
                $content = "You have successfully registered";
                $subject = "Welcome " . request()->has('name') . " to Lifegurukul";
                send_notification($params, 'registration', ['email'], $subject, $content);
            }
            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Learner added successfully";
            return redirect('backoffice/learners')->with('notification', $notification);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = 'Something went wrong.';

            return redirect()->back()->with('notification', $notification);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_learners')) {
            abort(403);
        }

        $learner = Learner::select(
            'learners.id',
            'learners.name',
            'learners.email',
            'learners.gender',
            'learners.mobile',
            'learners.country_id',
            'learners.d_o_b',
            'learners.profile_pic',
            'learners.created_at AS created_at',
            'c.name as countryName',
            'states.name as stateName',
            'cities.name as cityName',
            'do_occupation.name as occupation',
            'do_marital_status.name as marital_status',
            'do_education.name as education',
            \DB::raw('GROUP_CONCAT(do_your_interests.name) as your_interests')
        )
        //->leftJoin('countries', 'learners.country_id', 'countries.id')
            ->leftJoin('states', 'learners.state_id', 'states.id')
            ->leftJoin('cities', 'learners.city_id', 'cities.id')
            ->leftJoin('countries as  c', 'learners.country_id', 'c.id')
            ->leftJoin('dropdown_options as do_occupation', 'learners.occupation', 'do_occupation.id')
            ->leftJoin('dropdown_options as do_marital_status', 'learners.marital_status', 'do_marital_status.id')
            ->leftJoin('dropdown_options as do_education', 'learners.education', 'do_education.id')
            ->leftJoin('dropdown_options as do_your_interests', function ($join) {
                $join->whereRaw('FIND_IN_SET(do_your_interests.id, learners.your_interests)');
            })
            ->where('learners.id', $id)
            ->first();

        $user_count = UserCoin::where("learner_id", $id)
            ->where(function ($query) {
                $query->where("type", "!=", "2");
                $query->Where("type", "!=", "4");
            });

        $minus = UserCoin::where("learner_id", $id)
            ->where(function ($query) {
                $query->where("type", "=", 2)
                    ->orWhere("type", "=", 4);
            });

        // if (Auth::user()->roles()->first()->name == "instructor") {
        //     $user_count->whereHas("getCourse", function ($q) {
        //         $q->where("instructor_id", Auth::user()->roles()->first()->id);
        //     });
        //     $minus->whereHas("getCourse", function ($q) {
        //         $q->where("instructor_id", Auth::user()->roles()->first()->id);
        //     });
        // }
        $total = $user_count->sum("coins") - $minus->sum("coins");

        if (view()->exists('admin.learners.view')) {
            return view('admin.learners.view', compact('learner', 'total'));
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_learners')) {
            abort(403);
        }

        $learner = Learner::findOrFail($id);
        $occupations = DropdownOption::getDropdownCategories('occupation')->where('status', "1")->get();
        $marital_status = DropdownOption::getDropdownCategories('marital-status')->where('status', "1")->get();
        $educations = DropdownOption::getDropdownCategories('education')->where('status', "1")->get();
        $your_interests = DropdownOption::getDropdownCategories('your-interests')->where('status', "1")->get();
        $user_interests = explode(',', $learner->your_interests);
        $phonecode = Countries::where("id", $learner->country_id)->first();
        if (view()->exists('admin.learners.edit')) {
            return view('admin.learners.edit', compact('learner', 'occupations', 'marital_status', 'educations', 'your_interests', 'user_interests', 'phonecode'));
        }
        abort(404);
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

        if (!$this->user->can('edit_learners')) {
            abort(403);
        }

        $notification = [];
        $learner = Learner::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            // 'email' => ['required', 'filled', 'regex:/(.+)@(.+)\.(.+)/i', 'email', Rule::unique('learners', 'email')->ignore($id)->whereNull('deleted_at')],
            'email' => ['required', 'filled', 'regex:/(.+)@(.+)\.(.+)/i', 'email'],
            // 'mobile' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', Rule::unique('learners', 'mobile')->ignore($id)->whereNull('deleted_at')],
            'profile_pic' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
            // 'gender' => 'required|in:'.implode(",",Learner::GENDER),
            // 'city_id' =>'required',
            // 'country_id' => 'required',
            // 'state_id' => 'required',
        ], [
            "profile_pic.mimes" => "The profile picture must be a file of type: jpeg, png, jpg, svg.",
            // "profile_pic.max"=>"The profile picture must be less than 500KB."
            // 'd_o_b.required'=>'The date of birth filed is required.',
            // 'country_id.required' => 'The country field is required.',
            // 'state_id.required' => 'The state field is required.',
            // 'city_id.required'=>'The city field is required.',
        ]);

        $file_path = $learner->profile_pic;
        if (request()->hasFile('profile_pic')) {
            if (!empty($learner->profile_pic)) {
                Storage::delete($learner->profile_pic);
            }
            $file_path = Storage::putFileAs('profile', $request->profile_pic, $learner->id . '_' . $request->profile_pic->getClientOriginalName());
        }

        $your_interests = '';
        if (!empty($request->input('your_interests'))) {
            $your_interests = $request->input('your_interests');
            $your_interests = implode(',', $your_interests);
        }

        Learner::where('id', $learner->id)->update([
            'name' => request()->has('name') ? allowWhiteSpace($request->name) : null,
            'email' => request()->has('email') ? $request->email : null,
            'gender' => request()->has('gender') ? $request->gender : null,
            // 'mobile' => request()->has('mobile') ? $request->mobile : '',
            // 'd_o_b' => request()->has('d_o_b') && !empty($request->d_o_b) ? date('Y-m-d', strtotime($request->d_o_b)) : null,
            'd_o_b' => request()->has('d_o_b') && !empty($request->d_o_b) ? Carbon::createFromFormat('d/m/Y', $request->d_o_b)->format('Y-m-d')
            : null,
            // 'country_id' => request()->has('country_id') ? $request->country_id : null,
            'state_id' => request()->has('state_id') ? $request->state_id : null,
            'city_id' => request()->has('city_id') ? $request->city_id : null,
            'occupation' => request()->has('occupation') ? $request->occupation : null,
            'marital_status' => request()->has('marital_status') ? $request->marital_status : null,
            'education' => request()->has('education') ? $request->education : null,
            'your_interests' => $your_interests ?? null,
            'profile_pic' => $file_path,
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner updated successfully";
        return redirect('backoffice/learners')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $notification = [];

        if (!$this->user->can('delete_learners')) {
            abort(403);
        }

        $learner = Learner::where('id', $id)->first();

        $learner->delete();
        $rate = RatingReview::where('learner_id', $id)->get();
        $rate->each->delete(); // permanent delete rating associate with learner_id.
        $userCourse = UserCourse::where('learner_id', $id)->get();
        $userCourse->each->delete();
        $whis = Wishlist::where('learner_id', $id)->get();
        $whis->each->delete();
        $user_progress = UserCourseProgress::where('learner_id', $id)->get(); //permanent delete usercourseprogress
        $user_progress->each->delete();
        $notify = Notification::where('learnerId', $id)->get(); //remove notification associate with learner_id.
        $notify->each->delete();
        $divice = DeviceToken::where('learner_id', $id)->get(); // also remove deviceToken associate with learner_id.
        // Finally, force delete the learner record itself
        $divice->each->delete();
        $coin = UserCoin::where("learner_id", $id)->get();
        $coin->each->delete();

        $coin = UserCoin::where("learner_id", $id)->get();
        $coin->each->delete();

        $public_forum = PublicForum::where("created_by_learner", $id)->get();
        $public_forum->each->delete();

        $public_forum_reply = PublicForumReply::where("reply_by_learner", $id)->get();
        $public_forum_reply->each->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner deleted successfully";
        return redirect('backoffice/learners')->with('notification', $notification);
    }

    public function restore($id)
    {
        if (!$this->user->can('restore_learners')) {
            abort(403);
        }

        $notification = [];

        $learner = Learner::withTrashed()->where('id', $id)->firstOrFail();
        $learner->restore();

        RatingReview::withTrashed()->where('learner_id', $id)->restore();
        Notification::withTrashed()->where('learnerId', $id)->restore();
        DeviceToken::withTrashed()->where('learner_id', $id)->restore();
        $learner->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner restored successfully";

        return redirect('backoffice/learners')->with('notification', $notification);
    }
    public function getLearnerActivityLog(Request $request)
    {

        $query = LearnerLog::with("getCourse:id,title")->where("learner_id", $request->learner_id)->orderByDesc("id");

        $query = $query->get();

        return DataTables::of($query)

            ->editColumn('device_name', function ($row) {
                return $row->device_name == 1 ? "Android" : ($row->device_name == 2 ? "IOS" : $row->device_name . ' (WEB)');
            })
            ->editColumn('course_name', function ($row) {
                return $row->getCourse->title ?? '';
            })
            ->editColumn('description', function ($row) {
                $label = "";
                if ($row->type == 2) {
                    $label = "<span class='badge badge-danger'>Logout</span></h1>";
                } else {
                    $label = "<span class='badge badge-success'>$row->description</span></h1>";
                }
                return $label;
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
            })
            ->rawColumns(['device_name', 'course_name', 'description', 'created_at'])
            ->addIndexColumn()
            ->toJson();
    }

    public function getLearnerHistory(Request $request)
    {

        if (!$this->user->can('edit_courses')) {
            abort(403);
        }

        if (!$request->ajax()) {
            $pg_header = 'Coin History';
            return view("admin.learners.user-coin-history", compact('pg_header'));
        }
        $query = UserCoin::where("learner_id", $request->learner_id)->with("getChapter:id,title")->orderBy("id", "desc");

        // if (Auth::user()->roles()->first()->name == "instructor") {
        //     $query->whereHas('getCourse', function ($q) {
        //         $q->where("instructor_id", Auth::user()->id);
        //     })->with(['getCourse' => function($q) {
        //         $q->select('id', 'title', 'type');
        //     }]);
        // } else {
        $query->with("getCourse:id,title,type")->get();
        // }
        $query = $query->get();

        return DataTables::of($query)

            ->editColumn('course_id', function ($row) {
                return @$row?->getCourse?->title;
            })
            ->editColumn('chapter_id', function ($row) {
                return @$row?->getChapter?->title;
            })
            ->editColumn('comment', function ($row) {
                return @$row?->comment;
            })
            ->editColumn('type', function ($row) {
                if ($row->type == 1) {
                    if ($row->getCourse->type == 1) {
                        return '<span class="badge badge-success">Course Purchased</span></h1>';
                    } else {
                        return '<span class="badge badge-success">Package Purchased</span></h1>';
                    }
                }
                if ($row->type == 2) {
                    return '<span class="badge badge-warning">Redeem</span></h1>';
                }
                if ($row->type == 3) {
                    return '<span class="badge badge-primary">Add by Admin</span></h1>';
                }
                if ($row->type == 4) {
                    return '<span class="badge badge-warning">Deduct by Admin</span></h1>';
                }
                if ($row->type == 5) {
                    return '<span class="badge badge-success">whole video completed</span></h1>';
                }
                if ($row->type == 6) {
                    return '<span class="badge badge-success">profile completed</span></h1>';
                }
                if ($row->type == 7) {
                    return '<span class="badge badge-success">Finishing certain courses</span></h1>';
                }
                if ($row->type == 8) {
                    return '<span class="badge badge-success"> Finishing a course in a certain time</span></h1>';
                }
            })
            ->addColumn('action', function ($row) {
                $url = route("delete_coin_history", $row->id);
                $button = "";
                if ($this->user->can('delete_user_coin')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['name', 'type', 'course_id', 'created_at', "action", "chapter_id"])
            ->addIndexColumn()
            ->toJson();
    }

    public function deleteCoinHistory($id)
    {
        if ($id) {
            UserCoin::where("id", $id)->delete();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "User Coin deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            return redirect()->back();
        }
    }

    public function getLearners(Request $request)
    {

        if (!$this->user->can('browse_learners')) {
            abort(403);
        }

        $user = Auth::user();

        if ($user->hasRole('instructor')) {
            DB::enableQueryLog();
            $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile', 'learners.country_id', 'learners.d_o_b', 'learner_status')
                ->whereHas("user_courses.course", function ($q) use ($user) {
                    $q->where('courses.instructor_id', $user->id);
                });
        } else {
            $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile', 'learners.country_id', 'learners.d_o_b', 'learner_status');
        }

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];
            $query->where(function ($q) use ($searchValue) {
                $q->where('learners.name', 'like', "%$searchValue%")
                    ->orWhere('learners.email', 'like', "%$searchValue%")
                    ->orWhere('learners.mobile', 'like', "%$searchValue%")
                    ->orWhere('learners.id', 'like', "%$searchValue%");
            });
        }

        if ($request->has('mobile_no') && !empty($request->input('mobile_no'))) {
            $query->where('mobile', 'like', "%" . $request->mobile_no . "%");
        }

        if ($request->has('device_count') && !empty($request->device_count)) {
            $deviceCount = $request->device_count;
            $query->withCount(["getDevice" => function ($q) {
                $q->select(DB::raw('count(*)'));
            }])->having('get_device_count', $deviceCount);

            // dd($query->get()->toArray());
        }

        if ($request->has('signup_date') && !empty($request->signup_date)) {
            $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d') . "%");
        }
        if ($request->has('course') && !empty($request->course)) {
            // $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id')
            //       ->whereIn('uc.course_id', $request->course)
            //       ->select('learners.id', 'uc.learner_id', 'uc.plan_id',"uc.course_id")
            //       ->groupBy('uc.course_id')
            //       ->orderBy('learners.id');

            // $course_packages = CoursePackage::whereIn("course_id", $request->course)
            // ->Orwhere("course_id", @$course_package->package_id)
            // ->pluck('package_id');

            // $course_ids = $course_packages->push($request->course);

            //  $query->whereHas("user_courses", function ($q) use ($request,$course_ids) {
            //     $q->whereIn('course_id', $course_ids);
            // });

            $course_package = CoursePackage::whereIn("course_id", $request->course)->first();

            $course_packages = CoursePackage::whereIn("course_id", $request->course)
                ->orWhere("course_id", optional($course_package)->package_id)
                ->pluck('package_id');
            $course_ids = $course_packages->merge($request->course)->unique();

            $query->whereHas('user_courses', function ($q) use ($course_ids) {
                $q->whereIn('course_id', $course_ids);
            });

        }

        if ($request->has('instructor') && !empty($request->instructor)) {

            $query->whereHas("user_courses.course", function ($q) use ($request) {
                $q->where('courses.instructor_id', $request->instructor);
            });
        }
        if ($request->has('email') && !empty($request->email)) {
            $query->where('learners.email', 'like', "%" . $request->email . "%");
        }
        if ($request->has('mobile') && !empty($request->mobile)) {
            $query->where('learners.mobile', 'like', "%" . $request->mobile . "%");
        }
        if ($request->has('city') && !empty($request->city)) {
            $query->where('learners.city_id', 'like', $request->city);
        }
        if ($request->has('state') && !empty($request->state)) {
            $query->where('learners.state_id', 'like', $request->state);
        }
        if ($request->has('country') && !empty($request->country)) {
            $query->where('learners.country_id', 'like', $request->country);
        }
        if ($request->has('occupation') && !empty($request->occupation)) {
            $query->where('learners.occupation', 'like', $request->occupation);
        }
        if ($request->has('marital_status') && !empty($request->marital_status)) {
            $query->where('learners.marital_status', 'like', $request->marital_status);
        }
        if ($request->has('education') && !empty($request->education)) {
            $query->where('learners.education', 'like', $request->education);
        }
        $interests = $request->your_interests;
        if ($request->has('your_interests') && !empty($request->your_interests)) {
            $query->where(function ($q) use ($interests) {
                foreach ($interests as $interest) {
                    $q->orWhere('learners.your_interests', 'like', '%' . $interest . '%');
                }
            });
        }
        // if ($request->has('age') && !empty($request->age)) {
        //     // dd($request->age);
        //     // $request->age = 50;
        //     $birthdate = now()->subYears($request->age);

        //     $query->where('d_o_b', '<=', $birthdate);
        // }

        // if($request->has('age') && !empty($request->age)|| $request->ageTo){
        //     $birthdate1 = now()->subYears($request->age);
        //     $birthdate2 = now()->subYears($request->ageTO);
        //     $query->where('d_o_b', '>=' ,$birthdate1??'');
        //     $query->where('d_o_b', '<=' ,$$birthdate2??'');
        // }

        if ($request->has('age') && !empty($request->age) || $request->has('ageTo') && !empty($request->ageTo)) {
            if ($request->has('age') && !empty($request->age)) {
                $birthdate1 = now()->subYears($request->age);
                $query->where('d_o_b', '<=', $birthdate1);
            }

            if ($request->has('ageTo') && !empty($request->ageTo)) {
                $birthdate2 = now()->subYears($request->ageTo);
                $query->where('d_o_b', '>=', $birthdate2);
            }
        }
        if ($request->has('gender') && !empty($request->gender)) {
            if ($request->gender == "1") {
                $query->where('learners.gender', 1);
            }
            if ($request->gender == "2") {
                $query->where('learners.gender', 2);
            }
            if ($request->gender == "3") {
                $query->where('learners.gender', 3);
            }
        }
        $query->whereNull('learners.deleted_at');
        // dd($query->count());
        if ($request->order == null) {
            $query->orderBy('id', 'desc');
        }
        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $count_record = $query->count();
        $data = $query->skip($start)->take($pageSize);

        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])
        // return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $path = 'backoffice/learners/';
                $url = route("learners.destroy", ["learner" => $row->id]);
                $button = "";

                $status = $row->learner_status ? "checked" : "";
                $button .= '<div class="custom-control custom-switch mx-1">
                <input type="checkbox" class="custom-control-input learner_status_cls" name="learner_status" data-id="' . $row->id . '" id="toggle_learnerSwitch_' . $row->id . '" ' . $status . ' >
                <label class="custom-control-label" for="toggle_learnerSwitch_' . $row->id . '"></label>
                </div>';

                if ($this->user->can('read_learners')) {
                    $button .= '<a class="mx-1" title="View" href="' . url($path . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }

                if ($this->user->can('edit_learners')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url($path . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }

                if ($this->user->can('edit_learners')) {
                    $button .= '<a class="mx-1 logs_learners_get" data-id="' . $row->id . '" data-toggle="modal" data-target="#logs_learner" title="Learner Logs" href=""><i class="fa fa-history" aria-hidden="true"></i>
                    </a>';
                }

                $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile), 'c_id' => $row->country_id]);

                $button .= '<a class="text-olive login_url mx-1 show_text_' . $row->id . '"  data-id="' . $row->id . '" title="Generate Login URL" type="button" href="javascript:void(0)"><i class="fa fa-random" aria-hidden="true"></i>
                </a><span  class="spinner-border text-primary spinner_' . $row->id . '" style="display:none;"></span> <a class="mx-1 text-olive show_url_' . $row->id . '" style="display:none" title="Copy login URL" type="button" href="javascript:void(0)"
                onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
                // return $ganerat_link;

                $button .= "<a href='' class='enroll_course mx-1' data-toggle='modal' title='Manage Course/Package
                ' data-target='#exampleModal' data-id='$row->id'><i class='fa fa-tasks' aria-hidden='true'></i>
                </a>";

                // if ($this->user->can('edit_learners')) {
                //     $button .= '<a class="mx-1 coin_learners_get" data-id="'.$row->id.'" data-toggle="modal" data-target="#coins_learner" title="Learner coins" href=""><i class="fa fa-coins" aria-hidden="true"></i>
                //     </a>';
                // }

                $button .= '<a class="mx-1 loggedDevice"
                                id="Logged-device-list"
                                href="javascript:void(0)"
                                title="Logged Devices"
                                data-toggle="modal"
                                data-url="' . route('get.logged.device', ['id' => $row->id]) . '"
                                data-id="' . $row->id . '" >
                                <i class="nav-icon fas fa-th"></i>
                            </a>';

                if (is_null($row->deleted_at)) {

                    if ($this->user->can('delete_learners')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }

                } else {
                    if ($this->user->can('restore_learners')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path . 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center align-items-center'>$button</div>";
            })
            ->editColumn('profile_pic', function ($row) {
                $profilePictureURL = '';

                !empty($row->profile_pic)
                ? $profilePictureURL = Storage::exists($row->profile_pic) ? Storage::url($row->profile_pic) : ''
                : $profilePictureURL = asset('admin/dist/img/avatar.png');

                return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                // return date('d/m/Y', strtotime($row->created_at));

            })
            ->editColumn('mobile', function ($row) {
                $country_code = Countries::where("id", @$row->country_id)->first();

                return @"+$country_code->phonecode " . @$row->mobile ?? '';

            })
        // ->editColumn('login_url', function ($row) {
        //     // $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile),"c_id"=>$row->country_id]);
        //     // return '<a class="mx-1 text-olive" title="Copy Payment URL" type="button" href="javascript:void(0)"
        //     //  onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
        //     $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile), 'c_id' => $row->country_id]);

        //     $ganerat_link = '<a class="text-olive login_url show_text_' . $row->id . '"  data-id="' . $row->id . '" title="Generate Login URL" type="button" href="javascript:void(0)">Generate URL</a><span  class="spinner-border text-primary spinner_' . $row->id . '" style="display:none;"></span> <a class="mx-1 text-olive show_url_' . $row->id . '" style="display:none" title="Copy login URL" type="button" href="javascript:void(0)"
        //     onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
        //     return $ganerat_link;
        // })
        // ->addColumn('enroll_course', function ($row) {
        //     return "<a href='' class='enroll_course' data-toggle='modal' data-target='#exampleModal' data-id='$row->id'>Manage Course/Package</a>";
        // })
            ->rawColumns(['action', 'profile_pic', 'created_at', "enroll_course", "login_url"])
            ->toJson();
    }

    public function getLearnersDeleted(Request $request)
    {
        if (!$this->user->can('browse_learners')) {
            abort(403);
        }

        // //    $query = DB::table('learners')
        // //    ->select('learners.id','learners.name','learners.email','learners.profile_pic','learners.created_at AS created_at','learners.deleted_at','learners.mobile')
        // //    ->whereNotNull('learners.deleted_at')
        // //    ->groupBy('learners.id')->latest()
        // //    ->select("*");

        // //    if($request->has('signup_date') && !empty($request->signup_date)){
        // //     $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d')."%");
        // //     }
        // //     if($request->has('course') && !empty($request->course)){
        // //         $query->leftJoin('user_courses as uc', 'uc.learner_id','=','learners.id');
        // //         $query->where('uc.course_id',$request->course);
        // //     }
        // //     if($request->has('email') && !empty($request->email)){
        // //         $query->where('learners.email', 'like', $request->email);
        // //     }
        // //     if($request->has('mobile') && !empty($request->mobile)){
        // //         $query->where('learners.mobile', 'like', $request->mobile);
        // //     }
        // //     if($request->has('city') && !empty($request->city)){
        // //         $query->where('learners.city_id', 'like', $request->city);
        // //     }
        // //     if($request->has('state') && !empty($request->state)){
        // //         $query->where('learners.state_id', 'like', $request->state);
        // //     }
        // //     if($request->has('country') && !empty($request->country)){
        // //         $query->where('learners.country_id', 'like', $request->country);
        // //     }
        //     $query->get();
        $query = DB::table('learners')
            ->select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile');

        if ($request->has('signup_date') && !empty($request->signup_date)) {
            $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d') . "%");
        }
        if ($request->has('course') && !empty($request->course)) {
            $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
            $query->where('uc.course_id', $request->course);
        }
        if ($request->has('email') && !empty($request->email)) {
            $query->where('learners.email', 'like', $request->email);
        }
        if ($request->has('mobile') && !empty($request->mobile)) {
            $query->where('learners.mobile', 'like', $request->mobile);
        }
        if ($request->has('city') && !empty($request->city)) {
            $query->where('learners.city_id', 'like', $request->city);
        }
        if ($request->has('age') && !empty($request->age)) {
            $birthdate = now()->subYears($request->age);
            $query->where('learners.d_o_b', '<=', $birthdate);
        }
        if ($request->has('state') && !empty($request->state)) {
            $query->where('learners.state_id', 'like', $request->state);
        }
        if ($request->has('country') && !empty($request->country)) {
            $query->where('learners.country_id', 'like', $request->country);
        }
        $query = $query->whereNotNull('learners.deleted_at')->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $button = "";
                $deleteurl = route("learners.delete", ["id" => $row->id]);
                $button = "";
                $button = "";
                if ($this->user->can('delete_learners')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_learners')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/learners/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('profile_pic', function ($row) {
                $profilePictureURL = '';

                !empty($row->profile_pic)
                ? $profilePictureURL = Storage::exists($row->profile_pic) ? Storage::url($row->profile_pic) : ''
                : $profilePictureURL = asset('admin/dist/img/avatar.png');

                return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('login_url', function ($row) {
                return '';
            })
            ->editColumn('enroll_course', function ($row) {
                return "<a href='' class='enroll_course' data-toggle='modal' data-target='#exampleModal' data-id='$row->id'>View Enroll Course</a>";
            })
            ->rawColumns(['action', 'profile_pic', 'created_at', "enroll_course", "login_url"])
            ->toJson();
    }

    public function restore_all(Request $request)
    {
        $notification = [];
        if (!$this->user->can('restore_learners')) {
            abort(403);
        }

        foreach (array_keys($request->selected_checkbox) as $key => $id) {
            $learner = Learner::withTrashed()->where('id', $id)->firstOrFail();
            $learner->restore();
            RatingReview::withTrashed()->where('learner_id', $id)->restore();
            Notification::withTrashed()->where('learnerId', $id)->restore();
            DeviceToken::withTrashed()->where('learner_id', $id)->restore();
            $learner->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner restored successfully";
        return redirect('backoffice/learners')->with('notification', $notification);
    }

    public function delete($id)
    {

        if (!$this->user->can('delete_learners')) {
            abort(403);
        }

        $notification = [];
        $learner = Learner::withTrashed()->where('id', $id)->firstOrFail();
        RatingReview::where('learner_id', $id)->forceDelete(); // permanent delete rating associate with
        Wishlist::where('learner_id', $id)->forceDelete();
        UserCourse::where('learner_id', $id)->forceDelete(); //permanenet delete UserCourse associate with learner_id.
        UserCourseProgress::where('learner_id', $id)->forceDelete(); //permanent delete usercourseprogress
        Notification::where('learnerId', $id)->forceDelete(); //remove notification associate with learner_id.
        DeviceToken::where('learner_id', $id)->forceDelete(); // also remove deviceToken associate with learner_id.
        // Finally, force delete the learner record itself
        $learner->forceDelete();

        $public_forum = PublicForum::where("created_by_learner", $id)->get();
        $public_forum->each->delete();

        $public_forum_reply = PublicForumReply::where("reply_by_learner", $id)->get();
        $public_forum_reply->each->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Learner deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    // used for first import learner and then assign course - used in course module learner page
    public function import(Request $request, $courseId = 0)
    {
        // dd($request->all());
        if (request()->hasFile('import')) {
            try {
                if (in_array(strtolower($request->file('import')->extension()), ['xls', 'xlsx', 'csv'])) {
                    // echo "<prE>";print_r($request->file('import')->extension());die;
                    $planId = isset($request->planId) && !empty($request->planId) ? $request->planId : "";
                    $importLearner = new ImportLearner($courseId, $planId);
                    // dd($importLearner);
                    Excel::import($importLearner, request()->file('import')->store('temp'));
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "success";
                    $notification['title'] = "Success";
                    $notification['msg'] = "Learner Imported successfully";
                } else {
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "error";
                    $notification['title'] = "Error";
                    $notification['msg'] = "File type should be .xls or .xlsx ";
                }
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

                $data = Excel::toArray($importLearner, request()->file('import'));
                $learners_data = $data['0'];
                //  dd($learners_data);

                $failures = $e->failures();
                // dump($failures);
                $failure_rows = $failure_error = array();
                foreach ($failures as $key => $failure) {
                    // echo "<pre>";print_r($failure);
                    foreach ($failure->errors() as $error) {
                        if (isset($failure->values()[$failure->attribute()])) {
                            // $error = $failure->errors();
                            $value = $failure->values()[$failure->attribute() ?? ''];
                        } else {
                            $value = $failure->values()['0'];
                        }
                    }

                    $failure_error = str_replace($failure->attribute(), '"' . $value . '"', $error);
                    $failure_rows[$failure->row() - 2] = $failure_error;

                    // $failure->row(); // row that went wrong
                    // $failure->attribute(); // either heading key (if using heading row concern) or column index
                    // $failure->errors(); // Actual error messages from Laravel validator
                    // $failure->values(); // The values of the row that has failed.
                    // echo "<pre>";print_r($failure_rows);die;
                }
                // die;
                // echo "<pre>";print_r($learners_data);die;
                return view('admin.learners.import', compact('failures', 'learners_data', 'failure_rows'));
                // foreach ($failures as $failure) {
                //     $failure->row(); // row that went wrong
                //     $failure->attribute(); // either heading key (if using heading row concern) or column index
                //     $failure->errors(); // Actual error messages from Laravel validator
                //     $failure->values(); // The values of the row that has failed.
                // }
            }
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No File Imported";
        }
        return redirect()->back()->with('notification', $notification);
    }

    // used for only import learner from learner module
    public function importlearner(Request $request)
    {
        // dd("");
        if (request()->hasFile('import')) {
            try {
                if (in_array(strtolower($request->file('import')->extension()), ['xls', 'xlsx', 'csv'])) {
                    // echo "<prE>";print_r($request->file('import')->extension());die;
                    $importLearner = new ImportOnlyLearner();
                    Excel::import($importLearner, request()->file('import')->store('temp'));
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "success";
                    $notification['title'] = "Success";
                    $notification['msg'] = "Learner Imported successfully";
                } else {
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "error";
                    $notification['title'] = "Error";
                    $notification['msg'] = "File type should be .xls or .xlsx ";
                }
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

                $data = Excel::toArray($importLearner, request()->file('import'));
                $learners_data = $data['0'];
                $failures = $e->failures();
                // dd($e);
                $failure_rows = $failure_error = array();
                foreach ($failures as $key => $failure) {
                    // echo "<pre>";print_r($failure);
                    foreach ($failure->errors() as $error) {
                        if (isset($failure->values()[$failure->attribute()])) {
                            // $error = $failure->errors();
                            $value = $failure->values()[$failure->attribute() ?? ''];
                        } else {
                            $value = $failure->values()['0'];
                        }
                    }

                    $failure_error = str_replace($failure->attribute(), '"' . $value . '"', $error);
                    $failure_rows[$failure->row() - 2] = $failure_error;

                    // $failure->row(); // row that went wrong
                    // $failure->attribute(); // either heading key (if using heading row concern) or column index
                    // $failure->errors(); // Actual error messages from Laravel validator
                    // $failure->values(); // The values of the row that has failed.
                    // echo "<pre>";print_r($failure_rows);die;
                }
                // die;
                // echo "<pre>";print_r($learners_data);die;
                return view('admin.learners.import', compact('failures', 'learners_data', 'failure_rows'));
                // foreach ($failures as $failure) {
                //     $failure->row(); // row that went wrong
                //     $failure->attribute(); // either heading key (if using heading row concern) or column index
                //     $failure->errors(); // Actual error messages from Laravel validator
                //     $failure->values(); // The values of the row that has failed.
                // }
            }
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No File Imported";
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function exportUsers(Request $request)
    {
        return Excel::download(new ExportLearner, 'learner.xlsx');
    }

    public function learnerPrice(Request $request, $courseId = 0)
    {
        $text = $request->input('text');

        $learner_plans = Learner::where('name', 'Like', "$text")
            ->orWhere('email', 'Like', "$text")
            ->orWhere('mobile', 'Like', "$text")
            ->select('name', 'email', 'mobile')->get();

        return response()->json($learner_plans);
    }

    public function search(Request $request)
    {
        if (!empty(array_filter($request->all()))) {

            $arr = ["signup_date", "course", "email", "mobile", "city", "state"];

            if (!$this->user->can('browse_learners')) {
                abort(403);
            }
            $query = DB::table('learners')
                ->select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile');
            // foreach ($arr as $key => $value) {
            if ($request->has('signup_date') && !empty($request->signup_date)) {
                // $query->where(DB::raw('created_at'), 'like',$request->signup_date);
                $query->where('created_at', 'like', $request->signup_date . "%");
            }
            if ($request->has('course') && !empty($request->course)) {
                $query->leftJoin('user_courses as uc', 'on', 'uc.learner_id', '=', 'learners.id');
                $query->where('uc.course_id', $request->course);
            }
            // }
            $query->whereNull('learners.deleted_at')
                ->orderByDesc('learners.id')->get();
            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    $path = 'backoffice/learners/';
                    $url = route("learners.destroy", ["learner" => $row->id]);
                    $button = "";
                    if ($this->user->can('read_learners')) {
                        $button .= '<a class="mx-1" title="View" href="' . url($path . $row->id) . '"><i class="fas fa-eye"></i></a>';
                    }
                    if ($this->user->can('edit_learners')) {
                        $button .= '<a class="mx-1" title="Edit" href="' . url($path . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                    }
                    if (is_null($row->deleted_at)) {
                        // if(auth()->user()->hasRole('admin')){
                        if ($this->user->can('delete_learners')) {
                            $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        }
                        // }
                    } else {
                        if ($this->user->can('restore_learners')) {
                            $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path . 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                        }
                    }
                    return "<div class='d-flex justify-content-center'>$button</div>";
                })
                ->editColumn('profile_pic', function ($row) {
                    $profilePictureURL = '';

                    !empty($row->profile_pic)
                    ? $profilePictureURL = Storage::exists($row->profile_pic) ? Storage::url($row->profile_pic) : ''
                    : $profilePictureURL = asset('admin/dist/img/avatar.png');

                    return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
                })
                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->rawColumns(['action', 'profile_pic', 'created_at'])
                ->toJson();
        } else {
            return response()->json(false, 200);
        }
    }
    public function getLoggedDevice(Request $request)
    {
        $devices = DB::table('device_tokens')
            ->select('device_tokens.*')
            ->where('learner_id', $request->id)->get();
        $content = view('admin.learners.logged-device-list', compact('devices'))->render();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'content' => $content,
        ], 200);
    }
    public function deviceBlock(Request $request)
    {
        // dd($request->status);
        if ($request->status == "true") {
            $status = 1;
            $content = "Device is unblocked";
        } else {
            $status = 0;
            $content = "Device is blocked";
        }

        DeviceToken::where("id", $request->id)->update(["block_device" => $status]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'content' => $content,
        ], 200);
    }

    public function deviceDelete($id)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $page = DeviceToken::where('id', $id)->firstOrFail();
        $page->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        return response()->json([
            'success' => true,
            'url' => 1,
            'message' => 'Logged Device deleted successfully!',
            'content' => $id,
        ], 200);
    }

    // public function importNew(Request $request,$courseId = 0)
    // {

    //     try {
    //         if(request()->hasFile('import')) {

    //             if(in_array(strtolower($request->file('import')->extension()), ['xls','xlsx','csv'])){

    //                 $planId = isset($request->planId) && !empty($request->planId) ? $request->planId : "";
    //                 $file = $request->file('import')->store('import123');

    //                 $importLearner = new ImportLearner($courseId, $planId);
    //                 $importLearner->import($file);

    //                 // dd($importLearner,"125");

    //                 if ($importLearner->failures()->isNotEmpty()) {
    //                     // dd("");
    //                     $data["failures"] = $importLearner->failures();
    //                     // dd($data["failures"]);
    //                     $inst = new ImportExcelError("import excel error",$data["failures"]);
    //                     $to = auth()->user()->email;
    //                     if(!empty($inst) && !empty($to)){
    //                         Mail::to($to)->send($inst);
    //                     }
    //                     // return view("admin.learners.new-import",$data);
    //                 }

    //                 $notification['type'] = "sweet-alert";
    //                 $notification['status'] = "success";
    //                 $notification['title'] = "Success";
    //                 $notification['msg'] = "Learner Imported successfully";
    //                 return redirect()->back()->with('notification', $notification);
    //             } else{

    //                 $notification['type'] = "sweet-alert";
    //                 $notification['status'] = "error";
    //                 $notification['title'] = "Error";
    //                 $notification['msg'] = "File type should be .xls or .xlsx Or file is empty";
    //                 return redirect()->back()->with('notification', $notification);
    //             }

    //         }else{
    //             $notification['type'] = "sweet-alert";
    //             $notification['status'] = "error";
    //             $notification['title'] = "Error";
    //             $notification['msg'] = "No File Imported";
    //             return redirect()->back()->with('notification', $notification);
    //         }
    //     } catch (\Exception $e) {
    //         $notification['type'] = "sweet-alert";
    //         $notification['status'] = "error";
    //         $notification['title'] = "Error";
    //         $notification['msg'] = "No File Imported";
    //         return redirect()->back()->with('notification', $notification);
    //     }

    // }

    public function importNew1(Request $request, $courseId = 0)
    {

        $maximumenErollLearners = config()->has('settings.maximumenrolllearners') ? config('settings.maximumenrolllearners') : "5000";

        // try {
        if (request()->hasFile('import')) {

            if (in_array(strtolower($request->file('import')->extension()), ['xls', 'xlsx', 'csv'])) {

                $recordsCount = Excel::toArray([], $request->file('import'))[0];

                $filteredData = array_filter($recordsCount, function ($item) {
                    return !empty($item[0]);
                });
                $filteredData = array_values($filteredData);

                if ((count($filteredData) - 1) > $maximumenErollLearners) {
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "error";
                    $notification['title'] = "Error";
                    $notification['msg'] = "The number of records in the file exceeds the limit " . $maximumenErollLearners . ".";
                    return redirect()->back()->with('notification', $notification);
                }

                $filePath = $request->file('import')->store('error_uploads', 'public');
                $fullFilePath = Storage::disk('public')->path($filePath);

                // $import = new ImportLearner($courseId, $request->planId);
                // $rows = Excel::toCollection($import, $fullFilePath)->first();

                // $filteredRows = $rows->filter(function ($row) {
                //     return !empty(array_filter($row->toArray()));
                // });

                // if (!$filteredRows->isEmpty()) {
                //     $filteredFilePath = $filePath;
                //     $filteredFullFilePath = Storage::disk('public')->path($filteredFilePath);

                //     $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                //     $sheet = $spreadsheet->getActiveSheet();

                //     foreach ($filteredRows as $index => $row) {
                //         $rowIndex = $index + 1;
                //         foreach ($row as $colIndex => $cell) {
                //             $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $cell);
                //         }
                //     }

                //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                //     $writer->save($filteredFullFilePath);

                ImportExcelErrorJob::dispatch([
                    "import_file" => $filePath,
                    "course_id" => $courseId,
                    "plan_id" => $request->planId,
                    "email" => auth()->user()->email,
                    "admin_id" => auth()->user()->id,
                ]);

                Storage::delete($fullFilePath);
                // }

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = "Import is in-progress. Will send you email if any record not imported.";
                return redirect()->back()->with('notification', $notification);
            } else {

                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = "File type should be .xls or .xlsx Or file is empty";
                return redirect()->back()->with('notification', $notification);
            }
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No File Imported";
            return redirect()->back()->with('notification', $notification);
        }
        // } catch (\Exception $e) {
        //     $notification['type'] = "sweet-alert";
        //     $notification['status'] = "error";
        //     $notification['title'] = "Error";
        //     $notification['msg'] = "No File Imported";
        //     return redirect()->back()->with('notification', $notification);
        // }

    }

    public function importNew(Request $request, $courseId = 0)
    {

        if (request()->hasFile('import')) {

            if (in_array(strtolower($request->file('import')->extension()), ['xls', 'xlsx', 'csv'])) {

                $maximumenErollLearners = config()->has('settings.maximumenrolllearners') ? config('settings.maximumenrolllearners') : "5000";

                $recordsCount = Excel::toArray([], $request->file('import'))[0];

                // $filteredData = array_filter($recordsCount, function ($item) {
                //     return !empty($item[0]);
                // });
                // $filteredData = array_values($filteredData);

                if ((count($recordsCount)) > $maximumenErollLearners) {
                    $notification['type'] = "sweet-alert";
                    $notification['status'] = "error";
                    $notification['title'] = "Error";
                    $notification['msg'] = "The number of records in the file exceeds the limit " . $maximumenErollLearners . ".";
                    return redirect()->back()->with('notification', $notification);
                }

                $filePath = $request->file('import')->store('error_uploads', 'public', "learner.xlsx");

                ImportExcelErrorJob::dispatch([
                    "import_file" => $filePath,
                    // "course_id" => $courseId,
                    // "plan_id" => $request->planId,
                    "email" => auth()->user()->email,
                    "admin_id" => auth()->user()->id,
                ]);

                // Storage::delete($filePath);

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = "Import is in-progress. Will send you email if any record not imported.";
                return redirect()->back()->with('notification', $notification);
            } else {

                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = "File type should be .xls or .xlsx Or file is empty";
                return redirect()->back()->with('notification', $notification);
            }

        } else {

            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No File Imported";
            return redirect()->back()->with('notification', $notification);
        }

        // $import = new NewImportUser($courseId = 0, $request->plan_id);
        // $import->import($file);

        // if ($import->failures()->isNotEmpty()) {
        //     // return back()->withFailures($import->failures());

        //     $inst = new ImportExcelError("ok",$import->failures());
        //     $to = auth()->user()->email;
        //     if(!empty($inst) && !empty($to)){
        //         Mail::to($to)->send($inst);
        //     }

        // }
        return back()->withStatus('Import in queue, we will send notification after import finished.');
    }

    public function importCourse(Request $request, $course_id = 0)
    {

        // if ($request->file('import')) {
        //     $data   =   file(request()->import);
        //     // Chunking file
        //     $chunks = array_chunk($data, 1000);

        //     $header = [];
        //     $batch  = Bus::batch([])->dispatch();

        //     foreach ($chunks as $key => $chunk) {
        //         $data = array_map('str_getcsv', $chunk);

        //         if ($key === 0) {
        //             $header = $data[0];
        //             unset($data[0]);
        //         }

        //         $batch->add(new SalesCsvProcess($data, $header));
        //     }

        //     return $batch;
        // }

        // return 'please upload file';

        $maximumenErollLearners = config()->has('settings.maximumenrolllearners') ? config('settings.maximumenrolllearners') : "5000";

        $planId = isset($request->planId) && !empty($request->planId) ? $request->planId : "";

        $recordsCount = Excel::toArray([], $request->file('import'))[0];

        // $filteredData = array_filter($recordsCount, function ($item) {
        //     return !empty($item[0]);
        // });
        // $filteredData = array_values($filteredData);

        if ((count($recordsCount)) > $maximumenErollLearners) {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "The number of records in the file exceeds the limit " . $maximumenErollLearners . ".";
            return redirect()->back()->with('notification', $notification);
        }

        $filePath = $request->file('import')->store('error_uploads', 'public');
        // $fullFilePath = Storage::disk('public')->path($filePath);

        $filteredFilePath = $filePath;

        ImportExcelErrorJob::dispatch([
            "import_file" => $filteredFilePath,
            "course_id" => $course_id,
            "plan_id" => $planId,
            "email" => @auth()->user()->email,
            "admin_id" => auth()->user()->id,
        ]);

        // Storage::delete($fullFilePath);
        // }

        // $filePath = $request->file('import')->store('error_uploads');
        // ImportExcelErrorJob::dispatch([
        //     "import_file" => $filePath,
        //     "course_id" => $course_id,
        //     "plan_id" => $planId,
        //     "email" => @auth()->user()->email,
        // ]);
        // dd("");

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Import is in-progress. Will send you email if any record not imported";
        return redirect()->back()->with('notification', $notification);
    }

    public function batch()
    {
        $batchId = request('id');
        return Bus::findBatch($batchId);
    }

    public function batchInProgress()
    {
        $batches = DB::table('job_batches')->where('pending_jobs', '>', 0)->get();
        if (count($batches) > 0) {
            return Bus::findBatch($batches[0]->id);
        }

        return [];
    }

    public function importNew13()
    {
        $data["failures"] = collect([]);
        return view("admin.learners.new-import", $data);
    }

    public function NewImportLearners(Request $request)
    {

        $file = $request->file('import')->store('import123');

        $import = new NewImportUser("1", "2");
        $import->import($file);

        dd($import->failures());
        if ($import->failures()->isNotEmpty()) {
            return back()->withFailures($import->failures());
        }

        return back()->withStatus('Import in queue, we will send notification after import finished.');
    }

    public function bulkHardDelete(Request $request)
    {
        if (!$this->user->can('delete_courses')) {
            abort(403);
        }

        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $learner = Learner::withTrashed()->where('id', $id)->firstOrFail();
                RatingReview::where('learner_id', $id)->forceDelete();
                Wishlist::where('learner_id', $id)->forceDelete();
                UserCourse::where('learner_id', $id)->forceDelete();
                UserCourseProgress::where('learner_id', $id)->forceDelete();
                Notification::where('learnerId', $id)->forceDelete();
                DeviceToken::where('learner_id', $id)->forceDelete();
                $learner->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Learner deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Course selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function LearnerGenerateUrl(Request $request)
    {

        $learner = Learner::where("id", $request->learner_id)->first();
        // dd($learner);
        $rand = rand(400000, 50000);
        // dd($learner->created_at);
        $learner->update([
            "password" => Hash::make($rand),
            "token" => $rand,
            "is_used" => 1,
            "expire_at" => Carbon::parse($learner->updated_at)->addMinutes(8),
        ]);
        // dd($learner->mobile);
        $is_url = LoginUrl::where("mobile", $learner->mobile)->where("country_id", $learner->country_id)->first();

        if (!$is_url) {
            LoginUrl::Create([
                "mobile" => $learner->mobile,
                "country_id" => $learner->country_id,
            ]);
        } else {
            LoginUrl::where("mobile", $learner->mobile)->where("country_id", $learner->country_id)->update([
                "mobile" => $learner->mobile,
                "country_id" => $learner->country_id,
            ]);
        }

        return response()->json(['status' => "success"]);
    }

    public function learnerLogs(request $request)
    {
        if ($request->ajax()) {
            $data = LearnerLog::where("learner_id", $request->learner_id)->with("getLerner:name,id")->orderBy('created_at', 'desc')->get();
            // dd($data);
            return Datatables::of($data)
                ->addIndexColumn()

                ->addColumn('name', function ($row) {
                    return @$row->getLerner->name;
                })
                ->addColumn('device_name', function ($row) {
                    if ($row->device_name == 1) {
                        return "Android";
                    } elseif ($row->device_name == 2) {
                        return "IOS";
                    } else {
                        return $row->device_name . ' (WEB)';
                    }
                })
                ->addColumn('description', function ($row) {
                    return @$row->description;
                })

                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->rawColumns(['action', "name", "created_at", 'description'])
                ->make(true);
        }
    }

    public function getCoin(Request $request)
    {
        // dd("");
        if (!$this->user->can('read_user_coin')) {
            abort(403);
        }

        $query = UserCoin::where("learner_id", $request->learner_id)->with(["getLerner:id,name"]);

        if (Auth::user()->roles()->first()->name == "instructor") {
            $query->whereHas("getCourse", function ($q) {
                $q->where("instructor_id", Auth::user()->roles()->first()->id);
            });
        } else {
            $query->with("getCourse")->get();
        }
        $query = $query->get();

        // dd($query->toArray());

        return DataTables::of($query)

            ->editColumn('name', function ($row) {
                return @$row->getLerner->name;
            })
            ->editColumn('course_id', function ($row) {
                return @$row?->getCourse?->title;
            })
            ->editColumn('type', function ($row) {
                if ($row->type == 1) {
                    return '<span class="badge badge-success">Course Purchased</span></h1>';
                }
                if ($row->type == 2) {
                    return '<span class="badge badge-warning">Redeem</span></h1>';
                }
                if ($row->type == 3) {
                    return '<span class="badge badge-warning">Add by Admin</span></h1>';
                }
            })

            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->addColumn('action', function ($row) {
                $url = route("course_orders.destroy", ["course_order" => $row->id]);
                $button = '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick="delete_confirmation(\'' . $url . '\')"><i class="fas fa-trash-alt"></i></a>';
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->rawColumns(['name', 'type', 'course_id', 'created_at', "action"])
            ->addIndexColumn()
            ->toJson();
    }

    public function getCourseOrders(Request $request)
    {

        if (!$this->user->can('browse_course_orders')) {
            abort(403);
        }

        $user = Auth::user() ?? null;

        $onecoinprice = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : '';
        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;
        $orders = UserCourse::with("learner:name,id", "course:title,id", "getCoupon:code,id", "createdBy:id,name")->where("learner_id", $request->learner_id);

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];
            $orders->where(function ($query) use ($searchValue) {
                $query->whereHas('learner', function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%$searchValue%");
                })
                    ->orWhereHas('course', function ($q) use ($searchValue) {
                        $q->where('title', 'like', "%$searchValue%");
                    })->orWhere('transaction_id', 'like', "%$searchValue%");
                //->orWhere('payment_gateway', 'like', "%$searchValue%");
            });
        }

        if ($user->hasRole('instructor')) {
            $orders->whereHas("course", function ($q) use ($user) {
                $q->where("instructor_id", $user->id);
            });
        }
        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $count_record = $orders->count();
        $data = $orders->skip($start)->take($pageSize);
        // dd($orders->skip($start)->take($pageSize)->get());

        // $order = $orders->get();

        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])

        // ->editColumn('name', function ($row) {
        //     return isset($row->learner) && !empty($row->learner) ? $row->learner->name : '';
        // })
            ->editColumn('title', function ($row) {
                return isset($row->course) && !empty($row->course) ? $row->course->title : '';
            })
        // ->editColumn('text', function ($row)use($igst,$sgst, $cgst) {

        //     $sgst_price = 0.0;
        //     $cgst_price = 0.0;
        //     $igst_price = 0.0;

        //     if ($row->country_id == 1) {
        //         if (strtolower(@$row->stateName->name) == 'gujarat') {

        //             return gstCal($row, $sgst) + gstCal($row, $cgst);

        //         } else {
        //             return gstCal($row, $igst);
        //         }
        //     } else {
        //         return "";
        //     }
        // })
            ->editColumn('status', function ($row) {
                $role_name = Role::where("id", @$row->createdBy->id)->first();
                if ($row->order_status == 1) {
                    return '<span class="badge badge-success">completed</span>';
                } else if ($row->order_status == 2) {
                    return '<span class="badge badge-danger">Failure</span>';
                } else if ($row->order_status == 3) {
                    return '<span class="badge badge-success">Free</span>';
                } else if ($row->order_status == 4) {
                    if ($role_name) {
                        return "<span class='badge badge-success'>" . $role_name?->display_name . " (" . @$row->createdBy->name . ")</span>";
                    } else {
                        return '<span class="badge badge-success">Enroll by admin</span>';
                    }
                } else if ($row->order_status == 5) {
                    return "<span class='badge badge-success'>Zapier</span>";
                }
            })
            ->editColumn('price', function ($row) {
                return isset($row->price) && !empty($row->price) ? $row->price : '';
            })

            ->editColumn('after_deduction_price', function ($row) {
                return isset($row->price) && !empty($row->after_deduction_price) ? $row->after_deduction_price : '';
            })
            ->editColumn('user_coin', function ($row) use ($onecoinprice) {
                return isset($row->user_coin) && !empty($row->user_coin) ? $row->user_coin * $onecoinprice . "(" . $row->user_coin . ' * ' . $onecoinprice . ')' : '';
            })
            ->editColumn('coupon_id', function ($row) {
                return isset($row->getCoupon->code) && !empty($row->getCoupon->code) ? $row->getCoupon->code : '';
            })
            ->editColumn('after_coupon_applied_deduction_price', function ($row) {
                return isset($row->after_coupon_applied_deduction_price) && !empty($row->after_coupon_applied_deduction_price) ? $row->after_coupon_applied_deduction_price : '';
            })
            ->editColumn('payment_gateway', function ($row) {
                // return @$row->payment_gateway == 1 ? "razorpay" : (@$row->payment_gateway == 2 ? "instamojo" : '');
                if ($row->payment_gateway == 1) {
                    return "Razorpay";
                } elseif ($row->payment_gateway == 2) {
                    return "Instamojo";
                } elseif ($row->payment_gateway == 3) {
                    return "In-app purchase";
                } else {
                    return "";

                }
            })
            ->editColumn('transaction_id', function ($row) {
                return isset($row->transaction_id) && !empty($row->transaction_id) ? $row->transaction_id : '';
            })
            ->editColumn('invoice', function ($row) {
                if ($row->invoice) {

                    return "<a target='_blank' download href='" . url("storage/" . $row->invoice) . "' class='btn-style' title='Download Invoice'><i class='fa fa-download' aria-hidden='true'></i>
                    </a>";
                } else {
                    return "";
                }
            })
        // '".getImageIfExists($row->invoice, course_img_default())."'
        // ->editColumn('created_at', function ($row) {
        //     return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
        // })
            ->addColumn('expire_at', function ($row) {
                return !empty($row->expire_at) ? $row->expire_at : '';
            })
            ->addColumn('action', function ($row) {
                $url = route("course_orders.destroy", ["course_order" => $row->id]);
                // $url ='';
                $button = '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick="delete_confirmation(\'' . $url . '\')"><i class="fas fa-trash-alt"></i></a>';
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->rawColumns(['order_id', 'title', 'status', 'amount', 'date', 'action', "invoice", "payment_gateway"])
            ->addIndexColumn()
            ->toJson();
    }

    public function addcoin(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coin' => 'required|numeric|gt:0',
            'comment' => 'required',
        ], [
            //"link"=>"The video field is required.",
            // "link.mimetypes"=>"Only video file are allowed."
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }

        $coin = UserCoin::create([
            "learner_id" => $request->learner_id,
            "coins" => $request->coin,
            "comment" => $request->comment,
            "type" => 3,
        ]);

        if ($coin) {

            $subject = "Congratulations! Success Coins Allocated to Your Account!";
            // $content = "Congratulations, You have got $request->coin coins to successfully.";
            $content = "$request->comment (Added by admin)";

            $plus = UserCoin::where("learner_id", $request->learner_id)
                ->where(function ($query) {
                    $query->where("type", "!=", 2);
                    $query->Where("type", "!=", "4");
                })->sum("coins");

            $deduct = UserCoin::where("learner_id", $request->learner_id)
                ->where(function ($query) {
                    $query->where("type", "=", 2)
                        ->orWhere("type", "=", 4);
                })->sum("coins");

            $total = $plus - $deduct;
            // $user_coin = UserCoin::where("learner_id", $request->learner_id)->where("type", 6)->first();

            $learner = Learner::where("id", $request->learner_id)->first();
            $inst = new EarnCoinMail($content, $subject, $learner, $request->coin, $total);

            if ($learner) {
                \Mail::to($learner->email)->send($inst);
            }
        }

        return response()->json(["status" => 1]);
    }
    public function minusCoin(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coin' => 'required|numeric',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }

        $plus = UserCoin::where("learner_id", $request->learner_id)
            ->where(function ($query) {
                $query->where("type", "!=", 2);
                $query->Where("type", "!=", "4");
            })->sum("coins");

        $deduct = UserCoin::where("learner_id", $request->learner_id)
            ->where(function ($query) {
                $query->where("type", "=", 2)
                    ->orWhere("type", "=", 4);
            })->sum("coins");

        $total = $plus - $deduct;

        if ($request->coin >= $total) {
            return response()->json(["status" => 2, "user_count" => $total]);
        }

        $coin = UserCoin::create([
            "learner_id" => $request->learner_id,
            "coins" => $request->coin,
            "comment" => $request->comment,
            "type" => 4,
        ]);

        if ($coin) {

            $subject = "Congratulations! You've Redeemed Success Coins on Lifegurukul App.";
            $content = "$request->comment (Deducated by Admin)";
            $plus = UserCoin::where("learner_id", $request->learner_id)
                ->where(function ($query) {
                    $query->where("type", "!=", 2);
                    $query->Where("type", "!=", "4");
                })->sum("coins");

            $deduct = UserCoin::where("learner_id", $request->learner_id)
                ->where(function ($query) {
                    $query->where("type", "=", 2)
                        ->orWhere("type", "=", 4);
                })->sum("coins");

            $total = $plus - $deduct;

            //$user_coin = UserCoin::where("learner_id", $request->learner_id)->where("type", 6)->first();

            $learner = Learner::where("id", $request->learner_id)->first();
            $inst = new RedeemCoinMail($content, $subject, $learner, $total, $request->coin);
            if ($learner) {
                \Mail::to($learner->email)->send($inst);
            }
        }

        return response()->json(["status" => 1]);
    }

    public function userActivityLogs(Request $request)
    {

        if ($request->ajax()) {
            if ($request->date_range) {
                $all_date = $request->date_range;
                $all_date_arr = explode(' - ', $all_date);
                $start_date = "";
                if (isset($all_date_arr[0]) && $all_date_arr[0] != '') {
                    $start_date = date('Y-m-01');
                    $start = str_replace('/', '-', $all_date_arr[0]);
                    $start_date = date("Y-m-d", strtotime($start));
                }
                $end_date = "";
                if (isset($all_date_arr[1]) && $all_date_arr[1] != '') {
                    $end_date = date('Y-m-d');
                    $end = str_replace('/', '-', $all_date_arr[1]);
                    $end_date = date("Y-m-d", strtotime($end));
                }
            }

            $query = LearnerLog::query()->with("getLerner:id,name", "getCourse:id,title", "getChapter:id,title");
            if ($request->date_range) {
                $query->whereDate('created_at', '>=', $start_date);
                $query->whereDate('created_at', '<=', $end_date);
            }

            $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
            $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

            $orderColumn = isset($_GET['order'][0]['column']) ? $_GET['order'][0]['column'] : null;
            $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc';

            $columns = [
                0 => 'learner_id',
                1 => 'course_id',
                2 => 'chapter_id',
                5 => 'created_at',
            ];

            if ($orderColumn !== null && isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            } else {
                $query->orderBy('learner_logs.id', 'desc');
            }

            $count_record = $query->count();
            $data = $query->skip($start)->take($pageSize);

            return DataTables::of($data)->with([
                "recordsTotal" => $count_record,
                "recordsFiltered" => $count_record,
            ])
                ->addIndexColumn()
                ->addColumn("learner_id", function ($row) {
                    return isset($row->getLerner) ? $row->getLerner->name : "";
                })
                ->addColumn("course_id", function ($row) {
                    return isset($row->getCourse) ? $row->getCourse->title : "-";
                })
                ->addColumn("chapter_id", function ($row) {
                    return isset($row->getChapter) ? $row->getChapter->title : "-";
                })
                ->addColumn("device_name", function ($row) {
                    return $row->device_name == 1 ? "Android" : ($row->device_name == 2 ? "IOS" : $row->device_name . ' (WEB)');
                })
                ->rawColumns(['learner_id', "course_id", "chapter_id", "device_name"])
                ->make(true);
        }
        $pg_header = "Learner Activity";
        return view("admin.reports.user_activity_logs", compact("pg_header"));
    }

    public function userLogsActivityExport(Request $request)
    {

        $scope = [];
        $params = [];

        $scope[] = "email";
        $subject = "Learner Activity Logs";
        $content = "Hi," . @auth()->user()->name;

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        UserLogExpertJob::dispatch($request->all(), $params, 'export_email', $scope, $subject, $content, $user_id);

        return back()->with("msg", 'Report will be generated shortly. Kindly check your email.');

    }

    public function learnerStatus(Request $request)
    {

        if (!$this->user->can('edit_learners')) {
            abort(403);
        }
        if ($request->status == 1) {
            Learner::where('id', $request->id)->update([
                'learner_status' => 1,
            ]);
        } else {
            Learner::where('id', $request->id)->update([
                'learner_status' => 0,
            ]);
        }

        return response()->json(true, 200);
    }

}
