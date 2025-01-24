<?php

namespace App\Http\Controllers\admin;

use Validator;
use App\Models\Role;
use App\Models\User;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\UserCoin;
use App\Models\CoursePlan;
use App\Models\SupportChat;
use App\Models\RatingReview;
use Illuminate\Http\Request;
use App\Models\CourseCategory;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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
        $url = $_SERVER['PHP_SELF'];

        if (str_contains($url, 'subadmin')) {
            $role = 'subadmin';
            $pg_header = 'Other Users';
            if (!$this->user->can('browse_subadmin')) {
                abort(403);
            }

        }
        //  else if (str_contains($url, 'instructor')){
        //     $role = 'instructor';
        //     $pg_header = 'Instructors';
        //     if (!$this->user->can('browse_instructors')) abort(403);

        // }
        else {
            $role = 'admin';
            $pg_header = 'Users';
            if (!$this->user->can('browse_users')) {
                abort(403);
            }

        }

        if (view()->exists('admin.users.list')) {
            return view('admin.users.list', compact('pg_header'));
        }abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_users')) {
            abort(403);
        }

        $url = \Request::server('HTTP_REFERER');
        if (str_contains($url, 'subadmin')) {
            $role = 'subadmin';
            $pg_header = 'Add Other Users';
        } else {
            $role = 'admin';
            $pg_header = 'Add User';
        }
        $users = User::get()->groupBy('module_name')->toArray();
        $roles = Role::whereNotIn('name', ["admin", "instructor"])->get();
        if (view()->exists('admin.users.add')) {
            return view('admin.users.add', compact('users', 'roles', 'pg_header'));
        }abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd(preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $request->name));

        Validator::extend('without_spaces', function ($attr, $value) {
            return preg_match('/^\S*$/u', $value);
        });

        if (!$this->user->can('add_users')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'email' => 'required|filled|regex:/(.+)@(.+)\.(.+)/i|email|unique:users,email|without_spaces',
            'password' => 'required|without_spaces|min:8|max:16|filled|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#>])[A-Za-z\d@$!%*?&#>]+$/',
            'password_confirmation' => 'required|required_with:password|same:password',
            'roles' => 'required_unless:user_type,users|filled',
            'profile_picture' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
        ], [
            'roles.required_unless' => "The role field is required",
            // "password_confirmation.without_spaces" => "The whitespace is not allowed",
            "password.without_spaces" => "The password field is required and must be at least 8 characters and not be greater than 16 characters , contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            'password.required' => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            // "profile_picture.max" => "The profile picture must be less than 500KB.",
            // "password_confirmation.min"=>"The confirmation password must be at least 8 characters.",
            // "password_confirmation.min"=>"The confirmation password must be at least 8 characters.",
            "password.regex" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.min" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.max" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
        ]);

        try {
            $notification = [];
            $roles = Auth::user()->getRoleNames();
            DB::beginTransaction();
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'created_by' => auth()->id(),
            ]);

            if (request()->hasFile('profile_picture')) {
                $file_path = Storage::putFileAs('profile_pic', $request->profile_picture, $user->id . '_' . $request->profile_picture->getClientOriginalName());
                $user->update(['profile_picture' => $file_path]);
            }
            if ($request->user_type == "users") {
                $user->assignRole("admin");
            } else {
                if (isset($request->roles) && $request->roles != null) {
                    $user->assignRole(array_keys($request->roles));
                }
            }
            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "User registered successfully";
            return redirect($this->get_path())->with('notification', $notification);

        } catch (\Exception $e) {
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
        if (!$this->user->can('read_users') && !$this->user->can('read_subadmin')) {
            abort(403);
        }

        $user = User::findOrFail($id);
        $assigned_roles = $user->getRoleNames()->toArray();
        $roles = implode(', ', $assigned_roles);
        if (view()->exists('admin.users.view')) {
            return view('admin.users.view', compact('user', 'roles'));
        }abort(404);
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
        if (!$this->user->can('edit_users')) {
            abort(403);
        }

        $url = \Request::server('HTTP_REFERER');
        if (str_contains($url, 'users')) {
            $role = ['admin'];
            // if (!$this->user->can('edit_subadmin')) abort(403);
        } else {
            $role = Role::whereNotIn('name', ['admin', 'instructor'])->pluck('name')->toArray();
            // if (!$this->user->can('edit_users')) abort(403);
        }

        $user = User::findOrFail($id);
        $roles = Role::whereIn('name', $role)->get();
        $assigned_roles = $user->getRoleNames();
        if (view()->exists('admin.users.edit')) {
            return view('admin.users.edit', compact('user', 'roles', 'assigned_roles'));
        }abort(404);
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

        // Only admin can update 'Sub Admin/Instructor'
        if (!$this->user->can('edit_users')) {
            abort(403);
        }

        $user = User::findOrFail($id);
        $notification = [];

        Validator::extend('without_spaces', function ($attr, $value) {
            return preg_match('/^\S*$/u', $value);
        });

        $validated = $request->validate([
            'name' => 'required|filled|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'email' => ['required', 'without_spaces', 'filled', 'regex:/(.+)@(.+)\.(.+)/i', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            // 'password' => 'sometimes|nullable|min:8|max:16',
            'password' => 'sometimes|without_spaces|nullable|min:8|max:16|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#>])[A-Za-z\d@$!%*?&#>]+$/',
            'roles' => 'required_unless:user_type,users|array',
            'profile_picture' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
        ], [
            'roles.required_unless' => "the role field is required",
            // "password_confirmation.without_spaces" => "The whitespace is not allowed",
            "password.without_spaces" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            // "profile_picture.max" => "The profile picture must be less than 500KB.",
            "password.regex" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.min" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",
            "password.max" => "The password field is required and must be at least 8 characters not be greater than 16 characters, contain no whitespace, and include at least one uppercase letter, one lowercase letter, one digit, and one special character ( @$!%*?&# ).",

        ]);
        $roles = Auth::user()->getRoleNames();
        $file_path = $user->profile_picture;
        if (request()->hasFile('profile_picture')) {
            if (!empty($user->profile_picture)) {
                Storage::delete($user->profile_picture);
            }
            $file_path = Storage::putFileAs('profile_pic', $request->profile_picture, $user->id . '_' . $request->profile_picture->getClientOriginalName());
        }

        if ($request->has('password') && !empty($request->password)) {
            $user->password = bcrypt($request->password);
        } else {
            $user->password = $request->old_password;
        }

        if ($request->user_type == "users") {
            $user->syncRoles("admin");
        } else {
            if ($request->has('roles') && is_array($request->roles) && count($request->roles) > 0) {
                $roles_to_sync = Role::whereIn('id', array_keys($request->roles))->pluck('name')->toArray();
                $user->syncRoles($roles_to_sync);
            }
        }

        User::where('id', $user->id)->update([
            'name' => request()->has('name') ? $request->name : '',
            'email' => request()->has('email') ? $request->email : '',
            'password' => $user->password,
            'profile_picture' => $file_path,
            'updated_by' => auth()->id(),
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "User updated successfully";
        return redirect($this->get_path())->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // Only admin can delete 'Sub Admin/Instructor'
        if (!$this->user->can('delete_users')) {
            abort(403);
        }

        $notification = [];
        // $user = User::where('id', $id)->first();
        // $user->delete();

        $user = User::where('id', $id)->first();

        $course = Course::where("instructor_id", $user->id)->get();
        if ($course) {
            foreach ($course as $val) {
                $course_plan = CoursePlan::where("course_id", $val->id)->get();
                $course_plan->each->Delete();
                $course_cat = CourseCategory::where("course_id", $val->id)->get();
                $course_cat->each->Delete();
                $rating = RatingReview::where("course_id", $val->id)->get();
                $rating->each->Delete();
            }
            $user->Delete();
            $course->each->Delete();
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "User deleted successfully";
        return redirect($this->get_path())->with('notification', $notification);
    }

    public function restore($id)
    {
        // Only admin can restore 'Sub Admin/Instructor'
        if (!$this->user->can('restore_users')) {
            abort(403);
        }

        $notification = [];

        $user = User::withTrashed()->where('id', $id)->firstOrFail();
        $user->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "User restored successfully";

        return redirect($this->get_path())->with('notification', $notification);
    }

    public function get_users()
    {
        if (!$this->user->can('browse_users') && !$this->user->can('browse_subadmin')) {
            abort(403);
        }

        $url = \Request::server('HTTP_REFERER');
        if (str_contains($url, 'users')) {
            $role = ['admin'];
        } else {
            $role = Role::withTrashed()->whereNotIn('name', ['admin', 'instructor'])->pluck('name')->toArray();

        }

        $query = DB::table('model_has_roles')->join('users', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', 'model_has_roles.role_id')
            ->whereNot("users.id",1)
            ->select('users.id', 'users.name', 'users.email', 'users.profile_picture', 'users.created_at AS created_at', 'users.deleted_at', 'model_has_roles.role_id', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'), 'roles.deleted_at as role_deleted')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', $role)
        // ->whereNotIn('users.id',[Auth::id()])
            ->orderBy("users.id", "DESC")
            ->groupBy("users.id")
            ->get();

        // dd($query);
        return DataTables::of($query)

            ->addColumn('action', function ($row) {
                $permission_data = $this->get_permissions();
                $path = 'backoffice/' . $permission_data['role'] . "/";
                $url = route("users.destroy", ["user" => $row->id]);
                $button = "";
                // if ($this->user->can($permission_data['read_permission'])) {
                //     $button .= '<a class="mx-1" title="View" href="' . url($path . $row->id) . '"><i class="fas fa-eye"></i></a>';
                // }
                // if ($this->user->can($permission_data['edit_permission'])) {
                //     $button .= '<a class="mx-1" title="Edit" href="' . url($path . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }
                // if (is_null($row->deleted_at)) {
                //     if(auth()->user()->hasRole('admin')){
                //         if ($this->user->can($permission_data['delete_permission'])) {
                //             $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //         }
                //     }
                // } else {
                //     if ($this->user->can($permission_data['restore_permission'])) {
                //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path.'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                if ($this->user->can('read_users')) {
                    $button .= '<a class="mx-1" title="View" href="' . url($path . $row->id) . '"><i class="fas fa-eye"></i></a>';
                    if ($this->user->can('edit_users')) {
                        $button .= '<a class="mx-1" title="Edit" href="' . url($path . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                    }
                }
                if (is_null($row->deleted_at)) {
                    // if(auth()->user()->hasRole('admin')){
                    if ($row->id !== Auth::id()) {
                        if ($this->user->can('delete_users')) {
                            $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        }
                    } else {
                        $button .= '';
                    }
                    // }
                } else {
                    if ($this->user->can('restore_users')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path . 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('profile_picture', function ($row) {
                $profilePictureURL = '';

                !empty($row->profile_picture)
                ? $profilePictureURL = Storage::exists($row->profile_picture) ? Storage::url($row->profile_picture) : ''
                : $profilePictureURL = asset('admin/dist/img/avatar.png');

                return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
        // ->editColumn('created_at', function($row){
        //     return $row->created_at;
        // })
            ->rawColumns(['action', 'profile_picture', 'created_at'])
            ->toJson();
    }

    public function get_users_deleted()
    {
        // Only admin can show-deleted 'Sub Admin/Instructor'
        if (!$this->user->can('browse_users')) {
            abort(403);
        }

        $url = \Request::server('HTTP_REFERER');
        if (str_contains($url, 'subadmin')) {
            $role = 'subadmin';
        } else {
            $role = 'admin';
        }

        $query = DB::table('model_has_roles')->join('users', 'users.id', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', 'model_has_roles.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.profile_picture', 'users.created_at AS created_at', 'users.deleted_at', 'model_has_roles.role_id', DB::raw('GROUP_CONCAT(roles.name SEPARATOR ", ") as rolesName'))->whereNotNull('users.deleted_at')->whereNotIn('users.id', [Auth::id()])->where('roles.name', $role)->groupBy("users.id")->latest()->get();



        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $permission_data = $this->get_permissions();
                $path = 'backoffice/' . $permission_data['role'] . "/";

                $url = route("users.destroy", ["user" => $row->id]);
                $button = "";
                $deleteurl = route("subadmin.delete", ["id" => $row->id]);

                // if(auth()->user()->hasRole('admin')){
                if ($this->user->can('delete_users')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                // }

                if ($this->user->can('restore_users')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path . 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }

                // if (is_null($row->deleted_at)) {
                //     if(auth()->user()->hasRole('admin')){
                //         if ($this->user->can('delete_users')) {
                //             $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //         }
                //     }
                // }
                // else {
                //     if ($this->user->can('restore_users')) {
                //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url($path. 'restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('profile_picture', function ($row) {
                $profilePictureURL = '';

                !empty($row->profile_picture)
                ? $profilePictureURL = Storage::exists($row->profile_picture) ? Storage::url($row->profile_picture) : ''
                : $profilePictureURL = asset('admin/dist/img/avatar.png');

                return '<a href="' . $profilePictureURL . '" target="_blank"><img src="' . $profilePictureURL . '" style="height: 60px;width: 60px;"></a>';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'profile_picture', 'created_at'])
            ->toJson();
    }

    public function restore_all(Request $request)
    {
        // Only admin can restore all 'Sub Admin/Instructor'
        $notification = [];
        if (!$this->user->can('restore_users')) {
            abort(403);
        }

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $user = User::withTrashed()->where('id', $value)->firstOrFail();
            $user->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Users restored successfully";
        return redirect($this->get_path())->with('notification', $notification);
    }
    public function get_path()
    {
        $url = \Request::server('HTTP_REFERER');
        if (str_contains($url, 'subadmin')) {
            $path = 'backoffice/subadmin';
        } else {
            $path = 'backoffice/users';
        }

        return $path;
    }
    public function get_permissions()
    {
        $url = \Request::server('HTTP_REFERER');

        if (str_contains($url, 'subadmin')) {
            $role = 'subadmin';
            $read_permission = 'read_subadmin';
            $edit_permission = 'edit_subadmin';
            $delete_permission = 'delete_subadmin';
            $restore_permission = 'restore_subadmin';
        } else {
            $role = 'users';
            $read_permission = 'read_users';
            $edit_permission = 'edit_users';
            $delete_permission = 'delete_users';
            $restore_permission = 'restore_users';
        }

        return [
            'role' => $role,
            'read_permission' => $read_permission,
            'edit_permission' => $edit_permission,
            'delete_permission' => $delete_permission,
            'restore_permission' => $restore_permission,
        ];
    }

    public function delete($id)
    {

        if (!$this->user->can('delete_users')) {
            abort(403);
        }

        $notification = [];
        try {
            $user = User::where('id', $id)->withTrashed()->first();
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            if ($user) {
                $course = Course::where("instructor_id", $user->id)->withTrashed()->get();

                if ($course) {
                    foreach ($course as $val) {
                        $course_plan = CoursePlan::where("course_id", $val->id)->withTrashed()->get();
                        $course_plan->each->forceDelete();

                        $course_cat = CourseCategory::where("course_id", $val->id)->withTrashed()->get();
                        $course_cat->each->forceDelete();

                        $rating = RatingReview::where("course_id", $val->id)->withTrashed()->get();
                        $rating->each->forceDelete();

                        UserCoin::where("course_id", $val->id)->forceDelete();
                    }

                    $course->each->forceDelete();
                }

                SupportChat::where("created_by",$user->id)->forceDelete();
                $user->forceDelete();

                $notification['type'] = "sweet-alert";
                $notification['status'] = "success";
                $notification['title'] = "Success";
                $notification['msg'] = "Instructor deleted successfully";

                return redirect()->back()->with('notification', $notification);
            } else {
                throw new Exception("User not found.");
            }
        } catch (QueryException $e) {

            if ($e->getCode() == '23000') {
                $notification['type'] = "sweet-alert";
                $notification['status'] = "error";
                $notification['title'] = "Error";
                $notification['msg'] = "Cannot delete this user as there are related records in another table.";

                return redirect()->back()->with('notification', $notification);
            }
        } catch (Exception $e) {

            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = $e->getMessage();

            return redirect()->back()->with('notification', $notification);
        }
    }

    public function bulkHardUserDelete(Request $request)
    {

        foreach ($request->bd as $id => $value) {

            $user = User::where('id', $id)->withTrashed()->first();
            $course = Course::where("instructor_id", $user->id)->withTrashed()->get();
            if ($course) {
                foreach ($course as $val) {
                    $course_plan = CoursePlan::where("course_id", $id)->withTrashed()->get();
                    $course_plan->each->forceDelete();
                    $course_cat = CourseCategory::where("course_id", $id)->withTrashed()->get();
                    $course_cat->each->forceDelete();
                    $rating = RatingReview::where("course_id", $id)->withTrashed()->get();
                    $rating->each->forceDelete();
                }
                $user->forceDelete();
                $course->each->forceDelete();
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "User deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }
}
