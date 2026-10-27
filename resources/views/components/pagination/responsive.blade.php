<nav class="pagination cms-responsive-pagination" aria-label="{{ $label ?? 'Records pagination' }}">
    <div class="page-info" role="status">
        Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} records
    </div>
    <div class="page-btns">
        @if ($paginator->onFirstPage())
            <span class="pbtn disabled" aria-disabled="true" aria-label="Previous page">
        @else
            <a class="pbtn" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous page">
        @endif
            <svg class="cms-control-icon cms-pagination-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        @if ($paginator->onFirstPage()) </span> @else </a> @endif

        <span class="cms-pagination-status">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @for ($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++)
            <a class="pbtn cms-pagination-number {{ $page === $paginator->currentPage() ? 'active' : '' }}" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}" @if($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
        @endfor

        @if ($paginator->hasMorePages())
            <a class="pbtn" href="{{ $paginator->nextPageUrl() }}" aria-label="Next page">
        @else
            <span class="pbtn disabled" aria-disabled="true" aria-label="Next page">
        @endif
            <svg class="cms-control-icon cms-pagination-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        @if ($paginator->hasMorePages()) </a> @else </span> @endif
    </div>
</nav>
