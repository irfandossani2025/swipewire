<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Imports the Payhip catalogue as unpublished draft products.
 *
 * Products that already exist (matched by Payhip ID) are left untouched, so the
 * seeder is safe to re-run and never overwrites edits made in the admin area.
 * Drafts stay unpublished until their downloadable file has been attached.
 *
 * Run in production with: php artisan db:seed --class=PayhipCatalogSeeder --force
 */
class PayhipCatalogSeeder extends Seeder
{
    private const SEO_TITLE_LIMIT = 60;

    private const SEO_DESCRIPTION_LIMIT = 155;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->catalogue() as $item) {
            if (Product::where('payhip_product_id', $item['payhip_id'])->exists()) {
                continue;
            }

            $description = $this->cleanDescription($item['description']);
            $shortTitle = $this->shortTitle($item['title']);

            Product::create([
                'title' => $item['title'],
                'slug' => $this->uniqueSlug($shortTitle, $item['payhip_id']),
                'payhip_product_id' => $item['payhip_id'],
                'description' => $description,
                'price' => $item['price'],
                'currency' => Str::upper($item['currency']),
                'seo_title' => $this->seoTitle($shortTitle),
                'seo_description' => $this->limitAtWord(Str::squish($description), self::SEO_DESCRIPTION_LIMIT),
                'is_published' => false,
                'is_featured' => false,
                'published_at' => null,
            ]);
        }
    }

    /**
     * @return array<int, array{payhip_id: string, title: string, price: string, currency: string, description: string}>
     */
    private function catalogue(): array
    {
        return json_decode(file_get_contents(__DIR__.'/data/payhip-catalog.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    private function cleanDescription(string $description): string
    {
        $text = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /**
     * Payhip titles carry keyword tails ("Name – Feature, Feature – A4"); keep the leading name.
     */
    private function shortTitle(string $title): string
    {
        return trim(preg_split('/\s+[–|-]\s+/u', $title)[0]);
    }

    private function seoTitle(string $shortTitle): string
    {
        $branded = $shortTitle.' | Swipewire';

        return mb_strlen($branded) <= self::SEO_TITLE_LIMIT
            ? $branded
            : $this->limitAtWord($shortTitle, self::SEO_TITLE_LIMIT);
    }

    private function limitAtWord(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit - 1);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim(mb_substr($cut, 0, $lastSpace ?: $limit - 1), ' ,;:-–').'…';
    }

    private function uniqueSlug(string $shortTitle, string $payhipId): string
    {
        $slug = Str::slug($shortTitle);

        return Product::where('slug', $slug)->exists()
            ? $slug.'-'.Str::lower($payhipId)
            : $slug;
    }
}
