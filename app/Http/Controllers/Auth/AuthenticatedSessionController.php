<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true])) {
            return back()->withErrors(['email' => 'The provided credentials are incorrect or the account is disabled.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }
}
