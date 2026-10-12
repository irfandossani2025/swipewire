<!doctype html>
<html lang="en">
<head>
    @include('layouts.partials.head', [
        'pageTitle' => trim($__env->yieldContent('title')) ?: ($siteSettings['site_title'] ?? 'Swipewire | Digital tools made to use'),
        'pageDescription' => trim($__env->yieldContent('description')) ?: ($siteSettings['site_description'] ?? 'Useful digital planners, printables and templates. Ready to download.'),
        'robots' => trim($__env->yieldContent('robots')) ?: ($siteSettings['robots'] ?? 'index,follow'),
    ])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="container">
            <a class="brand" href="{{ route('storefront.index') }}" aria-label="Swipewire home">
                <img class="brand-logo" src="{{ asset('images/brand/swipewire-logo.png') }}" alt="Swipewire" width="180" height="98">
                <img class="brand-mark" src="{{ asset('images/brand/swipewire-mark.png') }}" alt="Swipewire" width="48" height="48">
            </a>
            <nav class="site-nav" aria-label="Main">
                <a href="{{ route('storefront.index') }}#collection" @if(request()->routeIs('storefront.*')) aria-current="page" @endif>Shop</a>
                <a class="nav-optional" href="{{ route('support') }}" @if(request()->routeIs('support')) aria-current="page" @endif>Help</a>
                @auth
                    @if(auth()->user()->is_admin)
                        <a class="nav-optional" href="{{ route('admin.products.index') }}">Admin</a>
                    @endif
                    <a href="{{ route('customer.dashboard') }}" @if(request()->routeIs('customer.*')) aria-current="page" @endif>My library</a>
                    <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
                @else
                    <a href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Sign in</a>
                    <a class="nav-cta" href="{{ route('register') }}">Create account</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')
    @stack('scripts')
</body>
</html>
