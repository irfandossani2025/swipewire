<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\PayhipCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachStagedProductFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(PayhipCatalogSeeder::class);
    }

    public function test_a_dry_run_changes_nothing(): void
    {
        $this->stage('bilingual-classroom-organiser-a4.pdf', 6913);

        $this->artisan('swipewire:attach-staged-files', ['--dry-run' => true])->assertSuccessful();

        Storage::disk('local')->assertExists('incoming/bilingual-classroom-organiser-a4.pdf');
        $this->assertDatabaseCount('product_files', 0);
    }

    public function test_staged_files_are_attached_privately_and_left_unpublished(): void
    {
        $contents = $this->stage('bilingual-classroom-organiser-a4.pdf', 6913);
        $product = Product::where('payhip_product_id', 'z3acM')->sole();

        $this->artisan('swipewire:attach-staged-files')->assertSuccessful();

        $productFile = $product->files()->sole();
        $this->assertSame('bilingual-classroom-organiser-a4.pdf', $productFile->original_name);
        $this->assertSame(6913, $productFile->size_bytes);
        $this->assertSame(hash('sha256', $contents), $productFile->checksum);
        $this->assertStringStartsWith("products/{$product->id}/", $productFile->path);
        $this->assertSame($contents, Storage::disk('local')->get($productFile->path));
        Storage::disk('local')->assertMissing('incoming/bilingual-classroom-organiser-a4.pdf');
        $this->assertFalse($product->fresh()->is_published);
    }

    public function test_one_product_can_receive_several_files(): void
    {
        $this->stage('Retro-Wall-Art_2x3-ratio_4x6-24x36.zip', 7496583);
        $this->stage('Retro-Wall-Art_ISO-ratio_A5-A2.zip', 7654995);

        $this->artisan('swipewire:attach-staged-files')->assertSuccessful();

        $this->assertSame(2, Product::where('payhip_product_id', 'lDE5H')->sole()->files()->count());
    }

    public function test_a_file_of_the_wrong_size_is_rejected_and_left_in_place(): void
    {
        $this->stage('bilingual-classroom-organiser-a4.pdf', 100);

        $this->artisan('swipewire:attach-staged-files')->assertFailed();

        Storage::disk('local')->assertExists('incoming/bilingual-classroom-organiser-a4.pdf');
        $this->assertDatabaseCount('product_files', 0);
    }

    public function test_unrecognised_files_are_left_in_incoming(): void
    {
        $this->stage('Swipewire-Focus-Planner-2027.pdf', 50);

        $this->artisan('swipewire:attach-staged-files')
            ->expectsOutputToContain('not in map')
            ->assertSuccessful();

        Storage::disk('local')->assertExists('incoming/Swipewire-Focus-Planner-2027.pdf');
        $this->assertDatabaseCount('product_files', 0);
    }

    public function test_re_staging_an_attached_file_does_not_duplicate_it(): void
    {
        $contents = $this->stage('bilingual-classroom-organiser-a4.pdf', 6913);
        $this->artisan('swipewire:attach-staged-files')->assertSuccessful();

        Storage::disk('local')->put('incoming/bilingual-classroom-organiser-a4.pdf', $contents);
        $this->artisan('swipewire:attach-staged-files')->expectsOutputToContain('already attached')->assertSuccessful();

        $this->assertDatabaseCount('product_files', 1);
    }

    private function stage(string $fileName, int $sizeBytes): string
    {
        $contents = random_bytes($sizeBytes);
        Storage::disk('local')->put("incoming/{$fileName}", $contents);

        return $contents;
    }
}
