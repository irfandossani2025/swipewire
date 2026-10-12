@extends('layouts.store')

@php($featured = $products->firstWhere('is_featured', true) ?? $products->first(fn ($product) => ! $product->isFree()) ?? $products->first())

@section('content')
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow"><span class="sparkle" aria-hidden="true"></span> Digital tools made to use</span>
                <h1>Useful digital products. <span class="h1-accent">Ready now.</span></h1>
                <p class="lead">Practical downloads for work, creativity, and everyday life. Pick a planner, template or printable and start using it in minutes.</p>
                <div class="hero-actions">
                    <a class="button" href="#collection">Browse products</a>
                    @if($products->contains(fn ($product) => $product->isFree()))
                        <a class="button button-ghost" href="#collection" data-filter-link="free">Get a free download</a>
                    @endif
                </div>
                <ul class="hero-points">
                    <li>Instant download</li>
                    <li>Yours to keep</li>
                    <li>Secure checkout</li>
                </ul>
            </div>
            @if($featured)
                <div class="hero-showcase">
                    <a class="showcase-card" href="{{ route('storefront.show', $featured) }}">
                        @include('partials.product-tile', ['product' => $featured])
                        <span class="eyebrow" style="margin-top:20px"><span class="sparkle" aria-hidden="true"></span> Featured</span>
                        <h3 style="margin-top:6px;font-size:1.25rem">{{ $featured->shortTitle() }}</h3>
                        @if($featured->titleDetail())
                            <p class="detail">{{ $featured->titleDetail() }}</p>
                        @endif
                        <div class="card-meta">
                            <span class="{{ $featured->isFree() ? 'pill pill-free' : 'price' }}">{{ $featured->priceLabel() }}</span>
                            <span class="card-link">View details →</span>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section id="collection" class="section section-mist">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">The collection</span>
                    <h2>Find your next useful tool</h2>
                    <p>Planners, printables and templates you can download straight away and keep in your library.</p>
                </div>
            </div>

            @if($products->isNotEmpty())
                <div class="filters" role="group" aria-label="Filter products">
                    <button class="filter" type="button" data-filter="all" aria-pressed="true">All <span class="muted">({{ $products->count() }})</span></button>
                    @if($products->contains(fn ($product) => $product->isFree()))
                        <button class="filter" type="button" data-filter="free" aria-pressed="false">Free</button>
                    @endif
                    @foreach($categories as $category)
                        <button class="filter" type="button" data-filter="{{ $category['slug'] }}" aria-pressed="false">{{ $category['label'] }}</button>
                    @endforeach
                </div>

                <div class="product-grid" data-product-grid>
                    @foreach($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <h3>New products are on the way</h3>
                    <p>The collection is being prepared. Please check back soon.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">How it works</span>
                    <h2>From browsing to using it in three steps</h2>
                </div>
            </div>
            <ol class="steps">
                <li><h3>Choose your download</h3><p>Every listing shows exactly what's inside, the formats and the file sizes before you buy.</p></li>
                <li><h3>Check out securely</h3><p>Pay with PayPal, or add free products to your library in one click.</p></li>
                <li><h3>Download any time</h3><p>Your purchases stay in your Swipewire library, ready to download again whenever you need them.</p></li>
            </ol>
        </div>
    </section>

    <section class="section section-mist" id="assistant">
        <div class="container assistant">
            <div>
                <span class="eyebrow"><span class="sparkle" aria-hidden="true"></span> Ask Swipewire</span>
                <h2>Not sure where to start?</h2>
                <p class="lead" style="margin-top:12px">Tell us what you're working on and we'll point you to products from the collection that fit.</p>
            </div>
            <form class="assistant-panel" data-assistant action="{{ route('assistant.store') }}" method="post">
                @csrf
                <label for="assistant-message">What do you need help with?</label>
                <textarea id="assistant-message" name="message" maxlength="1000" required placeholder="For example: I want to get my small business finances organised."></textarea>
                <div class="assistant-suggestions">
                    <button class="filter" type="button" data-suggestion>I want to plan Ramadan with my kids</button>
                    <button class="filter" type="button" data-suggestion>What's free to download?</button>
                    <button class="filter" type="button" data-suggestion>I need to send invoices to clients</button>
                </div>
                <div class="form-actions"><button class="button" type="submit">Ask Swipewire</button></div>
                <div class="assistant-answer" data-assistant-answer aria-live="polite">Suggestions will appear here.</div>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Why Swipewire</span>
                    <h2>Simple to buy. Yours to keep.</h2>
                </div>
            </div>
            <div class="feature-grid">
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0 4.5-4.5M12 15l-4.5-4.5M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg></div>
                    <h3>Instant delivery</h3><p>Download straight after checkout. No waiting and nothing to ship.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16l-8-4-8 4z"/></svg></div>
                    <h3>Your permanent library</h3><p>Everything you get stays in your account to download again later.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></div>
                    <h3>Secure checkout</h3><p>Payments are handled by PayPal. Your files stay private until purchase.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 21l1.9-5.4A8 8 0 1 1 21 12z"/></svg></div>
                    <h3>Real support</h3><p>Questions about a file or an order? <a href="{{ route('support') }}">Our team is here to help.</a></p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-tight">
        <div class="container">
            <div class="promo">
                <div>
                    <span class="eyebrow"><span class="sparkle" aria-hidden="true"></span> New drop</span>
                    <h2 style="margin-top:10px">Better tools, one quick swipe.</h2>
                    <p>Digital downloads made for getting things done.</p>
                </div>
                <a class="button button-accent" href="#collection">Explore collection</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (() => {
        const grid = document.querySelector('[data-product-grid]');
        const filters = document.querySelectorAll('[data-filter]');

        const applyFilter = (value) => {
            if (!grid) {
                return;
            }
            filters.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.filter === value)));
            grid.querySelectorAll('.product-card').forEach((card) => {
                const matches = value === 'all' || (value === 'free' ? card.dataset.free === 'true' : card.dataset.category === value);
                card.hidden = !matches;
            });
        };

        filters.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.filter)));
        document.querySelectorAll('[data-filter-link]').forEach((link) => link.addEventListener('click', () => applyFilter(link.dataset.filterLink)));

        const form = document.querySelector('[data-assistant]');
        if (!form || !window.fetch) {
            return;
        }
        const answer = form.querySelector('[data-assistant-answer]');
        const textarea = form.querySelector('textarea');
        const submit = form.querySelector('button[type=submit]');

        form.querySelectorAll('[data-suggestion]').forEach((chip) => chip.addEventListener('click', () => {
            textarea.value = chip.textContent.trim();
            textarea.focus();
        }));

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            submit.disabled = true;
            answer.dataset.state = 'loading';
            answer.textContent = 'Finding products that fit…';
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value },
                    body: JSON.stringify({ message: textarea.value }),
                    credentials: 'same-origin',
                });
                const payload = await response.json().catch(() => ({}));
                answer.dataset.state = response.ok ? 'answer' : 'error';
                answer.textContent = payload.message || 'Something went wrong. Please try again.';
            } catch (error) {
                answer.dataset.state = 'error';
                answer.textContent = 'We could not reach the assistant. Please try again shortly.';
            } finally {
                submit.disabled = false;
            }
        });
    })();
</script>
@endpush
