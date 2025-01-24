<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Models\Dropdown;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Course;
use App\Models\DropdownOption;
use Illuminate\Support\Facades\Auth;

class DropdownController extends Controller
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
        if (!$this->user->can('browse_dropdowns')) abort(403);
        $pg_header = "Dropdowns";
        $dropdowns = Dropdown::whereNull('deleted_at')->get();
        if (view()->exists('admin.dropdowns.list')) {
            return view('admin.dropdowns.list', compact('pg_header', 'dropdowns'));
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
        $pg_header = "Add Dropdown Option";
        if (!$this->user->can('add_dropdowns')) abort(403);
        if (view()->exists('admin.dropdowns.add')) {
            return view('admin.dropdowns.add',compact('pg_header'));
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

        // dd("");
        if (!$this->user->can('add_dropdowns')) abort(403);
        $validated = $request->validate([
            'name' => 'required|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'slug' => 'required|filled|max:255|unique:dropdowns,slug|alpha_dash',
        ]);

        try {
            $notification = [];
            DB::beginTransaction();
            Dropdown::create([
                'name' =>allowWhiteSpace($validated['name']),
                'slug' => strtolower($validated['slug']),
                'status' => request()->has('status') ? 1 : 0,
                'created_by' => auth()->id(),
            ]);
            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Dropdown added successfully";
        } catch (\Exception $e) {
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.";
        }
        return redirect('backoffice/dropdowns')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_dropdowns')) abort(403);
        $dropdown = Dropdown::findOrFail($id);
        $created_at = date('d/m/Y H:i:s', strtotime($dropdown->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($dropdown->updated_at));
        $pg_header = "View Dropdown";
        if (view()->exists('admin.dropdowns.view')) {
            return view('admin.dropdowns.view', compact('dropdown', 'created_at', 'updated_at', 'pg_header'));
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
        if (!$this->user->can('edit_dropdowns')) abort(403);
        $dropdown = Dropdown::findOrFail($id);
        $pg_header = "Edit Dropdown";
        if (view()->exists('admin.dropdowns.edit')) {
            return view('admin.dropdowns.edit', compact('dropdown'));
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
        if (!$this->user->can('add_dropdowns')) abort(403);
        $notification = [];
        $dropdown = Dropdown::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
            'slug' => ['required', 'filled', Rule::unique('dropdowns', 'slug')->ignore($dropdown->id, 'id'), 'alpha_dash'],
        ]);
        $dropdown->name = allowWhiteSpace($validated['name']);
        $dropdown->slug = strtolower($validated['slug']);
        $dropdown->status = request()->has('status') ? 1 : 0;
        $dropdown->updated_by = auth()->id();
        $dropdown->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Dropdown updated successfully";
        return redirect('backoffice/dropdowns')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_dropdowns')) abort(403);
        $notification = [];
        $dropdown = Dropdown::where('id', $id)->firstOrFail();
        $dropdown->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Dropdown deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {
        if (!$this->user->can('restore_dropdowns')) abort(403);
        $notification = [];
        $dropdown = Dropdown::withTrashed()->where('id', $id)->firstOrFail();
        $dropdown->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Dropdown restored successfully";

        return redirect()->back()->with('notification', $notification);
    }


    /**
     * Get all Dropdown record where not deleted.
     */
    public function getDropdowns()
    {
        if (!$this->user->can('browse_dropdowns')) abort(403);
        $query = DB::table('dropdowns')->select('id', 'name', 'slug', 'status', 'created_at', 'deleted_at')->whereNull('deleted_at')->latest()->get();
        // $query->map(function ($value) {
        //     $value->created_at = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("dropdowns.destroy", ["dropdown" => $row->id]);
                $button = "";

                $button .= '<a class="mx-1 text-success" title="Add Options" href="' . url('backoffice/dropdown_options/' . $row->id) . '"><i class="fas fa-plus-square"></i></a>';

                if ($this->user->can('read_dropdowns')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/dropdowns/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_dropdowns')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/dropdowns/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if(!in_array($row->slug, ['blog_category','course_category','trusted_by']))
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_dropdowns')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_dropdowns')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdowns/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('status', function ($row) {
                return Helper::checkStatus($row->status);
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'status','created_at'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Get deleted Dropdown records.
     */
    public function geDropdownsDeleted()
    {
        if (!$this->user->can('browse_dropdowns')) abort(403);
        $query = DB::table('dropdowns')->select('id', 'name', 'slug', 'status', 'created_at', 'deleted_at')->whereNotNull('deleted_at')->latest()->get();
        // $query->map(function ($value) {
        //     $value->created_at = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("dropdowns.destroy", ["dropdown" => $row->id]);
                $button = "";
                $deleteurl = route("dropdowns.delete", ["id" => $row->id]);

                if ($this->user->can('delete_dropdowns')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_dropdowns')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdowns/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }

                // if (is_null($row->deleted_at)) {
                //     if ($this->user->can('delete_dropdowns')) {
                //         $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                //     }
                // } else {
                //     if ($this->user->can('restore_dropdowns')) {
                //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdowns/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                //     }
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('status', function ($row) {
                return Helper::checkStatus($row->status);
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'status','created_at'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Restore all Dropdown records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_dropdowns')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $dropdown = Dropdown::withTrashed()->where('id', $value)->firstOrFail();
            $dropdown->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Dropdown restored successfully";
        return redirect()->back()->with('notification', $notification);
    }


    public function delete($id)
    {
        if (!$this->user->can('delete_dropdowns')) abort(403);
        $notification = [];


        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        //course chapter and chapter info removed
        $dropDownIds = DropdownOption::where('dropdown_id', $id)->select('id')->get()->toArray();
        if(isset($dropDownIds) && count($dropDownIds) > 0){
            DB::table('courses')
                ->whereIn('course_category', $dropDownIds)
                ->update(['course_category' => null]);

            // Blog Removed category id
            Blog::whereIn('category_id', $dropDownIds)->update(['category_id' => null]);
        }

        // drop down Option
        $dropdownOption= DropdownOption::where('dropdown_id', $id)->delete();

        // drop down removed
        $dropdown= Dropdown::withTrashed()->where('id', $id)->firstOrFail();
        $dropdown->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Dropdown deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Permanant Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_dropdowns')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                //course chapter and chapter info removed
                $dropDownIds = DropdownOption::where('dropdown_id', $id)->select('id')->get()->toArray();
                if(isset($dropDownIds) && count($dropDownIds) > 0){
                    DB::table('courses')
                        ->whereIn('course_category', $dropDownIds)
                        ->update(['course_category' => null]);

                    // Blog Removed category id
                    Blog::whereIn('category_id', $dropDownIds)->update(['category_id' => null]);
                }

                // drop down Option
                $dropdownOption= DropdownOption::where('dropdown_id', $id)->delete();

                // drop down removed
                $dropdown= Dropdown::withTrashed()->where('id', $id)->firstOrFail();
                $dropdown->forceDelete();

            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Dropdowns deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Dropdown selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
