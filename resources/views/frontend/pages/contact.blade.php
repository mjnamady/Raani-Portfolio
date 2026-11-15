@extends('frontend.pages.layout.pages_temp')
@section('title', 'Contact')
@section('d-content')

  <!-- Breadcrumb Area Start -->
    <div class="breadcrumb-area breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title split-collab">Contact</h1>
                        <ul class="page-list">
                            <li class="tmp-breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="icon"><i class="fa-solid fa-angle-right"></i></li>
                            <li class="tmp-breadcrumb-item active">Contact</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Area End -->
    <div class="contact-area-wrapper tmp-section-gap">

        <!-- Contact details (GEN-2): managed on the admin "Contact Page"; cards without lines are hidden -->
        @if ($contactCards !== [])
            @php
                $columnClass = match (count($contactCards)) {
                    1 => 'col-lg-12',
                    2 => 'col-lg-6 col-md-6',
                    default => 'col-lg-4 col-md-6',
                };
            @endphp
            <div class="container">
                <div class="contact-info-wrap">
                    <div class="row">
                        @foreach ($contactCards as $card)
                            <div class="{{ $columnClass }}">
                                <div class="contact-info tmp-scroll-trigger tmponhover tmp-fade-in animation-order-{{ $loop->iteration }}">
                                    <div class="contact-icon">
                                        <i class="fa-solid {{ $card['icon'] }}"></i>
                                    </div>
                                    <h3 class="title">{{ $card['title'] }}</h3>
                                    @foreach ($card['lines'] as $line)
                                        @if ($line['href'])
                                            <a href="{{ $line['href'] }}">
                                                <p class="para">{{ $line['text'] }}</p>
                                            </a>
                                        @else
                                            <p class="para">{{ $line['text'] }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Tpm Get In touch start -->
        @include('frontend.partials.get_in_touch', ['spacing' => $contactCards !== [] ? 'tmp-section-gapTop' : ''])
        <!-- Tpm Get In touch End -->

    </div>

@endsection
