<?php

namespace App\Http\Controllers\Api;

use App\Models\Page;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function pageDetail(Request $request){
        $validator = Validator::make($request->all(), [
          'page_id' => 'required|exists:pages,id',
        ]);
  
        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }
  
        if ($request->has('page_id') && !empty($request->page_id) && $request->page_id !== "") {
            $page_details = Page::select('id', 'name', 'body','status')->where('id',$request->page_id)->paginate();
            $total_cnt = $page_details->count();
            if($total_cnt > 0){
                $is_paginated = TRUE;
                $data = Helper::apiResonse(1, "Success", $page_details, $is_paginated);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, "Record does not exist.", []);
                return response()->json($data, 400);
            }
        } else {
            $data = Helper::apiResonse(0, "Page Id is required", []);
            return response()->json($data, 400);
        }
    }
}