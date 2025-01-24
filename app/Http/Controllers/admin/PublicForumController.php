<?php

namespace App\Http\Controllers\admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use App\Mail\DiscussionChatMail;
use App\Models\Course;
use App\Helper\Helper;
use App\Models\User;
use App\Models\Learner;
use App\Models\PublicForum;
use App\Models\PublicForumReply;
use Illuminate\Http\Request;

class PublicForumController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function index($id)
    {


        if (!$this->user->can('browse_discussion')) abort(403);

        $pg_header = "";

        $getPublic_forum = PublicForum::where('course_id', $id)->orderBy('id', 'desc')->get();


        if (isset($getPublic_forum) && !empty($getPublic_forum) && count($getPublic_forum) > 0) {
            foreach ($getPublic_forum as $key => $value) {


                if (isset($value->created_by_learner) && !empty($value->created_by_learner)) {
                    $getPublic_forum[$key]['created_by'] = Learner::where('id', $value->created_by_learner)->select('name', 'profile_pic')->first();

                    if (!empty($getPublic_forum[$key]['created_by'])) {
                        $getPublic_forum[$key]['created_by']['profile_pic'] = getImageIfExists($getPublic_forum[$key]['created_by']->profile_pic);
                    }
                } else if (isset($value->created_by_admin) && !empty($value->created_by_admin)) {
                    $getPublic_forum[$key]['created_by'] = User::where('id', $value->created_by_admin)->select('name', 'profile_picture as profile_pic')->first();

                    if (!empty($getPublic_forum[$key]['created_by'])) {
                        $getPublic_forum[$key]['created_by']['profile_pic'] = getImageIfExists($getPublic_forum[$key]['created_by']->profile_pic);
                    }
                }

                $getPublic_forum[$key]['reply'] = PublicForumReply::with('user', 'learner')->where('public_forums_id', $value->id)->orderBy('id', 'desc')->get();
            }
        }

        if (view()->exists('admin.courses.public-forum.list')) {
            return view('admin.courses.public-forum.list', compact('pg_header', 'getPublic_forum', 'id'));
        } else {
            abort(404);
        }
    }



    public function create()
    {

        if (!$this->user->can('add_discussion')) abort(403);
        if (view()->exists('admin.public-forum.add')) {
            return view('admin.public-forum.add');
        }
        abort(404);
    }

    public function deleteQuestion($id)
    {

        if (!$this->user->can('delete_discussion')) abort(403);


        if ($id) {

            $courseId = PublicForum::where('id', $id)->first();

            PublicForum::where('id', $id)->delete();
            PublicForumReply::where('public_forums_id', $id)->delete();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Discussion deleted successfully";

            return redirect()->route('courses.public-forum', ['id' => $courseId->course_id])->with('notification', $notification);
        }
    }

    public function deleteReply($id)
    {

        if (!$this->user->can('delete_discussion')) abort(403);

        if ($id) {

            $getReply = PublicForumReply::where('id', $id)->first();
            $courseId = PublicForum::where('id', $getReply->public_forums_id)->first();
            PublicForumReply::where('id', $id)->delete();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Reply deleted successfully";

            return redirect()->route('courses.public-forum', ['id' => $courseId->course_id])->with('notification', $notification);
        }
    }

    public function store(Request $request)
    {


        if (!$this->user->can('add_discussion')) abort(403);
        $notification = [];
        $validated = $request->validate([
            'description' => [
                'required',
                'filled',
                function ($attribute, $value, $fail) {
                    $value = str_replace('&nbsp;', ' ', $value);
                    $trimmedValue = trim($value);
                    if ($trimmedValue === '') {
                        $fail('The ' . $attribute . ' field must contain at least one non-whitespace character.');
                    }
                }
            ],
            'image' => 'image|mimes:png,jpeg,jpg,svg|max:2048',
        ]);


        $users = User::role('admin')->select(['id', 'name', 'email'])->get();
        $instructor = User::select(['id', 'name', 'email'])->where('id', auth()->id())->firstOrFail();

        $content = $request->description;
        $dom = new \DomDocument();
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imageFile = $dom->getElementsByTagName('img');

        foreach ($imageFile as $item => $image) {
            $data = $image->getAttribute('src');
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            $imageData = base64_decode($data);
            $image_name = "/upload/" . time() . $item . '.png';
            $path = public_path() . $image_name;

            // Create directory if it doesn't exist
            $directory = public_path('upload');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write image data to file
            file_put_contents($path, $imageData);

            $image->removeAttribute('src');
            $image->setAttribute('src', $image_name);
        }


        $content = $dom->saveHTML();

        // dd(auth()->id());

        // if ($request->hasFile('profile_pic')) {
        //     $profilePicPath = $request->file('profile_pic')->store('users', 'public');
        //     $input['avatar'] = $profilePicPath;
        // }

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = time() . '_' . $file->getClientOriginalName();

            $location = $file->storeAs('discussion/Image', $filename);

            $data['image'] = $location;
        }


        $public_forum = PublicForum::create([
            'description' => $content ?? NULL,
            'created_by_admin' => auth()->id(),
            'course_id' => $request['course_id'],
            'image' => isset($data['image']) ? $data['image'] : null,
        ]);

        $url = route('support-ticket.index');


        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Discussion added successfully";

        $getPublic_forum = PublicForum::get();

        return redirect()->route('courses.public-forum', ['id' => $request['course_id']])->with('notification', $notification, 'getPublic_forum', $getPublic_forum);
    }

    public function getPublicForum()
    {
        if (!$this->user->can('browse_discussion')) abort(403);
        $query = PublicForum::with(['learner']);
        if (auth()->user()->hasRole(User::INSTRUCTOR)) {
            $query = $query->where('created_by', auth()->id());
        }
        $query = $query->latest()->get();


        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("support-ticket.destroy", ["support_ticket" => $row->id]);
                $user = Auth()->user();

                $button = "";
                if ($this->user->can('read_support')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/support-ticket/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_support')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/support-ticket/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }

                // chat button html(model code)
                $button .= view('admin.support.chatmodal', compact('row'));

                if ($this->user->can('delete_support')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('ticket_no', function ($row) {
                return "<strong>#" . $row->id . "</strong>";
            })
            ->editColumn('created_by', function ($row) {
                return isset($row->user) && !empty($row->user) ? $row->user->name : '';
            })
            ->editColumn('date', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'date', 'ticket_no'])
            ->toJson();
    }

    public function replyPublicForum(Request $request)
    {


        if (!$this->user->can('add_discussion')) abort(403);

        $notification = [];
        $validated = $request->validate([
            'reply' => [
                'required',
                'filled',
                function ($attribute, $value, $fail) {

                    $plainTextValue = strip_tags($value);

                    $plainTextValue = str_replace('&nbsp;', ' ', $plainTextValue);

                    $trimmedValue = trim($plainTextValue);

                    if ($trimmedValue === '') {
                        $fail('The ' . $attribute . ' field must contain at least one non-whitespace character.');
                    }
                },
            ],
            'reply_image' => 'image|mimes:png,jpeg,jpg,svg|max:2048',
        ]);


        // dd("sdsdsdsd");
        $users = User::role('admin')->select(['id', 'name', 'email'])->get();
        $instructor = User::select(['id', 'name', 'email'])->where('id', auth()->id())->firstOrFail();

        $content = $request->reply;
        $dom = new \DomDocument();
        $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imageFile = $dom->getElementsByTagName('img');

        foreach ($imageFile as $item => $image) {
            $data = $image->getAttribute('src');
            list($type, $data) = explode(';', $data);
            list(, $data)      = explode(',', $data);
            $imageData = base64_decode($data);
            $image_name = "/upload/" . time() . $item . '.png';
            $path = public_path() . $image_name;

            // Create directory if it doesn't exist
            $directory = public_path('upload');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write image data to file
            file_put_contents($path, $imageData);

            $image->removeAttribute('src');
            $image->setAttribute('src', $image_name);
        }


        $content = $dom->saveHTML();

        if ($request->hasFile('reply_image')) {
            $file = $request->file('reply_image');

            $filename = time() . '_' . $file->getClientOriginalName();

            $location = $file->storeAs('discussion/Image', $filename);

            $data['image'] = $location;
        }


        $public_forum = PublicForumReply::create([
            'reply' => $content ?? NULL,
            'reply_by_admin' => auth()->id(),
            'public_forums_id' => $request['public_forums_id'],
            'image' => isset($data['image']) ? $data['image'] : null,
        ]);


        $getForum = PublicForum::where('id', $request['public_forums_id'])->first();

        
        if (isset($getForum) && !empty($getForum)) {

            if (!empty($getForum->created_by_learner)) {

                $user = Learner::where('id', $getForum->created_by_learner)->select(['id', 'name', 'email'])->first();
                $sender = User::select(['id', 'name', 'email'])->where('id', auth()->id())->first();
                $course_id = $getForum->course_id;
                $course = Course::where('id', $course_id)->first();

                $url = route('courses.public-forum-front', ['id' => $course_id]);
                
                $subject = "You got reply in " . $course->title . " by " . $sender->name;

                if (isset($user) && !empty($user)) {

                    Mail::to($user->email)->send(new DiscussionChatMail($subject, $user, $sender, $course, $url));
                }
            }
        }
        

        $url = route('support-ticket.index');


        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Reply added successfully";

        // $getPublic_forum = PublicForum::get();

        return response()->json($notification);

        // return redirect('backoffice/public-forum')->with('notification', $notification);
    }

    public function getQuestion(Request $request)
    {

        if (!$this->user->can('browse_discussion')) abort(403);
        $getForum = PublicForum::where('id', $request->input('forumId'))->first();


        return response()->json($getForum);

        // return redirect('backoffice/public-forum')->with('notification', $notification);
    }
}
