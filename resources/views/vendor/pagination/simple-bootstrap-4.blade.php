@if ($paginator->hasPages())
    <nav class="courses-pagination mt-50">
        <ul class="pagination justify-content-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <a >@lang('pagination.previous')</a>
                </li>
            @else
                <li class="page-item">
                    <a  href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a  href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <a >@lang('pagination.next')</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
