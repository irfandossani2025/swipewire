<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:255'],
            'site_description' => ['required', 'string', 'max:320'],
            'robots' => ['required', Rule::in(['index,follow', 'noindex,nofollow'])],
        ];
    }
}
