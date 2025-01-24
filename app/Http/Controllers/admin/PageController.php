<?php

namespace App\Http\Controllers\admin;

use App\Models\Page;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\PageRequest;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
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
        if (!$this->user->can('browse_pages')) abort(403);
        $pg_header = "Pages";
        $pages = Page::whereNull('deleted_at')->get();
        if (view()->exists('admin.pages.list')) {
            return view('admin.pages.list', compact('pg_header', 'pages'));
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
        if (!$this->user->can('add_pages')) abort(403);
        $pg_header = "Add Pages";
        if (view()->exists('admin.pages.add')) {
            return view('admin.pages.add',compact("pg_header"));
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request)
    {
        if (!$this->user->can('add_pages')) abort(403);

        try {
            $notification = [];
            DB::beginTransaction();
            if(request()->has('meta_keywords')){
                $meta_keywords = implode(',',$request['meta_keywords']);
            }
                Page::create([
                    'name' => allowWhiteSpace($request['name']),
                    'slug' => $request['slug'],
                    'body' => $request['body'],
                    'meta_title' => request()->has('meta_title') ? $request['meta_title'] : NULL,
                    'meta_keywords' => $meta_keywords ?? NULL,
                    'meta_description' => request()->has('meta_description') ? $request['meta_description'] : NULL,
                    'status' => request()->has('status') ? 1 : 0,
                    'created_by' => auth()->id(),
                ]);
            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Page created successfully";
        } catch (\Exception $e) {
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.";
        }
        return redirect('backoffice/pages')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_pages')) abort(403);
        $page = Page::findOrFail($id);
        $created_at = date('d/m/Y H:i:s', strtotime($page->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($page->updated_at));
        $pg_header = "View Page";
        return view('admin.pages.view', compact('page', 'created_at', 'updated_at', 'pg_header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_pages')) abort(403);
        $page = Page::findOrFail($id);
        $keywords = [];
        if(isset($page) && isset($page->meta_keywords) && !empty($page->meta_keywords)){
            $keywords = explode(',',$page->meta_keywords);
        }
        return view('admin.pages.edit', compact('page','keywords'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PageRequest $request, $id)
    {
        if (!$this->user->can('edit_pages')) abort(403);
        $notification = [];
        if(request()->has('meta_keywords')){
            $meta_keywords = implode(',',$request['meta_keywords']);
        }

        Page::findOrFail($id)->update([
            'name' => allowWhiteSpace($request['name']),
            // 'slug' => $request['slug'],
            'body' => $request['body'],
            'meta_title' => request()->has('meta_title') ? $request['meta_title'] : NULL,
            'meta_keywords' => $meta_keywords ?? NULL,
            'meta_description' => request()->has('meta_description') ? $request['meta_description'] : NULL,
            'status' => request()->has('status') ? 1 : 0,
            'updated_by' => auth()->id()
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Page updated successfully";
        return redirect('backoffice/pages')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_pages')) abort(403);
        $notification = [];
        $page = Page::where('id', $id)->firstOrFail();
        $page->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Page deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {
        if (!$this->user->can('restore_pages')) abort(403);
        $notification = [];
        $page = Page::withTrashed()->where('id', $id)->firstOrFail();
        $page->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Page restored successfully";

        return redirect()->back()->with('notification', $notification);
    }


    /**
     * Get all Pages record where not deleted.
     */
    public function getPages()
    {
        if (!$this->user->can('browse_pages')) abort(403);

        $query = Page::whereNull('deleted_at')
                        ->select('id','name','slug','status','created_at','deleted_at')
                        ->latest()
                        ->get();
                        // $query->map(function($value){
                        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("pages.destroy", ["page" => $row->id]);
                $button = "";

                if ($this->user->can('read_pages')) {
                    $button .= '<a class="mx-1" title="View" target="_blank" href="' . route('page',['slug' => $row->slug]) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_pages')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/pages/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_pages')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_pages')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/pages/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('status', function($row){
                return Helper::checkStatus($row->status);
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','status','date'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Get deleted Pages records.
     */
    public function getPagesDeleted()
    {
        if (!$this->user->can('browse_pages')) abort(403);

        $query = Page::whereNotNull('deleted_at')
                        ->select('id','name','slug','status','created_at','deleted_at')
                        ->onlyTrashed()
                        ->latest()
                        ->get();
                        // $query->map(function($value){
                        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("pages.destroy", ["page" => $row->id]);
                $button = "";
                $deleteurl = route("pages.delete", ["id" => $row->id]);
                if ($row->deleted_at) {
                    if ($this->user->can('delete_pages')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                    if ($this->user->can('restore_pages')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/pages/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_pages')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/pages/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('status', function($row){
                return Helper::checkStatus($row->status);
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','status','date'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Restore all Page records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_pages')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $page = Page::withTrashed()->where('id', $value)->firstOrFail();
            $page->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Pages restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function delete($id)
    {
        if (!$this->user->can('delete_pages')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $page= Page::withTrashed()->where('id', $id)->firstOrFail();
            $page->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Page deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_pages')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $page= Page::withTrashed()->where('id', $id)->firstOrFail();
                $page->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Pages deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Page selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
}
