<?php

namespace App\Features\Profile\Livewire;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public $photo = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name     = $user->name;
        $this->username = $user->username;
        $this->email    = $user->email;
    }

    public function saveProfile(ProfileService $service): void
    {
        $user = auth()->user();

        $data = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $service->updateProfile($user, $data);
        session()->flash('status', 'Profil diperbarui.');
    }

    public function savePassword(ProfileService $service): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:5', 'confirmed'],
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Password saat ini salah.');

            return;
        }

        $service->updatePassword(auth()->user(), $this->password);
        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('status', 'Password diperbarui.');
    }

    public function savePhoto(ProfileService $service): void
    {
        $this->validate(['photo' => ['required', 'image', 'max:2048']]);

        $service->updatePhoto(auth()->user(), $this->photo);
        $this->photo = null;
        session()->flash('status', 'Foto profil diperbarui.');
    }

    public function removePhoto(ProfileService $service): void
    {
        $service->removePhoto(auth()->user());
    }

    public function render()
    {
        return view('livewire.profile.profile', ['user' => auth()->user()->fresh()]);
    }
}