<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile_and_password(): void
    {
        $service = new ProfileService();
        $user    = User::factory()->create();

        $user = $service->updateProfile($user, ['name' => 'Baru', 'username' => 'baru', 'email' => 'b@example.com']);
        $this->assertSame('baru', $user->username);

        $service->updatePassword($user, 'rahasia1');
        $this->assertTrue(Hash::check('rahasia1', $user->fresh()->password));
    }

    public function test_photo_lifecycle(): void
    {
        Storage::fake('public');
        $service = new ProfileService();
        $user    = User::factory()->create();

        $user  = $service->updatePhoto($user, UploadedFile::fake()->image('a.jpg'));
        $first = $user->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $user = $service->updatePhoto($user, UploadedFile::fake()->image('b.jpg'));
        Storage::disk('public')->assertMissing($first);

        $user = $service->removePhoto($user);
        $this->assertNull($user->profile_photo_path);

        $user = $service->removePhoto($user);
        $this->assertNull($user->profile_photo_path);
    }
}