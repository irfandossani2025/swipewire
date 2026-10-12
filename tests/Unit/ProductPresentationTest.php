<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProductPresentationTest extends TestCase
{
    public function test_long_marketplace_titles_are_split_into_a_name_and_detail(): void
    {
        $product = new Product(['title' => 'ADHD Cleaning Planner Printable – 15-Minute Daily Reset, Room Checklists – A4 + US Letter']);

        $this->assertSame('ADHD Cleaning Planner Printable', $product->shortTitle());
        $this->assertSame('15-Minute Daily Reset, Room Checklists · A4 + US Letter', $product->titleDetail());
    }

    public function test_a_simple_title_has_no_detail_line(): void
    {
        $product = new Product(['title' => 'Bilingual Classroom Organiser']);

        $this->assertSame('Bilingual Classroom Organiser', $product->shortTitle());
        $this->assertNull($product->titleDetail());
    }

    public function test_hyphenated_words_are_not_treated_as_separators(): void
    {
        $product = new Product(['title' => 'Arabic-English Kids Learning Pack - A4 Printable']);

        $this->assertSame('Arabic-English Kids Learning Pack', $product->shortTitle());
    }

    public function test_price_labels(): void
    {
        $this->assertSame('Free', (new Product(['price' => 0]))->priceLabel());
        $this->assertSame('$4.99', (new Product(['price' => 4.99]))->priceLabel());
        $this->assertSame('$15.00', (new Product(['price' => 15]))->priceLabel());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function categorisedTitles(): array
    {
        return [
            'stickers' => ['Retro Tech Laptop Sticker Sheet - A4 Printable', 'stickers'],
            'wall art' => ['Retro Wall Art Set of 6 – 70s Groovy Motivational Prints', 'wall-art'],
            'kids' => ['Ramadan Good Deeds for Kids Printable', 'kids'],
            'classroom' => ['Oman Celebration Classroom Pack - A4 Printable', 'kids'],
            'business' => ['Invoice & Quote Template – Excel & Google Sheets', 'business'],
            'celebrations' => ['Bridal Shower Games Bundle Printable – Sage Green', 'celebrations'],
            'planners' => ['2027 Printable Wall Calendar – Minimalist Boho', 'planners'],
        ];
    }

    #[DataProvider('categorisedTitles')]
    public function test_products_are_categorised_from_their_title(string $title, string $expectedSlug): void
    {
        $this->assertSame($expectedSlug, (new Product(['title' => $title]))->category()['slug']);
    }
}
