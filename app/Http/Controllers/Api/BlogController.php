<?php

namespace App\Http\Controllers\Api;

use App\Models\Blog;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{


    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'blog_id' => 'nullable|exists:blogs,id',
            'page' => 'nullable',
            'per_page' => 'nullable',
          ]);

          if ($validator->fails()) {
              $data = Helper::apiResonse(0, $validator->messages(), []);
              return response()->json($data, 400);
          }
          $page_num = isset($request->page) && !empty($request->page) ? $request->page : 1;
          $per_page = isset($request->per_page) && !empty($request->per_page) ? $request->per_page : 5 ;
        $query = Blog::select('id', 'title', 'created_at', 'category_id','cover')->where("status",1)
        ->with('blogCategoryOptions');
        if(isset($request->blog_id) && !empty($request->blog_id)){
            $query = $query->where('id', $request->blog_id);
        }

        $blogs = $query->orderBy('blogs.id', 'desc')->get();
        $blogs = cpaginate($blogs, $per_page, $page_num);
        //->paginate($per_page, ['*'], 'page', $page_num);
        $total_cnt = $blogs->count();
        if($total_cnt > 0){
            foreach($blogs as $blog){
                 $blog->cover = !empty($blog->cover) ? Helper::getImageUrl($blog->cover) : '';

                $blog->category_name = $blog->blogCategoryOptions->name ?? '';
                // $blog->blog_image = $blog->blogCategoryOptions->image ?? '';
                unset($blog->blogCategoryOptions, $blog->category_id);
            }
            $is_paginated = TRUE;
            $data = Helper::apiResonse(1, "Success", $blogs, $is_paginated);
            return response()->json($data, 200);
        } else {
            $data = Helper::apiResonse(0, 'No records found.', []);
            return response()->json($data, 400);
        }

    }
    public function blogDetail(Request $request){
        $validator = Validator::make($request->all(), [
          'blog_id' => 'required|exists:blogs,id',
        ]);

        if ($validator->fails()) {
            $data = Helper::apiResonse(0, $validator->messages(), []);
            return response()->json($data, 400);
        }

        if ($request->has('blog_id') && !empty($request->blog_id) && $request->blog_id !== "") {
            $blog_details = Blog::select('id', 'title', 'created_at', 'category_id','content','cover')->where('id', $request->blog_id)->with('blogCategoryOptions')->where("status",1)->first();
            // dd($total_cnt); exit;
            if(!empty($blog_details)){
                $blog_details->cover = !empty($blog_details->cover) ? Helper::getImageUrl($blog_details->cover) : '';
            $blog_details->category_name = $blog_details->blogCategoryOptions->name ?? '';
                // $blog_details->blog_image = !empty($blog_details->image) ? Helper::getImageUrl($blog_details->image) : '';

                unset($blog_details->blogCategoryOptions, $blog_details->category_id);
                $data = Helper::apiResonse(1, "Success", $blog_details);
                return response()->json($data, 200);
            } else {
                $data = Helper::apiResonse(0, 'No records found.', []);
                return response()->json($data, 400);
            }
        } else {
            $data = Helper::apiResonse(0, 'Blog Id is required.', []);
            return response()->json($data, 400);
        }
    }
}
