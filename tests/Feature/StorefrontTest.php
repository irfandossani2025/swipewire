<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_storefront_homepage_shows_the_creator_footer(): void
    {
        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }

    public function test_a_published_product_page_shows_the_creator_footer(): void
    {
        $product = Product::factory()->create(['is_published' => true, 'published_at' => now()]);

        $this->get(route('storefront.show', $product))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }

    public function test_the_support_page_shows_the_creator_footer(): void
    {
        $this->get(route('support'))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }
}
