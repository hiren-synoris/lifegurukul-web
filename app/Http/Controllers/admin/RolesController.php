<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Str;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use \App\Models\Role as ModelsRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Models\Permission as ModelsPermission;


class RolesController extends Controller
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

        // if (!$this->user->can('browse_roles')) abort(403);
        $pg_header = "roles";
        $delete_url = url('roles/delete');
        if (view()->exists('admin.roles.list')) {
            return view('admin.roles.list', compact('pg_header', 'delete_url'));
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
        $pg_header = "Add Role";
        // if (!$this->user->can('add_roles')) abort(403);
        $permissions = ModelsPermission::get()->where('module_name', '!=', "permissions")
            ->where('module_name', '!=', "dropdown_options")
            ->where('module_name', '!=', "categories")
            ->where('module_name', '!=', "Enroll")
            // ->where('module_name', '!=', "forums")
            ->where('module_name', '!=', "general")
            ->where('module_name', '!=', "user_coin")->groupBy('module_name')->toArray();

        if (view()->exists('admin.roles.add')) {
            return view('admin.roles.add', compact('permissions', 'pg_header'));
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
        // if (!$this->user->can('add_roles')) abort(403);
        $notification = [];
        $validated = $request->validate([
            'name' => 'required|filled|max:255|unique:roles,name',
        ]);
        //$rolesValue = $request->name;
        $roles_name = strtolower($request->name);
        $roles_name = str_replace(' ', '_', $roles_name);

        $display_name = strtolower($request->display_name);
        $display_name = str_replace(' ', '_', $display_name);
        $permission_id = $request->permission;
        $role = Role::create([
            'name' => $roles_name,
            'display_name' => $request->display_name,
        ]);

        if ($request->has('permission')) {

            foreach ($permission_id as $key => $value) {
                $permission = Permission::find($key);
                $role->givePermissionTo($permission);
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Role added successfully";
        return redirect('/backoffice/roles')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!$this->user->can('read_roles')) abort(403);
        //$role = ModelsRole::withTrashed()->where('id', $id)->first();
        /* $role = DB::table('role_has_permissions')->join('roles','roles.id','role_has_permissions.role_id')
                ->join('permissions','permissions.id','role_has_permissions.permission_id')
                ->select('roles.id', 'roles.name', 'roles.display_name','roles.created_at','roles.updated_at','roles.deleted_at', DB::raw('GROUP_CONCAT(permissions.name SEPARATOR ", ") as permissionsName'))
                ->where('roles.id', $id)->first(); */

        $role = DB::select('select `roles`.`id`, `roles`.`name`, `roles`.`display_name`, `roles`.`created_at`, `roles`.`updated_at`, `roles`.`deleted_at`, GROUP_CONCAT(permissions.name SEPARATOR ", ") as permissionsName from `role_has_permissions` inner join `roles` on `roles`.`id` = `role_has_permissions`.`role_id` inner join `permissions` on `permissions`.`id` = `role_has_permissions`.`permission_id` where `roles`.`id` = ' . $id . ' limit 1')[0];
        $created_at = date('d/m/Y H:i:s', strtotime($role->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($role->updated_at));
        $pg_header = "View Role";
        if (view()->exists('admin.roles.view')) {
            return view('admin.roles.view', compact('role', 'created_at', 'updated_at', 'pg_header'));
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

        // if (!$this->user->can('edit_roles')) abort(403);
        $permissions = ModelsPermission::get()->where('module_name', '!=', "permissions")
        ->where('module_name', '!=', "dropdown_options")
        ->where('module_name', '!=', "categories")
        ->where('module_name', '!=', "Enroll")
        // ->where('module_name', '!=', "forums")
        ->where('module_name', '!=', "general")
        ->where('module_name', '!=', "user_coin")->groupBy('module_name')->toArray();
        $permission_count = ModelsPermission::get()->where('module_name', '!=', "permissions")
        ->where('module_name', '!=', "dropdown_options")
        ->where('module_name', '!=', "categories")
        ->where('module_name', '!=', "Enroll")
        // ->where('module_name', '!=', "forums")
        ->where('module_name', '!=', "general")
        ->where('module_name', '!=', "user_coin")->groupBy('module_name')->count();
        $roles_Data = DB::table('role_has_permissions')
            ->join('roles', 'role_has_permissions.role_id', '=', 'roles.id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->select('role_has_permissions.permission_id')
            ->where('roles.id', $id)
            ->get();
        $t1 = $roles_Data->pluck('permission_id');
        $roles_Data = $t1->all();

        $role = ModelsRole::withTrashed()->where('id', $id)->first();

        $assigned_permissions_1 = Role::findByName($role->name)->permissions;

        $role_total = DB::select('SELECT DISTINCT count(roles.id) AS total, roles.name AS role, per.module_name AS `permissions` FROM roles INNER JOIN role_has_permissions AS rhp ON rhp.role_id = roles.id LEFT JOIN permissions as per ON per.id = rhp.permission_id WHERE roles.id = ' . $id . ' GROUP BY per.module_name, roles.name');

        $role_total_count = count($role_total);

        $in_total = 0;

        foreach ($role_total as $key => $value) {
            $in_total += $value->total;
            $value->permissions = strtolower($value->permissions);
        }

        $assigned_permissions = Role::findByName($role->name)->permissions->pluck('id');

        $pg_header = "Edit Role";

        if (view()->exists('admin.roles.edit')) {
            return view('admin.roles.edit', compact('role', 'pg_header', 'permissions', 'roles_Data', 'assigned_permissions', 'role_total', 'in_total', 'role_total_count', 'permission_count'));
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
        // if (!$this->user->can('edit_roles')) abort(403);
        //dd($request->all());
        $notification = [];
        $role = Role::find($id);

        $validated = $request->validate([
            'name' => ['required', 'filled', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $roles_name = strtolower($request->name);
        $roles_name = str_replace(' ', '_', $roles_name);

        /* $display_name = strtolower($request->display_name);
        $display_name = str_replace(' ', '_', $display_name); */

        $role->name = $roles_name;
        $role->display_name = $request->display_name;
        $role->save();

        if ($request->permission) {
            $role->syncPermissions(array_keys($request->permission));
        } else {
            $role->syncPermissions([]);
        }
        $role->updated_at = now();
        $role->save();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Role updated successfully";
        return redirect('/backoffice/roles')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (!$this->user->can('delete_roles')) abort(403);
        ModelsRole::destroy($id);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Role deleted successfully";
        return redirect('/backoffice/roles')->with('notification', $notification);
    }

    public function restore($id)
    {
        // if (!$this->user->can('restore_roles')) abort(403);

        ModelsRole::withTrashed()->where('id', $id)->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Role restored successfully";
        return redirect('/backoffice/roles')->with('notification', $notification);
    }

    public function get_roles()
    {
        // if (!$this->user->can('browse_roles')) abort(403);

        $query = DB::table('role_has_permissions')->rightJoin('roles', 'roles.id', 'role_has_permissions.role_id')
            ->leftJoin('permissions', 'permissions.id', 'role_has_permissions.permission_id')
            ->select('roles.id', 'roles.name', 'roles.display_name', 'roles.created_at', 'roles.deleted_at', DB::raw('GROUP_CONCAT(permissions.name SEPARATOR ", ") as permissionsName'))
            ->groupBy("roles.id")
            ->whereNull('roles.deleted_at')->get();

        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });
        // dd($query);
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("roles.destroy", ["role" => $row->id]);
                $button = "";
                // if ($this->user->can('read_roles')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/roles/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                // }
                // if ($this->user->can('edit_roles')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/roles/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }
                if (is_null($row->deleted_at)) {
                    // if ($this->user->can('delete_roles')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    // }
                } else {
                    // if ($this->user->can('restore_roles')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/roles/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    // }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'created_at'])
            ->toJson();
    }

    public function get_roles_deleted()
    {
        // if (!$this->user->can('browse_roles')) abort(403);
        $query = DB::table('role_has_permissions')->rightJoin('roles', 'roles.id', 'role_has_permissions.role_id')
            ->leftJoin('permissions', 'permissions.id', 'role_has_permissions.permission_id')
            ->select('roles.id', 'roles.name', 'roles.display_name', 'roles.created_at', 'roles.deleted_at', DB::raw('GROUP_CONCAT(permissions.name SEPARATOR ", ") as permissionsName'))
            ->groupBy("roles.id")->whereNotNull('roles.deleted_at')->get();

        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("roles.destroy", ["role" => $row->id]);
                $button = "";
                $deleteurl = route("roles.delete", ["id" => $row->id]);

                // if ($this->user->can('read_roles')) {
                //     $button .= '<a class="mx-1" href="' . url('backoffice/roles/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                // }
                // if ($this->user->can('edit_roles')) {
                //     $button .= '<a class="mx-1" href="' . url('backoffice/roles/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }

                // if ($this->user->can('delete_roles')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                // }
                // if ($this->user->can('restore_roles')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/roles/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                // }

                // if (is_null($row->deleted_at)) {
                //     if ($this->user->can('delete_roles')) {
                //         $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //     }
                // } else {
                //     if ($this->user->can('restore_roles')) {
                //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/roles/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'created_at'])
            ->toJson();
    }



    public function bulk_del(Request $request)
    {
        // if (!$this->user->can('delete_roles')) abort(403);
        if (!empty($request->bd) > 0) {
            ModelsRole::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Role deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No role selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    public function restore_all(Request $request)
    {
        // if (!$this->user->can('restore_roles')) abort(403);

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            ModelsRole::withTrashed()->where('id', $value)->restore();
        }
        //ModelsRole::onlyTrashed()->restore(array_keys($request->all()));
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Roles restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function delete($id)
    {
        // if (!$this->user->can('delete_roles')) abort(403);
        $notification = [];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // role permission remove
        DB::table('role_has_permissions')->where('role_id', $id)->delete();

        $role = ModelsRole::withTrashed()->where('id', $id)->firstOrFail();
        $role->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Roles deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_Hard_Delete(Request $request)
    {
        // if (!$this->user->can('delete_roles')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                // role permission remove
                DB::table('role_has_permissions')->where('role_id', $id)->delete();

                $role = ModelsRole::withTrashed()->where('id', $id)->firstOrFail();
                $role->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Roles deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Role selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
}
