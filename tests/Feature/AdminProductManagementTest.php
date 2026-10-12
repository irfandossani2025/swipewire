<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_create_a_draft_product(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->post(route('admin.products.store'), [
                'title' => 'Oman Creator Toolkit',
                'description' => 'A ready-to-use collection of templates for small businesses.',
                'price' => '9.99',
                'currency' => 'usd',
                'is_featured' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'title' => 'Oman Creator Toolkit',
            'slug' => 'oman-creator-toolkit',
            'currency' => 'USD',
            'is_published' => false,
            'is_featured' => true,
        ]);
    }

    public function test_a_new_product_cannot_be_published_before_it_has_a_file(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->post(route('admin.products.store'), $this->productFields(['is_published' => '1']))
            ->assertSessionHasErrors('is_published');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_an_existing_product_without_a_file_cannot_be_published(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['is_published' => false]);

        $this->actingAs($administrator)
            ->put(route('admin.products.update', $product), $this->productFields(['is_published' => '1']))
            ->assertSessionHasErrors('is_published');

        $this->assertFalse($product->fresh()->is_published);
    }

    public function test_a_product_with_a_file_can_be_published(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['is_published' => false]);
        $this->attachFile($product);

        $this->actingAs($administrator)
            ->put(route('admin.products.update', $product), $this->productFields(['is_published' => '1']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.edit', $product));

        $this->assertTrue($product->fresh()->is_published);
        $this->assertNotNull($product->fresh()->published_at);
    }

    public function test_removing_the_last_file_unpublishes_the_product(): void
    {
        Storage::fake('local');
        $administrator = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['is_published' => true, 'published_at' => now()]);
        $productFile = $this->attachFile($product);

        $this->actingAs($administrator)
            ->delete(route('admin.products.files.destroy', [$product, $productFile]))
            ->assertRedirect(route('admin.products.edit', $product));

        $this->assertFalse($product->fresh()->is_published);
        $this->assertNull($product->fresh()->published_at);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function productFields(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Oman Creator Toolkit',
            'description' => 'A ready-to-use collection of templates for small businesses.',
            'price' => '9.99',
            'currency' => 'USD',
        ], $overrides);
    }

    private function attachFile(Product $product): ProductFile
    {
        return $product->files()->create([
            'disk' => 'local',
            'path' => "products/{$product->id}/toolkit.zip",
            'original_name' => 'toolkit.zip',
            'mime_type' => 'application/zip',
            'size_bytes' => 1024,
            'checksum' => str_repeat('a', 64),
        ]);
    }

    public function test_an_administrator_can_upload_a_product_file_to_private_storage(): void
    {
        Storage::fake('local');
        $administrator = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $this->actingAs($administrator)
            ->post(route('admin.products.files.store', $product), [
                'file' => UploadedFile::fake()->create('creator-toolkit.zip', 256, 'application/zip'),
            ])
            ->assertRedirect(route('admin.products.edit', $product));

        $productFile = $product->files()->sole();

        Storage::disk('local')->assertExists($productFile->path);
        $this->assertSame('creator-toolkit.zip', $productFile->original_name);
        $this->assertSame('local', $productFile->disk);
    }
}
