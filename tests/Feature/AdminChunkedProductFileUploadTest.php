<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductFileChunkController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminChunkedProductFileUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->administrator = User::factory()->create(['is_admin' => true]);
        $this->product = Product::factory()->create();
    }

    public function test_an_administrator_can_upload_a_large_file_in_pieces_to_private_storage(): void
    {
        $contents = random_bytes(ProductFileChunkController::CHUNK_SIZE_BYTES * 2 + 1234);
        $upload = $this->startUpload('wedding-signs.zip', strlen($contents))->assertCreated()->json();

        $this->assertSame(3, $upload['total_chunks']);

        foreach (array_reverse($this->pieces($contents, $upload['chunk_size']), true) as $index => $piece) {
            $this->sendPiece($upload['upload_id'], $index, $piece)->assertOk();
        }

        $this->actingAs($this->administrator)
            ->postJson(route('admin.products.uploads.complete', [$this->product, $upload['upload_id']]))
            ->assertOk()
            ->assertJson(['redirect' => route('admin.products.edit', $this->product)]);

        $productFile = $this->product->files()->sole();

        $this->assertSame('wedding-signs.zip', $productFile->original_name);
        $this->assertSame(strlen($contents), $productFile->size_bytes);
        $this->assertSame(hash('sha256', $contents), $productFile->checksum);
        $this->assertStringStartsWith("products/{$this->product->id}/", $productFile->path);
        $this->assertSame($contents, Storage::disk('local')->get($productFile->path));
        Storage::disk('local')->assertMissing("uploads/{$upload['upload_id']}");
    }

    public function test_a_customer_cannot_start_an_upload(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->postJson(route('admin.products.uploads.start', $this->product), ['original_name' => 'file.zip', 'size_bytes' => 10])
            ->assertForbidden();
    }

    public function test_files_larger_than_the_limit_are_rejected(): void
    {
        $this->startUpload('huge.zip', 512000 * 1024 + 1)->assertUnprocessable()->assertJsonValidationErrors('size_bytes');
    }

    public function test_a_piece_of_the_wrong_size_is_rejected(): void
    {
        $upload = $this->startUpload('planner.pdf', ProductFileChunkController::CHUNK_SIZE_BYTES + 10)->json();

        $this->sendPiece($upload['upload_id'], 0, 'too short')->assertUnprocessable()->assertJsonValidationErrors('chunk');
    }

    public function test_completing_with_missing_pieces_creates_no_file(): void
    {
        $contents = random_bytes(ProductFileChunkController::CHUNK_SIZE_BYTES + 10);
        $upload = $this->startUpload('planner.pdf', strlen($contents))->json();
        $this->sendPiece($upload['upload_id'], 0, $this->pieces($contents, $upload['chunk_size'])[0]);

        $this->actingAs($this->administrator)
            ->postJson(route('admin.products.uploads.complete', [$this->product, $upload['upload_id']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('upload');

        $this->assertSame(0, $this->product->files()->count());
    }

    public function test_an_upload_cannot_be_completed_against_another_product(): void
    {
        $upload = $this->startUpload('planner.pdf', 10)->json();
        $otherProduct = Product::factory()->create();

        $this->actingAs($this->administrator)
            ->postJson(route('admin.products.uploads.complete', [$otherProduct, $upload['upload_id']]))
            ->assertNotFound();
    }

    private function startUpload(string $name, int $sizeBytes): TestResponse
    {
        return $this->actingAs($this->administrator)
            ->postJson(route('admin.products.uploads.start', $this->product), ['original_name' => $name, 'size_bytes' => $sizeBytes]);
    }

    private function sendPiece(string $uploadId, int $index, string $piece): TestResponse
    {
        return $this->actingAs($this->administrator)
            ->post(route('admin.products.uploads.chunks.store', [$this->product, $uploadId]), [
                'index' => $index,
                'chunk' => UploadedFile::fake()->createWithContent('blob', $piece),
            ], ['Accept' => 'application/json']);
    }

    /**
     * @return array<int, string>
     */
    private function pieces(string $contents, int $chunkSize): array
    {
        return str_split($contents, $chunkSize);
    }
}
