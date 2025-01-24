<?php

namespace App\Http\Controllers\admin;

use App\Models\Course;
use App\Models\Learner;
use App\Models\Countries;
use App\Models\LearnerLog;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role;
use App\Exports\CourseOrdersExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CourseOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->user->can('browse_course_orders')) {
            abort(403);
        }

        $pg_header = "Course/Package Orders";

        // $users = Learner::all();
        // $user = Auth::user();
        // if ($user->hasRole('instructor')) {
        //     $courses = Course::where('instructor_id', $user->id)->get();

        // } else {
        //     $courses = Course::all();

        // }

        if (view()->exists('admin.course_orders.list')) {
            return view('admin.course_orders.list', compact('pg_header'));
        }
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
    public function destroy(Request $request, $id)
    {

        try {
            $notification = [];
            UserCourse::destroy($id);
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Course Orders deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } catch (\Exception $e) {
            return redirect()->back()->with('msg', 'error');
        }
    }

    public function getCourseOrders(Request $request)
    {


        if (!$this->user->can('browse_course_orders')) {
            abort(403);
        }

        $user = Auth::user() ?? null;
        $all_date = $request->daterange;

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

        $orders = UserCourse::with("learner", "course:title,id", "getCoupon:code,id","createdBy:id,name");

        $igst = config()->has('settings.igst') ? config('settings.igst') : null;
        $sgst = config()->has('settings.sgst') ? config('settings.sgst') : null;
        $cgst = config()->has('settings.cgst') ? config('settings.cgst') : null;

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

        $orders->when($request->has('user_id') && !empty($request->user_id),
            function ($orders) use ($request) {
                return $orders->where('learner_id', $request->user_id);
            });
        // $orders->when($request->has('payment_status') && !empty($request->payment_status),
        // function ($orders) use ($request) {
        //     return $orders->where('order_status', $request->payment_status);
        // });
        if ($request->payment_status == 1) {
            $orders->where('order_status', 1);
        } else if ($request->payment_status == 2) {
            $orders->where('order_status', 2);
        } else if ($request->payment_status == 3) {

            $orders->where('order_status', 3);
        } else if ($request->payment_status == 4) {
            $orders->where('order_status', 4);
        } else if ($request->payment_status == 5) {
            $orders->where('order_status', 5);
        }

        $orders->when($request->has('courses') && !empty($request->courses),
            function ($orders) use ($request) {
                return $orders->where('course_id', $request->courses);
            });
        $orders->when($start_date && $end_date,
            function ($orders) use ($start_date, $end_date) {

                $orders->whereDate('user_courses.created_at', '>=', $start_date);
                $orders->whereDate('user_courses.created_at', '<=', $end_date);
            });
        if ($user->hasRole('instructor')) {
            $orders->whereHas("course", function ($q) use ($user) {
                $q->where("instructor_id", $user->id);
            });
        }
        //$orders->limit(20);
        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        // $count_record = $orders->count();
        // $data = $orders->skip($start)->take($pageSize);

        // $pageSize = request()->get('length', 10);
        // $start = request()->get('start', 0);

        $count_record = $orders->count();
        //dd($count_record);

        $data = $orders->skip($start)->take($pageSize);


        $onecoinprice = config()->has('settings.onecoinprice') ? config('settings.onecoinprice') : '';
        // $order = $orders->get();
        //$coin_price = config()->has('settings.completedprofile') ? config('settings.completedprofile') : 0;
        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])

            ->editColumn('name', function ($row) {
                return isset($row->learner) && !empty($row->learner) ? $row->learner->name : '';
            })
            ->editColumn('mobile', function ($row) {
                $country_code = Countries::where("id", @$row->learner->country_id)->first();

                return @"+$country_code->phonecode " . @$row->learner->mobile ?? '';
            })
            // ->editColumn('email', function ($row) {
            //     return $row->learner->email ?? '';
            // })
            ->editColumn('title', function ($row) {
                return isset($row->course) && !empty($row->course) ? $row->course->title : '';
            })

            ->editColumn('status', function ($row) {
                $role_name = Role::where("id",@$row->createdBy->id)->first();
                if ($row->order_status == 1) {
                    return '<span class="badge badge-success">completed</span>';
                } else if ($row->order_status == 2) {
                    return '<span class="badge badge-danger">Failure</span>';
                } else if ($row->order_status == 3) {
                    return '<span class="badge badge-success">Free</span>';
                } else if ($row->order_status == 4) {
                    if($role_name) {
                        return "<span class='badge badge-success'>".$role_name?->display_name." (".@$row->createdBy->name .")</span>";
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


                if ($row->payment_gateway == 1) {
                    return "Razorpay";
                } elseif ($row->payment_gateway == 2) {
                    return "Instamojo";
                } elseif ($row->payment_gateway == 3) {
                    return "In-app purchase";
                } else {
                    return "";
                }
                // return @$row->payment_gateway==1 ? "razorpay" : (@$row->payment_gateway==2 ? "instamojo" :'');
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
                return $row->expire_at != null ? $row->expire_at : '';
            })
            ->addColumn('action', function ($row) {
                $url = route("course_orders.destroy", ["course_order" => $row->id]);
                // $url ='';
                $button = '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick="delete_confirmation(\'' . $url . '\')"><i class="fas fa-trash-alt"></i></a>';
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->rawColumns(['order_id', 'title', 'name', 'status', 'amount', 'date', 'action', "invoice", "payment_gateway", "expire_at"])
            ->addIndexColumn()
            ->toJson();

    }
    public function exportCourseOrders(Request $request)
    {
        // dd($request->all());
        // $user = Auth::user() ?? null;
        // $all_date = $request->course_date_range_hidden;

        // $all_date_arr = explode(' - ', $all_date);

        //  // dd($all_date_arr);
        //  // echo date('01/m/Y H:i:s');
        //  // exit;
        //  $start_date = date('Y-m-01');
        //  if(isset($all_date_arr[0]) && $all_date_arr[0] != '') {
        //      $start = str_replace('/', '-', $all_date_arr[0]);
        //      $start_date = date("Y-m-d", strtotime($start));
        //  }
        // //  dd($start_date);
        //  $end_date = date('Y-m-d');
        //  if(isset($all_date_arr[1]) && $all_date_arr[1] != '') {
        //      $end = str_replace('/', '-', $all_date_arr[1]);
        //      $end_date = date("Y-m-d", strtotime($end));
        //  }
        //  $deviceType = Course::COURSE_WEBSITE;
        // $orders = Course::distinct()->select('title', 'learner_id', 'type', 'slug', 'user_courses.id as user_courses_id', 'user_courses.price', 'user_courses.order_status', 'user_courses.created_at', 'learners.name as learner_name')      ->whereDate('user_courses.created_at', '>=', $start_date)
        // ->whereRaw("find_in_set($deviceType , course_platform)")
        // ->whereDate('user_courses.created_at', '<=', $end_date)
        //     ->when($request->has('user_id_hidden') && !empty($request->user_id_hidden),
        //         function ($orders) use ($request) {
        //             return $orders->where('learner_id', $request->user_id_hidden);
        //         })
        //     ->when($request->has('course_id_hidden') && !empty($request->course_id_hidden),
        //         function ($orders) use ($request) {
        //             return $orders->where('course_id', $request->course_id_hidden);
        //         })

        //     // ->whereIn('course_platform', [Course::COURSE_ALL, Course::COURSE_WEBSITE])
        //     ->withCount(['chapters' => function ($query) {
        //         $query->where('parent_id', 0);
        //     }])
        //     ->with(['instructor' => function ($query) {
        //         $query->select('name', 'email', 'id', 'profile_picture');
        //     }, 'categories'])
        //     ->with('instructor.instructure')
        //     ->with(['plans'])
        //     ->leftJoin('user_courses', 'courses.id', '=', 'user_courses.course_id')
        //     ->leftJoin('learners', 'user_courses.learner_id', '=', 'learners.id')
        //     ->where('courses.status', '1');
        //     // dd($orders);
        // if ($user->hasRole('instructor')) {
        //     $orders->where('courses.instructor_id', $user->id);
        // }
        // $orders->whereNull('courses.deleted_at')
        //     ->whereNotNull('transaction_id')
        // //->groupBy('courses.id')
        //     ->orderBy('user_courses.id', "DESC");
        // $orders = $orders->get();
        if (!$this->user->can('browse_course_orders')) {
            abort(403);
        }
        $user = Auth::user() ?? null;
        $all_date = $request->course_date_range_hidden;

        $all_date_arr = explode(' - ', $all_date);

        // dd($all_date_arr);
        // echo date('01/m/Y H:i:s');
        // exit;
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

        $deviceType = Course::COURSE_WEBSITE;
        $orders = Course::distinct()->select('title', 'learner_id', 'type', 'slug', 'user_courses.id as user_courses_id', 'user_courses.price', 'user_courses.order_status', 'user_courses.created_at', 'learners.name as learner_name')
            ->when($request->has('user_id_hidden') && !empty($request->user_id_hidden),
                function ($orders) use ($request) {
                    return $orders->where('learner_id', $request->user_id_hidden);
                })
            ->when($request->has('course_id_hidden') && !empty($request->course_id_hidden),
                function ($orders) use ($request) {
                    return $orders->where('course_id', $request->course_id_hidden);
                })
            ->when($start_date && $end_date,
                function ($orders) use ($start_date, $end_date) {

                    $orders->whereDate('user_courses.created_at', '>=', $start_date);
                    $orders->whereDate('user_courses.created_at', '<=', $end_date);
                })

            ->withCount(['chapters' => function ($query) {
                $query->where('parent_id', 0);
            }])
            ->with(['instructor' => function ($query) {
                $query->select('name', 'email', 'id', 'profile_picture');
            }, 'categories'])
            ->with('instructor.instructure')
            ->with(['plans'])
            ->Join('user_courses', 'courses.id', '=', 'user_courses.course_id')
            ->leftJoin('learners', 'user_courses.learner_id', '=', 'learners.id');

        if ($user->hasRole('instructor')) {
            $orders->where('courses.instructor_id', $user->id);
        }
        $orders->whereNull('courses.deleted_at')
            ->orderBy('user_courses.id', "DESC");
        $orders = $orders->get();

        $arr = [];
        foreach ($orders as $key => $value) {
            $arr[$key]['OrderID'] = isset($value->user_courses_id) && !empty($value->user_courses_id) && !empty($value->user_courses_id) ? $value->user_courses_id : '';
            $arr[$key]['Title'] = isset($value->title) && !empty($value->title) ? $value->title : '';
            $arr[$key]['Learner'] = isset($value->learner_name) && !empty($value->learner_name) ? $value->learner_name : '';
            $arr[$key]['Status'] = isset($value->order_status) && !empty($value->order_status) && !empty($value->order_status) ? 'completed' : '';
            $arr[$key]['Amount'] = isset($value->price) && !empty($value->price) && !empty($value->price) ? $value->price : '';
            $arr[$key]['Date'] = !empty($value->created_at) ? date("d/m/Y H:i:s", strtotime($value->created_at)) : '';

            $learnerLog = LearnerLog::where('learner_id', $value->learner_id)->orderBy('updated_at', "DESC")->first();
            $arr[$key]['LastLoginDate'] = !empty($learnerLog->updated_at) ? date("d/m/Y H:i:s", strtotime($learnerLog->updated_at)) : '';
        }

        $course_orders = new CourseOrdersExport([$arr]);
        return Excel::download($course_orders, 'course_orders.xlsx');
    }
}
