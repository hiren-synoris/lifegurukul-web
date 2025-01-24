<aside class="main-sidebar sidebar-light-info elevation-4 cust-design-sidebar admin">
    <style>
        .builder-modal {
            display: none;
        }
    </style>
    <div class="brand">
        <div class="back">
            <a onclick="history.go(-1)">
                <i class="fas fa-arrow-left mr-2"></i> Back to Courses
            </a>
        </div>
    </div>
    <div class="builder-sidebar px-2">

           <div class="chapter-content mt-4"
            style="max-height: calc(100vh - 20px) !important;height: calc(100vh - 20px) !important;">
            @if (isset($courseChapters) && !empty($courseChapters) && count($courseChapters) > 0)
                <div id="accordion_chapter_content">
                    @foreach ($courseChapters as $chapterKey => $chapterValue)
                        @php
                            $display = 'false';
                        @endphp
                        <div class="card">
                            <div class="card-header" id="chapter_{{ $chapterValue->id }}">
                                <h5 class="mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a class="sidebar-titleName"
                                            href="{{ route('course.preview', ['slug' => $slug, 'chapterId' => $chapterValue->id]) }}">
                                            {{ Helper::setTypeWiseIcon(trim($chapterValue->asset_type)) }}
                                            {{ isset($chapterValue) && !empty($chapterValue->title) ? ucfirst($chapterValue->title) : '' }}
                                        </a>
                                        <span class="sidebar-title">
                                            <a class="btn btn-link text-left collapsed" data-toggle="collapse"
                                                data-target="#chapterCollapse_{{ $chapterValue->id }}"
                                                aria-expanded="{{ $display }}"
                                                onclick="courseselect(event,$(this))">
                                                <i class="fa fa-caret-down"></i>
                                            </a>
                                            {{-- <i class="fas fa-plus" data-toggle="modal"
                                                data-target="#addNewChapterBtn"
                                                onclick="updateType(0, {{ $chapterValue->id }})" style="right: 25px;"></i>
                                                <a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=confirmDelete('{{route('remove.chapter', ["id" => $chapterValue->id,"parentId" => $chapterValue->parent_id])}}')> <i class="fas fa-trash"></i>
                                            </a> --}}
                                        </span>
                                    </div>


                                </h5>
                            </div>
                            @if (count($chapterValue->childrens) > 0)
                                @php
                                    $show = '';
                                @endphp
                                @foreach ($chapterValue->childrens as $childKey => $childVal)
                                    <h6 class="mb-0 inner-title collapse {{ $show }} "
                                        id="chapterCollapse_{{ $childVal->parent_id }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a class="btn btn-link text-left "
                                                href="{{ route('course.preview', ['slug' => $slug, 'chapterId' => $chapterValue->id]) }}">
                                                {{ Helper::setTypeWiseIcon(trim($childVal->asset_type)) }}{{ $childVal->title }}
                                            </a>

                                            {{-- <div class="inner-add-remove">

                                                    <a class="mx-1" title="Delete" type="button" href="javascript:void(0)" onclick=confirmDelete('{{route('chapters-info.destroy', ["chapters_info" => $childVal->id])}}')> <i class="fas fa-trash"></i>
                                                    </a>
                                                </div> --}}
                                        </div>
                                        <!-- To do Next task -->
                                        {{-- <div style="display:none;">
                                            <div class="btn btn-light btn-sm border px-1 py-0 text-secondary"><a class="mx-1 text-danger" title="Delete" type="button" href="javascript:void(0)" onclick=confirmDelete('{{route('chapters-info.destroy', ["chapters_info" => $childVal->id])}}')> Remove
                                            </a>
                                            </div>

                                            <button class="btn btn-light btn-sm border px-1 py-0 text-secondary"
                                                data-toggle="modal" data-target="#addNewChapterBtn"
                                                onclick="updateType(0, {{ $chapterValue->id }})"><span
                                                    class="text-secondary">Add Chapter Item</span></button>
                                        </div> --}}
                                    </h6>
                                @endforeach
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</aside>
