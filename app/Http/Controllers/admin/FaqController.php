<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Helper\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
class FaqController extends Controller
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
        // if (!$this->user->can('browse_faq')) abort(403);
        $pg_header = "FAQ";
        $delete_url = url('faqs/delete');
        return view('admin.faqs.list', compact('pg_header', 'delete_url'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pg_header = "Add FAQ";
        if (!$this->user->can('add_faq')) abort(403);
        return view('admin.faqs.add',compact("pg_header"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request);
        if (!$this->user->can('add_faq')) abort(403);
        $notification = [];
        $data=[];
        $validated = $request->validate([
            'question' => ['required','filled'],
            'answer'=> 'required',
            'media'    => 'nullable|array',
            'media.*'  => 'file|mimes:jpg,jpeg,png,gif,svg,mp4,webm,mov,ogg|max:51200',
            // 'order'=> ['required','filled']
        ]);
        try {
            // $is_order_exist = Faq::where('order', $request->order)->get();
            // if(!$is_order_exist->isEmpty()){
            //     $notification['type'] = "sweet-alert";
            //     $notification['status'] = "error";
            //     $notification['title'] = "Error";
            //     $notification['msg'] = "Order already exist.";
            //     return Redirect()->back()->withInput()->with('notification', $notification);
            // }
            $mediaPaths = [];
            if ($request->hasFile('media')) {
                foreach($request->file('media') as $file) {
                    $path = $file->store('faqs', 'public');
                    $mediaPaths[] = $path;
                }
            }

            $user=Auth()->user();
            Faq::create([
                'question'=>allowWhiteSpace($request->question),
                'answer'=>$request->answer,
                'status' => $request->status ? 1 : 0,
                'order'=>$request->order,
                'media'      => json_encode($mediaPaths),
                'created_by'=>$user->id
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th->getMessage());
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "FAQ created successfully";
        return redirect('/backoffice/faq')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_faq')) abort(403);


        $faq = Faq::where('id',$id)->get()[0];
        $created_at = date('d/m/Y H:i:s', strtotime($faq['created_at']));
        $updated_at = date('d/m/Y H:i:s', strtotime($faq['updated_at']));
        $pg_header = "View Faq";
        return view('admin.faqs.view', compact('faq','created_at','updated_at', 'pg_header'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_faq')) abort(403);
        $faq = Faq::where('id', $id)->withTrashed()->get()[0];
        $pg_header = "Edit FAQ";
//dd($faq);
        return view('admin.faqs.edit', compact('faq'));
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
        if (!$this->user->can('edit_faq')) abort(403);
        $notification = [];
        $faq = Faq::find($id);

        $validated = $request->validate([
            'question' => ['required','filled'],
            'answer'=> 'required',
            'media'    => 'nullable|array',
            'media.*'  => 'file|mimes:jpg,jpeg,png,gif,svg,mp4,webm,mov,ogg|max:51200',
            // 'order' => 'required|filled'
        ]);
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach($request->file('media') as $file) {
                $path = $file->store('faqs', 'public');
                $mediaPaths[] = $path;
            }
            $faq->media = json_encode($mediaPaths);
        }
        $user=Auth()->user();
        $faq->question = allowWhiteSpace($request->question);
        $faq->answer = $request->answer;
        $faq->order = $request->order;
        $faq->status = $request->status ? 1 : 0;
        $faq->updated_by = $user->id;
        $faq->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Faq updated successfully";
        return redirect('backoffice/faq')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_faq')) abort(403);
        Faq::destroy($id);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "FAQs deleted successfully";
        return redirect('/backoffice/faq')->with('notification', $notification);
    }
    public function restore($id)
    {
        if (!$this->user->can('restore_faq')) abort(403);

        Faq::withTrashed()->where('id', $id)->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "FAQ restored successfully";
        return redirect('/backoffice/faq')->with('notification', $notification);
    }
    public function get_faqs_deleted()
    {
        if (!$this->user->can('browse_faq')) abort(403);

        $query =\DB::table('faqs')->whereNotNull('deleted_at')->get();

        // $query->map(function($value){
        //     $value->date = Helper::date_format($value->created_at);
        // });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("faq.destroy", ["faq" => $row->id]);
                $button = "";
                $deleteurl = route("faq.delete", ["id" => $row->id]);
                if ($row->deleted_at) {
                    if ($this->user->can('delete_faq')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                    if ($this->user->can('restore_faq')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/faq/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_faq')) {
                        $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/faq/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->editColumn('status', function($row){
                return Helper::checkStatus($row->status);
            })
            ->rawColumns(['action','date','status'])
            ->toJson();
    }



    public function bulk_del(Request $request)
    {
        if (!$this->user->can('delete_faq')) abort(403);
        if (!empty($request->bd) > 0) {
            Faq::destroy(array_keys($request->bd));
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "FAQs deleted successfully";
            return redirect()->back()->with('notification', $notification);
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No faq selected";
            return redirect()->back()->with('notification', $notification);
        }
    }

    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_faq')) abort(403);

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            Faq::withTrashed()->where('id',$value)->restore();
        }
        //ModelsRole::onlyTrashed()->restore(array_keys($request->all()));
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "FAQs restored successfully";
        return redirect()->back()->with('notification', $notification);
    }
    public function get_faqs()
    {
        if (!$this->user->can('browse_faq')) abort(403);

        $query = Faq::whereNull('deleted_at')->latest()->get();
        // echo "<pre>";print_r($query);die;

        // $query->map(function($value){
        //     $value->date = Helper::date_format($value->created_at);
        // });
     //   dd($query);
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("faq.destroy", ["faq" => $row->id]);
                $button = "";
                if ($this->user->can('read_faq')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/faq/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_faq')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/faq/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if (is_null($row->deleted_at)) {
                    if ($this->user->can('delete_faq')) {
                        $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                    }
                } else {
                    if ($this->user->can('restore_faq')) {
                        $button .= '<a class="mx-1 title="Restore" text-success" href="' . url('backoffice/faq/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
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
            ->toJson();
    }
    public function delete($id)
    {
        if (!$this->user->can('delete_faq')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $faq= Faq::withTrashed()->where('id', $id)->firstOrFail();
            $faq->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "FAQs deleted successfully";

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
        if (!$this->user->can('delete_faq')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $faq= Faq::withTrashed()->where('id', $id)->firstOrFail();
                $faq->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "FAQs deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No FAQ selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
    /**
     * Remove Media From storage and table.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMedia(Request $request, $id)
    {
        if (!$this->user->can('edit_faq')) abort(403);
        $faq = Faq::findOrFail($id);
        if ($faq->media) {
            $mediaFiles = json_decode($faq->media, true);
            $mediaToDelete = $request->media_path;

            $mediaFiles = array_filter($mediaFiles, function ($item) use ($mediaToDelete) {
                return $item !== $mediaToDelete;
            });
            if (Storage::disk('public')->exists('faqs/'.$mediaToDelete)) {
                Storage::disk('public')->delete('faqs/'.$mediaToDelete);
            }
            $faq->media = json_encode(array_values($mediaFiles));
            $faq->updated_by = auth()->id();
            $faq->deleted_by = auth()->id();
            $faq->save();
            return response()->json([
                'status' => 'success',
                'msg'    => 'Media deleted successfully',
            ]);
        }
        return response()->json([
            'status' => 'error',
            'msg'    => 'Media not found!',
        ]);
    }
}
