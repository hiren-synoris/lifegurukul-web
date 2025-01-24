@extends('front.layout.mainlayout')

@push('meta')
    <meta name="title" content="{{ isset($page->meta_title) && !empty($page->meta_title) ? $page->meta_title : '' }}">
    <meta name="description" content="{{ isset($page->meta_description) && !empty($page->meta_description) ? $page->meta_description : '' }}">
    <meta name="keywords" content="{{ isset($page->meta_keywords) && !empty($page->meta_keywords) ? $page->meta_keywords : '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta_title ?: $page->name }}">
    <meta property="og:description" content="{{ isset($page->meta_description) && !empty($page->meta_description) ? $page->meta_description : '' }}">
    <meta property="og:locale" content="{{ app()->getLocale() }}">
@endpush

@section('content')

    @component('front.components.breadcrumb')
        @slot('title') <a href="{{url('/')}}">Home</a> @endslot
        {{-- @slot('li1') Pages @endslot --}}
        @slot('li2') {{ isset($page) && !empty($page->name) ? $page->name : '' }} @endslot
    @endcomponent

    @component('front.components.pagebanner')
        @slot('title') {{ isset($page) && !empty($page->name) ? $page->name : '' }} @endslot
    @endcomponent

    <div class="page-content">
        <div class="container">
            <div class="row aos" data-aos="fade-up">
                <div class="col-lg-12">
                    @if(isset($page) && !empty($page->body))
                        {!! $page->body !!}
                    @else
                        <h4 class="text-center">Page content is not set. Thanks</h4>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
