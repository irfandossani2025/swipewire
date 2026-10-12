<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductFileRequest;
use App\Models\Product;
use App\Models\ProductFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductFileController extends Controller
{
    public function store(StoreProductFileRequest $request, Product $product): RedirectResponse
    {
        $uploadedFile = $request->file('file');
        $disk = Storage::disk('local');
        $path = $uploadedFile->store("products/{$product->id}", 'local');

        $product->files()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size_bytes' => $uploadedFile->getSize(),
            'checksum' => hash_file('sha256', $disk->path($path)),
        ]);

        return to_route('admin.products.edit', $product)->with('status', 'Private product file uploaded.');
    }

    public function destroy(Product $product, ProductFile $productFile): RedirectResponse
    {
        abort_unless($productFile->product_id === $product->id, 404);

        Storage::disk($productFile->disk)->delete($productFile->path);
        $productFile->delete();

        if ($product->is_published && ! $product->files()->exists()) {
            $product->update(['is_published' => false, 'published_at' => null]);

            return to_route('admin.products.edit', $product)->with('status', 'Product file removed. The product has been unpublished because it no longer has a downloadable file.');
        }

        return to_route('admin.products.edit', $product)->with('status', 'Product file removed.');
    }
}
