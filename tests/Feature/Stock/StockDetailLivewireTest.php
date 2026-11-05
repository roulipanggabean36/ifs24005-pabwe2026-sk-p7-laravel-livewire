<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->member()->create();
        $this->stock = Stock::factory()->withQuantity(20)->create(['user_id' => $this->user->id]);
    }

    private function lw(?Stock $stock = null)
    {
        return Livewire::actingAs($this->user)->test(StockDetail::class, ['stock' => $stock ?? $this->stock]);
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

    public function test_mount_loads_form(): void
    {
        $this->lw()->assertSet('name', $this->stock->name)->assertSet('quantity', 20);
    }

    public function test_save(): void
    {
        $this->lw()->set('name', 'Baru')->set('quantity', 2)->set('min_stock', 5)->call('save');

        $fresh = $this->stock->fresh();
        $this->assertSame('Baru', $fresh->name);
        $this->assertSame('low_stock', $fresh->status->value);
    }

    public function test_save_validation(): void
    {
        $this->lw()->set('name', '')->call('save')->assertHasErrors('name');
    }

    public function test_adjust(): void
    {
        $this->lw()->call('adjust', 1)->assertSet('quantity', 21);
        $this->assertSame(21, $this->stock->fresh()->quantity);
    }

    public function test_upload_and_remove_cover(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $this->lw()
            ->set('cover', UploadedFile::fake()->image('c.jpg'))
            ->call('uploadCover')
            ->assertHasNoErrors();

        $path = $this->stock->fresh()->cover_image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->lw($this->stock->fresh())->call('removeCover');
        $this->assertNull($this->stock->fresh()->cover_image_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_cover_required(): void
    {
        $this->lw()->call('uploadCover')->assertHasErrors('cover');
    }

    public function test_delete_redirects(): void
    {
        $this->lw()->call('delete')->assertRedirect(route('stocks.index'));
        $this->assertDatabaseMissing('stocks', ['id' => $this->stock->id]);
    }

    public function test_renders_markdown_preview(): void
    {
        $this->stock->update(['description' => '**tebal**']);

        $this->lw($this->stock->fresh())->assertSeeHtml('<strong>tebal</strong>');
    }

    public function test_other_member_cannot_view(): void
    {
        $this->assertDenied(fn () => Livewire::actingAs(User::factory()->member()->create())
            ->test(StockDetail::class, ['stock' => $this->stock]));
    }

    public function test_admin_can_view_but_not_modify(): void
    {
        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(StockDetail::class, ['stock' => $this->stock]);

        $component->assertSet('name', $this->stock->name);

        $this->assertDenied(fn () => $component->set('name', 'Diubah')->call('save'));
        $this->assertNotSame('Diubah', $this->stock->fresh()->name);
    }
}