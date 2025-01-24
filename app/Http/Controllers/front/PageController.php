<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{

    /**
     * Listing page for different types of page like Terms, About Us, Policy and etc based on 'slug'.
     * @return View
    */
    public function detail($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        
        if (view()->exists('front.pages.page_detail')) {
            return view('front.pages.page_detail', compact('page'));
        }
        abort(404);
    }
}
