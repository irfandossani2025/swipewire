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

    public function test_product_cards_show_a_short_title_price_and_category(): void
    {
        Product::factory()->create([
            'title' => 'Wedding Sign Bundle – 43 Printable Signs – Sage Green Arch',
            'price' => 15,
            'is_published' => true,
            'published_at' => now(),
        ]);
        Product::factory()->create([
            'title' => 'FREE Ramadan Tracker 2027 Printable – 30-Day Fasting Tracker',
            'price' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('Wedding Sign Bundle')
            ->assertSee('43 Printable Signs · Sage Green Arch')
            ->assertSee('$15.00')
            ->assertSee('data-category="celebrations"', false)
            ->assertSee('data-free="true"', false)
            ->assertSee('data-filter="free"', false);
    }

    public function test_unpublished_products_are_not_listed(): void
    {
        Product::factory()->create(['title' => 'Secret Draft Planner', 'is_published' => false]);

        $this->get(route('storefront.index'))->assertOk()->assertDontSee('Secret Draft Planner');
    }

    public function test_the_homepage_offers_the_product_assistant(): void
    {
        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('Ask Swipewire')
            ->assertSee(route('assistant.store'), false);
    }

    public function test_the_header_shows_the_brand_logo(): void
    {
        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('images/brand/swipewire-logo.png', false)
            ->assertSee('images/brand/swipewire-mark.png', false);
    }
}
