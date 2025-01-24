<?php

namespace App\Http\Controllers\front;

use App\Models\Contact;
use App\Models\Countries;
use App\Mail\ContactEmail;
use App\Models\NewsLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['country'] = Countries::where("id",@Auth::guard("learner")->user()->country_id)->first();

        if (view()->exists('front.contact.index')) {
            return view('front.contact.index',$data);
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
        // dd($request->all());
        $user = Auth::guard('learner')->id();
        $countryCode = DB::table("countries")->where("phonecode",$request->countryCode)->first();
        $notification = [];
        $validated = $request->validate([
            'name' => 'required|filled',
            'email' => ['required', 'filled', 'regex:/(.+)@(.+)\.(.+)/i', 'email'],
            'mobile' => ['required', 'filled', 'regex:/^([0-9]*)$/'],
            // 'mobile'=>['required','filled','regex:/^([0-9]*)$/','min:10',Rule::unique('contact_us', 'mobile')],
            'description' => ['required', 'filled'],
            'g-recaptcha-response' => ['required'],
        ]);

        try {

            // $contact_check_24hour = Contact::where("mobile", $validated['mobile'])
            // // ->where("country_id", $countryCode->id)
            // ->where("created_at", '>=', now()->subDay())
            // ->first();
            // if($contact_check_24hour) {

            //     $notification['type'] = "sweet-alert";
            //     $notification['status'] = "success";
            //     $notification['title'] = "Thank you";
            //     $notification['msg'] = "Thank you, your query is submitted, team will contact you soon.";
            //     return redirect()->back()->with('notification', $notification);
            // }

            $contact = Contact::create([
                'name' => $validated['name'] ?? NULL,
                'email' => $validated['email'] ?? NULL,
                'mobile' => $validated['mobile'] ?? NULL,
                'description' => $validated['description'] ?? NULL,
                'learner_id' => $user ?? NULL,
                'country_id' => $countryCode->id ?? NULL
            ]);

            Mail::to($validated['email'])->send(new ContactEmail($contact));

            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Thank you";
            $notification['msg'] = "Thank you, your query is submitted, team will contact you soon";

        } catch (\Exception $e) {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = $e->getMessage();
        }
        return redirect()->back()->with('notification', $notification);
    }

    public function storeNew(Request $request)
    {

        if (view()->exists('front.contact.index')) {
            return view('front.contact.index');
        }
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
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
}
