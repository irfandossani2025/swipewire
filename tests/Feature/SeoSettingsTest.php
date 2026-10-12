<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function robotsOptions(): array
    {
        return [
            'indexable' => ['index,follow'],
            'hidden from search' => ['noindex,nofollow'],
        ];
    }

    #[DataProvider('robotsOptions')]
    public function test_an_administrator_can_save_each_indexing_option(string $robots): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->put(route('admin.seo.update'), [
                'site_title' => 'Swipewire | Useful digital products',
                'site_description' => 'Planners, printables and templates ready to download.',
                'robots' => $robots,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.seo.edit'));

        $this->assertDatabaseHas('site_settings', ['key' => 'robots', 'value' => $robots]);
    }

    public function test_the_storefront_uses_the_saved_seo_settings(): void
    {
        SiteSetting::create(['key' => 'site_title', 'value' => 'Swipewire | Useful digital products']);
        SiteSetting::create(['key' => 'site_description', 'value' => 'Planners and printables ready to download.']);
        SiteSetting::create(['key' => 'robots', 'value' => 'noindex,nofollow']);

        $this->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('<title>Swipewire | Useful digital products</title>', false)
            ->assertSee('<meta name="description" content="Planners and printables ready to download.">', false)
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }

    public function test_a_product_page_prefers_its_own_seo_fields(): void
    {
        $product = Product::factory()->create([
            'is_published' => true,
            'published_at' => now(),
            'seo_title' => 'Wedding Sign Bundle | Swipewire',
            'seo_description' => '43 printable sage green wedding signs.',
        ]);

        $this->get(route('storefront.show', $product))
            ->assertOk()
            ->assertSee('<title>Wedding Sign Bundle | Swipewire</title>', false)
            ->assertSee('<meta name="description" content="43 printable sage green wedding signs.">', false);
    }

    public function test_the_customer_library_is_never_indexed(): void
    {
        $customer = User::factory()->create(['is_active' => true]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }
}
