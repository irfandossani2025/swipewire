@extends('layouts.store')

@section('title', 'Create account | Swipewire')

@section('content')
    <div class="container auth-shell">
        <div class="card auth-card">
            <span class="eyebrow">Free account</span>
            <h1 style="margin-top:8px">Create your account</h1>
            <p class="lead">Keep every product you get in one permanent library.</p>

            <form method="post" action="{{ route('register.store') }}">
                @csrf
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                </div>
                <div class="field">
                    <label class="checkbox"><input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent'))> <span>Send me occasional product news and useful offers. <span class="hint" style="display:inline">You can unsubscribe at any time.</span></span></label>
                </div>
                @if($errors->any())<p class="error" style="margin-top:16px">{{ $errors->first() }}</p>@endif
                <div class="form-actions"><button class="button button-block" type="submit">Create my account</button></div>
            </form>
            <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
@endsection
