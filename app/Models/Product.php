<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }

    /**
     * Customer-facing price label, e.g. "Free" or "$4.99".
     */
    public function priceLabel(): string
    {
        return $this->isFree() ? 'Free' : '$'.number_format((float) $this->price, 2);
    }

    /**
     * Leading product name without marketplace keyword tails ("Name – Feature – A4").
     */
    public function shortTitle(): string
    {
        return trim(preg_split('/\s+[–|-]\s+/u', $this->title)[0]);
    }

    /**
     * The keyword tail of the title, used as a supporting line under the short title.
     */
    public function titleDetail(): ?string
    {
        $parts = preg_split('/\s+[–|-]\s+/u', $this->title);

        return count($parts) > 1 ? trim(implode(' · ', array_slice($parts, 1))) : null;
    }

    /**
     * Storefront category inferred from the title, with the brand accent used for its preview tile.
     *
     * @return array{slug: string, label: string, tone: string}
     */
    public function category(): array
    {
        $title = Str::lower($this->title);
        $categories = [
            ['slug' => 'stickers', 'label' => 'Stickers', 'tone' => 'coral', 'keywords' => ['sticker']],
            ['slug' => 'wall-art', 'label' => 'Wall art', 'tone' => 'violet', 'keywords' => ['wall art']],
            ['slug' => 'kids', 'label' => 'Kids & classroom', 'tone' => 'orange', 'keywords' => ['kids', 'classroom', 'learning', 'chore']],
            ['slug' => 'business', 'label' => 'Business tools', 'tone' => 'cobalt', 'keywords' => ['business', 'invoice', 'resume', 'social media', 'brandable']],
            ['slug' => 'celebrations', 'label' => 'Celebrations', 'tone' => 'violet', 'keywords' => ['wedding', 'bridal', 'shower', 'party', 'gift tags']],
        ];

        foreach ($categories as $category) {
            if (Str::contains($title, $category['keywords'])) {
                return ['slug' => $category['slug'], 'label' => $category['label'], 'tone' => $category['tone']];
            }
        }

        return ['slug' => 'planners', 'label' => 'Planners & trackers', 'tone' => 'cobalt'];
    }

    /**
     * Plain-text summary for product cards and meta descriptions.
     */
    public function summary(int $limit = 120): string
    {
        return Str::limit(Str::squish(strip_tags($this->description)), $limit);
    }
}
