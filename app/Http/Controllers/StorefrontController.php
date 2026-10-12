<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $products = Product::published()->with('files:id,product_id,original_name')->latest('published_at')->get();

        return view('storefront.index', [
            'products' => $products,
            'categories' => $products->map(fn (Product $product): array => $product->category())->unique('slug')->sortBy('label')->values(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->published_at !== null, 404);

        return view('storefront.show', [
            'product' => $product->load('files:id,product_id,original_name,size_bytes'),
            'related' => Product::published()->with('files:id,product_id,original_name')->whereKeyNot($product->id)->latest('published_at')->get()
                ->filter(fn (Product $candidate): bool => $candidate->category()['slug'] === $product->category()['slug'])->take(3),
        ]);
    }
}
