<?php

namespace App\Http\Controllers\admin;

use App\Models\Log;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Settings;
use App\Models\CoursePlan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use \App\Models\Settings as SettingsModel;

class SettingsController extends Controller
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
        if (!$this->user->can('browse_settings')) abort(403);
        $pg_header = "settings";

        Log::create([
           "message"=> "Setting viewed by ".Auth::user()->name,
           "logable_id"=>Auth::user()->id,
           "logable_type"=>"-"
        ]);
        return view('admin.settings.list', compact('pg_header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pg_header = "Add Settings";
        if (!$this->user->can('add_settings')) abort(403);
        return view('admin.settings.add',compact("pg_header"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!$this->user->can('add_settings')) abort(403);
        $notification = [];
            if ($request->setting_type == 'number_coin') {
                // dd("");

                $validated = $request->validate([
                    // 'key' => 'required|filled|max:255|unique:settings',
                    'display_name' => 'required',
                    'setting_type' =>'required',
                    'value' => 'regex:/^\d*(\.\d{2})?$/',
                    'key' => 'required|filled|unique:settings,key'
                ]);
            }
        else if ($request->setting_type == 'file') {
            $validated = $request->validate([
                // 'key' => 'required|filled|max:255|unique:settings',
                'display_name' => 'required',
                'setting_type' =>'required',
                'value' => 'image|mimes:jpg,png,jpeg,gif,svg|max:8000',
                'key' => 'required|filled|unique:settings,key'
            ]);
        }
        else if($request->setting_type == 'boolean'){
            $validated = $request->validate([
                // 'key' => 'required|filled|max:255|unique:settings',
                'display_name' => 'required',
                'setting_type' =>'required',
                'value' => 'required',
                'boolean' => 'sometimes',
                'key' => 'required|filled|unique:settings,key'
            ]);
        }
        else if($request->setting_type == 'checkbox'){
            $validated = $request->validate([
                // 'key' => 'required|filled|max:255|unique:settings',
                'display_name' => 'required',
                'setting_type' =>'required',
                'platform' => 'sometimes',
                'boolean' => 'sometimes',
                'key' => 'required|filled|unique:settings,key'
            ]);
        }
        else{
            $validated = $request->validate([
                // 'key' => 'required|filled|max:255|unique:settings',
                'display_name' => 'required',
                'setting_type' =>'required',
                'value' => 'required',
                'key' => 'required|filled|unique:settings,key'
            ]);
        }
        $value = $request->value;
        if($request->has('platform')){
            $value=implode(',',$request->platform);
        }
        if ($request->setting_type == 'file') {
            $value = $request->value;
            $value = $value->store('admin/settings', 'public');
        }
        if ($request->setting_type == 'boolean') {
            if($request->has('value') && $request->value == "on"){
                $value = "1";
            }
            else{
                $value="0";
            }
        }
        $settings = SettingsModel::create([
            'key' => $request->key,
            'display_name' => allowWhiteSpace($request->display_name),
            'setting_type' => $request->setting_type,
            'value' => $value,
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Settings added successfully";
        return redirect('/backoffice/settings')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_settings')) abort(403);
        $settings = SettingsModel::withTrashed()->where('id', $id)->first();
        $created_at = date('d/m/Y H:i:s', strtotime($settings->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($settings->updated_at));
        $pg_header = "View Settings";
        return view('admin.settings.view', compact('settings','created_at','updated_at', 'pg_header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       if (!$this->user->can('edit_settings')) abort(403);
        $settings = SettingsModel::withTrashed()->where('id', $id)->first();
        $pg_header = "Edit Settings";

        return view('admin.settings.edit', compact('settings', 'pg_header'));
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
        if (!$this->user->can('edit_settings')) abort(403);
        $input = $request->all();
        if ($request->setting_type == 'number_coin') {
            $validated = $request->validate([
                'value' => 'regex:/^\d*(\.\d{2})?$/',
            ]);
        }
        $notification = [];
        $settings = SettingsModel::find($id);

        $validated = $request->validate([
            // 'key' => ['required', 'filled', Rule::unique('settings', 'key')->ignore($settings->id)],
            'display_name' => 'required',
            'setting_type' =>'required',
            // 'value' => 'required',
            // 'key' => 'required|unique:settings,key,'.$id
        ]);
        $value = $request->value;
        // $settings->key = $request->key;
        $settings->display_name = ($request->display_name);
        $settings->setting_type = $request->setting_type;
        if ($input['setting_type'] == 'file') {
            $path = $request->value;
            $value = $path->store('admin/settings', 'public');
        }
        if ($input['setting_type'] == 'boolean') {
            if ($request->has('value') && $request->value == "on") {
                $value = 1;
            } else {
                $value = 0;
            }
        }
        if($request->has('platform')){
            $settings->value=implode(',',$request->platform);
        }
        else{
            $settings->value = $value;
        }

        $settings->save();
        //check if razorpay is active disable instamojo option and all course unpublished which has subscription plan active
        // $ids = [];
        // $changeInactiveFlag = 0;
        // $changeActiveFlag = 0;
        // $selectAllCourse = Course::whereHas('plans', function ($query) {
        //     $query->where('plan_type',CoursePlan::PLAN_RECURRING);
        //    })->select(DB::raw('group_concat(id) as ids'))->get()->first();
        //    if(!empty($selectAllCourse)){
        //         if(isset($selectAllCourse['ids']) && !empty($selectAllCourse['ids'])){
        //             $ids = explode(",",$selectAllCourse['ids']);
        //         }
        //         if($settings->key == "instamojo_status" &&  $settings->value == 1){
        //             Settings::where("key", "razorpay_status")
        //             ->update([
        //                 'value' => 0,
        //             ]);
        //             $changeInactiveFlag = 1;
        //         }
        //         if($settings->key == "instamojo_status" &&  $settings->value == 0){
        //             Settings::where("key", "razorpay_status")
        //             ->update([
        //                 'value' => 1,
        //             ]);
        //             $changeActiveFlag = 1;
        //         }
        //         if($settings->key == "razorpay_status" &&  $settings->value == 1){
        //             Settings::where("key", "instamojo_status")
        //             ->update([
        //                 'value' => 0,
        //             ]);
        //             $changeActiveFlag = 1;
        //         }
        //         if($settings->key == "razorpay_status" &&  $settings->value == 0){
        //             Settings::where("key", "instamojo_status")
        //             ->update([
        //                 'value' => 1,
        //             ]);
        //             $changeInactiveFlag = 1;
        //         }
        //         if($changeInactiveFlag == 1) { // when instamojo enabled then recurring plan will be deactivaiing deafult
        //             if(!empty($ids)){
        //                 Course::whereIn("id", $ids)
        //                     ->update([
        //                         'status' => 0,
        //                     ]);
        //             }
        //         }
        //         if($changeActiveFlag == 1) {
        //             if(!empty($ids)){
        //                 Course::whereIn("id", $ids)
        //                     ->update([
        //                         'status' => 1,
        //                     ]);
        //             }
        //         }
        //    }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Settings updated successfully";
        return redirect('/backoffice/settings')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_settings')) abort(403);
        SettingsModel::destroy($id);
        $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Setting deleted successfully";
            return redirect()->back()->with('notification', $notification);
    }

    public function restore($id)
    {
        if (!$this->user->can('restore_settings')) abort(403);
        SettingsModel::withTrashed()->where('id',$id)->restore();
        $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Setting restored successfully";
            return redirect()->back()->with('notification', $notification);
    }

    public function get_settings(Request $request)
    {
        if (!$this->user->can('browse_settings')) abort(403);

        $query = DB::table('settings')->whereNull('deleted_at')
        ->when($request->has('type') && !empty($request->type), function($query) use($request)
        {
            $general = ['address','contact_no','message','logo','favicon','top_mobile_number','contact_us','opening_hours_weekdays','closing_hours_weekdays','opening_hours_sat','closing_hours_sat','footer_about_content','footer_addr_company_name','footer_addr_company_addr','footer_contact_email','footer_contact_insta_id','footer_contact_facebook_id','footer_insta_followers','footer_facebook_followers','footer_youtube_followers','footer_linkedin_followers','footer_insta_icon','footer_facebook_icon','footer_youtube_icon','footer_linkedin_icon','footer_twitter_icon','backend_favicon','maintenance_mode','login_logo','homepage_feature_course','home_top_category_description','home_top_category_title','home_about_title','home_about_description','home_trusted_by_subheading','home_blog_heading','home_slider_visibility','home_featured_courses_visibility','home_top_free_visibility','home_course_category_visibility','home_trusted_by_visibility','home_user_love_visibility','home_testimonial_visibility','home_about_us_visibility','home_top_blogs_visibility','home_connect_visibility','invoice_from','maximum_user_device','mail_logo'];
            $payment = ['razorpay_status','razorpay_key','razorpay_secret','razorpay_sandbox_key','razorpay_sandbox_secret','instamojo_status','instamojo_label','instamojo_description','instamojo_client_id','instamojo_client_secret','instamojo_sandbox_client_id',
            'instamojo_sandbox_client_secret','instamojo_sandbox','razorpay_sandbox'];
            $third_party = ['whats_app_token','plivo_auth_id','plivo_auth_token','plivo_send_mobile','whatsapp_contact_no','vdocipher_key','facebook_app_id','facebook_app_secret','google_client_id','google_client_secret','twitter_client_id','twitter_client_secret','instagram_client_id','instagram_client_secret','linkedin_client_id','linkedin_client_secret','youtube_client_id','youtube_client_secret','facebook_status','facebook_url','google_status','twitter_status','twitter_url','instagram_status','instagram_url','linkedin_status','linkedin_url','youtube_status','youtube_url'];

            if($request->type == "1"){
                return $query->whereIn('key',$general);
            }
            elseif($request->type == "2"){
                return $query->whereIn('key',$payment);
            }
            elseif($request->type == "3"){
                return $query->whereIn('key',$third_party);
            }
        })
        ->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });
        $query=$query->transform(function($val, $key){
            (($val->setting_type != "file") && (strlen($val->value) > 40)) ? $val->value = substr($val->value,0,40).'...':$val->value;
            return $val;
        })->all();
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("settings.destroy", ["setting" => $row->id]);
                $button = "";
                if ($this->user->can('read_settings')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/settings/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_settings')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/settings/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_settings')) {
                        if($row->key != 'tax_rate'){
                            $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        }
                    }
                } else {
                    if ($this->user->can('restore_settings')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/settings/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('value', function($row){
                if($row->setting_type == "boolean"){
                    return Helper::checkEnable($row->value);
                }
                else if($row->setting_type == 'file'){
                    return  '<a class="setting_default" href="'.asset(Storage::url($row->value)).'" target="_blank"><img src="'.asset(Storage::url($row->value)).'"></a>';
                }
                else
                {
                    return $row->value;
                }
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['value','action','created_at'])
            ->addIndexColumn()->toJson();
    }

    public function get_settings_deleted()
    {
        if (!$this->user->can('browse_settings')) abort(403);
        $query = DB::table('settings')->whereNotNull('deleted_at')->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("settings.destroy", ["setting" => $row->id]);
                $button = "";
                $deleteurl = route("settings.delete", ["id" => $row->id]);
                /* if ($this->user->can('read_settings')) {
                    $button .= '<a class="mx-1" href="' . url('backoffice/settings/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_settings')) {
                    $button .= '<a class="mx-1" href="' . url('backoffice/settings/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                } */
                if ($row->deleted_at) {
                    if ($this->user->can('delete_settings')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                    if ($this->user->can('restore_settings')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/settings/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_settings')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/settings/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })->editColumn('value', function($row){
                if($row->setting_type == "boolean"){
                    return Helper::checkEnable($row->value);
                }
                else if($row->setting_type == 'file'){
                    return  '<a class="setting_default" href="'.asset(Storage::url($row->value)).'" target="_blank"><img src="'.asset(Storage::url($row->value)).'"></a>';
                }
                else
                {
                    return $row->value;
                }
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['value','action','created_at'])->addIndexColumn()->toJson();
    }

    public function bulk_del(Request $request)
    {
        if (!$this->user->can('delete_settings')) abort(403);
        if (!empty($request->bd) > 0) {
            SettingsModel::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Settings deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No settings selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    public function restore_all(Request $request)
    {

        if (!$this->user->can('restore_settings')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            SettingsModel::withTrashed()->where('id',$value)->restore();
        }
        //SettingsModel::onlyTrashed()->restore(array_keys($request->all()));
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Settings restored successfully";
        return redirect()->back()->with('notification', $notification);
    }


    public function delete($id)
    {
        if (!$this->user->can('delete_settings')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $settings= SettingsModel::withTrashed()->where('id', $id)->firstOrFail();
            $settings->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Settings deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_settings')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $settings= SettingsModel::withTrashed()->where('id', $id)->firstOrFail();
                $settings->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Settings deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Setting selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
