<?php

namespace App\Http\Controllers\admin;

use DataTables;
use App\Models\States;
use App\Models\Countries;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StateManageController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = States::with("getCountry:id,name")->select("id","country_id","name")->orderBy('id', 'desc');

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
                    if (auth()->user()->can("edit_state")) {
                        $btn .= ' <a class="mx-1 edit_state" data-id=' . $row->id . '  title="Edit" href="javascript:void(0)"><i class="fas fa-edit text-orange"></i></a>';
                    }
                    if (auth()->user()->can("delete_state")) {
                        $btn .= ' <a class="mx-1 text-danger delete_state" data-id=' . $row->id . '  title="Delete" href="javascript:void(0)"><i class="fas fa-trash-alt" style="margin-top: 20px;"></i></a>';
                    }
                    return $btn;
                })
                ->addColumn("country_id",function($row){
                    return @$row->getCountry->name;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $pg_header = "States";
        $country = Countries::all();
        return view("admin.countries.state", compact("pg_header","country"));
    }

    public function stateStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country_id' => 'required',
        ], [
            "country_id.required" => "The Country is required",
            "name.required" => "The state is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = new States();
        $data->country_id = $request->country_id;
        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "State created successfully",
        ]);
    }

    public function stateEdit(Request $request)
    {
        $data = States::find($request->id);
        return response()->json($data);
    }

    public function stateUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'country_id' => 'required',
        ], [
            "country_id.required" => "the Country is required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = States::find($request->id);
        $data->country_id = $request->country_id;
        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "State updated successfully",
        ]);
    }

    public function stateDelete(Request $request)
    {
        $data = States::find($request->id);
        if ($data) {
            $data->forceDelete();
            return response()->json("data deleted successfully");
        }
    }
}
