@extends('layouts.frontend')

@section('content')

    <!-- tmp banner area start -->
    @include('frontend.partials.banner')
    <!-- tmp banner area end -->

    <!-- Tpm Service Area Start -->
    @include('frontend.partials.service')
    <!-- Tpm Service Area End -->

    <!-- Tpm Counter Area Start -->
    @include('frontend.partials.counter')
    <!-- Tpm Counter Area End -->

    <!-- Tpm Latest Service Area Start -->
    @include('frontend.partials.latest_services')
    <!-- Tpm Latest Service Area End -->

    <!-- Tpm Education Experience Area Start -->
    @include('frontend.partials.education_experience')
    <!-- Tpm Education Experience Area End -->

    <!-- Tpm Our Supported Company Area Start -->
    @include('frontend.partials.supported_company') 
    <!-- Tpm Our Supported Company Area End -->

    <!-- Tpm latest-portfolio Area Start -->
    @include('frontend.partials.latest_portfolio')
    <!-- Tpm latest-portfolio Area End -->

    <!-- Tpm My Price plan Start -->
    {{-- @include('frontend.partials.my_price_plan') --}}
    <!-- Tpm My Price plan End -->

    <!-- Tpm Testimonial Start -->
    @include('frontend.partials.testimonial')
    <!-- Tpm Testimonial End -->

    <!-- Tpm Blog and news Area Start -->
    @include('frontend.partials.blog_news')
    <!-- Tpm Blog and news Area End -->

@endsection
