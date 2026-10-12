@extends('layouts.store')

@section('title', 'My library | Swipewire')
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="container page-head">
        <span class="eyebrow">My library</span>
        <h1>Your downloads</h1>
        <p class="lead">Everything you get stays here, ready to download again whenever you need it.</p>
    </div>

    <div class="container section-tight">
        @if(session('status'))
            <p class="notice" style="margin-bottom:20px">{{ session('status') }}</p>
        @endif
        @error('checkout')
            <p class="error" style="margin-bottom:20px">{{ $message }}</p>
        @enderror

        <div class="library">
            @forelse($orders as $order)
                @foreach($order->items as $item)
                    <article class="library-item">
                        @include('partials.product-tile', ['product' => $item->product])
                        <div>
                            <h2 style="font-size:1.125rem">{{ $item->product_title }}</h2>
                            <p class="muted" style="font-size:.875rem">Order {{ $order->number }} · {{ $order->paid_at?->format('j M Y') }}</p>
                            <div class="library-files">
                                @foreach($item->product->files as $file)
                                    <div class="file-row">
                                        <span class="name">{{ $file->original_name }}</span>
                                        <a class="button button-small" href="{{ route('downloads.show', $file) }}">Download</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach
            @empty
                <div class="empty-state">
                    <h2 style="font-size:1.25rem">Your library is ready for its first download</h2>
                    <p>When you buy a product or add a free one, it appears here permanently.</p>
                    <a class="button" style="margin-top:20px" href="{{ route('storefront.index') }}#collection">Browse products</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
