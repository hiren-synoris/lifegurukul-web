<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\front\CommonController;
use App\Models\ChapterInfo;
use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\RenewingSubscriptions;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CoursePricingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $baseUrl = 'https://api.appstoreconnect.apple.com';
        $credentialsFilePath = storage_path('keys/AuthKey_5JK79XMBSY.p8');
        $privateKeyPath = $credentialsFilePath;
        $keyId = config('in_app_purchase.keyId');
        $ssl = config('in_app_purchase.ssl');
        $productId = config('in_app_purchase.product_id');
        // dd($keyId,$ssl,$productId);
        $privateKey = file_get_contents($privateKeyPath);

        $token = [
            'iss' => $ssl,
            'iat' => time(),
            'exp' => time() + (60 * 20),
            'aud' => 'appstoreconnect-v1',
        ];

        $jwt = JWT::encode($token, $privateKey, 'ES256', $keyId);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type' => 'application/json',
        ])->get($baseUrl . '/v1/apps/1566107921/inAppPurchasesV2', [
            'limit' => 200,
        ]);

        $apps = $response->json();
        // dd($apps);
        $inAppPurchases = $apps['data'] ?? [];

        if (!empty($inAppPurchases)) {
            $renewingSubscriptions = RenewingSubscriptions::get();

            foreach ($renewingSubscriptions as $subscription) {
                $subscription->delete();
            }
        }

        $results = [];
        foreach ($inAppPurchases as $inAppPurchase) {

            // if($inAppPurchase['attributes']['state']=="APPROVED") {
            $details = [
                'name' => $inAppPurchase['attributes']['name'],
                'productId' => $inAppPurchase['attributes']['productId'],
                'inAppPurchaseType' => $inAppPurchase['attributes']['inAppPurchaseType'],
                'state' => $inAppPurchase['attributes']['state'],
                'familySharable' => $inAppPurchase['attributes']['familySharable'],
            ];
            RenewingSubscriptions::create($details);
        }
        $renewingSubscriptions = RenewingSubscriptions::get();

        $view_file = view("admin.courses.info.fetch-subscriptions", compact("renewingSubscriptions"))->render();

        return response()->json($view_file);
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

        // dd($request->all());
        if (!request()->has('course_id')) {
            abort(404);
        }

        Course::where('id', $request['course_id'])->firstOrFail();

        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'selectedPlan' => 'required|in:free,one-time,recurring',
            'plan_name' => 'required_if:selectedPlan,free,one-time',
            'list_price' => 'required_if:selectedPlan,one-time',
            'final_payable_price' => 'required_if:selectedPlan,one-time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 'server-error',
                'errors' => $validator->getMessageBag()->toArray(),
            ], 400);
        }

        $validated = $validator->validated();
        session(['admin_add_plan' => true]);
        try {
            $planType = null;
            $planId = $subscriptionId = null;
            if (isset($validated['selectedPlan']) && !empty($validated['selectedPlan'])) {
                if ($validated['selectedPlan'] == 'free') {
                    $planType = CoursePlan::PLAN_FREE;
                } elseif ($validated['selectedPlan'] == 'one-time') {
                    $planType = CoursePlan::PLAN_ONE_TIME_PAYMENT;
                } elseif ($validated['selectedPlan'] == 'recurring') {
                    $planType = CoursePlan::PLAN_RECURRING;
                }
            }
            if (isset($request['limit_course']) && $request['limit_course'] == 'on') {
                $limit_course = 1;
                // if(isset($request['fixed_date']) && (!empty($request['fixed_date']) || $request['fixed_date'] != 'null')){
                if (isset($request['fixed_days']) && (!empty($request['fixed_days']) || $request['fixed_days'] != null)) {
                    $is_fixed_date = 2;
                    $access_value = $request['fixed_days'];
                } else if (isset($request['fixed_date_add']) && (!empty($request['fixed_date_add']) || $request['fixed_date_add'] != null)) {
                    $is_fixed_date = 1;
                    $access_value = $request['fixed_date_add'];
                } else {
                    $is_fixed_date = 0;
                    $access_value = '';
                }
                // }else{
                //     $is_fixed_date = 0;
                //     $access_value = '';
                // }

            } else {
                $limit_course = 0;
                $is_fixed_date = 0;
                $access_value = '';
            }

            // for recurring only
            if ($planType == CoursePlan::PLAN_RECURRING) {
                // create Plan
                $common = new CommonController;
                // dd( $common->createPlan($request));
                $planId = $common->createPlan($request);
                // $planId = (isset($subscriptionData['planId'])) ? $subscriptionData['planId'] : NULL;
                $subscriptionId = (isset($subscriptionData['subscriptionId'])) ? $subscriptionData['subscriptionId'] : null;
            }
            if ($planType == 2) {
                $final_payable_price = isset($request['price']) && !empty($request['price']) ? $request['price'] : 0.00;
            } else {
                $final_payable_price = isset($validated['final_payable_price']) && !empty($validated['final_payable_price']) ? $validated['final_payable_price'] : 0.00;
            }
            DB::beginTransaction();
            $courseplan = CoursePlan::create([
                'course_id' => $validated['course_id'],
                'plan_type' => $planType,
                'plan_name' => isset($validated['plan_name']) && !empty($validated['plan_name']) ? allowWhiteSpace($validated['plan_name']) : null,
                'list_price' => isset($validated['list_price']) && !empty($validated['list_price']) ? $validated['list_price'] : 0.00,
                'final_payable_price' => $final_payable_price,
                'course_limit' => $limit_course,
                'is_fixed_date' => $is_fixed_date,
                'access_value' => $access_value,
                // 'price' => $final_payable_price,
                'bill_learner_every' => isset($request['bill_learner_every']) && !empty($request['bill_learner_every']) ? $request['bill_learner_every'] : '',
                'calendar' => isset($request['calendar']) ? $request['calendar'] : 0,
                'plan_id' => $planId, // razorpay PaymentID
                // 'setup_fee' => isset($request['setup_fee']) && !empty($request['setup_fee']) ? $request['setup_fee'] : 0,
                // 'is_trial_fee_included' => isset($request['is_free_trial_included']) && $request['is_free_trial_included']== 'on' ? 1 : 0,
                'status' => request()->has('status') ? 1 : 0,
                'created_by' => auth()->id(),
                'renewing_subscriptions_id' => $request->renewing_subscriptions,
            ]);
            $courseplanId = $courseplan->id;
            $this->setPriceId($validated['course_id'], $courseplanId);
            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 'success',
                'message' => 'Plan added successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'code' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
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
        // dd($request->all());
        session(['admin_add_plan' => true]);
        $coursePlan = CoursePlan::findOrFail($request->edit_plan_id);
        try {
            // $planType = NULL;
            // // $planId = $coursePlan->plan_id ??'null';
            // if (isset($request['selectedPlan']) && !empty($request['selectedPlan'])) {
            //     if ($request['selectedPlan'] == 'free') {
            //         $planType = CoursePlan::PLAN_FREE;
            //     } elseif ($request['selectedPlan'] == 'one-time') {
            //         $planType = CoursePlan::PLAN_ONE_TIME_PAYMENT;
            //     } elseif ($request['selectedPlan'] == 'recurring') {
            //         $planType = CoursePlan::PLAN_RECURRING;
            //     }
            // }
            // $name = isset($request['name']) && !empty($request['name']) ? $request['name'] : '';
            if(isset($request['limit_course']) && $request['limit_course'] == 'on'){
            $limit_course = 1;
            // if(isset($request['fixed_date']) && $request['fixed_date'] != "on" && (!empty($request['edit_fixed_date']) || $request['edit_fixed_date'] != NULL )){
            //     $is_fixed_date = 1;
            //     $access_value = $request['edit_fixed_date'];
            //     // dd("ok");
            // } else if(isset($request['edit_fixed_days']) && (!empty($request['edit_fixed_days']) || $request['edit_fixed_days'] != NULL && $request['fixed_date'] == "on")){
            //     $is_fixed_date = 2;
            //     $access_value = $request['edit_fixed_days'];
            // }else{
            //     $is_fixed_date = 0;
            //     $access_value = NULL;
            // }
            $is_fixed_date = 0;
            $access_value = NULL;
            if($request->fixed_date=="fixed_date") {
            // dd("1");
            $is_fixed_date = 1;
            $access_value = $request->edit_fixed_date;
            }
            if($request->fixed_date=="fixed_day") {
            // dd("2");
            $is_fixed_date = 2;
            $access_value = $request->edit_fixed_days;
            }

            } else{
            $limit_course = 0;
            $is_fixed_date = 0;
            $access_value = NULL;
            }
            /*// for recurring only if plan is not created
            if($planType == CoursePlan::PLAN_RECURRING && $coursePlan->plan_id == NULL){
            // create Plan
            $common = new CommonController;
            $subscriptionData = $common->createPlan($request);
            $planId = (isset($subscriptionData['planId'])) ? $subscriptionData['planId'] : 'null';
            }
            if($planType == CoursePlan::PLAN_RECURRING){
            // $edit_final_payable_price = isset($request['edit_price']) && !empty($request['edit_price']) ? $request['edit_price'] : 0.00;
            $edit_final_payable_price = $coursePlan->final_payable_price;
            } else{
            $edit_final_payable_price = isset($request['edit_final_payable_price']) && !empty($request['edit_final_payable_price']) ? $request['edit_final_payable_price'] : 0.00;
            }*/
            // DB::enableQueryLog();Recurring subscription
            DB::beginTransaction();
            // $coursePlan->course_id = request()->has('edit_course_id') ? $request->edit_course_id : NULL;
            // $coursePlan->plan_type = $planType;
            $coursePlan->plan_name = request()->has('plan_name') ? $request->plan_name : null;
            // $coursePlan->list_price = request()->has('edit_list_price') ? $request->edit_list_price : 0.00;
            // $coursePlan->final_payable_price = $edit_final_payable_price;
            $coursePlan->course_limit = $limit_course;
            // $coursePlan->name = $name;
            // $coursePlan->is_fixed_date = $is_fixed_date;
            // $coursePlan->access_value = $access_value;
            // $coursePlan->price = isset($request['edit_price']) && !empty($request['edit_price']) ? $request['edit_price'] : 0.00;
            // $coursePlan->bill_learner_every = isset($request['bill_learner_every']) && !empty($request['bill_learner_every']) ? $request['bill_learner_every'] : NULL;
            // $coursePlan->calendar = $request['calendar'];
            // $coursePlan->plan_id = $planId; // razorpay PaymentID
            // $coursePlan->setup_fee = isset($request['setup_fee']) && !empty($request['setup_fee']) ? $request['setup_fee'] : 0;
            // $coursePlan->is_trial_fee_included = isset($request['is_free_trial_included']) && $request['is_free_trial_included']== 'on' ? 1 : 0;
            $coursePlan->status = request()->has('status') ? 1 : 0;
            $coursePlan->updated_by = auth()->id();
            $coursePlan->save();

            // $courseplanId = $request->edit_plan_id;
            // $this->setPriceId($request->edit_course_id,$courseplanId);

            // dd(DB::getQueryLog());
            DB::commit();
            // $notification['type'] = "sweet-alert";
            // $notification['status'] = "success";
            // $notification['title'] = "Success";
            // $notification['msg'] = "Plan updated successfully";
            return response()->json([
                'success' => true,
                'code' => 'success',
                'message' => 'Plan added successfully',
            ], 200);
            // return redirect()->back()->with('notification', $notification);
        } catch (\Exception $e) {
            DB::rollBack();
            // $notification['type'] = "sweet-alert";
            // $notification['status'] = "error";
            // $notification['title'] = "Error";
            // $notification['msg'] = $e->getMessage();
            // return redirect()->back()->with('notification', $notification);
            return response()->json([
                'success' => false,
                'code' => 'error',
                'errors' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = CoursePlan::where('id', $id)->firstOrFail();
        $page->delete();
        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully!',
        ], 200);

    }

    /**
     * Course plan listing where not deleted
     * @return toJSON response to DataTable
     */
    public function getCoursePlans($id)
    {
        $query = CoursePlan::join('courses as c', 'c.id', '=', 'course_plans.course_id')
            ->whereNull('course_plans.deleted_at')
            ->where('course_id', $id)
            ->select('course_plans.id', 'course_id', 'plan_type', 'plan_name', 'list_price', 'final_payable_price', 'course_limit', 'is_fixed_date', 'access_value', 'bill_learner_every', 'calendar', 'setup_fee', 'is_trial_fee_included', 'course_plans.status', DB::raw('0 as checkout_url'), 'c.default_web_price', 'c.status as publish_status', 'c.default_iphone_price', 'c.default_android_price', 'course_plans.order')
            ->orderBy('course_plans.order', 'ASC')
            ->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route('course-prices.delete', ['id' => $row->id]);
                $disabled = "";
                if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                    $disabled = " disabled_cls";
                }

                $button = "";
                $button .= '<a class="mx-1 ' . $disabled . ' editPlanModal"
                                id="edit-course-plan"
                                href="javascript:void(0)"
                                title="Edit"
                                data-toggle="modal"
                                data-url="' . route('course-prices.update', ['course_price' => $row->id]) . '"
                                data-id="' . $row->id . '"
                                data-plan-name="' . $row->plan_name . '"
                                data-course-id="' . $row->course_id . '"
                                data-plan-type="' . $row->plan_type . '"
                                data-list-price="' . $row->list_price . '"
                                data-final-payable-price="' . $row->final_payable_price . '" >
                                <i class="fas fa-edit"></i>
                            </a>';

                $button .= '<a class="mx-1 text-danger ' . $disabled . ' title="Delete" type="button" href="javascript:void(0)" onclick=confirmDelete("' . $url . '")><i class="fas fa-trash-alt"></i></a>';

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('plan_type', function ($row) {
                if (isset($row->plan_type)) {
                    if ($row->plan_type == CoursePlan::PLAN_FREE) {
                        return CoursePlan::FREE;
                    } elseif ($row->plan_type == CoursePlan::PLAN_ONE_TIME_PAYMENT) {
                        return CoursePlan::ONE_TIME_PAYMENT;
                    } elseif ($row->plan_type == CoursePlan::PLAN_RECURRING) {
                        return CoursePlan::RECURRING_SUBSCRIPTION;
                    }
                }
                return 'No Plan';
            })
            ->editColumn('access_value', function ($row) {
                if ($row->access_value == "" && $row->plan_type == 0) {
                    return "Free";
                } else if ($row->access_value == "" && $row->plan_type == 1) {
                    return "Lifetime";
                } else {

                    return $row->access_value;
                }

            })
            ->editColumn('barcode_checkout_url', function ($row) {
                // if($row->is_fixed_date==2 || $row->is_fixed_date==0){
                //         $disabled="";
                //         $url ="";
                //         if(empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING){
                //             $disabled=" disabled_cls";
                //         }
                //         $paymentUrl = route('checkout.index', ['planId' => Crypt::encrypt($row->id)]);
                //         return '<a class="mx-1 text-olive'.$disabled.'" title="Copy Payment URL" type="button" href="javascript:void(0)" onclick=copyUrl("' . $paymentUrl . '")><i class="fas fa-copy"></i></a>';

                // } else
                // if($row->status==1) {
                if ($row->plan_type == 1 && ($row->course_limit == 1 || $row->course_limit == 0) && ($row->is_fixed_date == 1 || $row->is_fixed_date == 2 || $row->is_fixed_date == 0)) {

                    $btn = "";
                    $disabled = "";
                    if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                        $disabled = " disabled_cls";
                    }
                    $paymentUrl = route('checkout.index', ['planId' => Crypt::encrypt($row->id), "updated_new_coin" => 0, "coupon_id" => "0"]);
                    $btn = '<a class="mx-1 text-olive' . $disabled . '" title="Copy Payment URL" type="button" href="javascript:void(0)" onclick=copyUrl("' . $paymentUrl . '")><i class="fas fa-copy"></i></a>';

                    if ($row->is_fixed_date == 1) {
                        $fixedDate = Carbon::parse($row->access_value);

                        if ($fixedDate->isPast() == true && $fixedDate->isToday() == false) {
                            return "";
                        } else {
                            return $btn;
                        }
                    } else {
                        return $btn;
                    }

                }
                // }

                // }
            })
            ->editColumn('checkout_url', function ($row) {
                $disabled = "";
                // if($row->is_fixed_date==2 || $row->is_fixed_date==0){
                //     $disabled="";
                //     $url ="";
                //     if(empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING){
                //         $disabled=" disabled_cls";
                //     }
                //     $paymentUrl = route('new_checkout.index', ['planId' => Crypt::encrypt($row->id),"status"=> Crypt::encrypt("true")]);
                //     return ' <a class="mx-1 text-danger'.$disabled.'" title="Copy Payment URL" type="button" href="javascript:void(0)" onclick=copyUrl("' . $paymentUrl . '")><i class="fas fa-copy"></i></a>';

                // } else
                // if($row->status==1) {
                if ($row->plan_type == 1 && ($row->course_limit == 1 || $row->course_limit == 0) && ($row->is_fixed_date == 1 || $row->is_fixed_date == 2 || $row->is_fixed_date == 0)) {

                    $btn = "";
                    $disabled = "";

                    if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                        $disabled = " disabled_cls";
                    }
                    $paymentUrl = route('new_checkout.index', ['planId' => Crypt::encrypt($row->id), "status" => Crypt::encrypt("true"), "updated_new_coin" => 0, "coupon_id" => 0]);
                    $btn .= ' <a class="mx-1 text-danger' . $disabled . '" title="Copy Payment URL" type="button" href="javascript:void(0)" onclick=copyUrl("' . $paymentUrl . '")><i class="fas fa-copy"></i></a>';

                    if ($row->is_fixed_date == 1) {
                        $fixedDate = Carbon::parse($row->access_value);

                        if ($fixedDate->isPast() == true && $fixedDate->isToday() == false) {
                            return "";
                        } else {
                            return $btn;
                        }
                    } else {
                        return $btn;
                    }

                }
                // }

            })
            ->editColumn('default_web_price', function ($row) {
                $disabled = "";
                if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                    $disabled = " disabled_cls";
                }
                if ($row->status == 0) {
                    $disabled = " disabled_cls";
                }
                return Helper::isPriceActive("web", $row->id, $row->course_id, $row->default_web_price, $disabled);
            })
            ->editColumn('default_iphone_price', function ($row) {
                $disabled = "";
                if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                    $disabled = "disabled_cls";
                }
                if ($row->status == 0) {
                    $disabled = "disabled_cls";
                }
                return Helper::isPriceActive("iphone", $row->id, $row->course_id, $row->default_iphone_price, $disabled);
            })
            ->editColumn('default_android_price', function ($row) {
                $disabled = "";
                if (empty(config('settings.razorpay_status')) && $row->plan_type == CoursePlan::PLAN_RECURRING) {
                    $disabled = "disabled_cls";
                }
                if ($row->status == 0) {
                    $disabled = " disabled_cls";
                }
                return Helper::isPriceActive("android", $row->id, $row->course_id, $row->default_android_price, $disabled);
            })
            ->editColumn('status', function ($row) {
                return Helper::checkStatus($row->status);
            })
            ->addColumn('calendar', function ($row) {
                if ($row->calendar == 1) {
                    return "Week";
                }
                if ($row->calendar == 2) {
                    return "month";
                }
                if ($row->calendar == 3) {
                    return "Year";
                }
            })
            ->addColumn('access_value', function ($row) {
                if ($row->is_fixed_date == 1) {
                    return "<b>Up to</b> " . $row->access_value;
                } else {
                    return $row->access_value;
                }

            })
            ->rawColumns(['action', 'status', 'checkout_url', 'default_web_price', 'default_iphone_price', 'default_android_price', 'access_value', 'barcode_checkout_url'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Course plan ordering
     */
    public function coursePlanSortable(Request $request)
    {
        $order_data = $request->order;
        if (isset($order_data) && (!empty($order_data))) {
            foreach ($order_data as $key => $order_row) {
                $tbl_order['order'] = $order_row['position'];
                $tbl_order['id'] = $order_row['id'];
                $already_data = CoursePlan::where('id', $tbl_order['id'])->firstOrFail();
                if (!empty($already_data)) {
                    CoursePlan::where('id', $tbl_order['id'])->update(['order' => $tbl_order['order']]);
                } else {
                    CoursePlan::create([
                        'order' => $tbl_order,
                    ]);
                }
            }
            return true;
        }
    }

    public function getCoursePlanForEdit(Request $request)
    {
        $coursePlan = CoursePlan::where('id', $request->id)
            ->select('id', 'course_id', 'plan_type', 'plan_name', 'order', 'list_price', 'final_payable_price', 'course_limit', 'is_fixed_date', 'access_value', 'bill_learner_every', 'calendar', 'setup_fee', 'is_trial_fee_included', 'status', "renewing_subscriptions_id")
            ->firstOrFail();

        $renewingSubscriptions = RenewingSubscriptions::where("productId", $coursePlan->renewing_subscriptions_id)->first();
        $content = view('admin.courses.info.edit-course-price', compact('coursePlan', 'renewingSubscriptions'))->render();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'content' => $content,
        ], 200);
    }

    public function delete($id)
    {

        $notification = [];
        session(['admin_add_plan' => true]);
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $page = CoursePlan::where('id', $id)->firstOrFail();

        $course = Course::where("id", $page->course_id)->first();

        if ($course != null) {

            if ($course->default_web_price == $page->id) {
                $course->update(["default_web_price" => null]);
            }
            if ($course->default_android_price == $page->id) {
                $course->update(["default_android_price" => null]);
            }
            if ($course->default_iphone_price == $page->id) {
                $course->update(["default_iphone_price" => null]);
            }
        }

        ChapterInfo::whereIn('chapter_id', function ($query) use ($id, $page) {
            $query->select('id')
                ->from('chapters')
                ->where('plan_id', $page->id);
        })->Delete();

        \DB::table('chapters')
            ->where('plan_id', $page->id)
            ->Delete();

        $page->Delete();

        $Course_plan_count = CoursePlan::where('course_id', $page->course_id)->count();

        if ($Course_plan_count == 1) {
            $assgin_plan = CoursePlan::where('course_id', $page->course_id)->first();
            $course->update([
                "default_web_price" => $assgin_plan->id,
                "default_android_price" => $assgin_plan->id,
                "default_iphone_price" => $assgin_plan->id,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        return response()->json([
            'success' => true,
            'url' => 1,
            'message' => 'Plan deleted successfully!',
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setPlanForCourse(Request $request)
    {
        session(['admin_add_plan' => true]);
        $request->validate([
            'id' => 'required|filled',
            'type' => 'required|filled',
            'courseId' => 'required|filled',
        ]);
        if ($request->id > 0 && $request->courseId > 0 && $request->type != null) {
            if ($request->type == 'web') {
                $data = ['default_web_price' => $request->id];
            }
            if ($request->type == 'iphone') {
                $data = ['default_iphone_price' => $request->id];
            }
            if ($request->type == 'android') {
                $data = ['default_android_price' => $request->id];
            }
            Course::findOrFail($request->courseId)->update($data);
        }
        return response()->json(true, 200);
    }
    public function setPriceId($courseId, $planId)
    {
        session(['admin_add_plan' => true]);
        $course_plan = CoursePlan::where("course_id", $courseId)->get();
        if ($course_plan->count() == 1) {

            $course = Course::findOrFail($courseId);
            // if ((int) $course->default_web_price == 0) {
            $course->default_web_price = $planId;
            // }
            // if ((int) $course->default_iphone_price == 0) {
            $course->default_iphone_price = $planId;
            // }
            // if ((int) $course->default_android_price == 0) {
            $course->default_android_price = $planId;
            // }
            $course->save();
        }

    }
}
