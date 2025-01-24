<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function index(Request $request){
        $faq_details = Faq::select('question','answer')->where("status",1)->orderBy('order', 'asc')->get();
        $total_cnt = count($faq_details);
        if($total_cnt > 0){
            $data = Helper::apiResonse(1, "Success", $faq_details);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, "Record does not exist.", []);
            return response()->json($data, 400);
        }

    }
}
