<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_create_a_published_product(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->post(route('admin.products.store'), [
                'title' => 'Oman Creator Toolkit',
                'description' => 'A ready-to-use collection of templates for small businesses.',
                'price' => '9.99',
                'currency' => 'usd',
                'is_published' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'title' => 'Oman Creator Toolkit',
            'slug' => 'oman-creator-toolkit',
            'currency' => 'USD',
            'is_published' => true,
            'is_featured' => true,
        ]);

        $this->assertNotNull(Product::query()->sole()->published_at);
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
