<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StartProductFileUploadRequest extends FormRequest
{
    /**
     * Largest product file accepted, matching StoreProductFileRequest (500 MB).
     */
    public const MAX_SIZE_BYTES = 512000 * 1024;

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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'original_name' => ['required', 'string', 'max:255'],
            'size_bytes' => ['required', 'integer', 'min:1', 'max:'.self::MAX_SIZE_BYTES],
        ];
    }
}
