<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\ProductFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __invoke(Request $request, ProductFile $productFile): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $user->is_active, 403);

        $hasPurchasedProduct = $user->orders()
            ->where('status', 'paid')
            ->whereHas('items', fn ($query) => $query->where('product_id', $productFile->product_id))
            ->exists();

        $isPublishedFreeProduct = $productFile->product->is_published && $productFile->product->price == 0.0;

        abort_unless($hasPurchasedProduct || $isPublishedFreeProduct, 403);
        abort_unless(Storage::disk($productFile->disk)->exists($productFile->path), 404);

        Download::create(['user_id' => $user->id, 'product_file_id' => $productFile->id, 'downloaded_at' => now(), 'ip_address' => $request->ip()]);

        return Storage::disk($productFile->disk)->download($productFile->path, $productFile->original_name);
    }
}
