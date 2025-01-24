<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Models\Dropdown;
use Illuminate\Http\Request;
use App\Models\DropdownOption;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Chapter;
use App\Models\CourseCategory;
use App\Models\ChapterInfo;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DropdownOptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function index($id)
    {

        if (!$this->user->can('browse_dropdowns')) abort(403);
        $dropdown = Dropdown::findOrFail($id);
        $pg_header = 'Dropdown - '.$dropdown->name;
        $dropdownOptions = DropdownOption::where('dropdown_id', $id)->whereNull('deleted_at')->get();
        if(view()->exists('admin.dropdowns.list')){
            return view('admin.dropdown_options.list', compact('pg_header','dropdownOptions', 'dropdown'));
        } abort(404);
    }

    public function create($id)
    {

        $pg_header = "Add Dropdown Option";
        if (!$this->user->can('add_dropdowns')) abort(403);
        $dropdown = Dropdown::findOrFail($id);
        if (view()->exists('admin.dropdown_options.add')){
            return view('admin.dropdown_options.add', compact('dropdown','pg_header'));
        } abort(404);
    }

    public function store(Request $request, $id)
    {
        

        if (!$this->user->can('add_dropdowns')) abort(403);
        $validator = Validator::make($request->all(), [
            'dropdown_id' => 'required|exists:dropdowns,id',
            'name' => 'required|max:255',
            "image3" => "sometimes|present|mimes:jpeg,png,jpg,svg|max:2048",
            'image1' => 'sometimes|present|mimes:jpeg,png,jpg,svg|max:2048',
            'image2' => 'sometimes|present|mimes:jpeg,png,jpg,svg|max:2048',
            'slug' => [Rule::unique('dropdown_options', 'slug')->where(fn($query) => $query->where('dropdown_id', $request->dropdown_id)->whereNull('deleted_at'))]
        ], [
            "image1.mimes" => "The image must be a file of type: jpeg, png, jpg, svg.",
            "image2.mimes" => "The image must be a file of type: jpeg, png, jpg, svg.",
            "image3.mimes" => "The image must be a file of type: jpeg, png, jpg, svg."
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $validated = $validator->validated();
        
        try{
            $notification = [];
            $file_path = NULL;
            $data =[];
            DB::beginTransaction();

            $image_all = "";
            if($request->image1){
                $image_all = $request->image1;
            }
            if($request->image2){
                $image_all = $request->image2;
            }
            if($request->image3){
                $image_all = $request->image3;
            }


            if($image_all){
                $file_path = Storage::putFileAs('dropdown', $image_all, date("YmdHis").'_'.$image_all->getClientOriginalName());
            }

            $data = [
                'dropdown_id' => $validated['dropdown_id'],
                'name' => allowWhiteSpace($validated['name']),
                'slug' => $validated['slug'],
                'status' => request()->has('status') ? 1 : 0 ,
                'home' => request()->has('home') ? 1 : 0,
                'created_by' => auth()->id(),
            ];
            if(!empty($file_path)){
                $data['image'] = $file_path;
            }
            $dropdown_option = DropdownOption::create($data);

            DB::commit();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = " Category Option added successfully";

        } catch (\Exception $e){
            DB::rollback();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = $e->getMessage();
        }
        return redirect('backoffice/dropdown_options/'.$validated['dropdown_id'])->with('notification', $notification);
    }

    public function show($option_id)
    {
        // dd("");
        if (!$this->user->can('read_dropdowns')) abort(403);
        $dropdownOption = DropdownOption::with('dropdown')->where('id', $option_id)->firstOrFail();
        $created_at = date('d/m/Y H:i:s', strtotime($dropdownOption->created_at));
        $updated_at = date('d/m/Y H:i:s', strtotime($dropdownOption->updated_at));
        $pg_header = "View Dropdown - ".$dropdownOption->dropdown->name;
        return view('admin.dropdown_options.view', compact('dropdownOption','created_at','updated_at','pg_header'));
    }

    public function edit($option_id)
    {
        if (!$this->user->can('edit_dropdowns')) abort(403);
        // $dropdownOption = DropdownOption::with('dropdown')->where('id', $option_id)->firstOrFail();
        $dropdownOption = DropdownOption::whereHas('dropdown')->where('id', $option_id)->firstOrFail();
        $pg_header = "Edit Dropdown - ".($dropdownOption->dropdown->name ?? '');
        return view('admin.dropdown_options.edit', compact('dropdownOption'));
    }

    public function update(Request $request, $option_id)
    {
        if (!$this->user->can('add_dropdowns')) abort(403);
        $notification = [];
        $dropdownOption = DropdownOption::findOrFail($option_id);
        $validated = $request->validate([
            'dropdown_id' => 'required|exists:dropdowns,id',
            'name' => 'required|max:255',
            "image3" => "sometimes|present|mimes:jpeg,png,jpg,svg|max:2048",
            'image1' => 'sometimes|present|mimes:jpeg,png,jpg,svg|max:2048',
            'image2' => 'sometimes|present|mimes:jpeg,png,jpg,svg|max:2048',
        ],[
            // "image1.max"=>"The image must be less than 500KB.",
            "image1.mimes"=>"The image must be a file of type: jpeg, png, jpg, svg.",
            // "image1.dimensions"=>"The image has invalid image dimensions.",
            // "image2.max"=>"The image must be less than 500KB.",
            "image2.mimes"=>"The image must be a file of type: jpeg, png, jpg, svg.",
            // "image2.dimensions"=>"The image has invalid image dimensions.",
            "image3.image"=>"The file must be an image."
        ]);

        $dropdownOption->name = allowWhiteSpace($validated['name']);
        $file_path = $dropdownOption->image;

        $image_all = "";
        if($request->image1){
            $image_all = $request->image1;
        }
        if($request->image2){
            $image_all = $request->image2;
        }
        if($request->image3){
            $image_all = $request->image3;
        }

        if($image_all){
            if(!empty($dropdownOption->image)){
                Storage::delete($dropdownOption->image);
            }
            $file_path = Storage::putFileAs('dropdown', $image_all, date("YmdHis").'_'.$image_all->getClientOriginalName());
        }
        $dropdownOption->image = $file_path;
        $dropdownOption->status = request()->has('status') ? 1 : 0;
        $dropdownOption->home = request()->has('home') ? 1 : 0;
        $dropdownOption->updated_by = auth()->id();
        $dropdownOption->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Option updated successfully";
        return redirect('backoffice/dropdown_options/'.$validated['dropdown_id'])->with('notification', $notification);
    }

    public function destroy($option_id)
    {
        if (!$this->user->can('delete_dropdowns')) abort(403);
        $notification = [];
        $dropdown = DropdownOption::where('id',$option_id)->firstOrFail();
        $dropdown->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Option deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function restore($option_id)
    {
        if (!$this->user->can('restore_dropdowns')) abort(403);
        $notification = [];
        $dropdownOption = DropdownOption::withTrashed()->where('id',$option_id)->first();
        $dropdownOption->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Option restored successfully";

        return redirect()->back()->with('notification', $notification);
    }


    /**
     * Get specific Dropdown Options record where not deleted.
     */
    public function get_dropdown_options($id)
    {
        if (!$this->user->can('browse_dropdowns')) abort(403);
        $query = DB::table('dropdown_options')->select('id','name','slug','image','status','created_at','deleted_at')->whereNull('deleted_at')->where('dropdown_id', $id)->latest()->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
                    ->addColumn('action', function ($row) {
                        $url = route("dropdown_options.destroy", ["option_id" => $row->id]);
                        $button = "";

                        if ($this->user->can('read_dropdowns')) {
                            $button .= '<a class="mx-1" title="View" href="' . url('backoffice/dropdown_options/' . $row->id. '/view') . '"><i class="fas fa-eye"></i></a>';
                        }
                        if ($this->user->can('edit_dropdowns')) {
                            $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/dropdown_options/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                        }
                        if (is_null($row->deleted_at)) {
                            if ($this->user->can('delete_dropdowns')) {
                                $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                            }
                        } else {
                            if ($this->user->can('restore_dropdowns')) {
                                $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdown_options/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                            }
                        }

                        return "<div class='d-flex justify-content-center'>$button</div>";
                    })
                    ->editColumn('image', function($row){
                        $imageURL = str_contains(asset(Storage::url($row->image)), 'front') ? asset($row->image) : getImageIfExists($row->image, course_img_default());

                        return '<a href="'.$imageURL.'" target="_blank"><img src="'.$imageURL.'" style="height: 60px;width: 60px;"></a>';
                    })
                    ->editColumn('status', function($row){
                        return Helper::checkStatus($row->status);
                    })
                    ->editColumn('created_at', function($row){
                        return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
                    })
                    ->rawColumns(['action','image','status','created_at'])
                    ->addIndexColumn()
                    ->toJson();
    }

    /**
     * Get specific deleted Dropdown options.
     */
    public function get_dropdown_options_deleted($id)
    {
        if (!$this->user->can('browse_dropdowns')) abort(403);
        $query = DB::table('dropdown_options')->select('id','name','slug','status','created_at','deleted_at')->where('dropdown_id', $id)->whereNotNull('deleted_at')->latest()->get();
        // $query->map(function($value){
        //     $value->created_at = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
                    ->addColumn('action', function ($row) {
                        $url = route("dropdown_options.destroy", ["option_id" => $row->id]);
                        $button = "";
                        $deleteurl = route("dropdown_options.delete", ["option_id" => $row->id]);

                        if ($this->user->can('delete_dropdowns')) {
                            $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                        }
                        if ($this->user->can('restore_dropdowns')) {
                            $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdown_options/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                        }

                        // if (is_null($row->deleted_at)) {
                        //     if ($this->user->can('delete_dropdown_options')) {
                        //         $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        //     }
                        // } else {
                        //     if ($this->user->can('restore_dropdown_options')) {
                        //         $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/dropdown_options/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                        //     }
                        // }
                        return "<div class='d-flex justify-content-center'>$button</div>";
                    })
                    ->editColumn('image', function($row){
                        $imageURL = '';

                        !empty($row->image)
                            ? $imageURL = Storage::exists($row->image) ? Storage::url("public/".$row->image) : ''
                            : $imageURL = asset('admin/dist/img/avatar.png');

                        return '<a href="'.$imageURL.'" target="_blank"><img src="'.$imageURL.'" style="height: 60px;width: 60px;"></a>';
                    })
                    ->editColumn('status', function($row){
                        return Helper::checkStatus($row->status);
                    })
                    ->editColumn('created_at', function($row){
                        return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
                    })
                    ->rawColumns(['action','status','image','created_at'])
                    ->addIndexColumn()
                    ->toJson();
    }

    public function restore_all(Request $request,$id)
    {
        if (!$this->user->can('restore_dropdowns')) abort(403);
        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $dropdownOption = DropdownOption::withTrashed()->where('id',$value)->first();
            $dropdownOption->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Options restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_del(Request $request, $id)
    {
        if (!$this->user->can('delete_dropdowns')) abort(403);
        if (!empty($request->bd) > 0) {
            DropdownOption::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Options deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No dropdown option selected";
            return redirect()->back()->with('notification', $notification);
        }
    }


    public function delete($option_id)
    {

        if (!$this->user->can('delete_dropdowns')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // course category null Remove based on dropdownOptions
        // $courseIds = Course::where('course_category', $option_id)->select('id')->get()->toArray();
        // dd($courseIds);
        // if(isset($courseIds) && count($courseIds) > 0){
        //     DB::table('courses')
        //         ->whereIn('id', $courseIds)
        //         ->update(['course_category' => null]);
        // }

        // Blog Removed category id
        Blog::where('category_id', $option_id)->update(['category_id' => null]);

        // drop down Option
        $dropdownOption= DropdownOption::withTrashed()->where('id',$option_id)->firstOrFail();
        $dropdownOption->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Option deleted successfully";

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
            foreach ($request->bd as $option_id => $value) {
                // course category null Remove based on dropdownOptions
                // dd($request->bd);
                // $courseIds = Course::where('course_category', $option_id)->select('id')->get()->toArray();
                // if(isset($courseIds) && count($courseIds) > 0){
                //     DB::table('courses')
                //         ->whereIn('id', $courseIds)
                //         ->update(['course_category' => null]);
                // }
                $courseIds = CourseCategory::where('category_id', $option_id)->update(['category_id' => null]);


                // Blog Removed category id
                Blog::where('category_id', $option_id)->update(['category_id' => null]);

                // drop down Option
                $dropdownOption= DropdownOption::withTrashed()->where('id',$option_id)->firstOrFail();
                $dropdownOption->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Options deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Option selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
}
