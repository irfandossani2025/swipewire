<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCustomerRequest;
use App\Mail\WelcomeCustomerMail;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class RegisteredUserController extends Controller
{
    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $hasMarketingConsent = (bool) ($data['marketing_consent'] ?? false);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'marketing_consent' => $hasMarketingConsent, 'marketing_consented_at' => $hasMarketingConsent ? now() : null]);
        if ($hasMarketingConsent) {
            Subscriber::updateOrCreate(['email' => $user->email], ['is_subscribed' => true, 'subscribed_at' => now(), 'unsubscribed_at' => null, 'unsubscribe_token' => Str::random(64)]);
        }
        try {
            Mail::to($user)->send(new WelcomeCustomerMail($user));
        } catch (Throwable $exception) {
            report($exception);
        }
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }
}
