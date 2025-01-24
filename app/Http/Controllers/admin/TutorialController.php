<?php

namespace App\Http\Controllers\admin;

use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DataTables;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TutorialController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }


    public function index(Request $request)
    {



        if ($request->ajax()) {
            $data = Tutorial::orderBy('id', 'desc')->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = "";


                    if (auth()->user()->hasRole(User::INSTRUCTOR)) {

                        if (auth()->user()->can("read_tutorial")) {
                            $btn .= ' <div class="action-btn"><a class="mx-1 read_tutorial" data-id=' . $row->id . '  title="View" href="'. $row->link .'" target="_blank"><i class="fas fa-eye text-orange"></i></a> </div>';
                        }
                        
                    } else {
                        if (auth()->user()->can("read_tutorial")) {
                            $btn .= ' <a class="mx-1 read_tutorial" data-id=' . $row->id . '  title="View" href="' . url('backoffice/tutorial/' . $row->id) . '"><i class="fas fa-eye text-orange"></i></a>';
                        }
                    }

                    // $btn .='<a class="mx-1" target="_blank" title="View Tutorial" href='.url($row->link).'><i class="fas fa-eye text-orange"></i></a>';
                    if (auth()->user()->can("edit_tutorial")) {
                        $btn .= ' <a class="mx-1 edit_tutorial" data-id=' . $row->id . '  title="Edit" href="javascript:void(0)"><i class="fas fa-edit text-orange"></i></a>';
                    }
                    if (auth()->user()->can("delete_tutorial")) {
                        $btn .= ' <a class="mx-1 text-danger delete_tutorial" data-id=' . $row->id . '  title="Delete" href="javascript:void(0)"><i class="fas fa-trash-alt" style="margin-top: 20px;"></i></a>';
                    }
                    return $btn;
                })->addColumn("link", function ($row) {
                    return '<video width="240" height="240" controls>
                        <source src=' . url("storage/tutorial/" . $row->link) . ' type="video/mp4">
                        Your browser does not support the video tag.
                      </video>';
                })
                ->rawColumns(['action', "link"])
                ->make(true);
        }


        $pg_header = "Tutorial";
        return view("admin.tutorial.index", compact("pg_header"));
    }

    public function tutorialStore(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'link' => 'required|url',
        ], [
            //"link"=>"The video field is required.",
            // "link.mimetypes"=>"Only video file are allowed."
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ]);
        }

        // $name = $request->link->getClientOriginalName();
        // Storage::disk("public")->putFileAs('tutorial', new File($request->link),$name);
        $data = new Tutorial();
        $data->title = $request->title;
        $data->link = $request->link;
        $data->description = $request->description;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "Tutorial created successfully"
        ]);
    }
    public function tutorialUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'link' => 'required|url',
        ], [
            // "link.mimetypes"=>"Only video file are allowed."
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ]);
        }
        $data = Tutorial::find($request->id);
        // if($request->link) {
        //     $name = $request->link->getClientOriginalName();
        //     Storage::disk("public")->putFileAs('tutorial', new File($request->link),$name);
        //     $data->link = $name;
        // }
        $data->link = $request->link;
        $data->title = $request->title;
        $data->description = $request->description;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "Tutorial updated successfully"
        ]);
    }

    public function tutorialEdit(Request $request)
    {
        $data = Tutorial::find($request->id);
        return response()->json($data);
    }

    public function show($id)
    {
        if (!$this->user->can('read_tutorial')) abort(403);
        $data = Tutorial::find($id);
        $pg_header = "View Tutorial";
        if (view()->exists('admin.tutorial.view')) {
            return view('admin.tutorial.view', compact('data', 'pg_header'));
        }
        abort(404);
    }


    public function tutorialDelete(Request $request)
    {
        $data = Tutorial::find($request->id);
        if ($data) {
            $data->delete();
            return response()->json("data deleted successfully");
        }
    }
}
