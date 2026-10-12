<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_with_an_empty_library_sees_the_dashboard_and_footer(): void
    {
        $customer = User::factory()->create(['is_active' => true]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }

    public function test_a_customer_sees_purchased_files_in_their_library(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['price' => 0, 'is_published' => true, 'published_at' => now()]);
        $product->files()->create([
            'disk' => 'local',
            'path' => 'private/products/planner.pdf',
            'original_name' => 'planner.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum' => str_repeat('a', 64),
        ]);

        $this->actingAs($customer)->post(route('checkout.store', $product));

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('planner.pdf')
            ->assertSee('Created by Irfan Dossani');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function administrationPages(): array
    {
        return [
            'users' => ['admin.users.index'],
            'products' => ['admin.products.index'],
            'new product' => ['admin.products.create'],
            'subscribers' => ['admin.subscribers.index'],
            'seo' => ['admin.seo.edit'],
        ];
    }

    #[DataProvider('administrationPages')]
    public function test_administration_pages_render_with_the_footer(string $routeName): void
    {
        $administrator = User::factory()->create(['is_admin' => true, 'is_active' => true]);

        $this->actingAs($administrator)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }

    public function test_the_product_edit_page_renders_with_the_footer(): void
    {
        $administrator = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $product = Product::factory()->create();

        $this->actingAs($administrator)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Created by Irfan Dossani');
    }
}
