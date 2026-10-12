<?php

namespace Tests\Feature;

use App\Mail\WelcomeCustomerMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_sends_a_welcome_email(): void
    {
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Maha Al Balushi',
            'email' => 'maha@example.test',
            'password' => 'a-long-secure-password',
            'password_confirmation' => 'a-long-secure-password',
            'marketing_consent' => true,
        ])->assertRedirect(route('customer.dashboard'));

        Mail::assertSent(WelcomeCustomerMail::class, fn (WelcomeCustomerMail $mail) => $mail->hasTo('maha@example.test'));
    }

    public function test_a_signed_in_customer_can_sign_out(): void
    {
        $customer = User::factory()->create(['is_active' => true]);

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('storefront.index'));

        $this->assertGuest();
    }

    public function test_the_store_header_offers_sign_out_to_signed_in_customers(): void
    {
        $customer = User::factory()->create(['is_active' => true]);

        $this->actingAs($customer)
            ->get(route('storefront.index'))
            ->assertOk()
            ->assertSee('Sign out')
            ->assertSee(route('logout'), false);
    }
}
