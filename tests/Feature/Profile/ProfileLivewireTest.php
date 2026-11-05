<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['password' => 'lama123']);
    }

    private function lw()
    {
        return Livewire::actingAs($this->user)->test(Profile::class);
    }

    public function test_mount_fills_data(): void
    {
        $this->lw()->assertSet('name', $this->user->name)->assertSet('email', $this->user->email);
    }

    public function test_save_profile(): void
    {
        $this->lw()
            ->set('name', 'Nama Baru')->set('username', 'baru')->set('email', 'baru@example.com')
            ->call('saveProfile');

        $this->assertSame('baru', $this->user->fresh()->username);
    }

    public function test_save_profile_validation(): void
    {
        $other = User::factory()->create();

        $this->lw()->set('username', $other->username)->call('saveProfile')->assertHasErrors('username');
    }

    public function test_save_password(): void
    {
        $this->lw()
            ->set('current_password', 'lama123')
            ->set('password', 'baru123')
            ->set('password_confirmation', 'baru123')
            ->call('savePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('baru123', $this->user->fresh()->password));
    }

    public function test_wrong_current_password(): void
    {
        $this->lw()
            ->set('current_password', 'salah')
            ->set('password', 'baru123')
            ->set('password_confirmation', 'baru123')
            ->call('savePassword')
            ->assertHasErrors('current_password');
    }

    public function test_upload_and_remove_photo(): void
    {
        Storage::fake('public');

        $this->lw()->set('photo', UploadedFile::fake()->image('p.jpg'))->call('savePhoto');
        $path = $this->user->fresh()->profile_photo_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->lw()->call('removePhoto');
        $this->assertNull($this->user->fresh()->profile_photo_path);
    }

    public function test_photo_required(): void
    {
        $this->lw()->call('savePhoto')->assertHasErrors('photo');
    }
}