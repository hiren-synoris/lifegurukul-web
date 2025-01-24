<?php

namespace App\Observers;

use App\Models\Chapter;
use App\Models\ChapterInfo;
use Illuminate\Http\Request;

class ChapterInfoObserver
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;//app('request');

    }
    /**
     * Handle the ChapterInfo "created" event.
     *
     * @param  \App\Models\ChapterInfo  $chapterInfo
     * @return void
     */
    public function created(ChapterInfo $chapterInfo)
    {
        //
    }
    /**
     * Handle the ChapterInfo "updated" event.
     *
     * @param  \App\Models\ChapterInfo  $chapterInfo
     * @return void
     */
    public function updated(ChapterInfo $chapterInfo)
    {
        if ($chapterInfo->isDirty('title') || $chapterInfo->isDirty('plan_id')) {
            if ((trim($chapterInfo->title) != trim($chapterInfo->getOriginal('title'))) ||
            (trim($chapterInfo->plan_id) != trim($chapterInfo->getOriginal('plan_id')))) {
                $chapter = Chapter::findOrFail($chapterInfo->chapter_id);
                $chapter->title = $chapterInfo->title;
                $chapter->plan_id = $chapterInfo->plan_id;
                $chapter->asset_type = $chapterInfo->asset_type;
                $chapter->upload_type = $chapterInfo->upload_type;
                $chapter->save();
            }
        }
    }
    /**
     * Handle the ChapterInfo "deleted" event.
     *
     * @param  \App\Models\ChapterInfo  $chapterInfo
     * @return void
     */
    public function deleted(ChapterInfo $chapterInfo)
    {
        //
    }

    /**
     * Handle the ChapterInfo "restored" event.
     *
     * @param  \App\Models\ChapterInfo  $chapterInfo
     * @return void
     */
    public function restored(ChapterInfo $chapterInfo)
    {
        //
    }

    /**
     * Handle the ChapterInfo "force deleted" event.
     *
     * @param  \App\Models\ChapterInfo  $chapterInfo
     * @return void
     */
    public function forceDeleted(ChapterInfo $chapterInfo)
    {
        //
    }
}
