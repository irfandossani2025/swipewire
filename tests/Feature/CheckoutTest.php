<?php

namespace Tests\Feature;

use App\Mail\PurchaseReceiptMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_customer_can_add_a_free_product_to_their_library(): void
    {
        Mail::fake();
        $customer = User::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'price' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($customer)
            ->post(route('checkout.store', $product))
            ->assertRedirect(route('customer.dashboard'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'paid',
            'total' => 0,
        ]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id]);
        Mail::assertSent(PurchaseReceiptMail::class, fn (PurchaseReceiptMail $mail) => $mail->hasTo($customer->email));
    }
}
