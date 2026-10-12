<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\PayhipCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayhipCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_the_whole_payhip_catalogue_as_unpublished_drafts(): void
    {
        $this->seed(PayhipCatalogSeeder::class);

        $this->assertSame(39, Product::count());
        $this->assertSame(0, Product::where('is_published', true)->count());
        $this->assertSame(5, Product::where('price', 0)->count());
        $this->assertSame(39, Product::distinct()->count('slug'));
    }

    public function test_it_keeps_payhip_prices_and_identifiers(): void
    {
        $this->seed(PayhipCatalogSeeder::class);

        $this->assertDatabaseHas('products', ['payhip_product_id' => 'lbI7Q', 'price' => 0.99, 'currency' => 'USD', 'slug' => 'oman-laptop-sticker-collection']);
        $this->assertDatabaseHas('products', ['payhip_product_id' => 'QDGLK', 'price' => 15.00, 'slug' => 'wedding-sign-bundle']);
        $this->assertDatabaseHas('products', ['payhip_product_id' => '34skW', 'price' => 12.00]);
    }

    public function test_descriptions_are_plain_text_and_seo_fields_fit_search_limits(): void
    {
        $this->seed(PayhipCatalogSeeder::class);

        Product::all()->each(function (Product $product): void {
            $this->assertStringNotContainsString('<p>', $product->description);
            $this->assertNotEmpty($product->seo_title);
            $this->assertLessThanOrEqual(60, mb_strlen($product->seo_title));
            $this->assertNotEmpty($product->seo_description);
            $this->assertLessThanOrEqual(155, mb_strlen($product->seo_description));
        });

        $this->assertSame('Bilingual Classroom Organiser | Swipewire', Product::where('payhip_product_id', 'z3acM')->value('seo_title'));
    }

    public function test_re_running_does_not_duplicate_or_overwrite_admin_edits(): void
    {
        $this->seed(PayhipCatalogSeeder::class);
        Product::where('payhip_product_id', 'lbI7Q')->update(['price' => 1.49, 'is_published' => true]);

        $this->seed(PayhipCatalogSeeder::class);

        $this->assertSame(39, Product::count());
        $this->assertDatabaseHas('products', ['payhip_product_id' => 'lbI7Q', 'price' => 1.49, 'is_published' => true]);
    }
}
