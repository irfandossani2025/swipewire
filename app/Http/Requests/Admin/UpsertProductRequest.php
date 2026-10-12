<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertProductRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Product::class, 'slug')->ignore($this->route('product'))],
            'payhip_product_id' => ['nullable', 'string', 'max:32', Rule::unique(Product::class, 'payhip_product_id')->ignore($this->route('product'))],
            'description' => ['required', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }

    /**
     * A product may only go on sale once a downloadable file is attached, so
     * customers can never pay for something they cannot download.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->boolean('is_published')) {
                    return;
                }

                $product = $this->route('product');

                if (! $product instanceof Product || ! $product->files()->exists()) {
                    $validator->errors()->add('is_published', 'Upload this product\'s downloadable file before publishing it.');
                }
            },
        ];
    }
}
