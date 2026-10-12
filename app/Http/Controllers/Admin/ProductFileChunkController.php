<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StartProductFileUploadRequest;
use App\Http\Requests\Admin\StoreProductFileChunkRequest;
use App\Models\Product;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Receives product files in small pieces so uploads are not limited by the host's
 * PHP upload_max_filesize, then assembles them into private product storage.
 */
class ProductFileChunkController extends Controller
{
    /**
     * Piece size sent by the browser; kept below the host's 2 MB upload_max_filesize.
     */
    public const CHUNK_SIZE_BYTES = 1536 * 1024;

    private const ABANDONED_AFTER_SECONDS = 86400;

    public function start(StartProductFileUploadRequest $request, Product $product): JsonResponse
    {
        $this->removeAbandonedUploads();

        $sizeBytes = $request->integer('size_bytes');
        $uploadId = (string) Str::uuid();
        $manifest = [
            'product_id' => $product->id,
            'original_name' => basename($request->string('original_name')),
            'size_bytes' => $sizeBytes,
            'chunk_size' => self::CHUNK_SIZE_BYTES,
            'total_chunks' => (int) ceil($sizeBytes / self::CHUNK_SIZE_BYTES),
            'created_at' => now()->timestamp,
        ];

        $this->disk()->put($this->manifestPath($uploadId), json_encode($manifest, JSON_THROW_ON_ERROR));

        return response()->json([
            'upload_id' => $uploadId,
            'chunk_size' => $manifest['chunk_size'],
            'total_chunks' => $manifest['total_chunks'],
        ], 201);
    }

    public function storeChunk(StoreProductFileChunkRequest $request, Product $product, string $upload): JsonResponse
    {
        $manifest = $this->manifest($product, $upload);
        $index = $request->integer('index');

        if ($index >= $manifest['total_chunks']) {
            throw ValidationException::withMessages(['index' => 'This piece is outside the expected range for the file.']);
        }

        if ($request->file('chunk')->getSize() !== $this->expectedChunkSize($manifest, $index)) {
            throw ValidationException::withMessages(['chunk' => 'This piece is not the expected size. Please retry the upload.']);
        }

        $this->disk()->putFileAs($this->uploadDirectory($upload), $request->file('chunk'), "{$index}.part");

        return response()->json(['received' => $index]);
    }

    public function complete(Product $product, string $upload): JsonResponse
    {
        $manifest = $this->manifest($product, $upload);
        $disk = $this->disk();

        for ($index = 0; $index < $manifest['total_chunks']; $index++) {
            $partPath = $this->uploadDirectory($upload)."/{$index}.part";

            if (! $disk->exists($partPath) || $disk->size($partPath) !== $this->expectedChunkSize($manifest, $index)) {
                throw ValidationException::withMessages(['upload' => 'Some pieces of the file are missing. Please upload it again.']);
            }
        }

        $extension = Str::lower(preg_replace('/[^A-Za-z0-9]/', '', pathinfo($manifest['original_name'], PATHINFO_EXTENSION)));
        $path = "products/{$product->id}/".Str::random(40).($extension !== '' ? ".{$extension}" : '');
        $disk->makeDirectory("products/{$product->id}");

        $hash = hash_init('sha256');
        $destination = fopen($disk->path($path), 'wb');

        for ($index = 0; $index < $manifest['total_chunks']; $index++) {
            $source = fopen($disk->path($this->uploadDirectory($upload)."/{$index}.part"), 'rb');

            while (! feof($source)) {
                $buffer = fread($source, 1024 * 1024);
                hash_update($hash, $buffer);
                fwrite($destination, $buffer);
            }

            fclose($source);
        }

        fclose($destination);
        $disk->deleteDirectory($this->uploadDirectory($upload));

        if ($disk->size($path) !== $manifest['size_bytes']) {
            $disk->delete($path);

            throw ValidationException::withMessages(['upload' => 'The assembled file did not match the original size. Please upload it again.']);
        }

        $product->files()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $manifest['original_name'],
            'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->file($disk->path($path)) ?: null,
            'size_bytes' => $manifest['size_bytes'],
            'checksum' => hash_final($hash),
        ]);

        session()->flash('status', 'Private product file uploaded.');

        return response()->json(['redirect' => route('admin.products.edit', $product)]);
    }

    /**
     * @return array{product_id: int, original_name: string, size_bytes: int, chunk_size: int, total_chunks: int, created_at: int}
     */
    private function manifest(Product $product, string $upload): array
    {
        abort_unless(Str::isUuid($upload) && $this->disk()->exists($this->manifestPath($upload)), 404);

        $manifest = json_decode($this->disk()->get($this->manifestPath($upload)), true, flags: JSON_THROW_ON_ERROR);

        abort_unless($manifest['product_id'] === $product->id, 404);

        return $manifest;
    }

    /**
     * @param  array{size_bytes: int, chunk_size: int, total_chunks: int}  $manifest
     */
    private function expectedChunkSize(array $manifest, int $index): int
    {
        return $index === $manifest['total_chunks'] - 1
            ? $manifest['size_bytes'] - ($manifest['chunk_size'] * ($manifest['total_chunks'] - 1))
            : $manifest['chunk_size'];
    }

    private function removeAbandonedUploads(): void
    {
        foreach ($this->disk()->directories('uploads') as $directory) {
            $manifestPath = "{$directory}/manifest.json";
            $createdAt = $this->disk()->exists($manifestPath)
                ? (json_decode($this->disk()->get($manifestPath), true)['created_at'] ?? 0)
                : 0;

            if ($createdAt < now()->timestamp - self::ABANDONED_AFTER_SECONDS) {
                $this->disk()->deleteDirectory($directory);
            }
        }
    }

    private function uploadDirectory(string $upload): string
    {
        return "uploads/{$upload}";
    }

    private function manifestPath(string $upload): string
    {
        return $this->uploadDirectory($upload).'/manifest.json';
    }

    private function disk(): Filesystem
    {
        return Storage::disk('local');
    }
}
