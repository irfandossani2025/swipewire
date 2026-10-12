@extends('layouts.store')

@section('title', $product->seo_title ?: $product->shortTitle().' | Swipewire')
@section('description', $product->seo_description ?: $product->summary(155))

@section('content')
    <div class="container section-tight">
        <a class="breadcrumb" href="{{ route('storefront.index') }}#collection">← Back to collection</a>

        <div class="product-layout">
            <article>
                @include('partials.product-tile', ['product' => $product])
                <h1 class="product-title">{{ $product->shortTitle() }}</h1>
                @if($product->titleDetail())
                    <p class="product-detail-line">{{ $product->titleDetail() }}</p>
                @endif
                <div class="prose">{{ $product->description }}</div>
            </article>

            <aside class="buy-panel" aria-label="Get this product">
                @if($product->isFree())
                    <span class="pill pill-free">Free download</span>
                @endif
                <p class="price" style="margin-top:8px">{{ $product->priceLabel() }}</p>
                <p class="muted">{{ $product->isFree() ? 'Add it to your library at no cost.' : 'One-time purchase · USD' }}</p>

                @error('checkout')<p class="error">{{ $message }}</p>@enderror

                @auth
                    <form method="post" action="{{ route('checkout.store', $product) }}">
                        @csrf
                        <button class="button button-block" type="submit">{{ $product->isFree() ? 'Add free product to library' : 'Pay securely with PayPal' }}</button>
                    </form>
                @else
                    <a class="button button-block" style="margin-top:20px" href="{{ route('register') }}">Create an account to get this product</a>
                    <p class="muted" style="margin-top:12px;font-size:.9375rem">Already a customer? <a href="{{ route('login') }}">Sign in</a> to purchase and keep downloads in your library.</p>
                @endauth

                <ul class="check-list">
                    <li>Instant download after {{ $product->isFree() ? 'adding it' : 'payment' }}</li>
                    <li>Kept permanently in your Swipewire library</li>
                    @foreach($product->files as $file)
                        <li>{{ \Illuminate\Support\Str::upper(pathinfo($file->original_name, PATHINFO_EXTENSION)) ?: 'File' }} · {{ \Illuminate\Support\Number::fileSize($file->size_bytes, precision: 1) }}</li>
                    @endforeach
                    <li>Questions? <a href="{{ route('support') }}">Contact support</a></li>
                </ul>
            </aside>
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="section section-mist">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">More like this</span>
                        <h2>You might also find useful</h2>
                    </div>
                </div>
                <div class="product-grid">
                    @foreach($related as $relatedProduct)
                        @include('partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
