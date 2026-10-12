@extends('layouts.store')

@section('title', 'Sign in | Swipewire')

@section('content')
    <div class="container auth-shell">
        <div class="card auth-card">
            <span class="eyebrow">Your library</span>
            <h1 style="margin-top:8px">Welcome back</h1>
            <p class="lead">Sign in to download everything in your library.</p>

            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                </div>
                @error('email')<p class="error" style="margin-top:16px">{{ $message }}</p>@enderror
                <div class="form-actions"><button class="button button-block" type="submit">Sign in</button></div>
            </form>
            <p class="auth-switch">New to Swipewire? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </div>
@endsection
