@extends('layouts.store')

@section('title', 'Help & support | Swipewire')
@section('description', 'Questions about a download or an order? Send the Swipewire support team a message.')

@section('content')
    <div class="container-narrow page-head">
        <span class="eyebrow">Help &amp; support</span>
        <h1>How can we help?</h1>
        <p class="lead">Tell us what happened and our team will reply by email. Messages go to <strong>support@techittechnologies.com</strong>.</p>
    </div>

    <div class="container-narrow section-tight">
        @if(session('support-status'))<p class="notice" style="margin-bottom:20px">{{ session('support-status') }}</p>@endif
        @error('support')<p class="error" style="margin-bottom:20px">{{ $message }}</p>@enderror

        <form class="card" method="post" action="{{ route('support.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Your name</label>
                    <input id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" autocomplete="name" required>
                    @error('name')<span class="hint" style="color:var(--danger)">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" required>
                    @error('email')<span class="hint" style="color:var(--danger)">{{ $message }}</span>@enderror
                </div>
                <div class="full">
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" required>
                    @error('subject')<span class="hint" style="color:var(--danger)">{{ $message }}</span>@enderror
                </div>
                <div class="full">
                    <label for="message">How can we help?</label>
                    <textarea id="message" name="message" maxlength="1000" required>{{ old('message') }}</textarea>
                    @error('message')<span class="hint" style="color:var(--danger)">{{ $message }}</span>@enderror
                    <span class="hint">Include your order number if your question is about a purchase.</span>
                </div>
            </div>
            <div class="form-actions"><button class="button" type="submit">Send to support</button></div>
        </form>
    </div>
@endsection
