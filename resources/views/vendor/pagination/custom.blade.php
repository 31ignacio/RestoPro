@if ($paginator->hasPages())
<nav>
    <ul class="pagination">

        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link"><i class="bi bi-chevron-left"></i></span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        @endif

        {{-- Numéros de pages --}}
        @foreach ($elements as $element)

            {{-- Séparateur "…" --}}
            @if (is_string($element))
            <li class="page-item disabled">
                <span class="page-link" style="border:none;background:transparent;color:#9299a8">…</span>
            </li>
            @endif

            {{-- Liens de pages --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
            @endif

        @endforeach

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        @else
        <li class="page-item disabled">
            <span class="page-link"><i class="bi bi-chevron-right"></i></span>
        </li>
        @endif

    </ul>
</nav>
@endif