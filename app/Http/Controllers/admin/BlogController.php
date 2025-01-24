<?php

namespace App\Http\Controllers\admin;

use App\Models\Tag;
use App\Models\Blog;
use App\Helper\Helper;
use App\Models\Dropdown;
use Illuminate\Http\Request;
use App\Models\DropdownOption;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\admin\BlogSaveUpdateRequest;

class BlogController extends Controller
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
        if (!$this->user->can('browse_blog')) abort(403);
        $pg_header = "Blogs";
        if (view()->exists('admin.blogs.list')) {
            return view('admin.blogs.list', compact('pg_header'));
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
        if (!$this->user->can('add_blog')) abort(403);
        $pg_header = "Add Blog";
        $blogCategories = DropdownOption::getDropdownCategories('blog_category')->get();
        if (view()->exists('admin.blogs.add')) {
            return view('admin.blogs.add',compact('blogCategories','pg_header'));
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(BlogSaveUpdateRequest $request)
    {
        if (!$this->user->can('add_blog')) abort(403);

        $notification = [];
        try {
            $path = $request->cover->store('blogs');

            if(request()->has('meta_keywords')){
                $meta_keywords = implode(',',$request['meta_keywords']);
            }

            DB::beginTransaction();
                Blog::create([
                    'title' => allowWhiteSpace($request->title) ?? NULL,
                    'slug' => $request->slug ?? NULL,
                    'category_id' => $request->category_id  ?? NULL,
                    'cover' => $path ?? NULL,
                    'content' => $request->content ?? NULL,
                    'meta_title' => request()->has('meta_title') ? $request['meta_title'] : NULL,
                    'meta_keywords' => $meta_keywords ?? NULL,
                    'meta_description' => request()->has('meta_description') ? $request['meta_description'] : NULL,
                    'status' => request()->has('status') ? 1 : 0,
                    'home' => request()->has('home') ? 1 : 0,
                    'created_by' => auth()->id()
                ]);
            DB::commit();

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Blog created successfully";

        } catch (\Exception $e) {
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.";
        }

        return redirect('backoffice/blogs')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_blog')) abort(403);

        $blog = Blog::withTrashed()->where('id', $id)->firstOrFail();
        $created_at = date('d/m/Y H:i:s', strtotime($blog['created_at']));
        $updated_at = date('d/m/Y H:i:s', strtotime($blog['updated_at']));
        $pg_header = "View Blog";
        return view('admin.blogs.view', compact('blog','created_at','updated_at', 'pg_header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_blog')) abort(403);
        $blog = Blog::findOrFail($id);
        $blogCategories = DropdownOption::getDropdownCategories('blog_category')->get();

        $keywords = [];
        if(isset($blog) && isset($blog->meta_keywords) && !empty($blog->meta_keywords)){
            $keywords = explode(',',$blog->meta_keywords);
        }
        $pg_header = "Edit Blog";

        if (view()->exists('admin.blogs.edit')) {
            return view('admin.blogs.edit', compact('blog','blogCategories','keywords'));
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
    public function update(BlogSaveUpdateRequest $request, $id)
    {
        if (!$this->user->can('edit_blog')) abort(403);
        $notification = [];
        $blog = Blog::findOrFail($id);


        $path = $blog->cover;
        if(request()->hasFile('cover')){
            if(!empty($blog->cover)){
                Storage::delete($blog->cover);
            }
            $path = $request->cover->store('blogs');;
        }

        if(request()->has('meta_keywords')){
            $meta_keywords = implode(',',$request['meta_keywords']);
        }

        $blog->category_id = $request->category_id ?? NULL;
        // $blog->slug = $request->slug ?? NULL;
        $blog->title = allowWhiteSpace($request->title) ?? NULL;

        $blog->cover = $path;
        $blog->content = $request->content ?? NULL;
        $blog->meta_title = request()->has('meta_title') ? $request['meta_title'] : NULL;
        $blog->meta_keywords = $meta_keywords ?? NULL;
        $blog->meta_description = request()->has('meta_description') ? $request['meta_description'] : NULL;
        $blog->status = request()->has('status') ? 1 : 0;
        $blog->home = request()->has('home') ? 1 : 0;
        $blog->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Blog updated successfully";
        return redirect('backoffice/blogs')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_blog')) abort(403);
        $blog = Blog::where('id', $id)->firstOrFail();
        $blog->delete();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Blog deleted successfully";
        return redirect('/backoffice/blogs')->with('notification', $notification);
    }
    public function restore($id)
    {
        if (!$this->user->can('restore_blog')) abort(403);

        $blog = Blog::withTrashed()->where('id', $id)->firstOrFail();
        $blog->restore();
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Blog restored successfully";
        return redirect('/backoffice/blogs')->with('notification', $notification);
    }

    public function getBlogsDeleted()
    {
        if (!$this->user->can('browse_blog')) abort(403);

        $query = Blog::with('blogCategoryOptions:id,name')
                        ->select('id','category_id','title','slug','status','created_at')
                        ->whereNotNull('deleted_at')
                        ->onlyTrashed()
                        ->latest()
                        ->get();
                        // $query->map(function($value){
                        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                        // });
        return DataTables::of($query)
                        ->addColumn('action', function ($row) {
                            $url = route("blogs.destroy", ["blog" => $row->id]);
                            $deleteurl = route("blogs.delete", ["id" => $row->id]);
                            $button = "";
                            if (!$row->deleted_at) {
                                if ($this->user->can('delete_blog')) {
                                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                                }
                                if ($this->user->can('restore_blog')) {
                                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/blogs/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                                }
                            } else {
                                if ($this->user->can('restore_blog')) {
                                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/blogs/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                                }
                            }
                            return "<div class='d-flex justify-content-center'>$button</div>";
                        })
                        ->editColumn('category_id', function($row){
                            return isset($row->blogCategoryOptions) && !empty($row->blogCategoryOptions) ? $row->blogCategoryOptions->name : '';
                        })
                        ->editColumn('status', function($row){
                            return Helper::checkStatus($row->status);
                        })
                        ->editColumn('date', function($row){
                            return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
                        })
                        ->rawColumns(['action','status','date'])
                        ->toJson();
    }

    public function bulk_del(Request $request)
    {
        if (!$this->user->can('delete_blog')) abort(403);
        if (!empty($request->bd) > 0) {
            Blog::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Blog deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No blog selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_blog')) abort(403);

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $blog = Blog::withTrashed()->where('id',$value)->firstOrFail();
            $blog->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Blogs restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function getBlogs()
    {
        if (!$this->user->can('browse_blog')) abort(403);

        $query = Blog::with('blogCategoryOptions:id,name')
                        ->select('id','category_id','title','slug','status','created_at')
                        ->whereNull('deleted_at')
                        ->latest()
                        ->get();
                        // $query->map(function($value){
                        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                        // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("blogs.destroy", ["blog" => $row->id]);
                $button = "";
                if ($this->user->can('read_blog')) {
                    $button .= '<a class="mx-1" title="View" target="_blank" href="' . url('blogs/' . $row->slug) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_blog')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/blogs/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_blog')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_blog')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/blogs/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('category_id', function($row){
                return isset($row->blogCategoryOptions) && !empty($row->blogCategoryOptions) ? $row->blogCategoryOptions->name : '';
            })
            ->editColumn('status', function($row){
                return Helper::checkStatus($row->status);
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','status','date'])
            ->toJson();
    }

    public function delete($id)
    {
        if (!$this->user->can('delete_blog')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $blog= Blog::withTrashed()->where('id', $id)->firstOrFail();
        $blog->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Blog deleted successfully";

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
        if (!$this->user->can('delete_blog')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $blog= Blog::withTrashed()->where('id', $id)->firstOrFail();
                $blog->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Blogs deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Blog selected";
        }
        return redirect()->back()->with('notification', $notification);
  }


}
