@if ($clientsVisible && $clients->isNotEmpty())
<div class="our-supported-company-area tmp-section-gap">
    <div class="container">
        <div class="row justify-content-center">
            @foreach ($clients as $client)
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-6">
                    <div class="support-company-logo tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                        @if ($client->url)
                            <a href="{{ $client->url }}" target="_blank" rel="noopener nofollow">
                                <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" loading="lazy">
                            </a>
                        @else
                            <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" loading="lazy">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
