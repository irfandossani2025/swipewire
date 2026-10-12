<!doctype html>
<html lang="en">
<head>
    @include('layouts.partials.head', [
        'pageTitle' => trim($__env->yieldContent('title')).' · Swipewire Admin',
        'pageDescription' => 'Swipewire administration',
        'robots' => 'noindex,nofollow',
    ])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="container">
            <a class="brand" href="{{ route('storefront.index') }}" aria-label="Swipewire store">
                <img class="brand-logo" src="{{ asset('images/brand/swipewire-logo.png') }}" alt="Swipewire" width="180" height="98">
                <img class="brand-mark" src="{{ asset('images/brand/swipewire-mark.png') }}" alt="Swipewire" width="48" height="48">
            </a>
            <nav class="site-nav" aria-label="Account">
                <span class="pill pill-admin">Admin</span>
                <a href="{{ route('storefront.index') }}">View store</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
            </nav>
        </div>
    </header>

    <div class="container">
        <nav class="admin-nav" aria-label="Administration">
            <a href="{{ route('admin.products.index') }}" @if(request()->routeIs('admin.products.*')) aria-current="page" @endif>Products</a>
            <a href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>Customers</a>
            <a href="{{ route('admin.subscribers.index') }}" @if(request()->routeIs('admin.subscribers.*')) aria-current="page" @endif>Subscribers</a>
            <a href="{{ route('admin.seo.edit') }}" @if(request()->routeIs('admin.seo.*')) aria-current="page" @endif>SEO</a>
        </nav>
    </div>

    <main id="main" class="container section-tight">
        @yield('content')
    </main>

    @include('partials.footer')
    @stack('scripts')
</body>
</html>
