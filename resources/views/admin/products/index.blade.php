@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="section-head">
        <div>
            <span class="eyebrow">Catalogue</span>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-top:6px">Digital products</h1>
            <p>Create, price, publish and optimise the products customers discover. A product can only be published once its file is attached.</p>
        </div>
        <a class="button" href="{{ route('admin.products.create') }}">Add product</a>
    </div>

    @if(session('status'))
        <p class="notice" style="margin-bottom:20px">{{ session('status') }}</p>
    @endif

    <div class="table-wrap">
        <table>
            <thead><tr><th>Product</th><th>Price</th><th>Listing</th><th>Files</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><strong>{{ $product->shortTitle() }}</strong><br><span class="muted" style="font-size:.875rem">/{{ $product->slug }}</span></td>
                        <td>{{ $product->isFree() ? 'Free' : $product->currency.' '.number_format((float) $product->price, 2) }}</td>
                        <td><span class="pill {{ $product->is_published ? 'pill-live' : 'pill-draft' }}">{{ $product->is_published ? 'Published' : 'Draft' }}</span></td>
                        <td>{{ $product->files_count }}</td>
                        <td style="text-align:right"><a class="button button-ghost button-small" href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No products yet. Add your first product to begin the catalogue.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <nav class="pagination" aria-label="Product pages">
            @if($products->onFirstPage())
                <span class="muted">Previous</span>
            @else
                <a href="{{ $products->previousPageUrl() }}">Previous</a>
            @endif
            <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}">Next</a>
            @else
                <span class="muted">Next</span>
            @endif
        </nav>
    @endif
@endsection
