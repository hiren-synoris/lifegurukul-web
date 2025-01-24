<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index(){
        $faqs=Faq::whereNull('deleted_at')->where("status",1)->select('id','question','answer')->orderBy('order','asc')->get()->toArray();//->paginate(10);
        if(view()->exists('front.faqs.faq')){
            return view('front.faqs.faq', compact('faqs'));
        } abort(404);
    }
}
