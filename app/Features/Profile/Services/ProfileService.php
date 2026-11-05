<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
        ]);

        return $user->refresh();
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => $password]);
    }

    public function updatePhoto(User $user, UploadedFile $file): User
    {
        $this->deletePhotoFile($user->profile_photo_path);
        $path = $file->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return $user->refresh();
    }

    public function removePhoto(User $user): User
    {
        $this->deletePhotoFile($user->profile_photo_path);
        $user->update(['profile_photo_path' => null]);

        return $user->refresh();
    }

    private function deletePhotoFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}