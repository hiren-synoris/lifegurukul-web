<?php

namespace App\Http\Controllers\admin;

use DataTables;
use App\Models\Cities;
use App\Models\States;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CityManageController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = Cities::with("state:id,name")->select("id", "state_id", "name")->orderBy('id', 'desc');

            $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
            $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

            $count_record = $query->count();
            $data = $query->skip($start)->take($pageSize);

            return DataTables::of($data)->with([
                "recordsTotal" => $count_record,
                "recordsFiltered" => $count_record,
            ])
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = "";
                    if (auth()->user()->can("edit_city")) {
                        $btn .= ' <a class="mx-1 edit_city" data-id=' . $row->id . '  title="Edit" href="javascript:void(0)"><i class="fas fa-edit text-orange"></i></a>';
                    }
                    if (auth()->user()->can("delete_city")) {
                        $btn .= ' <a class="mx-1 text-danger delete_city" data-id=' . $row->id . '  title="Delete" href="javascript:void(0)"><i class="fas fa-trash-alt" style="margin-top: 20px;"></i></a>';
                    }
                    return $btn;
                })

                ->addColumn("state_id", function ($row) {
                    return @$row->state->name;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $pg_header = "Cities";
        $states = States::all();
        return view("admin.countries.city", compact("pg_header", "states"));
    }

    public function cityStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'state_id' => 'required',
        ], [
            "state_id.required" => "The state is required",
            "name.required" => "The city is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = new Cities();
        $data->state_id = $request->state_id;
        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "City created successfully",
        ]);
    }

    public function cityEdit(Request $request)
    {
        $data = Cities::find($request->id);
        $state = States::where('id',$data->state_id)->first();
        return response()->json(["data"=>$data,"state"=>$state]);
    }

    public function cityUpdate(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'state' => 'required',
        ], [
            "state.required" => "The state is required",
            "name.required" => "The city is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = Cities::find($request->id);
        $data->state_id = $request->state_id;
        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "State updated successfully",
        ]);
    }

    public function cityDelete(Request $request)
    {
        $data = Cities::find($request->id);
        if ($data) {
            $data->forceDelete();
            return response()->json("data deleted successfully");
        }
    }

    public function searchState(Request $request)
    {
        $state = States::whereRaw("concat(name) like '%" . $request->term . "%' ")->paginate(50);
        $usersArray = [];
        foreach ($state as $states) {
            $usersArray[] = array(
                "label" => $states->name,
                "value" => $states->id,
            );
        }
        return response()->json($usersArray);
    }



}
