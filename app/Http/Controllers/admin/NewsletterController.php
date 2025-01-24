<?php

namespace App\Http\Controllers\admin;

use App\Helper\Helper;
use App\Models\NewsLetter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NewsletterController extends Controller
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
        if (!$this->user->can('browse_news_letters')) abort(403);
        $pg_header = "News Letters";
        if (view()->exists('admin.news_letters.list')) {
            return view('admin.news_letters.list', compact('pg_header'));
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

        $pg_header = "Add News Letters";
        if (!$this->user->can('add_news_letters')) abort(403);
        if (view()->exists('admin.news_letters.add')) {
            return view('admin.news_letters.add',compact("pg_header"));
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
        if (!$this->user->can('add_news_letters')) abort(403);
        $notification = [];
        $validated = $request->validate([
            'name' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
            'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i', 'email', Rule::unique('news_letters', 'email')],
        ]);


        NewsLetter::create([
            'name' => allowWhiteSpace($validated['name']) ?? NULL,
            'email' => $validated['email'] ?? NULL
        ]);

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "News Letter added successfully";
        return redirect('backoffice/news_letters')->with('notification', $notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->can('read_news_letters')) abort(403);

        $news_letter = NewsLetter::findOrFail($id);
            $news_letter->date = \Helper::date_format($news_letter->updated_at);
        $pg_header = "View News Letter";
        if (view()->exists('admin.news_letters.view')) {
            return view('admin.news_letters.view', compact('news_letter', 'pg_header'));
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
        if (!$this->user->can('edit_news_letters')) abort(403);
        $news_letter = NewsLetter::select('id','name','email')->where('id', $id)->firstOrFail();
        $pg_header = "Edit News Letter";

        if (view()->exists('admin.news_letters.edit')) {
            return view('admin.news_letters.edit', compact('news_letter', 'pg_header'));
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
        if (!$this->user->can('edit_news_letters')) abort(403);
        $notification = [];
        $newsletter = NewsLetter::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required','filled','regex:/^[a-zA-ZÑñ\s]+$/'],
            'email'=>['required','filled','regex:/(.+)@(.+)\.(.+)/i','email',Rule::unique('news_letters', 'email')->ignore($newsletter->id)],
        ]);

        $newsletter->name = allowWhiteSpace($validated['name']) ?? NULL;
        $newsletter->email = $validated['email'] ?? NULL;
        $newsletter->save();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "News Letter updated successfully";
        return redirect('backoffice/news_letters')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->can('delete_news_letters')) abort(403);
        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            $newsletter= NewsLetter::where('id', $id)->firstOrFail();
            $newsletter->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "NewsLetter deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function getNewsLetters()
    {
        if (!$this->user->can('browse_news_letters')) abort(403);
        $query = NewsLetter::latest()
                            ->get();
                            // $query->map(function($value){
                            //     $value->date = $value->created_at->format('d/m/Y H:i:s');
                            // });
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("news_letters.destroy", ["news_letter" => $row->id]);
                $button = "";

                if ($this->user->can('read_news_letters')) {
                    $button .= '<a class="mx-1" title="View" href="' . url('backoffice/news_letters/' . $row->id) . '"><i class="fas fa-eye"></i></a>';
                }
                if ($this->user->can('edit_news_letters')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/news_letters/' . $row->id . '/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                if ($this->user->can('delete_news_letters')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('date', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','date'])
            ->toJson();
    }

    public function bulk_del(Request $request)
    {
        if (!$this->user->can('delete_news_letters')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $newsletter= NewsLetter::where('id', $id)->firstOrFail();
                $newsletter->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "NewsLetters deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No NewsLetter selected";
        }
        return redirect()->back()->with('notification', $notification);
    }

}
