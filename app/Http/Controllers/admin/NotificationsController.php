<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Yajra\Datatables\Datatables;
use App\Helper\Helper;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
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
        if (!$this->user->can('browse_notifications')) abort(403);
        $pg_header = "Notification";
        if (view()->exists('admin.notifications.list')) {
            return view('admin.notifications.list', compact('pg_header'));
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
        if (!$this->user->can('add_notifications')) abort(403);
        if (view()->exists('admin.notifications.add')) {
            return view('admin.notifications.add');
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
        if (!$this->user->can('add_notifications')) abort(403);
        $notification = [];
        $request->validate([
            'title' => 'required|filled',
            'text' => 'required|filled',
        ]);

        Notification::create([
            'notificationTitle' => $request->title,
            'notificationText' => $request->text,
        ]);
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notification added successfully";
        return redirect('backoffice/notifications')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_notifications')) abort(403);

        $notification = Notification::withTrashed()->findOrFail($id);
        $notification->date = \Helper::date_format($notification->updated_at);
        $pg_header = "View Notification";
        if (view()->exists('admin.notifications.view')) {
            return view('admin.notifications.view', compact('notification', 'pg_header'));
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
        if (!$this->user->can('edit_notifications')) abort(403);
        $notification = Notification::select('id','notificationTitle','notificationText')->where('id', $id)->withTrashed()->firstOrFail();
        $pg_header = "Edit Notification";

        if (view()->exists('admin.notifications.edit')) {
            return view('admin.notifications.edit', compact('notification', 'pg_header'));
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
        if (!$this->user->can('edit_notifications')) abort(403);
        $notification = [];
        $notifications = Notification::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|filled',
            'text' => 'required|filled',
        ]);

        $notifications->update([
            'notificationTitle' => $request->title,
            'notificationText' => $request->text,
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notification updated successfully";
        return redirect('backoffice/notifications')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_notifications')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $notifications= Notification::withTrashed()->where('id', $id)->firstOrFail();
            $notifications->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notification deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function getNotifications()
    {
        if (!$this->user->can('browse_notifications')) abort(403);
        $query = Notification::whereNull('deleted_at')->get();
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("notifications.destroy", ["notification" => $row->id]);
                $button = "";
                if ($this->user->can('read_notifications')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/notifications/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_notifications')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/notifications/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_notifications')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_notifications')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/notifications/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','date'])
            ->toJson();
    }

    public function restore($id)
    {
        if (!$this->user->can('restore_notifications')) abort(403);
        $notifications = Notification::withTrashed()->where('id', $id)->firstOrFail();
        $notifications->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notification restored successfully";
        return redirect('backoffice/notifications')->with('notification', $notification);
    }

    public function getNotificationsDeleted()
    {
        if (!$this->user->can('browse_notifications')) abort(403);
        $query = Notification::whereNotNull('deleted_at')->onlyTrashed()->latest()->get();
        // $query->map(function($value){
        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("notifications.destroy", ["notification" => $row->id]);
                $button = "";
                $deleteurl = route("notifications.delete", ["id" => $row->id]);
                if ($row->deleted_at) {
                    if ($this->user->can('delete_notifications')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                    if ($this->user->can('restore_notifications')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/notifications/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_notifications')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/notifications/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','date'])
            ->toJson();
    }

    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_notifications')) abort(403);

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $notifications = Notification::withTrashed()->where('id', $value)->firstOrFail();
            $notifications->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notifications restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function delete($id)
    {
        if (!$this->user->can('delete_notifications')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $notifications= Notification::withTrashed()->where('id', $id)->firstOrFail();
            $notifications->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Notification deleted successfully";

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
        if (!$this->user->can('delete_notifications')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $notifications= Notification::withTrashed()->where('id', $id)->firstOrFail();
                $notifications->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Notifications deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Notification selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
