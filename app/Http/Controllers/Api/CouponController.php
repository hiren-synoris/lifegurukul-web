<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helper\Helper;
use App\Models\CouponUsage;
use App\Models\Course;

class CouponController extends Controller
{

    public function getCourseCoupon(Request $request)
    {
        $coupons = get_coupon_by_learner($request->learner_id, $request->course_id);
        
        if ($coupons->count() > 0) {
            $data = Helper::apiResonse(1, "Success", $coupons);
            return response()->json($data, 200);
        }

        $data = Helper::apiResonse(0, "No valid coupons available for this course", []);
        return response()->json($data, 404);

    }

}
