<?php

namespace App\Http\Controllers\admin;

use App\Models\Role;
use App\Models\User;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\UserCoin;
use App\Models\Wishlist;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\ChapterInfo;
use App\Models\Instructors;
use App\Models\RatingReview;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use App\Models\UserCourseProgress;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class InstructorsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            // @preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name);
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
        $role = User::INSTRUCTOR;
        $pg_header = 'Instructors';

        if (!$this->user->can('browse_instructors')) abort(403);

        if(view()->exists('admin.instructor.list')){
            return view('admin.instructor.list', compact('pg_header'));
        } abort(404);
    }

    public function get_instructor(Request $request)
    {
        if (!$this->user->can('browse_instructors')) abort(403);
        // dd($request['order'][0]['dir']);
        $role = [User::INSTRUCTOR];
        $query = DB::table('model_has_roles')->join('users', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', 'model_has_roles.role_id')
            ->join('instructors', 'instructors.user_id', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.profile_picture', 'users.created_at AS created_at', 'users.deleted_at', 'model_has_roles.role_id', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'), 'instructors.designation', 'instructors.bio', 'instructors.facebook_follower', 'instructors.instagram_follower', 'instructors.youtube_follower', 'instructors.twitter_follower', 'instructors.id as instructorsId')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', $role);
            $query->groupBy("users.id");
        if($request->order ==null){
            $query->orderBy('created_at', 'desc');
        }
        $query->get();
        // dd($query);
        // $query =  Helper::getInstructure();
        // $role = [User::INSTRUCTOR];
        // $query = DB::table('model_has_roles')->join('users','users.id','model_has_roles.model_id')
        // ->join('roles','roles.id','model_has_roles.role_id')
        // ->join('instructors','instructors.user_id','users.id')
        // ->select('users.id','users.name','users.email','users.profile_picture','users.created_at AS created_at','users.deleted_at','model_has_roles.role_id',DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'),'instructors.designation','instructors.bio','instructors.facebook_follower','instructors.instagram_follower','instructors.youtube_follower','instructors.twitter_follower','instructors.id as instructorsId')
        // ->whereNull('users.deleted_at')
        // ->whereIn('roles.name', $role)
        // //->whereNotIn('users.id',[Auth::id()])
        // ->groupBy("users.id")->latest()->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $permission_data = $this->get_permissions();
                $path = 'backoffice/instructors/';
                $url = route("instructors.destroy", ["instructor" => $row->id]);
                $button = "";
                if($this->user->can('read_instructors')){
                    $button .= '<a class="mx-1" target="_blank" title="View" href="'.url('instructor/' . $row->id).'"><i class="fas fa-eye"></i></a>';
                    if ($this->user->can('edit_instructors')) {
                        $button .= '<a class="mx-1" title="Edit" href="' . url($path . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                    }
                }
                if (is_null($row->deleted_at)) {
                    // if(auth()->user()->hasRole('admin')){
                        if ($this->user->can('delete_instructors')) {
                            $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        }
                    // }
                } else {
                    if ($this->user->can('restore_instructors')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path.'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('profile_picture', function($row){
                $profilePictureURL = getImageIfExists($row->profile_picture, user_img_default());
                return '<a href="'.$profilePictureURL.'" target="_blank"><img src="'.$profilePictureURL.'" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','profile_picture','created_at'])
            ->toJson();
    }

    public function get_instructor_deleted()
    {
        // Only admin can show-deleted 'Sub Admin/Instructor'
        if (!$this->user->can('browse_instructors')) abort(403);

        $query =  Helper::getInstructorDeleted();

        $query->map(function($value){
            $value->created_at = Helper::date_format($value->created_at);
        });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $path = 'backoffice/instructors/';
                $deleteurl = route("instructors.delete", ["id" => $row->id]);
                $button = "";
                $deleteurl = route("subadmin.delete", ["id" => $row->id]);
                    if ($this->user->can('delete_users')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                  }

                    if ($this->user->can('restore_users')) {
                             $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path. 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }

                    return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('profile_picture', function($row){
                $profilePictureURL = getImageIfExists($row->profile_picture, user_img_default());
                return '<a href="'.$profilePictureURL.'" target="_blank"><img src="'.$profilePictureURL.'" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','profile_picture','created_at'])
            ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('read_instructors')) abort(403);
        $pg_header = 'Add Instructor';
        $role = User::INSTRUCTOR;
        $users = User::get()->groupBy('module_name')->toArray();
        $roles = Role::where('name', $role)->get()->first()->toArray();
        if(view()->exists('admin.instructor.add')){
            return view('admin.instructor.add', compact('users', 'roles', 'pg_header'));
        } abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Only admin can create new 'Sub Admin/Instructor'
        // @preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name);

        \Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });

        if (!$this->user->can('add_instructors')) abort(403);
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'email' => 'required|filled|regex:/(.+)@(.+)\.(.+)/i|email|unique:users,email',
            // 'password' => 'required_with:password_confirmation|confirmed|min:8',
            // 'password' => 'required|without_spaces|min:8|filled',
            'password' => 'required|min:8|without_spaces|max:16|filled|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#>])[A-Za-z\d@$!%*?&#>]+$/',

            'password_confirmation' => 'required|without_spaces|min:8|required_with:password|same:password',
            // 'password' => 'required|filled|confirmed|min:8',

            'roles' => 'required_unless:user_type,users|filled',
            'profile_picture' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
            // 'is_notify_limit' => 'nullable|numeric',
            // 'facebook_follower' => 'numeric'
        ],[
            'roles.required_unless' => "the role field is required",
            "password_confirmation.without_spaces" => "The whitespace is not allowed",
            "password.without_spaces" => "The whitespace is not allowed",
            // "profile_picture.max"=>"The profile picture must less than 500KB.",
            "password_confirmation.min"=>"The confirmation password must be at least 8 characters.",
            "password_confirmation.same"=>"The confirmation password and password should be same.",
            "password.regex"=>"The :attribute must contain at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# )."
        ]);

        try {
            $notification = [];
            DB::beginTransaction();
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'created_by' => auth()->id()
                ]);

                if(request()->hasFile('profile_picture')){
                    $file_path = Storage::putFileAs('profile_pic', $request->profile_picture, $user->id.'_'.$request->profile_picture->getClientOriginalName());
                    $user->update(['profile_picture' => $file_path]);
                }

                // Role assign as Instructure
                if (isset($request->roles) && $request->roles != null) {
                    $user->assignRole(array_keys($request->roles));
                }

                if($user->id){
                    $instructor = Instructors::create([
                        'user_id' => $user->id,
                        'designation' => request()->has('designation') ? $request->designation : '',
                        'bio' => request()->has('bio') ? $request->bio : '',
                        'facebook_follower' => request()->has('facebook_follower') ? $request->facebook_follower : '',
                        'instagram_follower' => request()->has('instagram_follower') ? $request->instagram_follower : '',
                        'twitter_follower' => request()->has('twitter_follower') ? $request->twitter_follower : '',
                        'youtube_follower' => request()->has('youtube_follower') ? $request->youtube_follower : '',
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'allow_instructor' => $request->allow_instructor,
                        'is_notify_limit' => 4
                    ]);
                }

            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Instructor registered successfully";

        } catch (\Exception $e) {
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = 'Something went wrong.';
        }
        return redirect('backoffice/instructors')->with('notification', $notification);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Only admin can edit 'Sub Admin/Instructor'
        if (!$this->user->can('edit_instructors')) abort(403);
        $role = User::INSTRUCTOR;
        $user = User::instructorList()->findOrFail($id);
        $roles = Role::where('name', $role)->get()->first()->toArray();
        $assigned_roles = $user->getRoleNames();

        if(view()->exists('admin.instructor.edit')){
            return view('admin.instructor.edit', compact('user', 'roles', 'assigned_roles'));
        } abort(404);
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
        // @preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name);
        if (!$this->user->can('edit_instructors')) abort(403);
        $user = User::with('instructure')->whereNull('deleted_at')->findOrFail($id);
        $notification = [];

        \Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'email' => ['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('users', 'email')->ignore($user->id)],
            // 'password' => 'sometimes|nullable|min:8',
            // 'roles' => 'required_unless:user_type,users|array',
            'is_notify_limit' => 'nullable|numeric',
            'password' => 'sometimes|without_spaces|nullable|min:8|max:16|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/',
            'profile_picture' => 'mimes:jpeg,png,jpg,svg|sometimes|present'
        ],[
            // 'roles.required_unless' => "Role is required",
            "password.without_spaces" => "The whitespace is not allowed",
            // "profile_picture.max"=>"The profile picture must less than 500KB."
            "password.regex"=>"The :attribute must contain at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# )."
        ]);

        //$roles = Auth::user()->getRoleNames();
        // $file_path = $user->profile_picture;
        // if(request()->hasFile('profile_picture')){
        //     if(!empty($user->profile_picture)){
        //         Storage::delete($user->profile_picture);
        //     }
        //     $file_path = Storage::putFileAs('profile_pic', $request->profile_picture, $user->id.'_'.$request->profile_picture->getClientOriginalName());
        // }

        $profilePic = $user->profile_picture;
        if(request()->hasFile('profile_picture')){
            if(!empty($user->profile_picture)){
                Storage::delete($user->profile_picture);
            }
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            // File upload location
            $location = $file->storeAs('profile_pic', $filename);
            // Update Image
            $profilePic = $location;
        }
        if ($request->input('remove_banner_image') == '1') {
            if ($user && !empty($profilePic)) {
                Storage::delete($profilePic);
                $profilePic = null;
                $user->profile_picture = $profilePic;
                $user->save();
            }
        }
        // if ($request->input('profile_picture') === null) {
        //     // Delete the current profile picture
        //     Storage::delete($profilePic);
        //     $profilePic = null;
        // }

        if ($request->has('password') && !empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        else{
            $user->password = $request->old_password;
        }

        if ($request->has('roles') && is_array($request->roles) && count($request->roles) > 0) {
            $roles_to_sync = Role::whereIn('id',array_keys($request->roles))->pluck('name')->toArray();
            $user->syncRoles($roles_to_sync);
        }


        User::where('id', $user->id)->update([
            'name' => request()->has('name') ?preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name) : '',
            'email' => request()->has('email') ? $request->email : '',
            'password' => $user->password,
            'profile_picture' => $profilePic,
            'updated_by' => auth()->id()
        ]);

        $instructur = Instructors::where('user_id', $user->id)->update([
            'designation' => request()->has('designation') ? $request->designation : '',
            'bio' => request()->has('bio') ? $request->bio : '',
            'facebook_follower' => request()->has('facebook_follower') ? $request->facebook_follower : '',
            'instagram_follower' => request()->has('instagram_follower') ? $request->instagram_follower : '',
            'twitter_follower' => request()->has('twitter_follower') ? $request->twitter_follower : '',
            'youtube_follower' => request()->has('youtube_follower') ? $request->youtube_follower : '',
            'updated_at' => now(),
            'allow_instructor' => $request->allow_instructor,
            'is_notify_limit' => $request->is_notify_limit
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Instructor updated successfully";

        if(auth()->user()->roles[0]->name!="instructor") {
            return redirect('backoffice/instructors')->with('notification', $notification);
        } else {
            return redirect()->back()->with('notification', $notification);

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) // single delete
    {

        if (!$this->user->can('delete_instructors')) abort(403);

        $notification = [];
        $user = User::where('id', $id)->first();
        $course = Course::where("instructor_id",$user->id)->get();
        if($course) {
        foreach($course as $val){
            $chapter = Chapter::where("course_id",$val->id)->get();
            if($chapter) {
                foreach($chapter as $chapt_val) {
                    ChapterInfo::where("chapter_id",$chapt_val->id)->delete();
                    UserCourseProgress::where("chapter_id",$chapt_val->id)->delete();
                }
            }
            $course_plan = CoursePlan::where("course_id",$val->id)->delete();
            //$course_plan->each->delete();
            $course_cat = CourseCategory::where("course_id",$val->id)->delete();
           // $course_cat->each->delete();
            $chapter = Chapter::where("course_id",$val->id)->delete();
           // $chapter->each->delete();
            $rating = RatingReview::where("course_id",$val->id)->delete();
            ///$rating->each->delete();
            $wishlist = Wishlist::where("course_id",$val->id)->delete();
           // $wishlist->each->delete();
            $user_course = UserCourse::where("course_id",$val->id)->delete();

            $user_course = UserCoin::where("course_id",$val->id)->delete();
            //$user_course->each->delete();
        }
        $user->delete();
        $course->each->delete();
    }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Instructor deleted successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function restore($id)
    {

        if (!$this->user->can('restore_instructors')) abort(403);
        $notification = [];

        // $user = User::withTrashed()->where('id', $id)->firstOrFail();
        // $user->restore();

        $user = User::where('id', $id)->withTrashed()->first();
        // dd($user);
        $course = Course::where("instructor_id",$user->id)->withTrashed()->get();
        // dd($course);
        foreach($course as $val){
            $course_plan = CoursePlan::where("course_id",$val->id)->withTrashed()->get();
            $course_plan->each->restore();
            $course_cat = CourseCategory::where("course_id",$val->id)->withTrashed()->get();
            $course_cat->each->restore();
            $rating = RatingReview::where("course_id",$val->id)->withTrashed()->get();
            $rating->each->restore();
        }
        $user->restore();
        $course->each->restore();


        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Instructor restored successfully";

        return redirect()->back()->with('notification', $notification);
    }


    public function restore_all(Request $request)
    {
       // dd("");
        $notification = [];
        if (!$this->user->can('restore_instructors')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $id) {
            // $user = User::withTrashed()->where('id', $value)->firstOrFail();
            // $user->restore();
          //  foreach ($request->bd as $id => $value) {
                $user = User::where('id', $id)->withTrashed()->first();
                $course = Course::where("instructor_id",$user->id)->withTrashed()->get();
                foreach($course as $val){
                    $course_plan = CoursePlan::where("course_id",$val->id)->withTrashed()->get();
                    $course_plan->each->restore();
                    $course_cat = CourseCategory::where("course_id",$val->id)->withTrashed()->get();
                    $course_cat->each->restore();
                    $rating = RatingReview::where("course_id",$val->id)->withTrashed()->get();
                    $rating->each->restore();
                }
                $user->restore();
                $course->each->restore();

           // }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Instructor restored successfully";
        return redirect()->back()->with('notification', $notification);
    }
    public function get_permissions()
    {
        $role = User::INSTRUCTOR;
        $read_permission = 'read_instructors';
        $edit_permission = 'edit_instructors';
        $delete_permission = 'delete_instructors';
        $restore_permission = 'restore_instructors';

        return [
            'role'=> $role,
            'read_permission' => $read_permission,
            'edit_permission' => $edit_permission,
            'delete_permission' => $delete_permission,
            'restore_permission' => $restore_permission
        ];
    }

    public function delete($id)
    {

        if (!$this->user->can('delete_instructors')) abort(403);
        $notification = [];
        $user= User::withTrashed()->where('id', $id)->firstOrFail();
        $user->forceDelete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "instructor deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulkHardDel(Request $request) {
        // dd($request->all());
        if (!$this->user->can('delete_courses')) {
            abort(403);
        }

        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {

                $user = User::where('id', $id)->withTrashed()->first();
                $course = Course::where("instructor_id",$user->id)->withTrashed()->get();
                if($course) {
                    foreach($course as $val){
                        $course_plan = CoursePlan::where("course_id",$val->id)->withTrashed()->get();
                        $course_plan->each->forceDelete();
                        $course_cat = CourseCategory::where("course_id",$val->id)->withTrashed()->get();
                        $course_cat->each->forceDelete();
                        $rating = RatingReview::where("course_id",$val->id)->withTrashed()->get();
                        $rating->each->forceDelete();
                    }
                    $user->forceDelete();
                    $course->each->forceDelete();
                }

            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "instructor deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Course selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function bulkDel(Request $request) {
        dd($request->all);
    }
}
