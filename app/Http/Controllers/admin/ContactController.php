<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Models\Contact;
use App\Mail\ContactEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
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
        if (!$this->user->can('browse_contact')) abort(403);
        $pg_header = "Contact Us";
        if (view()->exists('admin.contact.list')) {
            return view('admin.contact.list', compact('pg_header'));
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
        if (!$this->user->can('add_contact')) abort(403);
        if (view()->exists('admin.contact.add')) {
            return view('admin.contact.add');
        }
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->can('add_contact')) abort(403);
        $notification = [];
        $validated = $request->validate([
            'name' => 'required|filled',
            'email'=>['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('contact_us', 'email')],
            'mobile'=>['required','filled','regex:/^([0-9]*)$/','min:10',Rule::unique('contact_us', 'mobile')],
            'description'=>['required','filled'],
        ]);

        Contact::create([
            'name' => $validated['name'] ?? NULL,
            'email' => $validated['email'] ?? NULL,
            'mobile' => $validated['mobile'] ?? NULL,
            'description' => $validated['description'] ?? NULL
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Contact added successfully";
        return redirect('backoffice/contact')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_contact')) abort(403);
        $contact = Contact::findOrFail($id);
        $pg_header = "View Contact Us";
        if (view()->exists('admin.contact.view')) {
            return view('admin.contact.view', compact('contact', 'pg_header'));
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
        if (!$this->user->can('edit_contact')) abort(403);
        $contact = Contact::select('id','name','email','mobile','description')->where('id', $id)->firstOrFail();
        $pg_header = "Edit Contact Us";

        if (view()->exists('admin.contact.edit')) {
            return view('admin.contact.edit', compact('contact', 'pg_header'));
        }
        abort(404);
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
        if (!$this->user->can('edit_contact')) abort(403);
        $notification = [];
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required','filled'],
            'email' => ['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('contact_us', 'email')->ignore($contact->id)],
            'mobile' => ['required','filled','regex:/^([0-9]*)$/','min:10',Rule::unique('contact_us', 'mobile')->ignore($contact->id)],
            'description' => ['required','filled'],
        ]);

        $contact->name = $validated['name'] ?? NULL;
        $contact->email = $validated['email'] ?? NULL;
        $contact->mobile = $validated['mobile'] ?? NULL;
        $contact->description = $validated['description'] ?? NULL;
        $contact->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Contact updated successfully";
        return redirect('backoffice/contact')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_contact')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $newsletter= Contact::where('id', $id)->firstOrFail();
            $newsletter->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Contact deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function getContacts()
    {
        if (!$this->user->can('browse_contact')) abort(403);
        $query = Contact::latest()
                            ->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("contact.destroy", ["contact" => $row->id]);
                $button = "";
                if ($this->user->can('read_contact')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/contact/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if(isset($row->id) && isset($row->name) && isset($row->email) && !empty($row->name) && !empty($row->email)){
                    $button .= view('admin.contact.contact-reply', compact('row'));
                }
                // if ($this->user->can('edit_contact')) {
                //     $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/contact/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                // }
                // if ($this->user->can('delete_contact')) {
                //     $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('ticket_no', function($row){
                return "<strong>#".$row->id."</strong>";
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','date','ticket_no'])
            ->toJson();
    }

    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_contact')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $newsletter= Contact::where('id', $id)->firstOrFail();
                $newsletter->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Contacts deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Contact selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Reply to contact(Contact will received an email)
     */
    public function ReplyToContact(Request $request)
    {
        $notification = [];
        if(request()->has('contact_id') && request()->has('message') && request()->filled('message')){
            $contact = Contact::findOrFail($request->contact_id);
            $contact->reply = $request->message ?? NULL;
            $contact->reply_by = auth()->id();
            $contact->save();

            //Here pass 1 means Admin will reply to particular contact via mail and user will get reply from admin via mail.
            Mail::to($contact->email)->send(new ContactEmail($contact->toArray(), 1));

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Email sent successfully!";

        }else{
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "Something went wrong!";
        }
        return redirect()->route('contact.index')->with('notification', $notification);
    }
}
