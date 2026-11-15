{{-- Laravel paginator in the theme's "tmp-pagination-button" style. --}}
@if ($paginator->hasPages())
    <nav class="tmp-pagination-button" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true" aria-label="Previous page"><i class="fa-sharp fa-regular fa-arrow-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev" aria-label="Previous page"><i class="fa-sharp fa-regular fa-arrow-left"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-btn disabled" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next" aria-label="Next page"><i class="fa-sharp fa-regular fa-arrow-right"></i></a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true" aria-label="Next page"><i class="fa-sharp fa-regular fa-arrow-right"></i></span>
        @endif
    </nav>
@endif
