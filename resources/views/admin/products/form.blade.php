@extends('layouts.admin')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
    <a class="breadcrumb" href="{{ route('admin.products.index') }}">← Back to products</a>
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin:16px 0 24px">
        <div>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem)">{{ $product->exists ? $product->shortTitle() : 'New digital product' }}</h1>
            @if($product->exists)
                <p class="muted" style="margin-top:6px">
                    <span class="pill {{ $product->is_published ? 'pill-live' : 'pill-draft' }}">{{ $product->is_published ? 'Published' : 'Draft' }}</span>
                    @if($product->is_published)
                        <a href="{{ route('storefront.show', $product) }}" style="margin-left:8px">View in store →</a>
                    @endif
                </p>
            @endif
        </div>
    </div>

    @if(session('status'))
        <p class="notice" style="margin-bottom:20px">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div class="error" style="margin-bottom:20px"><div><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <form class="card" method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif
        <h2 style="font-size:1.25rem;margin-bottom:20px">Listing details</h2>
        <div class="form-grid">
            <div class="full"><label for="title">Product title</label><input id="title" name="title" value="{{ old('title', $product->title) }}" required></div>
            <div><label for="price">Price</label><input id="price" name="price" type="number" min="0" max="999999.99" step="0.01" value="{{ old('price', $product->price) }}" required><span class="hint">Use 0 for a free product.</span></div>
            <div><label for="currency">Currency</label><input id="currency" name="currency" maxlength="3" value="{{ old('currency', $product->currency ?: 'USD') }}" required></div>
            <div class="full"><label for="description">Description</label><textarea id="description" name="description" required style="min-height:200px">{{ old('description', $product->description) }}</textarea></div>
            <div><label for="slug">URL slug</label><input id="slug" name="slug" value="{{ old('slug', $product->slug) }}"><span class="hint">Leave blank to generate it from the title.</span></div>
            <div><label for="payhip_product_id">Payhip reference</label><input id="payhip_product_id" name="payhip_product_id" value="{{ old('payhip_product_id', $product->payhip_product_id) }}"><span class="hint">For migration traceability only; not shown publicly.</span></div>
            <div class="full"><label for="seo_title">SEO page title</label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $product->seo_title) }}"><span class="hint">Aim for 60 characters or fewer.</span></div>
            <div class="full"><label for="seo_description">SEO meta description</label><textarea id="seo_description" name="seo_description" style="min-height:96px">{{ old('seo_description', $product->seo_description) }}</textarea><span class="hint">Aim for 155 characters or fewer.</span></div>
            <div><label class="checkbox"><input id="is_published" name="is_published" type="checkbox" value="1" @checked(old('is_published', $product->is_published))> <span>Publish in the store</span></label><span class="hint">Requires at least one downloadable file.</span></div>
            <div><label class="checkbox"><input id="is_featured" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product->is_featured))> <span>Feature on the home page</span></label></div>
        </div>
        <div class="form-actions">
            <button class="button" type="submit">{{ $product->exists ? 'Save product' : 'Create product' }}</button>
            <a href="{{ route('admin.products.index') }}">Cancel</a>
        </div>
    </form>

    @if($product->exists)
        <form id="product-file-upload" class="card" style="margin-top:24px" method="post" action="{{ route('admin.products.files.store', $product) }}" enctype="multipart/form-data" data-start-url="{{ route('admin.products.uploads.start', $product) }}" data-chunk-url="{{ route('admin.products.uploads.chunks.store', [$product, '00000000-0000-0000-0000-000000000000']) }}" data-complete-url="{{ route('admin.products.uploads.complete', [$product, '00000000-0000-0000-0000-000000000000']) }}">
            @csrf
            <h2 style="font-size:1.25rem">Private download files</h2>
            <p class="muted" style="margin-top:6px">Files are stored privately and are only served to customers who own the product.</p>
            <div style="margin-top:20px">
                <label for="file">Add a product file</label>
                <input id="file" type="file" name="file" required>
                <span class="hint">Maximum upload size: 500 MB. Large files are sent in small pieces, so keep this page open until the upload finishes.</span>
            </div>
            <div id="upload-progress" class="upload-progress" hidden style="margin-top:16px"><progress max="100" value="0"></progress><span class="hint" data-upload-status></span></div>
            <p id="upload-error" class="error" hidden style="margin-top:16px"></p>
            <div class="form-actions"><button class="button" type="submit">Upload file</button></div>

            @if($product->files->isNotEmpty())
                <div class="file-list">
                    @foreach($product->files as $file)
                        <div class="file-row">
                            <span><span class="name">{{ $file->original_name }}</span><br><span class="hint" style="margin-top:2px">{{ \Illuminate\Support\Number::fileSize($file->size_bytes, precision: 1) }} · {{ $file->mime_type ?: 'Unknown type' }}</span></span>
                            <button class="button button-danger button-small" type="submit" form="remove-file-{{ $file->id }}">Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </form>

        @foreach($product->files as $file)
            <form id="remove-file-{{ $file->id }}" method="post" action="{{ route('admin.products.files.destroy', [$product, $file]) }}" hidden>
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
@endsection

