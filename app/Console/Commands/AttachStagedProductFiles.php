<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches product files staged in private storage (storage/app/private/incoming)
 * to their products, using the filename map in database/seeders/data/staged-product-files.json.
 *
 * Files are matched by exact name and size, moved into private product storage with a
 * SHA-256 checksum, and never published automatically. Unrecognised files are left in place.
 */
#[Signature('swipewire:attach-staged-files {--dry-run : Report what would be attached without changing anything}')]
#[Description('Attach staged product files from private incoming storage to their products')]
class AttachStagedProductFiles extends Command
{
    private const INCOMING_DIRECTORY = 'incoming';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('local');
        $isDryRun = (bool) $this->option('dry-run');
        $staged = collect($disk->files(self::INCOMING_DIRECTORY))->mapWithKeys(fn (string $path): array => [basename($path) => $path]);
        $map = collect($this->fileMap())->keyBy('file');
        $rows = [];
        $problems = 0;

        foreach ($map as $fileName => $entry) {
            $product = Product::where('payhip_product_id', $entry['payhip_id'])->first();
            $path = $staged->get($fileName);
            [$status, $isProblem] = match (true) {
                $product === null => ['product not found', true],
                $path === null => ['not staged', false],
                $disk->size($path) !== $entry['size_bytes'] => ['size mismatch ('.$disk->size($path).' bytes)', true],
                default => [null, false],
            };

            if ($status === null) {
                $checksum = hash_file('sha256', $disk->path($path));
                $status = $product->files()->where('checksum', $checksum)->exists()
                    ? 'already attached'
                    : ($isDryRun ? 'ready' : $this->attach($disk, $product, $path, $fileName, $checksum));
            }

            $problems += (int) $isProblem;
            $rows[] = [$fileName, $product?->title ? Str::limit($product->title, 40) : $entry['payhip_id'], $status];
        }

        foreach ($staged->keys()->diff($map->keys()) as $unknownFile) {
            $rows[] = [$unknownFile, '—', 'not in map, left in incoming'];
        }

        $this->table(['File', 'Product', 'Result'], $rows);
        $this->line(($isDryRun ? 'Dry run: nothing was changed. ' : '').$problems.' problem(s).');

        return $problems > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function attach(Filesystem $disk, Product $product, string $stagedPath, string $fileName, string $checksum): string
    {
        $extension = Str::lower(preg_replace('/[^A-Za-z0-9]/', '', pathinfo($fileName, PATHINFO_EXTENSION)));
        $destination = "products/{$product->id}/".Str::random(40).($extension !== '' ? ".{$extension}" : '');
        $sizeBytes = $disk->size($stagedPath);

        $disk->move($stagedPath, $destination);

        $product->files()->create([
            'disk' => 'local',
            'path' => $destination,
            'original_name' => $fileName,
            'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->file($disk->path($destination)) ?: null,
            'size_bytes' => $sizeBytes,
            'checksum' => $checksum,
        ]);

        return 'attached';
    }

    /**
     * @return array<int, array{file: string, payhip_id: string, size_bytes: int}>
     */
    private function fileMap(): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/staged-product-files.json')), true, flags: JSON_THROW_ON_ERROR);
    }
}
