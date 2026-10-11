<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.seo.edit', ['settings' => SiteSetting::query()->pluck('value', 'key')]);
    }

    public function update(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return to_route('admin.seo.edit')->with('status', 'Website SEO settings saved.');
    }
}
