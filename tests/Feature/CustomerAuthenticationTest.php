<?php

namespace Tests\Feature;

use App\Mail\WelcomeCustomerMail;
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
}
