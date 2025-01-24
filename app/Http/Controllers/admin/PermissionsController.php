<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Events\ModelsPruned;
use App\Models\Permission as ModelsPermission;

class PermissionsController extends Controller
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
        //if (!$this->user->can('browse_permissions')) abort(403);
        $pg_header = "Permissions";
        if(view()->exists('admin.permissions.list')){
            return view('admin.permissions.list', compact('pg_header'));
        } abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pg_header = "Add Permission";
        //if (!$this->user->can('add_permissions')) abort(403);
        if(view()->exists('admin.permissions.add')){
            return view('admin.permissions.add',compact("pg_header"));
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
        //if (!$this->user->can('add_permissions')) abort(403);
        $input = $request->all();

        $notification = [];
        $validated = $request->validate([
            'name' => 'required|filled|max:255|unique:permissions,name|regex:/^[a-zA-Z_Ññ\s]+$/'
        ]);

        if ($request->permission == 'on') {
            $permission_name = strtolower($input['name']);
            $permission_name = str_replace(' ', '_', $permission_name);
            $permission_value =  ['browse', 'read', 'edit', 'add', 'delete', 'restore'];
            for ($i = 0; $i < count($permission_value); $i++) {
                $permission = Permission::create([
                    'name' => $permission_value[$i] . '_' . $permission_name,
                    'module_name' => $input['name'],
                ]);
            }
        } else {
            $permission_name = strtolower($input['name']);
            $permission_name = str_replace(' ', '_', $permission_name);
            $permission = Permission::create([
                'name' => $permission_name,
                'module_name' => 'general',
            ]);
        }

        $notification = [];
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permission added successfully";
        return redirect('/backoffice/permissions')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //if (!$this->user->can('read_permissions')) abort(403);
        $permission = ModelsPermission::withTrashed()->where('id', $id)->first();
        $created_at = date('d/m/Y H:i:s', strtotime($permission->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($permission->updated_at));
        $pg_header = "View Permission";
        if(view()->exists('admin.permissions.view')){
            return view('admin.permissions.view', compact('permission','created_at','updated_at', 'pg_header'));
        } abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //if (!$this->user->can('edit_permissions')) abort(403);
        $permission = ModelsPermission::withTrashed()->where('id', $id)->first();
        $pg_header = "Edit Permission";

        if(view()->exists('admin.permissions.edit')){
            return view('admin.permissions.edit', compact('permission', 'pg_header'));
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
        //if (!$this->user->can('edit_permissions')) abort(403);
        $notification = [];
        $input = $request->all();
        $notification = [];
        $permissions = Permission::find($id);
        $validated = $request->validate([
            'name' => 'required|filled|max:255|unique:permissions,name,' . $permissions->id,
        ]);

        $permission = strtolower($input['name']);
        $permissions->name = str_replace(' ', '_', $permission);
        $permissions->module_name = $input['module_name'];

        $permissions->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permission updated successfully";
        return redirect('/backoffice/permissions')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //if (!$this->user->can('delete_permissions')) abort(403);
        ModelsPermission::destroy($id);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permission deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function restore($id)
    {
        //if (!$this->user->can('restore_permissions')) abort(403);
        ModelsPermission::withTrashed()->findOrFail($id)->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permission restored successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function get_permissions()
    {
        //if (!$this->user->can('browse_permissions')) abort(403);
        $query = DB::table('permissions')->whereNull('deleted_at')->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("permissions.destroy", ["permission" => $row->id]);
                $button = "";
                if ($this->user->can('read_permissions'))
                {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/permissions/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_permissions')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/permissions/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_permissions')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_permissions')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/permissions/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','created_at'])
            ->addIndexColumn()->toJson();
    }

    public function get_permissions_deleted()
    {
        //if (!$this->user->can('browse_permissions')) abort(403);
        $query = DB::table('permissions')->whereNotNull('deleted_at')->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("permissions.destroy", ["permission" => $row->id]);
                $button = "";
                $deleteurl = route("permissions.delete", ["id" => $row->id]);
                // if ($this->user->can('read_permissions')) {
                //     $button .= '<a class="mx-1" href="' . url('backoffice/permissions/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                // }
                // if ($this->user->can('edit_permissions')) {
                //     $button .= '<a class="mx-1" href="' . url('backoffice/permissions/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }

                if ($this->user->can('delete_permissions')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_permissions')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/permissions/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }

                // if (is_null($row->deleted_at)) {
                //     if ($this->user->can('delete_permissions')) {
                //         $button .= '<a class="mx-1 text-danger" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //     }
                // } else {
                //     if ($this->user->can('restore_permissions')) {
                //         $button .= '<a class="mx-1 text-success" href="' . url('backoffice/permissions/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','created_at'])
            ->addIndexColumn()->toJson();
    }


    public function bulk_del(Request $request)
    {
        //if (!$this->user->can('delete_permissions')) abort(403);
        if (!empty($request->bd) > 0) {
            ModelsPermission::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Permissions deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No permission selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    public function restore_all(Request $request)
    {
        //if (!$this->user->can('restore_permissions')) abort(403);
        //dd($request->all());
         foreach (array_keys($request->selected_checkbox) as $key => $value) {
            ModelsPermission::withTrashed()->where('id',$value)->restore();
        }
       /*  ModelsPermission::onlyTrashed()->restore(array_keys($request->selected_checkbox)); */
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permissions restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function delete($id)
    {
        //if (!$this->user->can('delete_permissions')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $permissions= ModelsPermission::withTrashed()->where('id', $id)->firstOrFail();
            $permissions->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Permissions deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_Hard_Delete(Request $request)
    {
        //if (!$this->user->can('delete_permissions')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $permissions= ModelsPermission::withTrashed()->where('id', $id)->firstOrFail();
                $permissions->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Permissions deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Permission selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
