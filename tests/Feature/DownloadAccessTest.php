<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_a_customer_can_download_a_published_free_product(): void
    {
        $customer = User::factory()->create(['is_active' => true]);
        $productFile = $this->productFile(['price' => 0, 'is_published' => true, 'published_at' => now()]);

        $this->actingAs($customer)->get(route('downloads.show', $productFile))->assertOk();

        $this->assertDatabaseHas('downloads', ['user_id' => $customer->id, 'product_file_id' => $productFile->id]);
    }

    public function test_an_unpublished_free_product_cannot_be_downloaded(): void
    {
        $customer = User::factory()->create(['is_active' => true]);
        $productFile = $this->productFile(['price' => 0, 'is_published' => false]);

        $this->actingAs($customer)->get(route('downloads.show', $productFile))->assertForbidden();

        $this->assertDatabaseCount('downloads', 0);
    }

    public function test_a_paid_product_cannot_be_downloaded_without_a_purchase(): void
    {
        $customer = User::factory()->create(['is_active' => true]);
        $productFile = $this->productFile(['price' => 4.99, 'is_published' => true, 'published_at' => now()]);

        $this->actingAs($customer)->get(route('downloads.show', $productFile))->assertForbidden();
    }

    public function test_a_purchased_product_stays_downloadable_after_it_is_unpublished(): void
    {
        $customer = User::factory()->create(['is_active' => true]);
        $productFile = $this->productFile(['price' => 4.99, 'is_published' => false]);
        $this->purchase($customer, $productFile->product);

        $this->actingAs($customer)->get(route('downloads.show', $productFile))->assertOk();
    }

    public function test_a_disabled_customer_cannot_download_a_purchased_product(): void
    {
        $customer = User::factory()->create(['is_active' => false]);
        $productFile = $this->productFile(['price' => 4.99, 'is_published' => true, 'published_at' => now()]);
        $this->purchase($customer, $productFile->product);

        $this->actingAs($customer)->get(route('downloads.show', $productFile))->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $productAttributes
     */
    private function productFile(array $productAttributes): ProductFile
    {
        $product = Product::factory()->create($productAttributes);
        $path = "products/{$product->id}/planner.pdf";
        Storage::disk('local')->put($path, 'planner contents');

        return $product->files()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'planner.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 16,
            'checksum' => hash('sha256', 'planner contents'),
        ]);
    }

    private function purchase(User $customer, Product $product): void
    {
        $order = $customer->orders()->create([
            'number' => 'SW-TEST-'.$product->id,
            'status' => 'paid',
            'total' => $product->price,
            'currency' => 'USD',
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'currency' => 'USD',
        ]);
    }
}