@if($product->exists)
    @push('scripts')
    <script>
        (() => {
            const form = document.getElementById('product-file-upload');
            const progress = document.getElementById('upload-progress');
            const status = progress.querySelector('[data-upload-status]');
            const bar = progress.querySelector('progress');
            const errorBox = document.getElementById('upload-error');
            const placeholder = '00000000-0000-0000-0000-000000000000';
            const headers = { 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value, 'Accept': 'application/json' };

            const send = async (url, body, attempts = 3) => {
                for (let attempt = 1; ; attempt++) {
                    const response = await fetch(url, { method: 'POST', headers, body, credentials: 'same-origin' });
                    if (response.ok) {
                        return response.json();
                    }
                    if (attempt >= attempts || response.status < 500) {
                        const payload = await response.json().catch(() => ({}));
                        throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || `Upload failed (HTTP ${response.status}).`);
                    }
                    await new Promise((resolve) => setTimeout(resolve, 1000 * attempt));
                }
            };

            form.addEventListener('submit', async (event) => {
                if (event.submitter && event.submitter.getAttribute('form')) {
                    return;
                }
                const file = form.querySelector('input[type=file]').files[0];
                if (!file || !window.fetch) {
                    return;
                }
                event.preventDefault();
                const button = form.querySelector('.form-actions button[type=submit]');
                button.disabled = true;
                errorBox.hidden = true;
                progress.hidden = false;

                try {
                    const startBody = new FormData();
                    startBody.append('original_name', file.name);
                    startBody.append('size_bytes', file.size);
                    const upload = await send(form.dataset.startUrl, startBody);
                    const chunkUrl = form.dataset.chunkUrl.replace(placeholder, upload.upload_id);

                    for (let index = 0; index < upload.total_chunks; index++) {
                        const piece = new FormData();
                        piece.append('index', index);
                        piece.append('chunk', file.slice(index * upload.chunk_size, (index + 1) * upload.chunk_size), 'chunk');
                        await send(chunkUrl, piece);
                        const percent = Math.round(((index + 1) / upload.total_chunks) * 100);
                        bar.value = percent;
                        status.textContent = ` ${percent}% (${Math.min((index + 1) * upload.chunk_size, file.size).toLocaleString()} of ${file.size.toLocaleString()} bytes)`;
                    }

                    status.textContent = ' Finishing and verifying the file…';
                    const result = await send(form.dataset.completeUrl.replace(placeholder, upload.upload_id), new FormData(), 1);
                    window.location.assign(result.redirect);
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.hidden = false;
                    button.disabled = false;
                }
            });
        })();
    </script>
    @endpush
@endif
