<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\AllCourseProgressJob;
use App\Models\Cities;
use App\Models\Countries;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\DropdownOption;
use App\Models\Instructors;
use App\Models\Learner;
use App\Models\States;
use App\Models\UserCourse;
use App\Models\UserCourseProgress;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class ReportController extends Controller
{
    public function learnerReport(Request $request)
    {

        //dd($request->all());
        if ($request->ajax()) {

            $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
            $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

            $query = UserCourse::with("learner:name,id,name,country_id,mobile", "course:title,id", "getCoupon:code,id", "learnerLastLogin", "countryName");
            // ->whereIn('id', function ($query) use ($request) {
            //     $query->selectRaw('MAX(id)')
            //         ->from('user_courses')
            //         ->groupBy('course_id',"learner_id");
            // });

            if ($_GET['order'][0]["column"] != 0) {
                $columnIndex = $_GET['order'][0]['column'];
                $columnName = $_GET['columns'][$columnIndex]['data'];
                $columnSortOrder = $_GET['order'][0]['dir'];

                $query->orderBy($columnName, $columnSortOrder);
            } else {
                // Default order
                $query->orderBy("id", "desc");
            }
            $all_date = $request->signup_date;

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

            // if($request->has('signup_date') && !empty($request->signup_date)){
            //     $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d')."%");
            // }
            $query->when(
                $start_date && $end_date,
                function ($orders) use ($start_date, $end_date) {

                    $orders->whereDate('user_courses.created_at', '>=', $start_date);
                    $orders->whereDate('user_courses.created_at', '<=', $end_date);
                }
            );
            if ($request->has('course') && !empty($request->course)) {
                // $query->with(["course",function($q){

                // $query->where('course_id', $request->course);
                // }]);

                $course_package = CoursePackage::whereIn("course_id", $request->course)->first();

                $course_packages = CoursePackage::whereIn("course_id", $request->course)
                    ->orWhere("course_id", optional($course_package)->package_id)
                    ->pluck('package_id');
                $course_ids = $course_packages->merge($request->course)->unique();
                // $query->whereHas('user_courses', function ($q) use ($course_ids) {
                //     $q->whereIn('course_id', $course_ids);
                // });
                $query->whereIn('course_id', $course_ids);
            }
            if ($request->has('price') && !empty($request->price) || $request->priceTo) {

                $query->where('price', '>=', $request->price ?? '');
                $query->where('price', '<=', $request->priceTo ?? '');
            }
            if ($request->has('instructor') && !empty($request->instructor)) {
                $query->whereHas("course", function ($q) use ($request) {
                    $q->where('courses.instructor_id', $request->instructor);
                });
            }

            if ($request->has('email') && !empty($request->email)) {
                $query->where('email', 'like', "%" . $request->email . "%");
            }
            if ($request->has('mobile') && !empty($request->mobile)) {
                $query->where('mobile', 'like', "%" . $request->mobile . "%");
            }
            if ($request->has('payment_status') && !empty($request->payment_status)) {
                $query->where('order_status', $request->payment_status);
            }

            if ($request->has('city') && !empty($request->city)) {
                // $query->where('city_id',  $request->city);
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('city_id', $request->city);
                });
            }
            if ($request->has('state') && !empty($request->state)) {
                // $query->where('state_id',  $request->state);
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('state_id', $request->state);
                });
            }
            if ($request->has('age') && !empty($request->age)) {
                $birthdate = now()->subYears($request->age);
                $query->whereHas("learner", function ($q) use ($birthdate) {
                    $q->where('d_o_b', '<=', $birthdate);
                });
            }

            // if ($request->has('age') && !empty($request->age) || $request->ageTo) {

            //     $birthdate = now()->subYears($request->age);
            //     $birthdateTo = now()->subYears($request->ageTo);
            //     $query->whereHas("learner", function ($q) use ($birthdate,$birthdateTo) {
            //         $q->where('d_o_b', '>=',$birthdate ?? '');
            //         $q->where('d_o_b', '<=', $birthdateTo ?? '');
            //     });
            // }

            if ($request->has('country') && !empty($request->country)) {
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('country_id', $request->country);
                });
            }
            if ($request->has('login_in') && !empty($request->login_in)) {
                $query->whereHas("learnerLastLogin", function ($q) use ($request) {
                    $q->where('type', 1);
                });
            }

            if ($request->has('gender') && !empty($request->gender)) {
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('gender', $request->gender);
                });
            }

            $count_record = $query->count();
            $data = $query->skip($start)->take($pageSize);

            $igst = config()->has('settings.igst') ? config('settings.igst') : null;
            $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
            $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;
            // $query = $query->get();

            $coin_price = config()->has('settings.onecoinprice	') ? config('settings.onecoinprice	') : 0;
            return DataTables::of($data)->with([
                "recordsTotal" => $count_record,
                "recordsFiltered" => $count_record,
            ])
                ->addIndexColumn()
                ->addColumn('learner_id', function ($row) {
                    return $row->learner->name ?? '';
                })
                ->addColumn('mobile', function ($row) {

                    return $row->learner->mobile ?? '';
                })
                ->addColumn('country_id', function ($row) {

                    return $row->countryName->phonecode ?? '';
                })
                ->addColumn('course_name', function ($row) {
                    return $row->course->title ?? '';
                })
                ->addColumn('image', function ($row) {
                    $profilePictureURL = '';

                    !empty($row->learner->profile_pic)
                    ? $profilePictureURL = Storage::exists($row->learner->profile_pic) ? Storage::url($row->learner->profile_pic) : ''
                    : $profilePictureURL = asset('admin/dist/img/avatar.png');

                    return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
                })
                ->editColumn('after_deduction_price', function ($row) {
                    return isset($row->price) && !empty($row->after_deduction_price) ? $row->after_deduction_price : '';
                })
                ->editColumn('user_coin', function ($row) use ($coin_price) {

                    // return isset($row->user_coin) && !empty($row->user_coin) ? ($coin_price != 0 ? $row->user_coin . "*" . $coin_price : $row->user_coin) : '';
                    return isset($row->user_coin) ? $row->user_coin . '*' . $row->per_coin_price : "";

                })
                ->editColumn('coupon_id', function ($row) {
                    return isset($row->getCoupon->code) && !empty($row->getCoupon->code) ? $row->getCoupon->code : '';
                })
                ->editColumn('after_coupon_applied_deduction_price', function ($row) {
                    return isset($row->after_coupon_applied_deduction_price) && !empty($row->after_coupon_applied_deduction_price) ? $row->after_coupon_applied_deduction_price : '';
                })
                ->addColumn('created_at', function ($row) {

                    // return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->addColumn('payment_gateway', function ($row) {

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
                ->addColumn('invoice_id', function ($row) {

                    if (($row->order_status == 2) || ($row->order_status == 3) || ($row->order_status == 4) || ($row->order_status == 5)) {
                        return "-";
                    } else {
                        return $row->id;
                    }
                })
                ->addColumn('last_login', function ($row) {

                    if (!empty($row->learnerLastLogin) && $row->learnerLastLogin->type == 1) {
                        return $row->learnerLastLogin->updated_at;
                    }
                })
                ->editColumn('expire_at', function ($row) {
                    return !empty($row->expire_at) ? created_at_hidden($row->expire_at) . ' ' . Helper::only_date_format($row->expire_at) : 'LifeTime';
                })

                ->addColumn('order_status', function ($row) {

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
                ->rawColumns(["country_id", 'image', "created_at", "mobile", "payment_gateway", "order_status", "invoice_id", "last_login_date", "last_login", "course_name", "expire_at"])
                ->make(true);
        }

        $data['pg_header'] = 'Sales report';
        $data['countries'] = Countries::select('id', 'name')->distinct()->get();
        $data["states"] = States::select('id', 'name')->distinct()->get();
        $data["cities"] = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
        $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
        return view("admin.reports.lerner", $data);
    }

    public function loginLearnerReport(Request $request)
    {

        if ($request->ajax()) {
            $query = Learner::select('learners.id', 'learners.name', 'learners.email', 'learners.profile_pic', 'learners.created_at AS created_at', 'learners.deleted_at', 'learners.mobile', 'learners.country_id', 'learners.d_o_b');

            if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
                $searchValue = $_GET["search"]["value"];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('learners.name', 'like', "%$searchValue%")
                        ->orWhere('learners.email', 'like', "%$searchValue%")
                        ->orWhere('learners.mobile', 'like', "%$searchValue%")
                        ->orWhere('learners.id', 'like', "%$searchValue%");
                });
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
                $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
                $query->where('uc.course_id', $request->course);
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

                    return "+$country_code->phonecode " . @$row->mobile ?? '';

                })
                ->editColumn('login_url', function ($row) {
                    // $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile),"c_id"=>$row->country_id]);
                    // return '<a class="mx-1 text-olive" title="Copy Payment URL" type="button" href="javascript:void(0)"
                    //  onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
                    $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile), 'c_id' => $row->country_id]);

                    $ganerat_link = '<a class="text-olive login_url show_text_' . $row->id . '"  data-id="' . $row->id . '" title="Generate Login URL" type="button" href="javascript:void(0)">Generate URL</a><span  class="spinner-border text-primary spinner_' . $row->id . '" style="display:none;"></span> <a class="mx-1 text-olive show_url_' . $row->id . '" style="display:none" title="Copy login URL" type="button" href="javascript:void(0)"
            onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
                    return $ganerat_link;
                })
                ->addColumn('enroll_course', function ($row) {
                    return "<a href='' class='enroll_course' data-toggle='modal' data-target='#exampleModal' data-id='$row->id'>Manage Course/Package</a>";
                })
                ->addColumn('device_count', function ($row) {
                    // Retrieve the count of devices for the current learner
                    $deviceCount = $row->getDevice()->count();

                    // Return the device count
                    return $deviceCount;
                })
                ->rawColumns(['profile_pic', 'created_at', "enroll_course", "login_url", "device_count", "mobile"])
                ->make(true);
        }

        $data['pg_header'] = 'Learner login  report';

        $data['countries'] = Countries::select('id', 'name')->distinct()->get();
        $data["states"] = States::select('id', 'name')->distinct()->get();
        $data["cities"] = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
        $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
        return view("admin.reports.login-lerner", $data);
    }

    public function purchaseCourseReport(Request $request)
    {

        //dd($request->all());
        if ($request->ajax()) {

            $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
            $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

            $query = UserCourse::with("learner:name,id,mobile,country_id", "course:title,id", "getCoupon:code,id")->with(["userChpater" => function ($q) {
                $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
            }])->orderBy("id", "desc");

            $all_date = $request->signup_date;

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

            // if($request->has('signup_date') && !empty($request->signup_date)){
            //     $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d')."%");
            // }
            $query->when(
                $start_date && $end_date,
                function ($orders) use ($start_date, $end_date) {

                    $orders->whereDate('user_courses.created_at', '>=', $start_date);
                    $orders->whereDate('user_courses.created_at', '<=', $end_date);
                }
            );
            if ($request->has('course') && !empty($request->course)) {
                // $query->with(["course",function($q){

                $query->where('course_id', $request->course);
                // }]);
            }
            if ($request->has('price') && !empty($request->price) || $request->priceTo) {

                $query->where('price', '>=', $request->price ?? '');
                $query->where('price', '<=', $request->priceTo ?? '');
            }
            if ($request->has('instructor') && !empty($request->instructor)) {
                $query->whereHas("course", function ($q) use ($request) {
                    $q->where('courses.instructor_id', $request->instructor);
                });
            }

            if ($request->has('email') && !empty($request->email)) {
                $query->where('email', 'like', "%" . $request->email . "%");
            }
            if ($request->has('mobile') && !empty($request->mobile)) {
                $query->where('mobile', 'like', "%" . $request->mobile . "%");
            }
            if ($request->has('payment_status') && !empty($request->payment_status)) {
                $query->where('order_status', $request->payment_status);
            }

            if ($request->has('city') && !empty($request->city)) {
                // $query->where('city_id',  $request->city);
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('city_id', $request->city);
                });
            }
            if ($request->has('state') && !empty($request->state)) {
                // $query->where('state_id',  $request->state);
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('state_id', $request->state);
                });
            }
            if ($request->has('age') && !empty($request->age)) {
                $birthdate = now()->subYears($request->age);
                $query->whereHas("learner", function ($q) use ($birthdate) {
                    $q->where('d_o_b', '<=', $birthdate);
                });
            }

            if ($request->has('country') && !empty($request->country)) {
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('country_id', $request->country);
                });
            }

            if ($request->has('gender') && !empty($request->gender)) {
                $query->whereHas("learner", function ($q) use ($request) {
                    $q->where('gender', $request->gender);
                });
            }

            $count_record = $query->count();
            $data = $query->skip($start)->take($pageSize);

            // $query = $query->get();

            $coin_price = config()->has('settings.completedprofile') ? config('settings.completedprofile') : 0;
            return DataTables::of($data)->with([
                "recordsTotal" => $count_record,
                "recordsFiltered" => $count_record,
            ])
                ->addIndexColumn()
                ->addColumn('learner_id', function ($row) {
                    return $row->learner->name ?? '';
                })
            // ->addColumn('lerner_mobile', function ($row) {
            //     return $row->learner->mobile ?? '';
            // })
                ->editColumn('lerner_mobile', function ($row) {
                    $country_code = Countries::where("id", @$row->learner->country_id)->first();

                    return @"+$country_code->phonecode " . @$row->learner->mobile ?? '';

                })
                ->addColumn('course_id', function ($row) {
                    return $row->course->title ?? '';
                })
                ->addColumn('image', function ($row) {
                    $profilePictureURL = '';

                    !empty($row->learner->profile_pic)
                    ? $profilePictureURL = Storage::exists($row->learner->profile_pic) ? Storage::url($row->learner->profile_pic) : ''
                    : $profilePictureURL = asset('admin/dist/img/avatar.png');

                    return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
                })
                ->editColumn('after_deduction_price', function ($row) {
                    return isset($row->price) && !empty($row->after_deduction_price) ? $row->after_deduction_price : '';
                })
                ->editColumn('user_coin', function ($row) use ($coin_price) {

                    return isset($row->user_coin) && !empty($row->user_coin) ? ($coin_price != 0 ? $row->user_coin . "*" . $coin_price : $row->user_coin) : '';
                })
                ->editColumn('coupon_id', function ($row) {
                    return isset($row->getCoupon->code) && !empty($row->getCoupon->code) ? $row->getCoupon->code : '';
                })
                ->editColumn('after_coupon_applied_deduction_price', function ($row) {
                    return isset($row->after_coupon_applied_deduction_price) && !empty($row->after_coupon_applied_deduction_price) ? $row->after_coupon_applied_deduction_price : '';
                })
                ->addColumn('totalProgress', function ($row) {
                    if (!empty($row->course) && $row->course->chapters->isNotEmpty()) {
                        // Initialize new_chapterIds as an empty string to avoid undefined property issues
                        $row->new_chapterIds = '';

                        // Check if userChpater is not null and contains items
                        if (!empty($row->userChpater) && $row->userChpater->isNotEmpty()) {
                            $row->userChpater->each(function ($fl) use ($row) {
                                $row->new_chapterIds = $fl->chapterIds;
                            });

                            // Ensure new_chapterIds is not empty before proceeding
                            if (!empty($row->new_chapterIds)) {
                                $chapterIdsArray = explode(",", $row->new_chapterIds);

                                // Check if chapterIdsArray contains elements to avoid divide by zero
                                if (count($chapterIdsArray) > 0) {
                                    $user_progress = UserCourseProgress::whereIn("chapter_id", $chapterIdsArray)
                                        ->where("learner_id", $row->learner_id)
                                        ->where("is_completed", 1)
                                        ->count();

                                    return $user_progress != 0 ? round(($user_progress * 100) / count($chapterIdsArray)) . "%" : "0%";
                                }
                            }
                        }
                    }

                    // Default return value if any of the checks fail
                    return '0%';
                })
                ->addColumn('created_at', function ($row) {

                    // return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->rawColumns(['image', "created_at", "lerner_mobile"])
                ->make(true);
        }

        $data['pg_header'] = 'Purchase vs course complete report';
        $data['countries'] = Countries::select('id', 'name')->distinct()->get();
        $data["states"] = States::select('id', 'name')->distinct()->get();
        $data["cities"] = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
        $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
        return view("admin.reports.purchase-course-report", $data);
    }

    public function userSignupReport(Request $request)
    {
        if ($request->ajax()) {
            $query = Learner::with(['userLastLogin', "getOccupation", "maritalStatus", "education"])->orderBy("learners.id", "desc");

            if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
                $searchValue = $_GET["search"]["value"];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('learners.name', 'like', "%$searchValue%")
                        ->orWhere('learners.email', 'like', "%$searchValue%")
                        ->orWhere('learners.mobile', 'like', "%$searchValue%")
                        ->orWhere('learners.id', 'like', "%$searchValue%");
                });
            }

            if ($request->has('device_count') && !empty($request->device_count)) {
                $deviceCount = $request->device_count;
                $query->withCount(["getDevice" => function ($q) {
                    $q->select(DB::raw('count(*)'));
                }])->having('get_device_count', $deviceCount);
            }

            if ($request->has('signup_date') && !empty($request->signup_date)) {
                $query->where('learners.created_at', 'like', Carbon::createFromFormat('d/m/Y', $request->signup_date)->format('Y-m-d') . "%");
            }

            if ($request->has('course') && !empty($request->course)) {
                // $query->leftJoin('user_courses as uc', 'uc.learner_id', '=', 'learners.id');
                // $query->where('uc.course_id', $request->course);

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

            if ($request->has('day_filters') && !empty($request->day_filters)) {
                if ($request->day_filters == 'daterange' && $request->has("daterange") && $request['daterange'] != '') {
                    $all_date_arr = explode(' - ', $request['daterange']);

                    $start_date = "";
                    if (isset($all_date_arr[0]) && $all_date_arr[0] != '') {
                        $start = str_replace('/', '-', $all_date_arr[0]);
                        $start_date = date("Y-m-d", strtotime($start));
                    }
                    $end_date = "";
                    if (isset($all_date_arr[1]) && $all_date_arr[1] != '') {
                        $end = str_replace('/', '-', $all_date_arr[1]);
                        $end_date = date("Y-m-d", strtotime($end));
                    }

                    $query->whereDate('learners.created_at', '>=', $start_date);
                    $query->whereDate('learners.created_at', '<=', $end_date);
                } else {
                    $now = Carbon::now();
                    $daysAgo = $now->subDays($request->day_filters);
                    $query->where('learners.created_at', '>=', $daysAgo->format('Y-m-d'));
                }
            }

            $query->whereNull('learners.deleted_at');
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

                    return "+$country_code->phonecode " . @$row->mobile ?? '';

                })
                ->editColumn('occupation', function ($row) {

                    return @$row->getOccupation->name;

                })
                ->editColumn('marital_status', function ($row) {
                    return @$row->maritalStatus->name;

                })
                ->editColumn('education', function ($row) {
                    return @$row->getEducation->name;

                })
                ->editColumn('your_interests', function ($row) {

                    $your_interests = DropdownOption::whereIn("id", @explode(",", $row->your_interests))->pluck("name")->toArray();

                    return @implode(",", $your_interests);

                })
                ->editColumn('gender', function ($row) {
                    if ($row->gender == 1) {
                        return "Male";
                    } if ($row->gender == 2) {
                        return "Female";
                    } if($row->gender == 3) {
                        return "Other";
                    }

                })
                ->editColumn('login_url', function ($row) {
                    // $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile),"c_id"=>$row->country_id]);
                    // return '<a class="mx-1 text-olive" title="Copy Payment URL" type="button" href="javascript:void(0)"
                    //  onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
                    $logintUrl = route('direct_login', ['status' => Crypt::encrypt($row->mobile), 'c_id' => $row->country_id]);

                    $ganerat_link = '<a class="text-olive login_url show_text_' . $row->id . '"  data-id="' . $row->id . '" title="Generate Login URL" type="button" href="javascript:void(0)">Generate URL</a><span  class="spinner-border text-primary spinner_' . $row->id . '" style="display:none;"></span> <a class="mx-1 text-olive show_url_' . $row->id . '" style="display:none" title="Copy login URL" type="button" href="javascript:void(0)"
            onclick=copyUrl("' . $logintUrl . '")><i class="fas fa-copy"></i></a>';
                    return $ganerat_link;
                })
                ->addColumn('enroll_course', function ($row) {
                    return "<a href='' class='enroll_course' data-toggle='modal' data-target='#exampleModal' data-id='$row->id'>Manage Course/Package</a>";
                })
                ->addColumn('device_count', function ($row) {
                    // Retrieve the count of devices for the current learner
                    $deviceCount = $row->getDevice()->count();

                    // Return the device count
                    return $deviceCount;
                })
                ->addColumn('last_login', function ($row) {
                    // Retrieve the count of devices for the current learner
                    $lastLogin = !empty($row->userLastLogin) && !empty($row->userLastLogin->updated_at) ? date("d/m/Y H:i:s", strtotime($row->userLastLogin->updated_at)) : '';

                    // Return the device count
                    return $lastLogin;
                })
                ->rawColumns(['profile_pic', 'created_at', "enroll_course", "login_url", "device_count", "last_login", "gender", "occupation", "marital_status", "education", "your_interests"])
                ->make(true);
        }

        $data['pg_header'] = 'User Signup Report';
        $data['countries'] = Countries::select('id', 'name')->distinct()->get();
        $data["states"] = States::select('id', 'name')->distinct()->get();
        $data["cities"] = Cities::select('id', 'name')->distinct()->orderBy('name')->pluck("name")->toArray();
        $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
        $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
        return view("admin.reports.user-signup", $data);
    }

    // public function userProgressReport(Request $request)
    // {
    //     if ($request->ajax()) {

    //         if(!empty($request->instructor) && empty($request->course)){
    //             $courseId = Course::select('id', 'instructor_id')->where('instructor_id',$request->instructor)->pluck('id')->toArray();
    //         }
    //         elseif(!empty($request->course)){
    //             $courseId = [];
    //             $courseId[] = $request->course;
    //         }

    //         $all_date_arr = explode(' - ', $request['daterange']);

    //         $start_date = "";
    //         if(isset($all_date_arr[0]) && $all_date_arr[0] != '') {
    //             $start = str_replace('/', '-', $all_date_arr[0]);
    //             $start_date = date("Y-m-d", strtotime($start));
    //         }
    //         $end_date = "";
    //         if(isset($all_date_arr[1]) && $all_date_arr[1] != '') {
    //             $end = str_replace('/', '-', $all_date_arr[1]);
    //             $end_date = date("Y-m-d", strtotime($end));
    //         }

    //         $querys = UserCourse::select('course_id')->whereIn('course_id',$courseId)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->pluck('course_id')->toArray();

    //         $total_enrolled = count($querys);

    //         $currentDate = Carbon::now()->format('Y-m-d');
    //         $active_enrolled = UserCourse::whereIn('course_id', $courseId)
    //         ->where(function ($query) use ($currentDate) {
    //             $query->where('expire_at', '>=', $currentDate)
    //                 ->orWhereNull('expire_at');
    //         })
    //         ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)
    //         ->pluck('course_id')->toArray();

    //         $total_progress = 0;
    //         foreach($querys as $courseIdd){
    //             $chapter = Chapter::selectRaw('COUNT(*) as total_count, GROUP_CONCAT(id) as ids')
    //                 ->where('course_id', $courseIdd)
    //                 ->where('parent_id', '!=', 0)
    //                 ->whereNotIn('asset_type', [4, 7])
    //                 ->groupBy('course_id')
    //                 ->first();

    //             $userCourseProgress = UserCourseProgress::whereIn('chapter_id', explode(',', @$chapter->ids))
    //                 ->where('is_completed', 0)
    //                 ->count();

    //             $total_pro = 0;
    //             if(!empty($chapter) && $chapter->total_count != 0){
    //                 $total_pro = $userCourseProgress != 0 ? round(($userCourseProgress * 100) / $chapter->total_count) : 0;
    //             }

    //             if($total_pro > 1){
    //                 $total_progress++;
    //             }
    //         }

    //         $active_progess = 0;
    //         foreach($active_enrolled as $courseIdd){
    //             $chapter = Chapter::selectRaw('COUNT(*) as total_count, GROUP_CONCAT(id) as ids')
    //                 ->where('course_id', $courseIdd)
    //                 ->where('parent_id', '!=', 0)
    //                 ->whereNotIn('asset_type', [4, 7])
    //                 ->groupBy('course_id')
    //                 ->first();

    //             $userCourseProgress = UserCourseProgress::whereIn('chapter_id', explode(',', @$chapter->ids))
    //                 ->where('is_completed', 0)
    //                 ->count();

    //             $active_pro = 0;
    //             if(!empty($chapter) && $chapter->total_count != 0){
    //                 $active_pro = $userCourseProgress != 0 ? round(($userCourseProgress * 100) / $chapter->total_count) : 0;
    //             }

    //             if($active_pro > 1){
    //                 $active_progess++;
    //             }
    //         }

    //         return response()->json([
    //             'total_enrolled' => $total_enrolled,
    //             'active_enrolled' => count($active_enrolled),
    //             'total_progress' => $total_progress,
    //             'active_progess' => $active_progess
    //         ]);
    //     }

    //     $data['pg_header'] = 'User Progress Report';
    //     $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
    //     $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
    //     return view("admin.reports.user_progress_report", $data);
    // }
    public function userProgressReport(Request $request)
    {
        if ($request->ajax()) {

            $course_package = CoursePackage::where("course_id", $request->course_id)->whereHas("getPackageBasedCourse")->first();

            $package_course_id = @$course_package->course_id;

            $query = UserCourse::with(['learner', 'course', 'userChpater'])

                ->where('course_id', $request->course_id)
                ->orWhere("course_id", @$course_package->package_id)
                ->orderBy('id', 'desc')
                ->with(["userChpater" => function ($q) {
                    $q->select('course_id', DB::raw('GROUP_CONCAT(id) as chapterIds'))->groupBy('course_id');
                }])
                ->get()
                ->unique(function ($item) {
                    return $item['learner_id'] . $item['course_id'] . $item['plan_id'];
                });

            return DataTables::of($query)

                ->editColumn('name', function ($row) {
                    return $row->learner->name ?? '';
                })
                ->editColumn('mobile', function ($row) {
                    return $row->learner->mobile ?? '';
                })
                ->editColumn('email', function ($row) {
                    return $row->learner->email ?? '';
                })
                ->editColumn('created_at', function ($row) {
                    return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
                })
                ->editColumn('expire_at', function ($row) {
                    return !empty($row->expire_at) ? created_at_hidden($row->expire_at) . ' ' . Helper::date_format($row->expire_at) : '';
                })
                ->editColumn('order_status', function ($row) {
                    if ($row->order_status == 1) {
                        return '<span class="badge badge-success">completed</span>';
                    } else if ($row->order_status == 2) {
                        return '<span class="badge badge-danger">Failure</span>';
                    } else if ($row->order_status == 3) {
                        return '<span class="badge badge-success">Free</span>';
                    } else if ($row->order_status == 4) {
                        return '<span class="badge badge-success">Enrol by Admin</span>';
                    }
                })
                ->addColumn('course_progress', function ($row) {
                    $row->userChpater->filter(function ($fl) use ($row) {
                        return $row->new_chapterIds = $fl->chapterIds;
                    });
                    $user_progress = UserCourseProgress::whereIn("chapter_id", explode(",", $row->new_chapterIds))->where("learner_id", $row->learner_id)->where("is_completed", 1)->count();
                    return round($user_progress) != 0 ? round(($user_progress * 100) / count(explode(",", $row->new_chapterIds))) . "%" : "0%";
                })
                ->rawColumns(['name', 'email', 'created_at', 'expire_at', 'order_status', 'course_progress'])
                ->make(true);

        }

        $data['pg_header'] = 'User Progress Report';
        $data["courses"] = Course::select('id', 'title', "slug")->distinct()->get();
        $data["instructor"] = Instructors::select('id', 'user_id')->with("user")->get();
        return view("admin.reports.user_progress_report", $data);
    }

    public function LearnerAllCourseProgress(Request $request)
    {
        // dd($request->all());
        $scope = [];
        $params = [];

        $scope[] = "email";
        $subject = "Learner All Course Progress Report";
        $content = "Hi," . @auth()->user()->name;

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        AllCourseProgressJob::dispatch($request->all(), $params, 'export_email', $scope, $subject, $content, $user_id);

        return back()->with("msg", 'Usage report will be generated shortly. Kindly check your email.');

    }

    public function getInstructorCourses(Request $request)
    {
        $courses = Course::select('id', 'title', "slug")->where('instructor_id', $request->instructor_id)->distinct()->get();

        return response()->json($courses);
    }
}
