<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\User;
use App\Models\DropdownOption;

class BlogController extends Controller
{

    /**
     * Listing all blogs
     * @return View
    */
    public function index()
    {
        $blogs = Blog::with('blogCategoryOptions')->where("status","1")->whereNull('deleted_at')->orderBy('created_at', 'DESC')->paginate(10);
        if (view()->exists('front.blogs.list')) {
            return view('front.blogs.list', compact('blogs'));
        }
        abort(404);
    }

    /**
     * Listing single blog based on 'slug'
     * @return View
    */
    public function detail(Request $request, $slug)
    {
        $blog = Blog::with('blogCategoryOptions')->where('slug',$slug)->whereNull('deleted_at')->firstOrFail();
        $blog->user = NULL;
        if(!empty($blog->created_by)){
            $blog->user = User::where('id',$blog->created_by)->first();
        }
        $blog->date = date('M d, Y', strtotime($blog->created_at));
        if (view()->exists('front.blogs.detail')) {
            return view('front.blogs.detail',compact('blog'));
        }
        abort(404);
    }
}
