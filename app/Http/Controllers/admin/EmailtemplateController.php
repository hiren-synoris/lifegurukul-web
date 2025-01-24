<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\Emailtemplate;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmailtemplateController extends Controller
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
        $pg_header = "Email Templates";
        if (view()->exists('admin.email_templates.list')) {
            return view('admin.email_templates.list',compact('pg_header'));
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
        if (!$this->user->can('add_email_templates')) abort(403);
        if (view()->exists('admin.email_templates.add')) {
            return view('admin.email_templates.add');
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
        $notification = [];
        if (!$this->user->can('add_email_templates')) abort(403);
        $request->validate([
            'slug' => 'required|filled|unique:emailtemplates,slug',
        ]);
        $user = auth()->id();
        $slug = $request->slug;
        $body = $request->template;

        Emailtemplate::create([
            'user_id' => $user,
            'slug' => $slug,
            'body' => $body,
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email template created successfully";
        return redirect('backoffice/email_templates')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Emailtemplate  $emailtemplate
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_email_templates')) abort(403);
        $emailtemplate = Emailtemplate::find($id);
        $content = view('admin.email_templates.parse', compact('emailtemplate'));
        if (view()->exists('admin.email_templates.view')) {
            return view('admin.email_templates.view', compact('emailtemplate','content'));
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Emailtemplate  $emailtemplate
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $emailtemplate = Emailtemplate::findOrFail($id);
        if (!$this->user->can('edit_email_templates')) abort(403);
        if (view()->exists('admin.email_templates.edit')) {
            return view('admin.email_templates.edit', compact('emailtemplate'));
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Emailtemplate  $emailtemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->can('edit_email_templates')) abort(403);
        $emailtemplate = Emailtemplate::findOrFail($id);
        $notification = [];
        $request->validate([
            'slug' => 'required|filled|unique:emailtemplates,slug,'.$emailtemplate->id,
            'template' => 'required|filled',
        ]);
        $emailtemplate->slug = $request->slug;
        $emailtemplate->body = $request->template;
        $emailtemplate->updated_at = now();
        $emailtemplate->save();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email template updated successfully";
        return redirect('backoffice/email_templates')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Emailtemplate  $emailtemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_email_templates')) abort(403);
        $notification = [];
        $emailTemplate = Emailtemplate::where('id', $id)->firstOrFail();
        $emailTemplate->delete();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email template deleted successfully";

        return redirect('backoffice/email_templates')->with('notification', $notification);
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {
        if (!$this->user->can('restore_email_templates')) abort(403);
        $notification = [];
        $emailTemplate = Emailtemplate::withTrashed()->where('id', $id)->firstOrFail();
        $emailTemplate->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email template restored successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function getEmailTemplates()
    {
        if (!$this->user->can('browse_email_templates')) abort(403);

        $query = DB::table('emailtemplates as et')
                        ->join('users', 'users.id', 'et.user_id')
                        ->select('et.*', 'users.name as user')
                        ->whereNull('et.deleted_at')
                        ->latest()
                        ->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("email_templates.destroy", ["email_template" => $row->id]);
                $button = "";
                if ($this->user->can('read_email_templates')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/email_templates/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_email_templates')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/email_templates/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_email_templates')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_email_templates')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/email_templates/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','created_at'])
            ->toJson();
    }

    public function getEmailTemplatesDeleted()
    {
        if (!$this->user->can('browse_email_templates')) abort(403);

        $query = DB::table('emailtemplates as et')
                    ->join('users', 'users.id', 'et.user_id')
                    ->select('et.*', 'users.name as user')
                    ->whereNotNull('et.deleted_at')
                    ->latest()
                    ->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("email_templates.destroy", ["email_template" => $row->id]);
                $button = "";
                $deleteurl = route("email-templates.delete", ["id" => $row->id]);
                if ($row->deleted_at) {
                    if ($this->user->can('delete_email_templates')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                    if ($this->user->can('restore_email_templates')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/email-templates/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_email_templates')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/email-templates/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','created_at'])
            ->toJson();
    }

    /**
     * Restore all Email templates records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_email_templates')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $emailTemplate = Emailtemplate::withTrashed()->where('id', $value)->firstOrFail();
            $emailTemplate->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Email templates restored successfully";
        return redirect()->back()->with('notification', $notification);
    }


    public function delete($id)
    {
        if (!$this->user->can('delete_email_templates')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $emailTemplate= Emailtemplate::withTrashed()->where('id', $id)->firstOrFail();
            $emailTemplate->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Emailtemplate deleted successfully";

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
        if (!$this->user->can('delete_email_templates')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $emailTemplate= Emailtemplate::withTrashed()->where('id', $id)->firstOrFail();
                $emailTemplate->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Emailtemplates deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Emailtemplate selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
