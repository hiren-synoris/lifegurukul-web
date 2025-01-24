<?php

namespace App\Http\Controllers\admin;

use Carbon\Carbon;
use App\Helper\Helper;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CouponCourse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
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
        //
        if (!$this->user->can('browse_coupon')) {
            abort(403);
        }

        $pg_header = "Coupon Codes";
        if (view()->exists('admin.coupon_codes.list')) {
            return view('admin.coupon_codes.list', compact('pg_header'));
        }
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_coupon')) {
            abort(403);
        }

        $pg_header = "Add Coupon";
        $courses = Course::select('id', 'title')->whereNull('deleted_at')->where('status', '1')->get();
        // $blogCategories = DropdownOption::getDropdownCategories('blog_category')->get();
        if (view()->exists('admin.coupon_codes.add')) {
            return view('admin.coupon_codes.add', compact('courses', 'pg_header'));
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

        if (!$this->user->can('add_coupon')) {
            abort(403);
        }

        $notification = [];

        // try {
        // $path = $request->cover->store('blogs');

        // if(request()->has('meta_keywords')){
        //     $meta_keywords = implode(',',$request['meta_keywords']);
        // }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'code' => 'unique:coupons',
            // 'type' => 'required|in:2,1',
             'value' => 'required|numeric',
            // 'course_ids' => 'required|array',
            // 'course_ids.*' => 'exists:courses,id',
            'exp_date' => 'required',
            // 'max_amt' => ($request->type === '1') ? 'required|numeric|min:0' : '',
            // 'status' => (request()->has('status') && $request['status'] == 'on') ? 1 : 0,

        ]
        //  ["course_ids.required" => "The course field is required.",]
    );

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();

        } else {
            try {
                DB::beginTransaction();
                // Create a new coupon
                $coupon = new Coupon();
                $coupon->code = $request->code;
                $coupon->type = $request->type;
                $coupon->value = $request->value;
                // $coupon->courses = json_encode($request->course_ids);
                // $coupon->expiry_date = request()->has('exp_date') ? date('Y-m-d', strtotime($request->exp_date)) : null;
                $coupon->expiry_date = request()->has('exp_date') ? Carbon::createFromFormat('d/m/Y', $request->exp_date)->format('Y-m-d') : null;
                $coupon->max_amount = $request->max_amt;
                $coupon->status = request()->has('status') ? 1 : 0;
                $coupon->new_user = request()->has('new_user') ? $request->new_user : 0;
                $coupon->save();

                foreach ($request->course_ids as $val) {
                    CouponCourse::create([
                        "coupon_id" => $coupon->id,
                        "course_id" => $val,
                    ]);
                }

                DB::commit();

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = "Coupon created successfully";

            } catch (\Exception $e) {
                DB::rollback();
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = "Something went wrong: " . $e->getMessage();
            }
        }

        return redirect('backoffice/coupons')->with('notification', $notification);
    }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \App\Models\Coupon  $coupon
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(Coupon $coupon)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Models\Coupon  $coupon
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit($id)
    // {
    //     if (!$this->user->can('edit_coupon')) abort(403);
    //     $coupon = Coupon::findOrFail($id);
    //     $courses = Course::select('id','title')->whereNull('deleted_at')->where('status', '1')->get();
    //     $pg_header = "Edit Blog";

    //     if (view()->exists('admin.coupon_codes.edit')) {
    //         return view('admin.coupon_codes.edit', compact('coupon','courses'));
    //     }
    //     abort(404);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Models\Coupon  $coupon
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, $id)
    // {
    //     if (!$this->user->can('edit_coupon')) abort(403);
    //     $notification = [];

    //     // dd($request->all());
    //     $coupon = Coupon::findOrFail($id);

    //     $coupon->name = $request->name;
    //     $coupon->code = $request->code;
    //     $coupon->type = $request->type;
    //     $coupon->discount = $request->discount;
    //     $coupon->courses = json_encode($request->course_ids);
    //     $coupon->expiry_date = request()->has('exp_date') ? date('Y-m-d', strtotime($request->exp_date )): NULL;
    //     $coupon->max_amount = $request->max_amt;
    //     $coupon->status = request()->has('status') ? 1 : 0;
    //     $coupon->save();

    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Coupon updated successfully";
    //     return redirect('backoffice/coupons')->with('notification', $notification);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Models\Coupon  $coupon
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($id)
    // {
    //     if (!$this->user->can('delete_coupon')) abort(403);
    //     $coupon = Coupon::where('id', $id)->firstOrFail();
    //     $coupon->delete();
    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Coupon deleted successfully";
    //     return redirect('/backoffice/coupons')->with('notification', $notification);
    // }

    public function changeStatus($id, Request $request)
    {
        // dd($request->status);
        $coupon = Coupon::findOrFail($id);
        $coupon->status = $request->status;
        $coupon->save();

        if ($coupon) {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Status updated successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Somthing wenr wrong!";
        }

        return response()->json($notification, 200);
    }

    // public function restore($id)
    // {
    //     if (!$this->user->can('restore_coupon')) abort(403);

    //     $coupon = Coupon::withTrashed()->where('id', $id)->firstOrFail();
    //     $coupon->restore();
    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Coupon restored successfully";
    //     return redirect('/backoffice/coupons')->with('notification', $notification);
    // }

    // public function restore_all(Request $request)
    // {
    //     if (!$this->user->can('restore_coupon')) abort(403);

    //     foreach (array_keys($request->selected_checkbox) as $key => $value) {
    //         $coupon = Coupon::withTrashed()->where('id',$value)->firstOrFail();
    //         $coupon->restore();
    //     }
    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Coupon restored successfully";
    //     return redirect()->back()->with('notification', $notification);
    // }

    public function get_coupons()
    {
        if (!$this->user->can('browse_coupon')) {
            abort(403);
        }


        $query = Coupon::with("getCouponCourse")->
        select('id', 'code', 'type', 'value', 'courses', 'max_amount', 'expiry_date', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->latest();



        $data = $query->get();

        // dd($data);

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                // $url = route("coupons.destroy", ["coupon" => $row->id]);
                $button = "";
                // if ($this->user->can('read_coupon')) {
                //     $button .= '<a class="mx-1" title="View" target="_blank" href="' . url('coupons/' . $row->slug) . '"><i class="fas fa-eye"></i></a>';
                // }
                if ($this->user->can('edit_coupon')) {
                    $button = '<div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="statusToggle' . $row->id . '" ' . ($row->status ? 'checked' : '') . ' onchange="changeStatus(' . $row->id . ', this.checked)">
                                    <label class="custom-control-label" for="statusToggle' . $row->id . '">' . Helper::checkStatus($row->status) . '</label>
                                </div>';
                }
                // if (is_null($row->deleted_at)) {
                //     if ($this->user->can('delete_coupon')) {
                //         $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //     }
                // } else {
                //     if ($this->user->can('restore_coupon')) {
                //         $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/coupons/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
        // ->editColumn('category_id', function($row){
        //     return isset($row->blogCategoryOptions) && !empty($row->blogCategoryOptions) ? $row->blogCategoryOptions->name : '';
        // })
            ->editColumn('courses', function ($row) {
                // $courseIds = json_decode($row->courses, true); // Decode JSON array
                // $courseNames = Course::whereIn('id', $courseIds)->pluck('title')->toArray(); // Fetch course names
                // return implode(', ', $courseNames); // Return course names as comma-separated string
                if(isset($row->getCouponCourse)) {

                    $courseIds = $row->getCouponCourse->pluck("course_id")->filter();

                    $courses = Course::whereIn('id', $courseIds)->pluck('title')->toArray();
                    return $courseNames[$row->id] = $courses;
                }  else {
                    return "-";
                }

            })
            ->editColumn('type', function ($row) {
                return $row->type == 1 ? "Percentage" : "Fixed Price";
            })
        // ->editColumn('status', function($row){
        //     return Helper::checkStatus($row->status);
        // })
            ->editColumn('expiry_date', function ($row) {
                return !empty($row->expiry_date) ? date("d/m/Y", strtotime($row->expiry_date)) : '';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'status', 'date'])
            ->toJson();
    }

    // public function get_coupons_deleted(){
    //     if (!$this->user->can('browse_coupon')) abort(403);

    //     $query = Coupon::select('id','code','type','discount','courses','max_amount','expiry_date','status','created_at')
    //                     ->whereNotNull('deleted_at')
    //                     ->onlyTrashed()
    //                     ->latest()
    //                     ->get();

    //     return DataTables::of($query)
    //         ->addColumn('action', function ($row) {
    //             $url = route("coupons.destroy", ["coupon" => $row->id]);
    //             $deleteurl = route("coupons.delete", ["id" => $row->id]);
    //             $button = "";
    //             if (!$row->deleted_at) {
    //                 if ($this->user->can('delete_coupon')) {
    //                     $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
    //                 }
    //                 if ($this->user->can('restore_coupon')) {
    //                     $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/coupons/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
    //                 }
    //             } else {
    //                 if ($this->user->can('restore_coupon')) {
    //                     $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/coupons/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
    //                 }
    //             }
    //             return "<div class='d-flex justify-content-center'>$button</div>";
    //         })
    //         // ->editColumn('category_id', function($row){
    //         //     return isset($row->blogCategoryOptions) && !empty($row->blogCategoryOptions) ? $row->blogCategoryOptions->name : '';
    //         // })
    //         ->editColumn('courses',function($row){
    //             $courseIds = json_decode($row->courses, true); // Decode JSON array
    //             $courseNames = Course::whereIn('id', $courseIds)->pluck('title')->toArray(); // Fetch course names
    //             return implode(', ', $courseNames); // Return course names as comma-separated string
    //         })
    //         ->editColumn('status', function($row){
    //             return Helper::checkStatus($row->status);
    //         })
    //         ->editColumn('expiry_date', function($row){
    //             return !empty($row->expiry_date) ? date("d/m/Y", strtotime($row->expiry_date)) : '';
    //         })
    //         ->editColumn('created_at', function($row){
    //             return !empty($row->created_at) ? Helper::date_format($row->created_at) : '';
    //         })
    //         ->rawColumns(['action','status','date'])
    //         ->toJson();
    //     }

    // public function delete($id)
    // {
    //     if (!$this->user->can('delete_coupon')) abort(403);
    //     $notification = [];

    //     DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    //     $coupon= Coupon::withTrashed()->where('id', $id)->firstOrFail();
    //     $coupon->forceDelete();
    //     DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Coupon deleted successfully";

    //     return redirect()->back()->with('notification', $notification);
    // }

    /**
     * Permanant Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//     public function bulk_Hard_Delete(Request $request)
//     {
//         if (!$this->user->can('delete_coupon')) abort(403);
//         $notification = [];
//         if (!empty($request->bd) > 0) {
//             DB::statement('SET FOREIGN_KEY_CHECKS = 0');
//             foreach ($request->bd as $id => $value) {
//                 $coupon= Coupon::withTrashed()->where('id', $id)->firstOrFail();
//                 $coupon->forceDelete();
//             }
//             DB::statement('SET FOREIGN_KEY_CHECKS = 1');
//             $notification['type'] = "sweet-alert";
//             $notification['status'] = "success";
//             $notification['title'] = "Success";
//             $notification['msg'] = "Coupons deleted successfully";
//         } else {
//             $notification['type'] = "sweet-alert";
//             $notification['status'] = "error";
//             $notification['title'] = "Error";
//             $notification['msg'] = "No Blog selected";
//         }
//         return redirect()->back()->with('notification', $notification);
//   }

public function updateCouponExpiry(Request $request)
    {

        $data = Coupon::find($request->id);
        $data->expiry_date = request()->has('expiry_date') ? Carbon::createFromFormat('d/m/Y', $request->expiry_date)->format('Y-m-d') : null;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "Expiry date updated successfully"
        ]);
    }
}
