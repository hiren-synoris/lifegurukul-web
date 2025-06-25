<?php

namespace App\Http\Controllers\admin;

use App\Models\Slide;
use App\Helper\Helper;
use App\Models\Slider;
use App\Models\Course;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;


class SliderController extends Controller
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
        $pg_header = "Sliders";
        if(view()->exists('admin.slider.list')){
            return view('admin.slider.list', compact('pg_header'));
        } abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->can('add_slider')) abort(403);
        $sliders = Slider::whereNull('deleted_at')->select('id','name')->get();
        if (view()->exists('admin.slider.create')) {
            return view('admin.slider.create',compact('sliders'));
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

        //\Illuminate\Support\Facades\Log::debug('test');
        $request->validate([
            'caption1' => 'sometimes|present|array',
            'caption2' => 'sometimes|present|array',
            'direction' => 'sometimes|present|array',
            'c2a' => 'sometimes',
            'c2au' => 'sometimes',
            'newwin' => 'sometimes',
            'img' => 'required|array',
            'slider_image_mobile' => 'required|array',
            'caption1.*' => 'nullable',
            'caption2.*' => 'nullable',
            'direction.*' => 'nullable',
            'c2a.*' => 'nullable',
            'c2au.*' => 'nullable',
            'newwin.*' => 'sometimes',
            // 'img.*' => 'required|dimensions:max_width=1919,max_height=580',
            // 'img.*' => 'required|mimes:jpg,png,jpeg,svg',
            // 'slider_image_mobile.*' => 'required|mimes:jpg,png,jpeg,svg',
            'img.*' => 'required|mimes:jpg,jpeg,png,svg,gif,mp4,webm,mov,ogg',
            'slider_image_mobile.*' => 'required|mimes:jpg,jpeg,png,svg,gif,mp4,webm,mov,ogg',
            'name' => 'required|filled|unique:sliders,name',
            'autoplay' => 'sometimes',
            'slug' => 'required_with:name'
        ],
        [
            'img.' => 'image Shoud be required',
           // 'slider_image_mobile.' => 'All Mobile image Shoud be required'
        ],
        [
        //     'img.*' => 'All (Desktop) Image Dimention should be 1919*580',
        //    'slider_image_mobile.*' => 'All Mobile Image Dimention should be 480*256'
        ]);
        //  dd($request->all());



        $data = [];
        $data['name'] = $request->name;
        if($request->has('autoplay') && $request->autoplay = 'on') $data['autoplay'] = true;
        if($request->has('slug')) $data['slug'] = $request->slug;

        $slider = Slider::create($data);
        $total = count($request->except(['_token','name','slug','autoplay']));
        $x1 = $request->except(['_token','name','slug','autoplay']);
        //dd($x1);
$x = 0;
        foreach ($x1 as $key => $value) {
            try {
                if(count($value) > $x){
                    $x = count($value);
                }
            } catch (\Throwable $th) {


                \Illuminate\Support\Facades\Log::debug($th->getMessage());
            }
        }
        //\Illuminate\Support\Facades\Log::debug($x);
for ($i=0; $i < $x; $i++) {
    //\Illuminate\Support\Facades\Log::debug('tesr');

   // \Illuminate\Support\Facades\Log::debug('loop');
   // \Illuminate\Support\Facades\Log::info($request->img[$i]);
    //exit;

    $mobile_path = NULL;
    $flg = false;
    $mobile_img_flg = false;
    $path = NULL;
    $caption1 = NULL;
    $direction = false;
    $caption2 = NULL;
    $c2a = NULL;
    $c2au = NULL;
    $newwin = false;
    //dd($request->caption1[$i]);
    if(isset($request->img[$i]) && !empty($request->img[$i])) $flg=true;
    if(isset($request->slider_image_mobile[$i]) && !empty($request->slider_image_mobile[$i])) $mobile_img_flg=true;
    if(isset($request->caption1[$i]) && $request->has('caption1')) $caption1 = $request->caption1[$i];
    if(isset($request->caption2[$i]) && $request->has('caption2')) $caption2 = $request->caption2[$i];
    if(isset($request->direction[$i]) && $request->has('direction')) $direction = $request->direction[$i] == 'left' ? false : true;
    if(isset($request->c2a[$i]) && $request->has('c2a')) $c2a = $request->c2a[$i];
    if(isset($request->c2au[$i]) && $request->has('c2au')) $c2au = $request->c2au[$i];
    if(isset($request->newwin[$i]) && $request->has('newwin')) $newwin = $request->newwin[$i];
    //\Illuminate\Support\Facades\Log::debug($flg);
    //\Illuminate\Support\Facades\Log::debug($path);
    //\Illuminate\Support\Facades\Log::debug($caption1);
    //\Illuminate\Support\Facades\Log::debug($direction);
    //\Illuminate\Support\Facades\Log::debug($caption2);
    //\Illuminate\Support\Facades\Log::debug($c2a);
    //\Illuminate\Support\Facades\Log::debug($c2au);
    //\Illuminate\Support\Facades\Log::debug($newwin);
    if($mobile_img_flg) {
    //\Illuminate\Support\Facades\Log::debug('image in');
    try {
        //\Illuminate\Support\Facades\Log::debug('try in');
        //\Illuminate\Support\Facades\Log::debug(File($request->img[$i]));
        //\Illuminate\Support\Facades\Log::debug($request->hasFile($request->img[$i])?'yes':'no');
        $mobile_path = $request->slider_image_mobile[$i]->store('slides');          //\Illuminate\Support\Facades\Log::debug($path);
    } catch (\Throwable $th) {
        \Log::debug('Image');
        \Log::debug($th->getMessage());
        }
    }
    if($flg) {
        //\Illuminate\Support\Facades\Log::debug('image in');
        try {
            //\Illuminate\Support\Facades\Log::debug('try in');
            //\Illuminate\Support\Facades\Log::debug(File($request->img[$i]));
            //\Illuminate\Support\Facades\Log::debug($request->hasFile($request->img[$i])?'yes':'no');
            $path = $request->img[$i]->store('slides');          //\Illuminate\Support\Facades\Log::debug($path);
        } catch (\Throwable $th) {
            \Log::debug('Image');
            \Log::debug($th->getMessage());
            }
        }


     Slide::create([
      'slider_id' => $slider->id,
      'img' => $mobile_path,
      'slider_image_mobile' => $path,
      'caption1' => $caption1,
      'caption2' => $caption2,
      'direction' => $direction,
      'action_text' => $c2a,
      'action_url' => $c2au,
      'new_window' => $newwin,
      'created_by' => auth()->id(),
     ]);
}

$notification = [];
$notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Slider created successfully";
        return redirect('backoffice/slider')->with('notification', $notification);

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
        $slider=Slider::where('id',$id)->with('slides')->first();
        $courses = Course::all();

        return view('admin.slider.update',compact('slider','courses'));
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
        // dd($request->all());
        $total_json = json_decode($request->total, true);
        $has = is_array($total_json) ? count($total_json) : 0;
        // $has = count(json_decode($request->total));
        $total1 = $has > 0 ? str_replace(['[',']'],'',explode(',',$request->total)):$has;
        $total2 = $has > 0 ? (int)($has) : $has;
        $total_slide = (int)($request->total_slide);
        $slide_count = $request->has('slide_') ? count(array_keys($request->slide_)) : 1;
        $start=1;
        if($request->has('total_slide') && $total_slide == 0){
            $msg_bag = new MessageBag();
            $msg_bag->add('total', 'At least one slide is required');
            return redirect()->back()->withErrors($msg_bag);
        }
        // (($request->has('slide_') && $total_slide > $slide_count)) ||
        if((($request->has('slide_') && $total_slide > $slide_count && $request->has('img') && count(array_diff_key($request->img, $request->slide_)) <= 0)) || ($total_slide > 0 && $request->has('img') && empty($request->img)) || ($total_slide > 0 && !$request->has('img'))){
            // dd($request->all(),$slide_count,$total_slide > $slide_count ,$total_slide > 0 ,empty($request->img),($total_slide > 0 && empty($request->img)));
            $msg_bag = new MessageBag();
            $msg_bag->getMessageBag()->add('img', 'Image is required');
            return redirect()->back()->withErrors($msg_bag);
        }

        $erfg=[];
        $validator = Validator::make($request->all(),[
            // 'caption1' => 'sometimes|present|array',
            // 'caption2' => 'sometimes|present|array',
            // 'caption1_text_color' => 'nullable',
            // 'caption2_text_color' => 'nullable',
            // 'direction' => 'sometimes|present|array',
            // 'c2a' => 'sometimes',
            'c2au' => 'sometimes',
            'newwin' => 'sometimes',
            'url_mobile' => 'present|array',
            'url_mobile.*' => 'nullable|exists:courses,id',
            // 'caption1.*' => 'nullable',
            // 'caption2.*' => 'nullable',
            // 'direction.*' => 'nullable',
            // 'c2a.*' => 'nullable',
            'c2au.*' => 'nullable',
            'newwin.*' => 'sometimes',
            'img' => 'sometimes|nullable|array',
            'slider_image_mobile' => 'sometimes|nullable|array',
            // 'img.*' => 'nullable|mimes:jpg,png,jpeg,svg|dimensions:max_width=1920,max_height=700',
            // 'img.*' => 'nullable|mimes:jpg,png,jpeg,svg',
            // 'slider_image_mobile.*' => 'nullable|mimes:jpg,png,jpeg,svg|dimensions:max_width=480,max_height=256',
            // 'slider_image_mobile.*' => 'nullable|mimes:jpg,png,jpeg,svg',
            'img.*' => 'required|mimes:jpg,jpeg,png,svg,gif,mp4,webm,mov,ogg|max:51200', // max 50MB
            'slider_image_mobile.*' => 'required|mimes:jpg,jpeg,png,svg,gif,mp4,webm,mov,ogg|max:51200',
            'name' => 'required|filled|unique:sliders,name,'.$id,
            'autoplay' => 'sometimes',
            'slug' => 'required_with:name',
            // 'button_text_color'=> 'sometimes|present|array',
            // 'button_bg_color'=> 'sometimes|present|array',
            // 'transparent'=> 'sometimes|present|array',
        ],
        [
            'img' => "Image is required",
            // 'img.*.image' => 'File must be an image',
            // 'img.*.dimensions' => 'All Image Dimention should be 1920*700',
         //   'img.*.mimes' => 'File must be either jpg or png',
            // 'slider_image_mobile.*.image' => 'All Mobile Image must be an image',
            // 'slider_image_mobile.*.dimensions' => 'All Mobile Image Dimention should be 480*256',
            'slider_image_mobile.*.mimes' => 'All Mobile File must be either jpg or png'
        ]);

        if(in_array(true, $erfg) || $validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        if(isset($request->slider_image_mobile) && $request->total_slide > count($request->slider_image_mobile)){
            $validator->getMessageBag()->add('slider_image_mobile', 'Mobile image is required');
            $erfg[] = true;
        }

    }
    else{
        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    foreach($request->url_mobile as $key=>$new_url) {
        if($request->url_mobile[$key]==null) {
            Slide::where("id",$key)->update(["action_url_mobile"=>NULL]);
        }
    }

        $data = [];
        $data['name'] = $request->name;
        if($request->has('autoplay') && $request->autoplay = 'on') {
            $data['autoplay'] = true;
        }
        else{
            $data['autoplay'] = false;
        }
        // if($request->has('dots') && $request->dots = 'on') $data['dots'] = true;
        // // dd($request->speed, !empty($request->speed), $request->speed !== "NULL");
        // if($request->has('speed') && !empty($request->has('speed')) && $request->speed != "NULL" && !is_null($request->speed)) $data['speed'] = $request->speed;
        // if($request->has('arrows') && $request->arrows = 'on') $data['arrows'] = true;
        if($request->has('slug')) $data['slug'] = $request->slug;

        $slider = Slider::where('id',$id)->update($data);
        $total = count($request->except(['_token','name','slug','autoplay','total','slide_','_method','total_slide']));
        $x1 = $request->except(['_token','name','slug','autoplay','total','slide_','_method','total_slide']);
$x = [];
$y=0;
        foreach ($x1 as $key => $value) {
            if(!empty(array_filter($value))){
                try {
                    $xtr=array_keys($value);

                    $x[]=\Illuminate\Support\Arr::flatten($xtr);
                } catch (\Throwable $th) {}
            }
        }

        $x=array_unique(\Illuminate\Support\Arr::flatten($x));
        $y=NULL;
        if(!empty($x)){$y=max($x);}
        if(!is_null($y)){
            if($y == 0){$start = 0;}
for ($i=$start; $i <= $y; $i++) {
    //foreach($total1 as $key=>$value){
    $flg = false;
    $mobile_img_flg = false;
    $path = NULL;
    // $mobile_path = NULL;
    $caption1 = NULL;
    // $direction = false;
    $caption2 = NULL;
    // $c2a = NULL;
    $c2au = NULL;
    $newwin = false;
    // $btn_bg_color=NULL;
    // $caption1_text_color=NULL;
    // $caption2_text_color=NULL;
    // $button_text_color=NULL;
    $mobile_action_url=NULL;
    // $button_bg_color=NULL;
    //dd($request->caption1[$i]);
    if(isset($request->img[$i]) && !empty($request->img[$i])) $flg=true;
    if(isset($request->slider_image_mobile[$i]) && !empty($request->slider_image_mobile[$i])) $mobile_img_flg=true;
    if(isset($request->caption1[$i]) && $request->has('caption1')) $caption1 = $request->caption1[$i];
    // if(isset($request->caption1_text_color[$i]) && $request->has('caption1_text_color')) $caption1_text_color = $request->caption1_text_color[$i];
    if(isset($request->caption2[$i]) && $request->has('caption2')) $caption2 = $request->caption2[$i];
    // if(isset($request->caption2_text_color[$i]) && $request->has('caption2_text_color')) $caption2_text_color = $request->caption2_text_color[$i];
    // if(isset($request->direction[$i]) && $request->has('direction')) $direction = $request->direction[$i] == 'left' ? false : true;
    // if(isset($request->c2a[$i]) && $request->has('c2a')) $c2a = $request->c2a[$i];
    if(isset($request->c2au[$i]) && $request->has('c2au')) $c2au = $request->c2au[$i];
    if(isset($request->newwin[$i])) $newwin = true; else $newwin = false;
    // if(isset($request->transparent) && count($request->transparent) > 0 && !array_key_exists($i, $request->transparent) && array_key_exists($i, $request->button_bg_color)){
    //     $btn_bg_color=$request->button_bg_color[$i];
    // }
    // if(isset($request->caption1_text_color[$i])) $caption1_text_color = $request->caption1_text_color[$i];
    // if(isset($request->caption2_text_color[$i])) $caption2_text_color = $request->caption2_text_color[$i];
    // if(isset($request->button_text_color[$i])) $button_text_color = $request->button_text_color[$i];
    if(isset($request->url_mobile[$i]) && !empty($request->url_mobile[$i])) $mobile_action_url = $request->url_mobile[$i];

    if($flg) {
        try {

            $path = $request->img[$i]->store('slides');

        } catch (\Throwable $th) {}
        }
        if($mobile_img_flg) {
        try {
            $mobile_path = $request->slider_image_mobile[$i]->store('slides');
        } catch (\Throwable $th) {}
        }

$data1=[
    'slider_id' => $id,

    'caption1' => $caption1,
    'caption2' => $caption2,
    // 'direction' => $direction,
    // 'action_text' => $c2a,
    'action_url' => $c2au,
    'new_window' => $newwin,
    'created_by' => auth()->id(),
    'updated_by'=>auth()->id(),
    // 'button_bg_color' => $btn_bg_color,
    // 'caption1_text_color'=>$caption1_text_color,
    // 'caption2_text_color'=>$caption2_text_color,
    // 'button_text_color'=>$button_text_color,
    'action_url_mobile'=>$mobile_action_url
];

$flg=true;
// if($flg || $mobile_img_flg){
    if($flg){
    if(isset($request->img[$i]) && !empty($request->img[$i])) $data1['img'] = $path;
    if(isset($request->slider_image_mobile[$i]) && !empty($request->slider_image_mobile[$i])) $data1['slider_image_mobile'] = $mobile_path;
try {
        Slide::updateOrCreate(['id' => $i, 'slider_id'=>$id],$data1);
    } catch (\Exception $th) {
        if($th->getCode()=='23000' && isset($request->img[$i]) && !empty($request->img[$i])){
            try {
                Slide::create($data1);
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::debug($th->getMessage());
            }

        }else{
            \Illuminate\Support\Facades\Log::debug($th->getMessage());
        }
    }

}
// if($i==3){
//     DB::enableQueryLog();
//      Slide::updateOrCreate(['id'=>$i,'slider_id'=>$id],$data1);
//      dd(DB::getQueryLog());
// }
// else{
//     DB::enableQueryLog();
//     Slide::updateOrCreate(['id'=>$i,'slider_id'=>$id],$data1);
//     dd(DB::getQueryLog());
// }



}
    }
// if(($delids)>0){
//     foreach($delids as $key1=>$value1){
//         $result = Slide::where('id',$value1)->delete();

//     }
// }
$notification = [];
$notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Slider updated successfully";
        return redirect('backoffice/slider')->with('notification', $notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {

    //     $notification = [];
    //     if (!$this->user->can('delete_slider')) abort(403);
    //     //DB::enableQueryLog();
    //     $slider = Slider::where('id', $id)->first();
    //     //dd(DB::getQueryLog());
    //     //dd($slider);
    //     $slider->delete();
    //     $notification['type'] = "sweet-alert";
    //     $notification['status'] = "success";
    //     $notification['title'] = "Success";
    //     $notification['msg'] = "Slider deleted successfully";
    //     return redirect('backoffice/slider')->with('notification', $notification);
    // }


    public function get_slider()
    {
        if (!$this->user->can('browse_slider')) abort(403);
        $query = Slider::select('id','name', 'slug','created_at')->latest()->get();
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("slider.destroy", ["slider" => $row->id]);
                $deleteurl = route("slider.delete", ["id" => $row->id]);
                $button = "";
                if ($this->user->can('edit_slider')) {
                    $button .= '<a class="mx-1" title="Edit" href="' . url('backoffice/slider/' . $row->id.'/edit') . '"><i class="fas fa-edit"></i></a>';
                }
                // if ($this->user->can('delete_slider')) {
                //     $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                // }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('created_at', function($row){
                return !empty($row->created_at) ? created_at_hidden($row->created_at). ' ' .Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action','created_at'])
            ->toJson();
    }

    public function delete($id)
    {
        if (!$this->user->can('delete_slider')) abort(403);
        $notification = [];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            // slide remove remove sliders
            $slides = Slide::where('slider_id', $id)->delete();

            // Slider Remove
            $slider= Slider::withTrashed()->where('id', $id)->firstOrFail();
            $slider->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Slider deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_slider')) abort(403);
        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $id => $value) {
                $slides = Slide::where('slider_id', $id)->delete();

                // Slider Remove
                $slider= Slider::withTrashed()->where('id', $id)->firstOrFail();
                $slider->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Sliders deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Slider selected";
        }
        return redirect()->back()->with('notification', $notification);
    }
}
