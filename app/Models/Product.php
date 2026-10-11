<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'payhip_product_id',
        'description',
        'price',
        'currency',
        'cover_image_path',
        'seo_title',
        'seo_description',
        'is_published',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_published' => 'boolean', 'is_featured' => 'boolean', 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductFile::class);
    }
}
