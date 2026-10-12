<footer class="site-footer">
    <div class="container">
        <div class="footer-brand">
            <img src="{{ asset('images/brand/swipewire-mark.png') }}" alt="" width="40" height="40">
            <span>Digital products that make your next move easier.</span>
        </div>
        <nav class="footer-links" aria-label="Footer">
            <a href="{{ route('storefront.index') }}#collection">Shop</a>
            <a href="{{ route('customer.dashboard') }}">My library</a>
            <a href="{{ route('support') }}">Help &amp; support</a>
        </nav>
        <p class="footer-credit">&copy; {{ now()->year }} Swipewire · Created by Irfan Dossani</p>
    </div>
</footer>
