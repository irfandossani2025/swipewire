<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->exists ? 'Edit' : 'Add' }} product · Swipewire Admin</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:#172930;background:#f4f7f8}*{box-sizing:border-box}body{margin:0}main{max-width:900px;margin:auto;padding:42px 24px 72px}a{color:#08726b;font-weight:800;text-decoration:none}.eyebrow{display:inline-block;margin-bottom:18px;font-size:.85rem}h1{font-size:clamp(2rem,5vw,3.2rem);letter-spacing:-.05em;margin:0 0 8px}.intro{margin:0;color:#64727a}.notice{margin:24px 0;padding:14px 16px;color:#08554f;border:1px solid #a4ded7;background:#e7faf6;border-radius:12px}.errors{margin:24px 0;padding:14px 18px;color:#963a2d;background:#fff0eb;border-radius:12px}form{margin-top:28px;padding:28px;background:#fff;border:1px solid #dce5e7;border-radius:16px;box-shadow:0 14px 40px #1730400c}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.full{grid-column:1/-1}label{display:block;color:#344850;font-size:.87rem;font-weight:800;margin-bottom:7px}input,textarea{width:100%;padding:12px;border:1px solid #cad8db;border-radius:9px;background:#fff;color:#172930;font:inherit}textarea{min-height:150px;resize:vertical}.hint{display:block;margin-top:6px;color:#7a888d;font-size:.8rem}.check{display:flex;align-items:center;gap:9px;margin-top:4px}.check input{width:auto}.actions{display:flex;gap:14px;align-items:center;margin-top:26px}.button{padding:12px 16px;border:0;border-radius:10px;background:#087d75;color:#fff;font:inherit;font-weight:800;cursor:pointer}.secondary{color:#54666d}@media(max-width:620px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body><main>
    <a class="eyebrow" href="{{ route('admin.products.index') }}">← Back to products</a>
    <h1>{{ $product->exists ? 'Edit product' : 'New digital product' }}</h1><p class="intro">Product files are deliberately handled in the next secured library step; this screen controls the customer-facing catalogue.</p>
    @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="errors"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf @if($product->exists) @method('PUT') @endif
        <div class="grid">
            <div class="full"><label for="title">Product title</label><input id="title" name="title" value="{{ old('title', $product->title) }}" required></div>
            <div><label for="price">Price</label><input id="price" name="price" type="number" min="0" max="999999.99" step="0.01" value="{{ old('price', $product->price) }}" required></div>
            <div><label for="currency">Currency</label><input id="currency" name="currency" maxlength="3" value="{{ old('currency', $product->currency ?: 'USD') }}" required></div>
            <div class="full"><label for="description">Description</label><textarea id="description" name="description" required>{{ old('description', $product->description) }}</textarea></div>
            <div><label for="slug">URL slug</label><input id="slug" name="slug" value="{{ old('slug', $product->slug) }}"><span class="hint">Leave blank to generate it from the title.</span></div>
            <div><label for="payhip_product_id">Payhip reference</label><input id="payhip_product_id" name="payhip_product_id" value="{{ old('payhip_product_id', $product->payhip_product_id) }}"><span class="hint">For migration traceability only; not shown publicly.</span></div>
            <div class="full"><label for="seo_title">SEO page title</label><input id="seo_title" name="seo_title" value="{{ old('seo_title', $product->seo_title) }}"></div>
            <div class="full"><label for="seo_description">SEO meta description</label><textarea id="seo_description" name="seo_description" style="min-height:90px">{{ old('seo_description', $product->seo_description) }}</textarea></div>
            <div class="check"><input id="is_published" name="is_published" type="checkbox" value="1" @checked(old('is_published', $product->is_published))><label for="is_published">Publish in the store</label></div>
            <div class="check"><input id="is_featured" name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product->is_featured))><label for="is_featured">Feature on the home page</label></div>
        </div>
        <div class="actions"><button class="button" type="submit">{{ $product->exists ? 'Save product' : 'Create product' }}</button><a class="secondary" href="{{ route('admin.products.index') }}">Cancel</a></div>
    </form>
    @if($product->exists)
        <form method="post" action="{{ route('admin.products.files.store', $product) }}" enctype="multipart/form-data">
            @csrf
            <h2>Private download files</h2>
            <p class="intro">Uploaded files are stored privately and are served only through the customer download authorization check.</p>
            <div class="full" style="margin-top:18px"><label for="file">Add a product file</label><input id="file" type="file" name="file" required><span class="hint">Maximum upload size: 500 MB. ZIP, PDF, templates, spreadsheets, and other digital product formats are supported.</span></div>
        </form>
        @if($product->files->isNotEmpty())
            <div style="margin-top:24px; border-top:1px solid #e6edef; padding-top:16px">
                @foreach($product->files as $file)
                    <div style="display:flex; gap:14px; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #eef2f3">
                        <span><strong>{{ $file->original_name }}</strong><br><span class="hint">{{ number_format($file->size_bytes / 1024, 1) }} KB · {{ $file->mime_type ?: 'Unknown type' }}</span></span>
                        <form method="post" action="{{ route('admin.products.files.destroy', [$product, $file]) }}">@csrf @method('DELETE')<button type="submit" style="background:#fff;color:#a54232;border:1px solid #f0beb2">Remove</button></form>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</main></body></html>
