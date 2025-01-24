<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Helper\Helper;
use App\Models\Support;
use App\Models\Contact;
use App\Models\SupportChat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\SupportTicketChatMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupportTicketCreatedMail;


class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    public function index()
    {

        if (!$this->user->can('browse_support')) abort(403);

        $pg_header = "Support Ticket";

        if (view()->exists('admin.support.list')) {
            return view('admin.support.list', compact('pg_header'));

            abort(404);
        }
    }

    public function readAllSupportTicket()
    {

        if (!$this->user->can('browse_support')) abort(403);

        $pg_header = "Support Ticket";

        Support::where("created_by", "!=", auth()->id())->update(["is_read" => 1]);

        if (view()->exists('admin.support.list')) {
            return view('admin.support.list', compact('pg_header'));

            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_support')) abort(403);
        if (view()->exists('admin.support.add')) {
            return view('admin.support.add');
        }
        abort(404);
    }

    public function readNotification($id)
    {

        $support = Support::where("id", $id)->update(["is_read" => 1]);

        return redirect()->route('support-ticket.index')->with('success', 'Support Ticket read successfully.');
    }

    public function readContact($id)
    {

        $contact = Contact::where("id", $id)->update(["is_read" => 1]);


        return redirect()->route('contact.index')->with('success', 'Contact read successfully.');
    }


    public function readChatReply($id)
    {


        $reply = SupportChat::where("support_id", $id)->where("created_by", '<>', auth()->user()->id)->update(["is_read" => 1]);

        $replyCount = SupportChat::where("support_id", $id)->where("is_read", 0)->where("created_by", '<>', auth()->user()->id)->count();

        $response = [
            'success' => true,
            'message' => 'Chat read successfully.',
            'reply_count' => $replyCount
        ];

        // Return JSON response
        return response()->json($response);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        if (!$this->user->can('add_support')) abort(403);
        $notification = [];
        $validated = $request->validate([
            // 'subject' => 'required|filled|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
            'subject' => 'required|filled',
            'description' => ['required', 'filled'],
        ]);

        $users = User::role('admin')->where('id', '!=', auth()->id())->select(['id', 'name', 'email'])->get();

        $instructor = User::select(['id', 'name', 'email'])->where('id', auth()->id())->firstOrFail();
        
        $support = Support::create([
            'subject' => allowWhiteSpace($validated['subject']) ?? NULL,
            'description' => $request->description ?? NULL,
            'created_by' => auth()->id()
        ]);

        
        $subject = "New support ticket #" . $support->id . " has been created by " . $instructor->name;
        $url = route('support-ticket.index');
        if (isset($users) && !empty($users)) {
            foreach ($users as $user) {
                Mail::to($user->email)->send(new SupportTicketCreatedMail($user, $instructor, $support, $url, $subject));
            }
        }

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Support Ticket added successfully";
        return redirect('backoffice/support-ticket')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        
        if (!$this->user->can('read_support')) abort(403);
        $support = Support::with('user:id,name')->findOrFail($id);
        $pg_header = "View Support Ticket";
        if (view()->exists('admin.support.view')) {
            return view('admin.support.view', compact('support', 'pg_header'));
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->can('edit_support')) abort(403);
        $support = Support::select('id', 'subject', 'description')->where('id', $id)->firstOrFail();
        $pg_header = "Edit Support-Ticket";

        if (view()->exists('admin.support.edit')) {
            return view('admin.support.edit', compact('support', 'pg_header'));
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Support $support, $id)
    {
        if (!$this->user->can('edit_support')) abort(403);
        $notification = [];
        $support = Support::findOrFail($id);

        $validated = $request->validate([
            // 'subject' => ['required','filled',"regex:/^[a-zA-ZÑñ\s]+$/"],
            'subject' => ['required', 'filled'],
            'description' => ['required', 'filled'],
        ]);

        $support->subject = allowWhiteSpace($validated['subject']) ?? NULL;
        $support->description = $validated['description'] ?? NULL;
        $support->updated_by = auth()->id();
        $support->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Support Ticket updated successfully";
        return redirect('backoffice/support-ticket')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_support')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $support = Support::where('id', $id)->firstOrFail();
        $support->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Support Ticket deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }


    public function getSupports()
    {
        if (!$this->user->can('browse_support')) abort(403);
        $query = Support::with(['user', 'supportChats', 'supportChats.user']);
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

                $reply_count = SupportChat::where("is_read", 0)->where("created_by", '<>', auth()->user()->id)->where("support_id", $row->id)->count();
                $button .= view('admin.support.chatmodal', compact('row', 'reply_count'));

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

    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_support')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $support = Support::where('id', $id)->firstOrFail();
                $support->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Support Ticket deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Support selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Support reply will be save, If message is not empty.
     */
    public function supportReply(Request $request)
    {

        $notification = [];
        if (request()->has('message') && request()->filled('message') && request()->has('target_email')) {
            $supportChat = SupportChat::create([
                'support_id' => $request->support_id,
                'created_by' => auth()->id(),
                'message' => $request->message,
            ]);

            $emailArray = stringToArray($request->target_email);

            $users = User::whereIn('email', $emailArray)->select(['id', 'name', 'email'])->get();
                        
            $sender = User::select(['id', 'name', 'email'])->where('id', auth()->id())->firstOrFail();

            $subject = "New support ticket #" . $request->support_id . " has been created by " . $sender->name;

            $support = Support::select(['id'])->findOrFail($request->support_id);

            $url = route('support-ticket.index');
            if (isset($users) && !empty($users)) {

                foreach ($users as $user) {
                    Mail::to($user->email)->send(new SupportTicketChatMail($user, $sender, $support, $supportChat, $url , $subject));
                }
            }

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Message sent successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong.!";
        }
        return redirect()->route('support-ticket.index')->with('notification', $notification);
    }
}
