<?php

namespace App\Http\Controllers\admin;

use App\Models\Media;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Jobs\SendMedia;
use App\Jobs\VideoUpload;
use App\Models\ChapterInfo;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\admin\UploadMediaRequest;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Illuminate\Support\Str;


class MediaController extends Controller
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
        if (!$this->user->can('browse_media')) {
            abort(403);
        }

        $pg_header = "Media";
        $course = Course::whereNull('deleted_at');

        if ((auth()->user()->roles->first()->name) == "instructor") {
            $course->where("created_by", auth()->id());
        }
        $course = $course->get();
        $assetTypes = [
            [
                'text' => 'Video',
                'value' => 0,
            ],
            [
                'text' => 'Audio',
                'value' => 1,
            ],
            [
                'text' => 'Image',
                'value' => 8,
            ],
            [
                'text' => 'PDF',
                'value' => 2,
            ],
            [
                'text' => 'File',
                'value' => 3,
            ],
        ];
        if (view()->exists('admin.media.list')) {
            return view('admin.media.list', compact('pg_header', 'course', 'assetTypes'));
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
    public function store(UploadMediaRequest $request)
    {

        // $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        // if (!$receiver->isUploaded()) {
        //     return "error";
        // }
        // $fileReceived = $receiver->receive();

        // if ($fileReceived->isFinished()) {

        //     $file = $fileReceived->getFile();

        // // $file = $request->file('file');
        // // $type = explode('/',$file->getClientMimeType());
        // $type = explode('/',$file->getMimeType());
        // $asset_type = NULL;
        // // dd($file->getMimeType());

        //     if(isset($type) && !empty($type)){
        //         if($type[0] == 'video'){
        //             $asset_type = 0;
        //         }
        //         if($type[0] == 'audio'){
        //             $asset_type = 1;
        //         }
        //         if($type[0] == 'application'){
        //             if($type[1] == 'pdf'){
        //                 $asset_type = 2;
        //             }
        //         }
        //         if($type[0] != 'video' && $type[0] != 'audio' && $type[1] != 'pdf'){
        //             $asset_type = 3;
        //         }
        //     }
        //     $path = '';
        //     if($asset_type != '0' && $asset_type != '1'){
        //         $path = Storage::putFile('media', $file);
        //     }

        //     $media_id = Media::create([
        //         'title' => !empty($file) ? explode('.',$file->getClientOriginalName())[0] : NULL,
        //         'asset_type' => $asset_type,
        //         'file_name' => $file->getClientOriginalName(),
        //         'disk' => config('filesystems.default'),
        //         'path' => $path,
        //         'extension' => $file->guessClientExtension() ?? '',
        //         'mime' => $file->getClientMimeType(),
        //         'size' => $file->getSize(),
        //         'created_by' => auth()->id(),
        //     ]);

        //     if($asset_type == '0' || $asset_type == '1'){
        //         // $title = !empty($file) ? explode('.',$file->getClientOriginalName())[0] : NULL;
        //         $isUpload = upload_vdo_cipher($media_id->id,$file);
        //         // VideoUpload::dispatch([
        //         //     "media_id" =>$media_id->id,
        //         //     "file"=>$file->getPathName(),
        //         //     'title' => !empty($file) ? explode('.',$file->getClientOriginalName())[0] : NULL,
        //         //     'user_name' =>Auth::guard("web")->user()->name,
        //         // ]);
        //         unlink($file->getPathname());
        //     }
        //     $response = [
        //         'icon' => 'success',
        //         'title' => 'success',
        //         'message' => 'Media added successfully!',
        //         'status' => 1
        //     ];
        //         return response()->json($response);
        //     }
        //     return [
        //         'status' => false,
        //         'message' => 'Error',
        //     ];

        $fileKeys = array_keys($request->file());
        $file ="";

        // dd($fileKeys);
        foreach ($fileKeys as $fileKey) {
            $receiver = new FileReceiver($fileKey, $request, HandlerFactory::classFromRequest($request));

            if (!$receiver->isUploaded()) {
                return "error";
            }

            $fileReceived = $receiver->receive();

            if ($fileReceived->isFinished()) {
                $file = $fileReceived->getFile();

                // $asset_type = $this->getAssetType($file);
                $type = explode('/', $file->getMimeType());
                $asset_type = null;
                // \Log::info( mime_content_type($file->getPathname()));

                if (isset($type) && !empty($type)) {
                    if ($type[0] == 'video') {
                        $asset_type = 0;
                    } elseif ($type[0] == 'audio'  || ($type[0] == 'application' && $type[1] == 'octet-stream')) {
                        $asset_type = 1;
                    } elseif ($type[0] == 'application' && $type[1] == 'pdf') {
                        $asset_type = 2;
                    } else {
                        $asset_type = 3;
                    }
                }


                $path = '';
                if ($asset_type != '0' && $asset_type != '1') {
                    $path = Storage::putFile('media', $file);
                }

                $media = Media::create([
                    'title' => !empty($file) ? explode('.', $file->getClientOriginalName())[0] : null,
                    'asset_type' => $asset_type,
                    'file_name' => $file->getClientOriginalName(),
                    'disk' => config('filesystems.default'),
                    'path' => $path,
                    'extension' => $file->guessClientExtension() ?? '',
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => auth()->id(),
                ]);

                if ($asset_type == '0' || $asset_type == '1') {

                    $isUpload = upload_vdo_cipher($media->id, $file);
                //     VideoUpload::dispatch([
                //     "media_id" =>$media->id,
                //     "file"=>$file->getPathName(),
                //     'title' =>  @$file->getClientOriginalName(),
                //     'user_name' =>Auth::guard("web")->user()->name,
                // ]);
            }
            unlink($file->getPathname());
        }
    }

        $response = [
            'icon' => 'success',
            'title' => 'success',
            'message' => 'Media added successfully!',
            'status' => 1,
        ];

        return response()->json($response);

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
    public function destroy(Request $request)
    {

        if (!$this->user->can('delete_media')) {
            abort(403);
        }
        $notification = [];
        $media = Media::where('id', $request->media_id)->first();
        if($media->videoId) {
            deleteVideo([$media->videoId]);
        }
        $media->delete();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Media deleted successfully";

        // return redirect()->back()->with('notification', $notification);
        return response()->json("Media deleted successfully");
    }

    /**
     * Restore specified record .
     */
    public function restore($id)
    {
        if (!$this->user->can('restore_media')) {
            abort(403);
        }

        $notification = [];
        $media = Media::withTrashed()->where('id', $id)->firstOrFail();
        $media->restore();

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Media restored successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Get all Media record where not deleted.
     */
    public function getMedia(Request $request)
    {

        if (!$this->user->can('browse_media')) {
            abort(403);
        }

        // $pageSize  = (isset($_GET["length"])) ? $_GET["length"] : 10;
        // $start  = (isset($_GET["start"])) ? $_GET["start"] : 0;


        if (($request->has("assetTypes") && $request['assetTypes'] == "all") && ($request->has("courseFilter") && $request['courseFilter'] == "all")) {

            $query = Media::with(['course'])->select('id','videoId','asset_type', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at');
                // ->orderBydesc("id");
                // ->latest();
            // ->get();

        } else if (($request->has("assetTypes")) || ($request->has("courseFilter"))) {
            $query = Media::with(['course'])->select('media.id','media.videoId','media.asset_type', 'media.title', 'media.file_name', 'media.path', 'media.updated_at', 'media.created_at', 'media.created_by');
            if ($request['assetTypes'] != 'all') {
                $query->where('media.asset_type', $request['assetTypes']);
            }

            if ($request['courseFilter'] != 'all') {
                $query->join('chapters', 'media.id', 'chapters.media_id');
                $query->where('chapters.course_id', $request['courseFilter']);
                $query->groupBy('chapters.media_id');
            }

            $query->whereNull('media.deleted_at');
            //->orderByDesc("id");
            // $query->latest();
            // $query = $query->get();

        } else {

            $query = Media::with(['course'])->select('id','videoId','asset_type', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at');
                // ->orderBydesc("id");
                // ->latest();
            // ->get();
        }
        // dd(auth()->id());
        if ((auth()->user()->roles->first()->name) == "instructor") {
            $query->where("created_by", auth()->id());
        }

        if (($request->has("created_date") && $request['created_date'] != '')){

            $all_date_arr = explode(' - ', $request['created_date']);

            $start_date = "";
            if(isset($all_date_arr[0]) && $all_date_arr[0] != '') {
                $start = str_replace('/', '-', $all_date_arr[0]);
                $start_date = date("Y-m-d", strtotime($start));
            }
            $end_date = "";
            if(isset($all_date_arr[1]) && $all_date_arr[1] != '') {
                $end = str_replace('/', '-', $all_date_arr[1]);
                $end_date = date("Y-m-d", strtotime($end));
            }

            $query->whereDate('media.created_at', '>=', $start_date);
            $query->whereDate('media.created_at', '<=', $end_date);
        }

        // $query = $query->where("id",62)->get();
        // dd($query);

        if (isset($_GET['search']['value']) && !empty($_GET['search']['value'])) {
            $searchValue = $_GET['search']['value'];
            $query = $query->where('title', 'LIKE', "%$searchValue%")
                             ->orWhere('file_name', 'LIKE', "%$searchValue%")
                             ->orWhere('videoId', 'LIKE', "%$searchValue%")
                             ->orWhereHas('course.course', function ($q) use ($searchValue) {
                                $q->where('title', 'like', "%$searchValue%");
                            });
        }

        $count_record_filtered = $query->count();

        $pageSize = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start = (isset($_GET["start"])) ? $_GET["start"] : 0;
        $data = $query->skip($start)->take($pageSize);

        return DataTables::of($data)->with([
            "recordsTotal" => $count_record_filtered,
            "recordsFiltered" => $count_record_filtered,
        ])

            ->addColumn('action', function ($row) {
                $url = route("media.destroy", ["medium" => $row->id]);

                // dd($url );
                $button = "";
                $courseString = Helper::getMediaCourse($row->id);

                if ($courseString == '') {
                    if (is_null($row->deleted_at)) {
                        // if ($this->user->can('delete_media')) {
                        //     $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        // }
                        if ($this->user->can('delete_media')) {
                            $button .= '<a class="mx-1 text-danger delete_media" title="Delete" type="button" href="javascript:void(0)" data-id=' . $row->id . '><i class="fas fa-trash-alt"></i></a>';
                        }
                    } else {
                        if ($this->user->can('restore_media')) {
                            $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/media/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                        }
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function ($row) {
                if (Storage::exists($row->path) && !empty($row->path)) {
                    $extension = pathinfo(Storage::url($row->path), PATHINFO_EXTENSION);
                    if (strtolower($extension) == 'jpg' || strtolower($extension) == 'svg' || strtolower($extension) == 'jpeg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif') {
                        return '<a href="' . Storage::url($row->path) . '" target="_blank"><img src="' . Storage::url($row->path) . '" style="height: 60px;width: 60px;"></a>';
                    } else {
                        return '<a target="_blank" href="' . Storage::url($row->path) . '"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    }
                } else {
                    return 'No Image';
                }
            })
            ->editColumn('updated_at', function ($row) {
                return !empty($row->updated_at) ? date('d M, Y', strtotime($row->updated_at)) : '';
            })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('created_by', function ($row) {
                return !empty($row->createdBy) ? $row->createdBy->name : '';
            })
            ->editColumn('course', function ($row) {
                return Helper::getMediaCourse($row->id);
            })
            ->editColumn('videoId', function ($row) {
                return $row->asset_type==0 || $row->asset_type==1 ? $row->videoId :'-';
            })
            ->editColumn('id', function ($row) {
                $courseString = Helper::getMediaCourse($row->id);
                if ($courseString == '') {
                    $checkbox = '<input type="checkbox" class="children_checkbox" name="checkbox_index[' . $row->id . ']" data-id=' . $row->id . ' style="cursor: pointer;"/>';
                    return $checkbox;
                } else {
                    return;
                }
            })
            ->rawColumns(['action', 'image', 'created_at', 'id'])
            ->addIndexColumn()
            ->toJson();
    }

    public function getMediaChpter(Request $request)
    {

        if (!$this->user->can('browse_media')) {
            abort(403);
        }

        if (($request->has("assetTypes") && $request['assetTypes'] == "all") && ($request->has("courseFilter") && $request['courseFilter'] == "all")) {

            $query = Media::with(['course'])->select('id', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at')
                ->orderBy("created_at","desc");

        } else if (($request->has("assetTypes")) || ($request->has("courseFilter"))) {

            $query = Media::with(['course'])->select('media.id', 'media.title', 'media.file_name', 'media.path', 'media.updated_at', 'media.created_at', 'media.created_by');

            if ($request['assetTypes'] != 'all') {
                $query->where('media.asset_type', $request['assetTypes']);
            }

            if ($request['courseFilter'] != 'all') {

                $query->join('chapters', 'media.id', 'chapters.media_id');
                $query->where('chapters.course_id', $request['courseFilter']);
                $query->groupBy('chapters.media_id');
            }

            $query->whereNull('media.deleted_at');
            $query->orderBy("media.created_at","desc");
            // $query = $query;



        } else {

            $query = Media::with(['course'])->select('id', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->whereNull('deleted_at')
                ->orderBy("created_at","desc");;
        }

        if (isset($_GET["search"]["value"]) && !empty($_GET["search"]["value"])) {
            $searchValue = $_GET["search"]["value"];
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', "%$searchValue%");
            });
        }


        $pageSize  = (isset($_GET["length"])) ? $_GET["length"] : 10;
        $start  = (isset($_GET["start"])) ? $_GET["start"] : 0;

        // $count_record = $query->count();
        $count_record = $query->count();
        $data = $query->skip($start)->take($pageSize);


        return DataTables::of($data)->with([
            "recordsTotal"=>$count_record,
            "recordsFiltered"=>$count_record
        ])
            ->addColumn('action', function ($row) {
                $url = route("media.destroy", ["medium" => $row->id]);

                // dd($url );
                $button = "";
                $courseString = Helper::getMediaCourse($row->id);

                if ($courseString == '') {
                    if (is_null($row->deleted_at)) {
                        // if ($this->user->can('delete_media')) {
                        //     $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=delete_confirmation("' . $url . '")><i class="fas fa-trash-alt"></i></a>';
                        // }
                        if ($this->user->can('delete_media')) {
                            $button .= '<a class="mx-1 text-danger delete_media" title="Delete" type="button" href="javascript:void(0)" data-id=' . $row->id . '><i class="fas fa-trash-alt"></i></a>';
                        }
                    } else {
                        if ($this->user->can('restore_media')) {
                            $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/media/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                        }
                    }
                }

                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function ($row) {
                if (Storage::exists($row->path) && !empty($row->path)) {
                    $extension = pathinfo(Storage::url($row->path), PATHINFO_EXTENSION);
                    if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif') {
                        return '<a href="' . Storage::url($row->path) . '" target="_blank"><img src="' . Storage::url($row->path) . '" style="height: 60px;width: 60px;"></a>';
                    } else {
                        return '<a target="_blank" href="' . Storage::url($row->path) . '"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    }
                } else {
                    return 'No Image';
                }
            })
            ->editColumn('updated_at', function ($row) {
                return !empty($row->updated_at) ? date('d M, Y', strtotime($row->updated_at)) : '';
            })
            // ->addColumn('title', function ($row) {
            //     $string = $row->title;
            //     $delimiter = '_';
            //     $count = 5;
            //     $parts = explode($delimiter, $string);
            //     $chunks = [];
            //     $temp = '';

            //     foreach ($parts as $index => $part) {
            //         if ($index % $count == 0 && $index != 0) {
            //             $chunks[] = $temp;
            //             $temp = $part;
            //         } else {
            //             $temp .= ($temp === '' ? '' : $delimiter) . $part;
            //         }
            //     }

            //     if (!empty($temp)) {
            //         $chunks[] = $temp;
            //     }

            //     return nl2br(implode("\n", $chunks));
            // })
            ->editColumn('created_at', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->editColumn('created_by', function ($row) {
                return !empty($row->createdBy) ? $row->createdBy->name : '';
            })
            // ->editColumn('course', function ($row) {
            //     return Helper::getMediaCourse($row->id);
            // })
            ->editColumn('id', function ($row) {
                // $courseString = Helper::getMediaCourse($row->id);
                $checkbox = '<input type="checkbox" class="children_checkbox" name="checkbox_index[' . $row->id . ']" data-id=' . $row->id . ' style="cursor: pointer;"/>';
                return $checkbox;

            })
            ->rawColumns(['action', 'image', 'created_at', 'id',"file_name","title"])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Get deleted Media records.
     */
    public function getMediaDeleted(Request $request)
    {
        // dd($request->assetType);
        if (!$this->user->can('browse_media')) {
            abort(403);
        }

        // $query = Media::with("course")
        //                ->onlyTrashed()
        //                 // ->oderBy()
        //                 ->select("*");

        // // $query->join('chapters', 'media.id', "=",'chapters.media_id');
        // if($request['courseFilter'] != 'all'){
        //     // $query->join('chapters', 'media.id', 'chapters.media_id');
        //     $filter= $request['courseFilter'];
        //     $query->whereHas("course",function($query)use($filter){
        //         $query->where('chapters.course_id',$filter);
        //         $query->groupBy('chapters.media_id');
        //     });
        // }
        // dd($query->get()->toarray());
        // if($request->assetType==0) {
        //     $query->where("asset_type",0);
        // }
        // if($request->assetType==1) {
        //     $query->where("asset_type",1);
        // }
        // if($request->assetType==2) {
        //     $query->where("asset_type",2);
        // }
        // if($request->assetType==3) {
        //     $query->where("asset_type",3);
        // }
        // $query->get();

        // $query->map(function($value){
        //     $value->date = $value->created_at->format('d/m/Y H:i:s');
        // });

        if (($request->has("assetTypes") && $request['assetTypes'] == "all") && ($request->has("courseFilter") && $request['courseFilter'] == "all")) {

            $query = Media::with(['course'])->select('id', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->onlyTrashed()
                ->latest()
                ->get();

        } else if (($request->has("assetTypes")) || ($request->has("courseFilter"))) {
            $query = Media::with(['course'])->select('media.id', 'media.title', 'media.file_name', 'media.path', 'media.updated_at', 'media.created_at', 'media.created_by');
            if ($request['assetTypes'] != 'all') {
                $query->where('media.asset_type', $request['assetTypes']);
            }

            if ($request['courseFilter'] != 'all') {
                $query->join('chapters', 'media.id', 'chapters.media_id');
                $query->where('chapters.course_id', $request['courseFilter']);
                $query->groupBy('chapters.media_id');
            }

            $query->onlyTrashed();
            $query->latest();
            $query = $query->get();
        } else {

            $query = Media::with(['course'])->select('id', 'title', 'file_name', 'path', 'updated_at', 'created_at', 'created_by')
                ->onlyTrashed()
                ->latest()
                ->get();
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $url = route("media.destroy", ["medium" => $row->id]);
                $deleteurl = route("media.delete", ["id" => $row->id]);
                $button = "";
                if ($this->user->can('delete_media')) {
                    $button .= '<a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=permanent_delete_confirmation("' . $deleteurl . '")><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->user->can('restore_media')) {
                    $button .= '<a class="mx-1 text-success" title="Restore" href="' . url('backoffice/media/restore/' . $row->id) . '"><i class="fas fa-trash-restore"></i></a>';
                }
                return "<div class='d-flex justify-content-center'>$button</div>";
            })
            ->editColumn('image', function ($row) {
                if (Storage::exists($row->path) && !empty($row->path)) {
                    $extension = pathinfo(Storage::url($row->path), PATHINFO_EXTENSION);
                    if (strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg' || strtolower($extension) == 'png' || strtolower($extension) == 'gif') {
                        return '<a href="' . Storage::url($row->path) . '" target="_blank"><img src="' . Storage::url($row->path) . '" style="height: 60px;width: 60px;"></a>';
                    } else {
                        return '<a target="_blank" href="' . Storage::url($row->path) . '"><i class="fa fa-download" aria-hidden="true"></i></a>';
                    }
                } else {
                    return 'No Image';
                }
            })
            ->editColumn('course', function ($row) {
                return Helper::getMediaCourse($row->id);
            })
            ->editColumn('date', function ($row) {
                return !empty($row->created_at) ? created_at_hidden($row->created_at) . ' ' . Helper::date_format($row->created_at) : '';
            })
            ->rawColumns(['action', 'image', 'date'])
            ->addIndexColumn()
            ->toJson();
    }

    /**
     * Restore all Media records.
     */
    public function restore_all(Request $request)
    {
        if (!$this->user->can('restore_media')) {
            abort(403);
        }

        foreach (array_keys($request->selected_checkbox) as $key => $value) {
            $media = Media::withTrashed()->where('id', $value)->firstOrFail();
            $media->restore();
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Media restored successfully";
        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Permanant Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {

        if (!$this->user->can('delete_media')) {
            abort(403);
        }

        $notification = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $chapterIds = Chapter::where('media_id', $id)->select('id')->get()->toArray();
        if (isset($chapterIds) && count($chapterIds) > 0) {
            $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
            $chapter = Chapter::whereIn('id', $chapterIds)->delete();
        }

        $media = Media::withTrashed()->where('id', $id)->firstOrFail();
        $media->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Media deleted successfully";

        return redirect()->back()->with('notification', $notification);
    }

    /**
     * Permanant Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bulk_Hard_Delete(Request $request)
    {
        if (!$this->user->can('delete_media')) {
            abort(403);
        }

        $notification = [];
        if (!empty($request->bd) > 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($request->bd as $key => $value) {
                $chapterIds = Chapter::where('media_id', $key)->select('id')->get()->toArray();
                if (isset($chapterIds) && count($chapterIds) > 0) {
                    $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
                    $chapter = Chapter::whereIn('id', $chapterIds)->delete();
                }

                $media = Media::withTrashed()->where('id', $key)->firstOrFail();
                $media->forceDelete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            $notification['type'] = "sweet-alert";
            $notification['status'] = "success";
            $notification['title'] = "Success";
            $notification['msg'] = "Media deleted successfully";
        } else {
            $notification['type'] = "sweet-alert";
            $notification['status'] = "error";
            $notification['title'] = "Error";
            $notification['msg'] = "No Media selected";
        }

        return redirect()->back()->with('notification', $notification);

    }

    public function addable_learners($courseId, $learnerId)
    {

    }

    public function mediaExport(Request $request) {
        $scope = [];
        $params = [];

        $scope[]="email";
        $subject="Media Report";
        $content="Hi,";

        $params['email']['to'] = auth()->user()->email;
        $user_id = auth()->user()->id;

        SendMedia::dispatch($request->all(),$params,'export_email', $scope, $subject,$content,"true",$user_id);

        return back()->with("msg",'Media report will be generated shortly. Kindly check your email.');

    }

}
