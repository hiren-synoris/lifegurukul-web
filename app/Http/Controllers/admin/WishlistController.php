<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Learner;
use App\Models\Wishlist;
use App\Models\Countries;
use Illuminate\Http\Request;
use App\Exports\WishlistsExport;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function index(Request $request){
        if (!$this->user->can('browse_wishlist')) abort(403);
        $pg_header = "Wishlist";
        // $users = Learner::all();
        // $courses = Course::all();
        if (view()->exists('admin.wishlist.list')) {
            return view('admin.wishlist.list', compact('pg_header'));
        }
        abort(404);
    }

    public function searchLerner(Request $request) {
        //    dd($request->term);
            $learner = Learner::where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->term . '%')
                    ->orWhere('mobile', 'like', '%' . $request->term . '%');
            })->paginate(50);
            $usersArray = [];
            foreach($learner as $learners ){
                $usersArray[] = array(
                  "label" => $learners->name."($learners->mobile)",
                  "value" => $learners->id
                );
              }
              return response()->json($usersArray);
       }

    public function searchCourse(Request $request) {
        //    dd($request->term);
            $course = Course::where(function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->term . '%');
           })->paginate(50);
            $courseArray = [];
            foreach($course as $courses ){
                $courseArray[] = array(
                  "label" => $courses->title,
                  "value" => $courses->id
                );
              }
              return response()->json($courseArray);
       }

    public function getWishlist(Request $request)
    {
        if (!$this->user->can('browse_wishlist')) abort(403);

        $query = Wishlist::with(['learner:id,name,email,mobile,country_id','course:id,title'])
                            ->when($request->has('user_id') && !empty($request->user_id),
                            function($query) use ($request){
                                return $query->where('learner_id', $request->user_id);
                            })
                            ->when($request->has('courses') && !empty($request->courses),
                            function($query) use ($request){
                                return $query->where('course_id', $request->courses);
                            })
                            ->whereNull('deleted_at')->latest()->get();

                            // $query->map(function($value){
                            //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                            // });
                            // $query->map(function($value){
                            //     $value->updt_at = $value->created_at->format('d/m/Y H:i:s');
                            // });
        return DataTables::of($query)
            ->editColumn('name', function($row){
                return isset($row->learner) && !empty($row->learner) && !empty($row->learner->name) ? $row->learner->name : '';
            })
            ->editColumn('email', function($row){
                return isset($row->learner) && !empty($row->learner) && !empty($row->learner->email) ? $row->learner->email : '';
            })
            // ->editColumn('mobile', function($row){
            //     return isset($row->learner) && !empty($row->learner) && !empty($row->learner->mobile) ? $row->learner->mobile : '';
            // })

            ->editColumn('mobile', function ($row) {
                $country_code = Countries::where("id",@$row->learner->country_id)->first();

                return @"+$country_code->phonecode ".@$row->learner->mobile ?? '';

            })

            ->editColumn('title', function($row){
                return isset($row->course) && !empty($row->course) && !empty($row->course->title) ? $row->course->title : '';
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->editColumn('updt_at', function($row){
                return !empty($row->updated_at) ? created_at_hidden($row->updated_at). ' ' .Helper::date_format($row->updated_at) : '';
            })
            ->rawColumns(['name','title','date','updt_at'])
            ->addIndexColumn()
            ->toJson();


        // $wishlist = Wishlist::query();
        // if($request->has('user_id') && !empty($request->user_id)){
        //     $wishlist->where('user_id',$request->user_id);
        // }
        // if($request->has('courses') && !empty($request->courses)){
        //     $wishlist->where('course_id',$request->courses);
        // }
        // $query =  $wishlist->get();
        // if(count($query)) {
        //     $query->transform(function($value, $key){
        //         $value->name = Learner::find($value->user_id)->name;
        //         $value->title = Course::find($value->course_id)->title;
        //         return $value;
        //     });
        // }
        // return DataTables::of($query)->toJson();
    }

    public function exportWishlists(Request $request)
    {
        $query = Wishlist::with(['learner:id,name,email,mobile','course:id,title'])
                            ->when($request->has('user_id_hidden') && !empty($request->user_id_hidden),
                            function($query) use ($request){
                                return $query->where('learner_id', $request->user_id_hidden);
                            })
                            ->when($request->has('course_id_hidden') && !empty($request->course_id_hidden),
                            function($query) use ($request){
                                return $query->where('course_id', $request->course_id_hidden);
                            })
                            ->whereNull('deleted_at')->latest()->get();

        $arr = [];
        foreach($query as $key => $value){
            // $arr[$key]['id'] = $value->id;
            $arr[$key]['name'] = isset($value->learner) && !empty($value->learner) && !empty($value->learner->name) ? $value->learner->name : '';
            $arr[$key]['email'] = isset($value->learner) && !empty($value->learner) && !empty($value->learner->email) ? $value->learner->email : '';
            $arr[$key]['mobile'] = isset($value->learner) && !empty($value->learner) && !empty($value->learner->mobile) ? $value->learner->mobile : '';
            $arr[$key]['course_name'] = isset($value->course) && !empty($value->course) && !empty($value->course->title) ? $value->course->title : '';
            $arr[$key]['created_at'] = !empty($value->created_at) ? date("d/m/Y H:i:s", strtotime($value->created_at)) : '';
        }

        $wishlist = new WishlistsExport([$arr]);
        return Excel::download($wishlist, 'wishlists.xlsx');
    }
}
