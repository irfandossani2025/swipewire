@php($category = $product->category())
@php($formats = $product->files->map(fn ($file) => \Illuminate\Support\Str::upper(pathinfo($file->original_name, PATHINFO_EXTENSION)))->filter()->unique()->implode(' · '))
<div class="tile tile-{{ $category['tone'] }}" aria-hidden="true">
    <svg class="tile-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        @switch($category['slug'])
            @case('stickers')
                <path d="M15.5 3H6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h7.5L21 13.5V6a3 3 0 0 0-3-3h-2.5z"/><path d="M13.5 21v-4.5a3 3 0 0 1 3-3H21"/><path d="M8.5 9.5h.01M14.5 9.5h.01M8.5 13.5c1 1 2.2 1.5 3.5 1.5"/>
                @break
            @case('wall-art')
                <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>
                @break
            @case('kids')
                <path d="M4 19V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v13"/><path d="M4 19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2"/><path d="M9 9h6M9 13h4"/><path d="m15 16 1.5 1.5L19 15"/>
                @break
            @case('business')
                <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18M11 13v2h2v-2"/>
                @break
            @case('celebrations')
                <path d="M4 21 9 8l7 7-12 6z"/><path d="M14 4v2M19 9h2M17.5 6.5 19 5M12 10c1.5-1.5 3.5-2 5-1"/>
                @break
            @default
                <rect x="4" y="4" width="16" height="17" rx="2"/><path d="M8 2v4M16 2v4M4 10h16M8 14h3M8 17h6"/>
        @endswitch
    </svg>
    <span class="tile-label">{{ $category['label'] }}</span>
    <span class="tile-format">{{ $formats ?: 'Digital' }}</span>
</div>
