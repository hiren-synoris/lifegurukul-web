<?php

namespace App\Http\Controllers\admin;

use App\Models\Media;
use App\Helper\Helper;
use App\Models\Course;
use App\Models\Chapter;
use App\Jobs\VideoUpload;
use App\Models\ChapterInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Smalot\PdfParser\Parser;
use Illuminate\Http\File;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if (!request()->has('course_id')) {
            abort(404);
        }

        Course::where('id', $request['course_id'])->firstOrFail();

        try {
            DB::beginTransaction();
            // set common for all chapter
            // dd( $request->chapterid);
            if ($request->has('type') && $request['type'] == "0" && $request->has('chapterid') && !empty($request['chapterid'])) {
                $request['parent_id'] = (integer) ($request->chapterid);
                // dd($request['parent_id']);
                $request['order'] = Helper::setOrder((integer) ($request->chapterid));
            } else {
                $request['parent_id'] = 0;
                // dd($request['parent_id']);
                $request['order'] = Helper::setOrder($request['course_id'], 1);
            }
            $chapter_id = 0;

            if (request()->has('asset_type_pdf') && (request()->hasFile('upload_pdf_file') || (request()->has('pub_url') && request()->filled('pub_url')))) {

                $chapter_id = $this->saveChapterPdf($request); //PDF Modal method

            }

            elseif (request()->has('asset_type_video') && (request()->hasFile('upload_video_file') || (request()->has('youtube_link') && request()->filled('youtube_link')) || (request()->has('vimeo_link') && request()->filled('vimeo_link')) || (request()->has('link') && request()->filled('link')))) {

                $chapter_id = $this->saveChapterVideo($request); //Video Modal method

            }

            elseif (request()->has('asset_type_audio') && request()->hasFile('upload_audio_file')) {

                $chapter_id = $this->saveChapterAudio($request); // Audio Modal method

            } elseif (request()->has('asset_type_file') && request()->hasFile('upload_file')) {

                $chapter_id = $this->saveChapterFile($request); // File Modal method

            } elseif (request()->has('asset_type_image') && request()->hasFile('upload_image_file')) {

                $chapter_id = $this->saveChapterImage($request); // Image Modal method

            } elseif (request()->has('asset_type_heading') && request()->has('heading') && request()->filled('heading')) {

                $chapter_id = $this->saveChapterHeading($request); // Heading Modal method

            } elseif (request()->has('asset_type_text') && request()->has('title') && request()->filled('title')) {

                $chapter_id = $this->saveChapterText($request); // Text Modal method

            } elseif (request()->has('asset_type_link') && request()->has('link_title') && request()->filled('link_title') && request()->has('link_url') && request()->filled('link_url')) {

                $chapter_id = $this->saveChapterLink($request); // Link Modal method

            } elseif (request()->has('asset_type_sellbuy') && request()->has('title') && request()->filled('title') && request()->has('plan_id') && request()->filled('plan_id')) {

                $chapter_id = $this->saveChapterSellBuy($request); // Link Modal method

            } elseif ($request->file && $request->asset_type_video == 1) {

                $chapter_id = $this->saveChapterVideo($request);
            } else {
                return $this->errorMessage();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'url' => route('courses.builder', ['id' => $request['course_id'], 'chapterId' => $chapter_id]),
                'message' => 'Chapter Item added successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 400);
        }
    }

    protected function errorMessage()
    {
        return response()->json([
            'success' => false,
            'code' => 1,
            'errors' => 'Chapter not created',
        ], 400);
    }

    /**
     * Save Chapter PDF Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    private function saveChapterPdf($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */
        // dd($request->all());



        $title = $media_id = null;
        $asset_type = Chapter::FLAG_PDF;
        $upload_type = 3;
        $file_url = request()->has('pub_url') && request()->filled('pub_url') ? request()->get('pub_url') : null;

        if (request()->hasFile('upload_pdf_file')) {
            $file = $request->file('upload_pdf_file');
            $title = '';
            $titlewithext = '';
            try {
                //code...
                // $originalName = $request->file->getClientOriginalName();
                $title_name = explode('.', $file->getClientOriginalName())[0];
                $title = str_replace(' ', '_',$title_name);
                $titlewithext = $title . '_' . time() . '.' . $file->guessClientExtension();
                $path = Storage::putFileAs('media', $file, $titlewithext);
            } catch (\Throwable $th) {
                //throw $th;
                dd($th->getMessage());
            }

            $media = Media::create([
                'title' => !empty($title) ? $title : null,
                'asset_type' => $asset_type,
                'file_name' => $titlewithext,
                'disk' => config('filesystems.default'),
                'path' => $path,
                'extension' => $file->guessClientExtension() ?? '',
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'created_by' => auth()->id(),
            ]);
            $media_id = $media->id;
            $upload_type = 0;
        }
        if (request()->has('pub_url') && request()->filled('pub_url')) {
            $filename = pathinfo($request['pub_url'], PATHINFO_FILENAME); // File name without extension
            $title = $filename;
        }
        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter Video Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterVideo($request)
    {

        // dd($request->all());

        // dd($request->course_id);
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        // if (request()->hasFile('upload_video_file')) {
        //     // $file = $request->file('upload_video_file');
        //     // // $path = Storage::putFile('media', $file);
        //     // $title = explode('.', $file->getClientOriginalName())[0];
        //     // $asset_type = Chapter::FLAG_VIDEO;
        //     // $upload_type = 0;

        //     // $media = Media::create([
        //     //     'title' => !empty($title) ? $title : null,
        //     //     'asset_type' => $asset_type,
        //     //     'file_name' => $file->getClientOriginalName(),
        //     //     'disk' => config('filesystems.default'),
        //     //     'path' => 'null',
        //     //     'extension' => $file->guessClientExtension() ?? '',
        //     //     'mime' => $file->getClientMimeType(),
        //     //     'size' => $file->getSize(),
        //     //     'created_by' => auth()->id(),
        //     // ]);
        //     // $media_id = $media->id;
        //     // $isUpload = upload_vdo_cipher($media_id, $file);
            if ($request->file && $request->asset_type_video==155454) {

        } elseif (request()->has('youtube_title') && request()->filled('youtube_title') && request()->has('youtube_link') && request()->filled('youtube_link')) {
            $title = $request['youtube_title'];
            $file_url = $request['youtube_link'];
            $asset_type = 0;
            $upload_type = Chapter::YOUTUBE;
        } elseif (request()->has('vimeo_title') && request()->filled('vimeo_title') && request()->has('vimeo_link') && request()->filled('vimeo_link')) {
            $title = $request['vimeo_title'];
            $file_url = $request['vimeo_link'];
            $asset_type = 0;
            $upload_type = Chapter::VIMEO;
        } elseif (request()->has('link_title') && request()->filled('link_title') && request()->has('link') && request()->filled('link')) {
            $title = $request['link_title'];
            $file_url = $request['link'];
            $asset_type = 0;
            $upload_type = Chapter::FLAG_FILE;
        }
        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        // dd($chapter->id);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);
        // dd($chapter_id);
        return $chapter_id;
    }

    /**
     * Save Chapter Audio Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterAudio($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->hasFile('upload_audio_file')) {
            $file = $request->file('upload_audio_file');
            // $path = Storage::putFile('media', $file);
            $title = explode('.', $file->getClientOriginalName())[0];
            $asset_type = Chapter::FLAG_AUDIO;
            $upload_type = 0;

            $media = Media::create([
                'title' => !empty($title) ? $title : null,
                'asset_type' => $asset_type,
                'file_name' => $file->getClientOriginalName(),
                'disk' => config('filesystems.default'),
                'path' => 'null',
                'extension' => $file->guessClientExtension() ?? '',
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'created_by' => auth()->id(),
            ]);
            $media_id = $media->id;
            $isUpload = upload_vdo_cipher($media_id, $file,"","", auth()->user()->name);
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter File Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterFile($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */
        $title = $titlewithext = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->hasFile('upload_file')) {
            $file = $request->file('upload_file');

            try {
                $title = explode('.', $file->getClientOriginalName())[0];
                $titlewithext = $title . '_' . time() . '.' . $file->guessClientExtension();
                $path = Storage::putFileAs('media', $file, $titlewithext);

            } catch (\Throwable $th) {
                //throw $th;
                dd($th->getMessage());
            }
            $asset_type = Chapter::FLAG_FILE;
            $upload_type = 0;

            $media = Media::create([
                'title' => !empty($title) ? $title : null,
                'asset_type' => $asset_type,
                'file_name' => $titlewithext,
                'disk' => config('filesystems.default'),
                'path' => $path,
                'extension' => $file->guessClientExtension() ?? '',
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'created_by' => auth()->id(),
            ]);
            $media_id = $media->id;
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter Image Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterImage($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link, 7 = sell & buy, 8 = image.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */
        $title = $file_url = $asset_type = $upload_type = $media_id = $path = null;

        if (request()->hasFile('upload_image_file')) {
            $file = $request->file('upload_image_file');

            $title = explode('.', $file->getClientOriginalName())[0];
            $titlewithext = $title . '_' . time() . '.' . $file->guessClientExtension();
            try {
                $path = Storage::putFileAs('media', $file, $titlewithext);
            } catch (\Throwable $th) {
                //throw $th;
                dd($th->getMessage());
            }
            $asset_type = Chapter::FLAG_IMAGE;
            $upload_type = 0;

            $media = Media::create([
                'title' => !empty($title) ? $title : null,
                'asset_type' => $asset_type,
                'file_name' => $titlewithext,
                'disk' => config('filesystems.default'),
                'path' => $path,
                'extension' => $file->guessClientExtension() ?? '',
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'created_by' => auth()->id(),
            ]);
            $media_id = $media->id;
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter Heading Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterHeading($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->has('heading') && request()->filled('heading')) {
            $title = $request['heading'];
            $asset_type = Chapter::FLAG_HEADING;
            $upload_type = 0;
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter Text Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterText($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->has('title') && request()->filled('title')) {
            $title = $request['title'];
            $asset_type = Chapter::FLAG_TEXT;
            $upload_type = 0;
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);

        return $chapter_id;
    }

    /**
     * Save Chapter Link Modal Data
     * @param \Illuminate\Http\Request  $request
     * @return TRUE
     */
    public function saveChapterLink($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link.
         * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->has('link_title') && request()->filled('link_title') && request()->has('link_url') && request()->filled('link_url')) {
            $title = $request['link_title'];
            $file_url = $request['link_url'];
            $asset_type = Chapter::FLAG_LINK;
            $upload_type = 0;
        }

        // dd($request->all());

        $image_name = "";
        if($request->image_link) {
            $image_name = $request->image_link->getClientOriginalName();
            $path = Storage::disk("public")->putFileAs('chapter_link', new File($request->image_link), $image_name);
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'upload_type' => $upload_type,
            'media_id' => $media_id,
            'file_url' => $file_url,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),
            'image' => $image_name,
        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id);
        return $chapter_id;
    }

    public function saveChapterSellBuy($request)
    {
        /**
         * -----------------------------------------------------------------------------------
         * Note
         * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link , 7 = sell & Buy.
         * -----------------------------------------------------------------------------------
         */

        $title = $file_url = $asset_type = $upload_type = $media_id = null;

        if (request()->has('title') && request()->filled('title') && request()->has('plan_id') && request()->filled('plan_id')) {
            $title = $request['title'];
            $plan_id = $request['plan_id'];
            $asset_type = Chapter::SELL_BUY;
        }

        $data = [
            'course_id' => $request['course_id'],
            'title' => $title,
            'asset_type' => $asset_type,
            'plan_id' => $plan_id,
            'parent_id' => $request['parent_id'],
            'order' => $request['order'],
            'created_by' => auth()->id(),

        ];

        $chapter = Chapter::create($data);
        $chapter_id = $chapter->id;
        $this->createChapterInfo($chapter_id,$request->timer);
        return $chapter_id;
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
        // $page = Chapter::where('id', $id)->firstOrFail();
        // $page->delete();
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Record deleted successfully!'
        // ], 200);
    }

    public function createChapterInfo($chapterId,$timer="")
    {
        $pdf_page_count = 0;
        $chapterData = Chapter::with('media:id,path')->where('id', $chapterId)->firstOrFail();
        // dd($chapterData);
       if ($chapterData->asset_type == Chapter::FLAG_PDF && env('APP_ENV') != 'local') {
            if ($chapterData->upload_type == Chapter::UPLOAD && $chapterData->asset_type == Chapter::FLAG_PDF)  {
                if (isset($chapterData->media) && !empty($chapterData->media)) {
                    // $pdf_page_count = Helper::pageCount(url('storage/' . $chapterData->media->path));
                    $pdfPath = url('storage/'. $chapterData->media->path);
                    // dd($pdfPath);
                    $parser = new Parser();
                    $pdf = $parser->parseFile($pdfPath);
                    $pdf_page_count = count($pdf->getPages());
                }

            }
            if ($chapterData->upload_type == Chapter::PDF) {
                // $pdf_page_count = Helper::pageCount($chapterData->file_url);
                $pdfPath = $chapterData->file_url;
                $parser = new Parser();
                $pdf = $parser->parseFile($pdfPath);
                $pdf_page_count = count($pdf->getPages());
            }
       }
        $duration = '';
        if (($chapterData->asset_type != Chapter::FLAG_VIDEO) && ($chapterData->asset_type != Chapter::FLAG_AUDIO)) {
            $duration = 1;
        }
        $chapterInfo = ChapterInfo::create([
            'title' => $chapterData->title,
            'plan_id' => $chapterData->plan_id ?? null,
            'asset_type' => $chapterData->asset_type,
            'upload_type' => $chapterData->upload_type,
            'pdf_page_count' => $pdf_page_count,
            'duration' => $duration,
            'chapter_id' => $chapterId,
            'created_by' => auth()->id(),
            'timer' => $timer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function removeChapter($id, $parentId = 0)
    {
        $data = Chapter::where('id', $id)->firstOrFail();
        $courseId = $data->course_id;
        $url = route('courses.builder', ['id' => $courseId]);
        if ($parentId > 0) {
            $page = ChapterInfo::where('chapter_id', $id)->delete();
            $chapter = Chapter::where('id', $id)->delete();
        } else {
            $chapterIds = Chapter::where('parent_id', $id)->select('id')->get()->toArray();
            if (isset($chapterIds) && count($chapterIds) > 0) {
                $chapterInfo = ChapterInfo::whereIn('chapter_id', $chapterIds)->delete();
            }
            $page = ChapterInfo::where('chapter_id', $id)->delete();
            $chapterparent = Chapter::where('parent_id', $id)->delete();
            $chapter = Chapter::where('id', $id)->delete();
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Record deleted successfully!',
        ], 200);
    }

    /**
     * Add Multiple course to from asset Library
     */
    public function addMultipleCourseFromLibrary(Request $request, $course_id)
    {

        if (request()->has('bd') && !empty($course_id)) {
            $media = [];
            if ($request->has('type') && $request['type'] == "0" && $request->has('chapterid') && !empty($request['chapterid'])) {
                $request['parent_id'] = (integer) ($request->chapterid);
                $request['order'] = Helper::setOrder((integer) ($request->chapterid));
            } else {
                $request['parent_id'] = 0;
                $request['order'] = Helper::setOrder();
            }
            $upload_type = 0;
            $chapter_id = '';

            foreach ($request->bd as $key => $value) {
                $media = Media::find($key);
                if (!empty($media)) {
                    $data = [
                        'course_id' => $course_id,
                        'title' => $media['title'],
                        'asset_type' => $media['asset_type'],
                        'upload_type' => $upload_type,
                        'media_id' => $key,
                        'file_url' => null,
                        'parent_id' => $request['parent_id'],
                        'order' => $request['order'],
                        'created_by' => auth()->id(),
                    ];
                    // dd($data);
                    $chapter = Chapter::create($data);
                    $chapter_id = $chapter->id;
                    $this->createChapterInfo($chapter_id);
                }
            }
        }
        $notification['type'] = "sweet-alert";
        $notification['status'] = "success";
        $notification['title'] = "Success";
        $notification['msg'] = "Chapter Item added successfully";
        return redirect()->route('courses.builder', ['id' => $course_id, 'chapterId' => $chapter_id])->with('notification', $notification);
        //return redirect()->back()->with('notification', $notification);
    }

    public function pdf_download(Request $request, $id)
    {
        $chapter = Chapter::with('media')->find($id);
        $name = $chapter->title;
        if (!empty($chapter->media)) {
            if (isset($chapter->media_id) && $chapter->media->path && $chapter->media_id != null) {
                return response()->download($chapter->media->path, $name);
            }
        } else {
            if (!empty($chapter->file_url)) {
                return redirect($chapter->file_url);
            }
        }
    }

    public function uploadLarge(){
        return view("test");
    }


    // public function uploadLargeFiles(Request $request)
    // {
    //     // dd($request);
    //     $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

    //     if (!$receiver->isUploaded()) {
    //         return "error";
    //     }
    //     $fileReceived = $receiver->receive();

    //     if ($fileReceived->isFinished()) {
    //         unlink($file->getPathname());
    //          return response()->json([
    //             'success' => true,
    //             'message' => 'Chapter added successfully',
    //         ], 200);
    //     }
    //     $handler = $fileReceived->handler();
    //     return [
    //         'done' => $handler->getPercentageDone(),
    //         'status' => true,
    //     ];

    // }

    public function uploadLargeFiles(Request $request)
    {

        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            return "error";
        }
        $fileReceived = $receiver->receive();

        if ($fileReceived->isFinished()) {

            if ($request->has('type') && $request['type'] == "0" && $request->has('chapterid') && !empty($request['chapterid'])) {
                $request['parent_id'] = (integer) ($request->chapterid);
                $request['order'] = Helper::setOrder((integer) ($request->chapterid));
            } else {
                $request['parent_id'] = 0;
                $request['order'] = Helper::setOrder($request['course_id'], 1);
            }
            $chapter_id = 0;

            $file = $fileReceived->getFile();
            $title = explode('.', $file->getClientOriginalName())[0];
            $asset_type = Chapter::FLAG_VIDEO;
            $upload_type = 0;

            $media = Media::create([
                'title' => !empty($title) ? $title : null,
                'asset_type' => $asset_type,
                'file_name' => $file->getClientOriginalName(),
                'disk' => config('filesystems.default'),
                'path' => 'null',
                'extension' => $file->guessClientExtension() ?? '',
                'mime' => "",
                'size' => "",
                'created_by' => "",
            ]);
            $media_id = $media->id;

            VideoUpload::dispatch([
                "media_id" =>$media_id,
                "file"=>$file->getPathName(),
                // 'course_id' => $request['course_id'],
                'title' => $title,
                // 'asset_type' => $asset_type,
                // 'upload_type' => $upload_type,
                // 'file_url' => "",
                // 'parent_id' => $request['parent_id'],
                // 'order' => $request['order'],
                // 'created_by' => auth()->id(),
                'user_name' =>Auth::guard("web")->user()->name,
                // 'email' =>Auth::guard("web")->user()->email,

            ]);

            $data = [
                'course_id' => $request['course_id'],
                'title' => $title,
                'asset_type' => $asset_type,
                'upload_type' => $upload_type,
                'media_id' => $media_id,
                'file_url' => "",
                'parent_id' => $request['parent_id'],
                'order' => $request['order'],
                'created_by' => auth()->id(),
            ];

            $chapter = Chapter::create($data);
            // dd($chapter->id);
            $chapter_id = $chapter->id;
            $this->createChapterInfo($chapter_id);
            unlink($file->getPathname());
            return response()->json([
                'success' => true,
                'url' => route('courses.builder', ['id' => $request['course_id'], 'chapterId' => $chapter_id]),
                'message' => 'Chapter Item added successfully',
            ], 200);
        }
        $handler = $fileReceived->handler();
        return [
            'status' => false,
            'message' => 'Error',
        ];

    }

    public function uploadLargeFiless(Request $request) {

        return response()->json(['success' => true]);

    }

    // private function concatenateChunks($identifier, $filename, $totalChunks)
    // {
    //     $chunkPaths = [];

    //     for ($i = 1; $i <= $totalChunks; $i++) {
    //         $chunkPaths[] = storage_path("app/uploads/{$identifier}/{$filename}.part{$i}");
    //     }

    //     $outputPath = storage_path("app/uploads/{$identifier}/{$filename}");

    //     if (!is_dir(dirname($outputPath))) {
    //         mkdir(dirname($outputPath), 0755, true);
    //     }
    //     // Concatenate chunks to reconstruct the original file
    //     file_put_contents($outputPath, '');

    //     foreach ($chunkPaths as $chunkPath) {
    //         file_put_contents($outputPath, file_get_contents($chunkPath), FILE_APPEND);
    //     }

    //     // Clean up temporary chunk files
    //     foreach ($chunkPaths as $chunkPath) {
    //         unlink($chunkPath);
    //     }
    // }
}
