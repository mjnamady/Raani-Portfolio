@if (($latestSection?->is_active ?? true) && $latestServices->isNotEmpty())
<section class="latest-service-area tmp-section-gapTop">
    <div class="container">
        @if ($latestSection?->eyebrow || $latestSection?->title || $latestSection?->subtitle)
            <div class="section-head mb--60">
                @if ($latestSection->eyebrow)
                    <div class="section-sub-title center-title tmp-scroll-trigger tmp-fade-in animation-order-1">
                        <span class="subtitle">{{ $latestSection->eyebrow }}</span>
                    </div>
                @endif
                @if ($latestSection->title)
                    <h2 class="title split-collab tmp-scroll-trigger tmp-fade-in animation-order-2">{{ $latestSection->title }}</h2>
                @endif
                @if ($latestSection->subtitle)
                    <p class="description section-sm tmp-scroll-trigger tmp-fade-in animation-order-3">{{ $latestSection->subtitle }}</p>
                @endif
            </div>
        @endif

        <div class="row">
            <div class="col-lg-6">
                @foreach ($latestServices as $item)
                    <div class="service-card-v2 tmponhover tmp-scroll-trigger tmp-fade-in animation-order-{{ $loop->iteration }}">
                        <h2 class="service-card-num"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}.</span>{{ $item->title }}</h2>
                        <p class="service-para">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
            <div class="col-lg-6">
                <div class="service-card-user-image">
                    <img class="tmp-scroll-trigger tmp-zoom-in animation-order-1"
                         src="{{ $latestSection ? $latestSection->imageUrl(\App\Models\LatestService::DEFAULT_IMAGE) : asset(\App\Models\LatestService::DEFAULT_IMAGE) }}"
                         alt="{{ $latestSection?->image_alt }}"
                         width="{{ $latestSection?->image ? \App\Models\LatestService::IMAGE_WIDTH : 567 }}"
                         height="{{ $latestSection?->image ? \App\Models\LatestService::IMAGE_HEIGHT : 588 }}"
                         loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>
@endif
