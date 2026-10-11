<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products · Swipewire Admin</title>
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, sans-serif; color:#172930; background:#f4f7f8; } *{box-sizing:border-box} body{margin:0} main{max-width:1120px;margin:auto;padding:48px 24px 72px}.eyebrow{color:#16867e;font-size:.76rem;font-weight:800;letter-spacing:.13em;text-transform:uppercase}h1{margin:7px 0;font-size:clamp(2rem,5vw,3.2rem);letter-spacing:-.05em}.top{display:flex;gap:24px;justify-content:space-between;align-items:end}.intro{margin:0;color:#64727a}.button{display:inline-block;padding:11px 14px;border-radius:10px;background:#087d75;color:#fff;text-decoration:none;font-weight:800}.notice{margin:24px 0;padding:14px 16px;color:#08554f;border:1px solid #a4ded7;background:#e7faf6;border-radius:12px}.table-wrap{margin-top:28px;overflow-x:auto;border:1px solid #dce5e7;background:#fff;border-radius:16px;box-shadow:0 14px 40px #1730400c}table{width:100%;min-width:720px;border-collapse:collapse}th,td{padding:17px 18px;border-bottom:1px solid #edf1f2;text-align:left}th{color:#69777d;font-size:.72rem;letter-spacing:.1em;text-transform:uppercase}tr:last-child td{border-bottom:0}.muted{color:#68777d}.pill{display:inline-block;padding:5px 9px;border-radius:999px;font-size:.75rem;font-weight:800}.live{color:#06685f;background:#daf5ed}.draft{color:#704b00;background:#fff4cf}.edit{color:#08726b;font-weight:800;text-decoration:none}.pagination{display:flex;gap:8px;align-items:center;margin-top:24px;color:#64727a}.pagination a{color:#08726b;font-weight:800;text-decoration:none}@media(max-width:620px){.top{align-items:start;flex-direction:column}}
    </style>
</head>
<body><main>
    <div class="top"><div><div class="eyebrow">Swipewire administration</div><h1>Digital products</h1><p class="intro">Create, price, publish, and optimize the products customers discover.</p></div><a class="button" href="{{ route('admin.products.create') }}">Add product</a></div>
    @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
    <div class="table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Listing</th><th>Files</th><th></th></tr></thead><tbody>
    @forelse($products as $product)<tr><td><strong>{{ $product->title }}</strong><br><span class="muted">/{{ $product->slug }}</span></td><td>{{ $product->currency }} {{ number_format((float) $product->price, 2) }}</td><td><span class="pill {{ $product->is_published ? 'live' : 'draft' }}">{{ $product->is_published ? 'Published' : 'Draft' }}</span></td><td>{{ $product->files()->count() }}</td><td><a class="edit" href="{{ route('admin.products.edit', $product) }}">Edit</a></td></tr>
    @empty<tr><td colspan="5">No products yet. Add your first product to begin the catalogue.</td></tr>@endforelse
    </tbody></table></div>
    @if($products->hasPages())<nav class="pagination" aria-label="Product pages">@if($products->onFirstPage())<span>Previous</span>@else<a href="{{ $products->previousPageUrl() }}">Previous</a>@endif <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span> @if($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
@include('partials.footer')</main></body></html>
