@extends('front.layout.mainlayout')
@section('content')
<div class="main-wrapper">
    @component('front.components.breadcrumb')
        @slot('title') <a href="{{url('/')}}">Home</a> @endslot
        {{-- @slot('li1') <a href="{{url('/blogs')}}">All Blogs </a> @endslot --}}
        @slot('li2') FAQ @endslot
    @endcomponent
    <div class="page-banner">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-12">
                    <h1 class="mb-0">Most frequently asked questions</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="help-sec">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="help-title">
                        <h1>Most frequently asked questions</h1>
                        <p>Here are the most frequently asked questions you may check before getting started</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @php
                    $faqArray = [];
                    $j = 1;
                    if(count($faqs) > 0){
                        $faqArray = array_chunk($faqs,ceil(count($faqs)/2),true);
                    }
                @endphp

                @if($faqArray)
                    @foreach($faqArray as $key => $faqs )
                    <div class="col-lg-6 aos" data-aos="fade-up">
                        @foreach($faqs as $faq )
                        <div class="faq-card">
                            <h6 class="faq-title">
                                {{-- <a class="collapsed" data-bs-toggle="collapse" href="#faq{{$faq['order']}}"
                                    aria-expanded="false">{{$faq['question']}}</a> --}}
                                    <a class="collapsed" data-bs-toggle="collapse" href="#faq{{ $j }}"
                                    aria-expanded="false">{{$faq['question']}}</a>
                            </h6>
                            {{-- <div id="faq{{$faq['order']}}" class="collapse"> --}}
                            <div id="faq{{ $j }}" class="collapse">
                                <div class="faq-detail">
                                    {!! html_entity_decode($faq['answer'], ENT_QUOTES, 'UTF-8') !!}
                                </div>
                            </div>
                        </div>
                        @php
                            $j++;
                        @endphp
                        @endforeach
                    </div>
                    @endforeach

                @else
                    <p><h6>No FAQ found</h6></p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
