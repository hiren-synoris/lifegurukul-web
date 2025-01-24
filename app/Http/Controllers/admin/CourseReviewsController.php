<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Learner;
use App\Models\RatingReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class CourseReviewsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->user->can('browse_course_review')) {
            abort(403);
        }

        $pg_header = "Course Reviews";
        // $users = Learner::all();
        $user = Auth::user();
        if ($user->hasRole('instructor')) {
            $courses = Course::where('instructor_id', $user->id)->get();
        } else {
            $courses = Course::all();
        }
        if (view()->exists('admin.reviews.course.list')) {
            return view('admin.reviews.course.list', compact('pg_header', 'user', 'courses'));
        }
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_course_review')) {
            abort(403);
        }

        $course_review = RatingReview::where('id', $id)->with(['learner', 'course'])->first();
        $course_review->date = \Helper::date_format($course_review->updated_at);
        $pg_header = "View Course Review";
        if (view()->exists('admin.reviews.course.view')) {
            return view('admin.reviews.course.view', compact('course_review', 'pg_header'));
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->can('edit_course_review')) {
            abort(403);
        }

        if (!empty($id)) {
            $request->validate([
                'status' => 'required|filled',
            ]);
            $status = false;
            if ($request->status == "true") {$status = true;}
            RatingReview::where('id', $id)->update([
                'is_approve' => $status,
            ]);
        }
        return response()->json(true, 200);
    }
    public function homeReviews(Request $request)
    {
        // DD($request->status);
        if (!$this->user->can('edit_course_review')) {
            abort(403);
        }

        if ($request->status == 1) {
            RatingReview::where('id', $request->id)->update([
                'home_status' => 1,
            ]);
        } else {
            RatingReview::where('id', $request->id)->update([
                'home_status' => 0,
            ]);
        }

        return response()->json(true, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function course_reviews(Request $request)
    {

        $user_role = \Auth::user()->load('roles.permissions');

        if (!$this->user->can('browse_course_review')) {
            abort(403);
        }

        $query = RatingReview::with(['learner:id,name,email,mobile', 'course:id,instructor_id,title'])
            ->when($request->has('user_id') && !empty($request->user_id),
                function ($query) use ($request) {
                    return $query->where('learner_id', $request->user_id);
                })
            ->when($request->has('courses') && !empty($request->courses),
                function ($query) use ($request) {
                    return $query->where('course_id', $request->courses);
                })

            ->when($user_role->roles->first()->name == User::INSTRUCTOR,
                function ($query) use ($request) {
                    // dd("ok");
                    return $query->whereHas('course', function ($subquery) {
                        $subquery->where('instructor_id', auth()->user()->id);
                    });
                });

        // ->get();

        // dd($query->toSql());
        // $query->transform(function($value){
        //     $value->created_date = Helper::date_format($value->created_at);
        //     $value->comment = (strlen(strip_tags($value->comment)) > 150) ? utf8_encode(substr(strip_tags($value->comment,'...'), 0, 100))."..." :  strip_tags($value->comment);
        //     return $value;
        // })->all();

        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;

        $count_record = $query->count();
        $data = $query->skip($start)->take($pageSize);

        return DataTables::of($data)->with([
            "recordsTotal" => $count_record,
            "recordsFiltered" => $count_record,
        ])
            ->editColumn('name', function ($row) {
                return isset($row->learner) && !empty($row->learner) && !empty($row->learner->name) ? $row->learner->name : '';
            })
            ->editColumn('email', function ($row) {
                return isset($row->learner) && !empty($row->learner) && !empty($row->learner->email) ? $row->learner->email : '';
            })
            ->editColumn('mobile', function ($row) {
                return isset($row->learner) && !empty($row->learner) && !empty($row->learner->mobile) ? $row->learner->mobile : '';
            })
            ->editColumn('title', function ($row) {
                return isset($row->course) && !empty($row->course) && !empty($row->course->title) ? $row->course->title : '';
            })
            ->editColumn('comment', function ($row) {
                return (strlen(strip_tags($row->comment)) > 150) ? utf8_encode(substr(strip_tags($row->comment,'...'), 0, 100))."..." :  strip_tags($row->comment);
            })
            ->addColumn('approved_status', function ($row) {
                $status = $row->is_approve ? "checked" : "";
                $button = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input approved_cls" name="status" data-id="' . $row->id . '" id="toggleSwitch_' . $row->id . '" ' . $status . ' >
                <label class="custom-control-label" for="toggleSwitch_' . $row->id . '"></label>
                </div>';
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->addColumn('approved_home_status', function ($row) {
                $status = $row->home_status ? "checked" : "";
                $button = '<div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input home_status_cls" name="home_status" data-id="' . $row->id . '" id="toggle_homeSwitch_' . $row->id . '" ' . $status . ' >
                <label class="custom-control-label" for="toggle_homeSwitch_' . $row->id . '"></label>
                </div>';
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->addColumn('action', function ($row) {
                $button1 = "";
                $url = route("course_reviews.destroy", ["course_review" => $row->id]);

                if ($this->user->can('read_course_review')) {
                    $button1 .= '<a class="mx-1" title="View" href="' . url('backoffice/course_reviews/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }

                if ($this->user->can('edit_course_review')) {
                    $button1 .= '<a class="mx-1 edit_course_review" data-id=' . $row->id . ' title="Edit" href="javascript:void(0)"><i class="fas fa-edit"></i></a>';
                }

                return "<div class='d-flex justify-content-center'>$button1</div>";
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['name', 'title', 'approved_status', 'action', 'comment', 'created_at', 'approved_home_status'])
            ->toJson();
    }

    public function courseReviewsEdit(Request $request)
    {
        $data = RatingReview::find($request->id);

        return response()->json($data);
    }

    public function courseReviewsUpdate(Request $request)
    {
        if (!$this->user->can('edit_course_review')) {
            abort(403);
        }

        if (!empty($request->id)) {

            RatingReview::where('id', $request->id)->update([
                'comment' => $request->comment,
                'rating' => $request->rating_val,
            ]);
        }
        return response()->json([
            "status" => "1",
            "msg" => "Course Reviews updated successfully",
        ]);

    }
}
