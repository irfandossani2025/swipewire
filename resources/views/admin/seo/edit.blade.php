@extends('layouts.admin')

@section('title', 'SEO settings')

@section('content')
    <div class="section-head">
        <div>
            <span class="eyebrow">Search</span>
            <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);margin-top:6px">Website SEO</h1>
            <p>Set the default search title, description and indexing for the store. Individual products can still override their own SEO fields.</p>
        </div>
    </div>

    @if(session('status'))
        <p class="notice" style="margin-bottom:20px">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <p class="error" style="margin-bottom:20px">{{ $errors->first() }}</p>
    @endif

    <form class="card" method="post" action="{{ route('admin.seo.update') }}" style="max-width:760px">
        @csrf
        @method('PUT')
        <div class="field">
            <label for="site_title">Default site title</label>
            <input id="site_title" name="site_title" value="{{ old('site_title', $settings['site_title'] ?? 'Swipewire | Digital tools made to use') }}" required>
            <span class="hint">Shown in browser tabs and search results. Aim for 60 characters or fewer.</span>
        </div>
        <div class="field">
            <label for="site_description">Default meta description</label>
            <textarea id="site_description" name="site_description" required style="min-height:110px">{{ old('site_description', $settings['site_description'] ?? 'Useful digital planners, printables and templates. Ready to download.') }}</textarea>
            <span class="hint">Aim for 155 characters or fewer.</span>
        </div>
        <div class="field">
            <label for="robots">Search indexing</label>
            <select id="robots" name="robots">
                <option value="index,follow" @selected(old('robots', $settings['robots'] ?? 'index,follow') === 'index,follow')>Allow indexing and links</option>
                <option value="noindex,nofollow" @selected(old('robots', $settings['robots'] ?? '') === 'noindex,nofollow')>Keep the store out of search engines</option>
            </select>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save SEO settings</button></div>
    </form>
@endsection
