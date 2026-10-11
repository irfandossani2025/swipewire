<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->latest()->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(UpsertProductRequest $request): RedirectResponse
    {
        $product = Product::create($this->productData($request));

        return to_route('admin.products.edit', $product)->with('status', 'Product created. Add its secure file before publishing it for sale.');
    }

    public function edit(Product $product): View
    {
        $product->load('files');

        return view('admin.products.form', compact('product'));
    }

    public function update(UpsertProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->productData($request, $product));

        return to_route('admin.products.edit', $product)->with('status', 'Product details saved.');
    }

    /** @return array<string, mixed> */
    private function productData(UpsertProductRequest $request, ?Product $product = null): array
    {
        $data = $request->safe()->except(['is_published', 'is_featured']);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['currency'] = Str::upper($data['currency']);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['published_at'] = $data['is_published'] ? ($product?->published_at ?? now()) : null;

        return $data;
    }
}
