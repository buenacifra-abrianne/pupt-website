@if ($paginator->hasPages())
    <div class="pagination">
        <div class="page-info">
            Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }} event{{ $paginator->total() !== 1 ? 's' : '' }}
        </div>
        <div class="page-btns">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="pbtn disabled" disabled><i class="fas fa-chevron-left"></i></button>
            @else
                <a class="pbtn" href="{{ $paginator->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <button class="pbtn disabled" disabled>{{ $element }}</button>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="pbtn active">{{ $page }}</button>
                        @else
                            <a class="pbtn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="pbtn" href="{{ $paginator->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
            @else
                <button class="pbtn disabled" disabled><i class="fas fa-chevron-right"></i></button>
            @endif
        </div>
    </div>
@endif
