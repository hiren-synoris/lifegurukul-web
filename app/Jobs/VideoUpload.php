<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Models\ChapterInfo;
use App\Mail\VideoDoneNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class VideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */



    public $fileInfo;

    public function __construct($fileInfo)
    {

        $this->fileInfo = $fileInfo;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // dd($this->fileInfo);
        $isUpload = upload_vdo_cipher($this->fileInfo['media_id'], $this->fileInfo['file'], $this->fileInfo['file'],$this->fileInfo['title'],$this->fileInfo['user_name']);

        // $data = [
        //     'course_id' => $this->fileInfo['course_id'],
        //     'title' => $this->fileInfo['title'],
        //     'asset_type' => $this->fileInfo['asset_type'],
        //     'upload_type' => $this->fileInfo['upload_type'],
        //     'media_id' => $this->fileInfo['media_id'],
        //     'file_url' => "",
        //     'parent_id' => $this->fileInfo['parent_id'],
        //     'order' => 0,
        //     'created_by' => $this->fileInfo['created_by'],
        // ];

        // $chapter = Chapter::create($data);
        // $chapterId = $chapter->id;
        // //$this->createChapterInfo($chapter_id);

        // $pdf_page_count = 0;
        // $chapterData = Chapter::with('media:id,path')->where('id', $chapterId)->firstOrFail();
        // // dd($chapterData);
        // if ($chapterData->asset_type == Chapter::FLAG_PDF && env('APP_ENV') != 'local') {
        //     if ($chapterData->upload_type == Chapter::UPLOAD) {
        //         if (isset($chapterData->media) && !empty($chapterData->media)) {
        //             // dd("1");
        //             $pdf_page_count = Helper::pageCount(url('storage/' . $chapterData->media->path));
        //         }

        //     }
        //     if ($chapterData->upload_type == Chapter::PDF) {
        //         // dd($chapterData->file_url);
        //         $pdf_page_count = Helper::pageCount($chapterData->file_url);
        //     }
        // }
        // $duration = '';
        // if (($chapterData->asset_type != Chapter::FLAG_VIDEO) && ($chapterData->asset_type != Chapter::FLAG_AUDIO)) {
        //     $duration = 1;
        // }
        // $chapterInfo = ChapterInfo::create([
        //     'title' => $chapterData->title,
        //     'plan_id' => $chapterData->plan_id ?? null,
        //     'asset_type' => $chapterData->asset_type,
        //     'upload_type' => $chapterData->upload_type,
        //     'pdf_page_count' => $pdf_page_count,
        //     'duration' => $duration,
        //     'chapter_id' => $chapterId,
        //     'created_by' => auth()->id(),
        // ]);

        // $inst = new VideoDoneNotify("Your file has been uploded, now you can check", "Video file uploding", $this->fileInfo['name']);
        // Mail::to($this->fileInfo['email'])->send($inst);

    }
}
