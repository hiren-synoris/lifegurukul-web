<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use App\Mail\DiscussionChatMail;
use Illuminate\Support\Facades\Validator;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\User;
use App\Models\Learner;
use App\Models\PublicForum;
use App\Models\PublicForumReply;
use Illuminate\Http\Request;

class PublicForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:learner');
    }

    public function index($id)
    {

        $learner_id = Auth::guard('learner')->id();
        
        $pg_header = "Public Forum";
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

        
        if (view()->exists('front.course.public-forum.list')) {
            return view('front.course.public-forum.list', compact('pg_header', 'getPublic_forum', 'id'));

            abort(404);
        }
    }

    public function create()
    {

        if (!$this->user->can('add_forums')) abort(403);
        if (view()->exists('admin.public-forum.add')) {
            return view('admin.public-forum.add');
        }
        abort(404);
    }

    public function store(Request $request)
    {

        $learner_id = Auth::guard('learner')->id();

        $notification = [];
        $validated = $request->validate([
            'description' => [
                'required',
                'filled',
                function ($attribute, $value, $fail) {
                    // Convert non-breaking space entities to regular spaces
                    $value = str_replace('&nbsp;', ' ', $value);

                    // Trim white spaces from the value
                    $trimmedValue = trim($value);

                    // Check if the trimmed value is empty
                    if ($trimmedValue === '') {
                        $fail('The ' . $attribute . ' field must contain at least one non-whitespace character.');
                    }
                },
            ],
            'image' => 'image|mimes:png,jpeg,jpg,svg|max:2048',

        ]);

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


        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = time() . '_' . $file->getClientOriginalName();

            $location = $file->storeAs('discussion/Image', $filename);

            $data['image'] = $location;
        }

        $public_forum = PublicForum::create([
            'description' =>  $content,
            'created_by_learner' => $learner_id,
            'course_id' => $request['course_id'],
            'image' => isset($data['image']) ? $data['image'] : null,
        ]);



        $course_id = $request['course_id'];
        $course = Course::where('id', $request['course_id'])->first();

        $user = User::where('id', $course->instructor_id)->select(['id', 'name', 'email'])->first();

        $sender = Learner::select(['id', 'name', 'email'])->where('id', auth()->id())->first();

        $url = route('courses.public-forum', ['id' => $course_id]);
        $subject = "New discussion comment added in " . $course->title . " by " . $sender->name;

        if (isset($user) && !empty($user)) {
            Mail::to($user->email)->send(new DiscussionChatMail($subject, $user, $sender, $course, $url));
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Discussion added successfully";
        $getPublic_forum = PublicForum::get();


        return redirect()->route('courses.public-forum-front', ['id' => $request['course_id']])->with('notification', $notification, 'getPublic_forum', $getPublic_forum);

        // return redirect('backoffice/public-forum')->with('notification', $notification, 'getPublic_forum', $getPublic_forum);
    }

    public function getPublicForum()
    {
        if (!$this->user->can('browse_forums')) abort(403);
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

    public function replyPublicForumFront(Request $request)
    {

        $learner_id = Auth::guard('learner')->id();

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

        // dd(auth()->id());
        $public_forum = PublicForumReply::create([
            'reply' => $content ?? NULL,
            'reply_by_learner' => $learner_id,
            'public_forums_id' => $request['public_forums_id'],
            'image' => isset($data['image']) ? $data['image'] : null,
        ]);


        $getForum = PublicForum::where('id', $request['public_forums_id'])->first();
        $course_id = $getForum->course_id;
        $course = Course::where('id', $course_id)->first();

        $user = User::where('id', $course->instructor_id)->select(['id', 'name', 'email'])->first();

        $sender = Learner::select(['id', 'name', 'email'])->where('id', auth()->id())->first();


        $url = route('courses.public-forum', ['id' => $course_id]);
        $subject = "You got reply in " . $course->title . " by " . $sender->name;

        if (isset($user) && !empty($user)) {
            Mail::to($user->email)->send(new DiscussionChatMail($subject, $user, $sender, $course_id, $url));
        }


        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Reply added successfully";

        // dd($notification);
        // $getPublic_forum = PublicForum::get();

        return response()->json($notification);
    }

    public function getQuestion(Request $request)
    {

        $getForum = PublicForum::where('id', $request->input('forumId'))->first();

        return response()->json($getForum);

        // return redirect('backoffice/public-forum')->with('notification', $notification);
    }

    public function publicForumDelete(Request $request)
    {

        if (isset($request->description_id)) {
            $notification = [];
            $courseId = PublicForum::where('id', $request->description_id)->first();

            PublicForum::where('id', $request->description_id)->delete();
            PublicForumReply::where('public_forums_id', $request->description_id)->delete();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Discussion deleted successfully";

            return redirect()->route('courses.public-forum-front', ['id' => $courseId->course_id])->with('notification', $notification);
        }
    }

    public function publicForumReplyDelete(Request $request)
    {

        if (isset($request->reply_id)) {
            $notification = [];
            $getReply = PublicForumReply::where('id', $request->reply_id)->first();
            $courseId = PublicForum::where('id', $getReply->public_forums_id)->first();
            PublicForumReply::where('id', $request->reply_id)->delete();
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Reply deleted successfully";

            return redirect()->route('courses.public-forum-front', ['id' => $courseId->course_id])->with('notification', $notification);
        }
    }
}
