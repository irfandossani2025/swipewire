<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('storefront.index', ['products' => Product::published()->latest('published_at')->get(), 'seo' => SiteSetting::query()->pluck('value', 'key')]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->published_at !== null, 404);

        return view('storefront.show', ['product' => $product, 'seo' => SiteSetting::query()->pluck('value', 'key')]);
    }
}
