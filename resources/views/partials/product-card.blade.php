<a class="product-card" href="{{ route('storefront.show', $product) }}" data-category="{{ $product->category()['slug'] }}" data-free="{{ $product->isFree() ? 'true' : 'false' }}">
    @include('partials.product-tile', ['product' => $product])
    <div class="body">
        <h3>{{ $product->shortTitle() }}</h3>
        <p class="detail">{{ $product->titleDetail() ?: $product->summary(90) }}</p>
        <div class="card-meta">
            @if($product->isFree())
                <span class="pill pill-free">Free</span>
            @else
                <span class="price">{{ $product->priceLabel() }}</span>
            @endif
            <span class="card-link">View details →</span>
        </div>
    </div>
</a>
