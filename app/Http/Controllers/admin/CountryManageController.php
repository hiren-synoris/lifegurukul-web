<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Countries;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryManageController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = Countries::orderBy('id', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = "";

                    // $btn .='<a class="mx-1" target="_blank" title="View Tutorial" href='.url($row->link).'><i class="fas fa-eye text-orange"></i></a>';
                    if (auth()->user()->can("edit_country")) {
                        $btn .= ' <a class="mx-1 edit_country" data-id=' . $row->id . '  title="Edit" href="javascript:void(0)"><i class="fas fa-edit text-orange"></i></a>';
                    }
                    if (auth()->user()->can("delete_country")) {
                        $btn .= ' <a class="mx-1 text-danger delete_country" data-id=' . $row->id . '  title="Delete" href="javascript:void(0)"><i class="fas fa-trash-alt" style="margin-top: 20px;"></i></a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $pg_header = "Countries";
        return view("admin.countries.country", compact("pg_header"));
    }

    public function countryStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = new Countries();
        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "Country created successfully",
        ]);
    }

    public function countryEdit(Request $request)
    {
        $data = Countries::find($request->id);
        return response()->json($data);
    }

    public function countryUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }
        $data = Countries::find($request->id);

        $data->name = $request->name;
        $data->save();
        return response()->json([
            "status" => "1",
            "msg" => "Country updated successfully",
        ]);
    }

    public function countryDelete(Request $request)
    {
        $data = Countries::find($request->id);
        if ($data) {
            $data->forceDelete();
            return response()->json("data deleted successfully");
        }
    }
}
