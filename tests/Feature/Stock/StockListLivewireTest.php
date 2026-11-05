<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->member()->create();
    }

    private function lw()
    {
        return Livewire::actingAs($this->user)->test(StockList::class);
    }

    /** Lolos jika aksi ditolak (exception ATAU status 403). */
    private function assertDenied(callable $action): void
    {
        try {
            $action()->assertForbidden();
        } catch (AuthorizationException|HttpException) {
            $this->assertTrue(true);
        }
    }

    public function test_lists_only_own_stocks(): void
    {
        Stock::factory()->create(['user_id' => $this->user->id, 'name' => 'Milik Saya']);
        Stock::factory()->create(['name' => 'Milik Orang Lain']);

        $this->lw()->assertSee('Milik Saya')->assertDontSee('Milik Orang Lain');
    }

    public function test_search_and_status_filter(): void
    {
        Stock::factory()->withQuantity(50)->create(['user_id' => $this->user->id, 'name' => 'Kabel', 'sku' => 'KB-1']);
        Stock::factory()->withQuantity(0)->create(['user_id' => $this->user->id, 'name' => 'Lampu', 'sku' => 'LP-1']);

        $this->lw()
            ->set('search', 'Kabel')->assertSee('Kabel')->assertDontSee('Lampu')
            ->set('search', 'LP-1')->assertSee('Lampu')->assertDontSee('Kabel')
            ->set('search', '')
            ->set('statusFilter', 'out_of_stock')->assertSee('Lampu')->assertDontSee('Kabel');
    }

    public function test_create_stock(): void
    {
        $this->lw()
            ->call('openCreate')->assertSet('showForm', true)
            ->set('name', 'Kabel')
            ->set('sku', 'K-1')
            ->set('quantity', 3)
            ->set('min_stock', 5)
            ->set('unit', 'pcs')
            ->call('save')
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->user->id, 'name' => 'Kabel', 'status' => 'low_stock',
        ]);
    }

    public function test_create_validation_error(): void
    {
        $this->lw()->call('openCreate')->set('name', '')->call('save')->assertHasErrors('name');
    }

    public function test_edit_stock(): void
    {
        $stock = Stock::factory()->withQuantity(50)->create(['user_id' => $this->user->id]);

        $this->lw()
            ->call('openEdit', $stock->id)
            ->assertSet('editingId', $stock->id)
            ->assertSet('name', $stock->name)
            ->set('name', 'Nama Baru')
            ->set('quantity', 0)
            ->call('save');

        $this->assertDatabaseHas('stocks', ['id' => $stock->id, 'name' => 'Nama Baru', 'status' => 'out_of_stock']);
    }

    public function test_close_form_resets(): void
    {
        $this->lw()->call('openCreate')->set('name', 'x')->call('closeForm')
            ->assertSet('showForm', false)->assertSet('name', '');
    }

    public function test_delete_stock(): void
    {
        $stock = Stock::factory()->create(['user_id' => $this->user->id]);

        $this->lw()->call('delete', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_adjust_quantity_syncs_status(): void
    {
        $stock = Stock::factory()->withQuantity(0)->create(['user_id' => $this->user->id]);

        $this->lw()->call('adjust', $stock->id, 1);
        $this->assertSame('low_stock', $stock->fresh()->status->value);

        $this->lw()->call('adjust', $stock->id, -5);
        $this->assertSame(0, $stock->fresh()->quantity);
        $this->assertSame('out_of_stock', $stock->fresh()->status->value);
    }

    public function test_cannot_touch_other_users_stock(): void
    {
        $other = Stock::factory()->withQuantity(10)->create();

        try {
            $this->lw()->call('adjust', $other->id, 1);
        } catch (\Throwable) {
            // ditolak lewat exception: sesuai harapan
        }
        try {
            $this->lw()->call('delete', $other->id);
        } catch (\Throwable) {
        }

        $this->assertSame(10, $other->fresh()->quantity);
        $this->assertDatabaseHas('stocks', ['id' => $other->id]);
    }

    public function test_admin_cannot_create_via_component(): void
    {
        $component = Livewire::actingAs(User::factory()->admin()->create())->test(StockList::class);

        $this->assertDenied(fn () => $component->call('openCreate'));
        $component->assertSet('showForm', false);
    }

    public function test_export_and_template_download(): void
    {
        Stock::factory()->create(['user_id' => $this->user->id]);

        $this->lw()->call('export')->assertFileDownloaded();
        $this->lw()->call('downloadTemplate')->assertFileDownloaded();
    }

    public function test_import_excel(): void
    {
        Storage::fake('local');
        $content = app(StockExcelService::class)->template();
        $file    = UploadedFile::fake()->createWithContent('stok.xlsx', $content);

        $this->lw()->set('importFile', $file)->call('import')->assertHasNoErrors();

        $this->assertDatabaseHas('stocks', ['user_id' => $this->user->id, 'name' => 'Contoh Barang']);
    }

    public function test_import_requires_file(): void
    {
        $this->lw()->call('import')->assertHasErrors('importFile');
    }

    public function test_ai_generate_and_save(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willReturn([
            new GeneratedStockItem('Lem', 'L-1', 'desc', 10, 'pcs', 5000.0, 2),
        ]);
        $this->app->instance(AiClientInterface::class, $stub);

        $this->lw()
            ->call('openAi')->assertSet('showAi', true)
            ->set('aiPrompt', 'alat tulis')
            ->call('generateAi')
            ->assertSet('aiSelected', [0])
            ->call('saveAi')
            ->assertSet('showAi', false);

        $this->assertDatabaseHas('stocks', ['user_id' => $this->user->id, 'name' => 'Lem']);
    }

    public function test_ai_save_ignores_unknown_index(): void
    {
        $this->lw()->set('aiSelected', [9])->call('saveAi');

        $this->assertSame(0, Stock::count());
    }

    public function test_ai_prompt_required(): void
    {
        $this->lw()->call('openAi')->call('generateAi')->assertHasErrors('aiPrompt');
    }

    public function test_ai_failure_shows_error(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willThrowException(new RuntimeException('AI error'));
        $this->app->instance(AiClientInterface::class, $stub);

        $this->lw()->set('aiPrompt', 'x')->call('generateAi')->assertHasErrors('aiPrompt');
    }

    public function test_close_ai(): void
    {
        $this->lw()->call('openAi')->call('closeAi')->assertSet('showAi', false);
    }
}