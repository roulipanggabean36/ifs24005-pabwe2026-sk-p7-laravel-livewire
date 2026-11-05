<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-xl font-bold mb-4">Daftar</h1>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label for="reg-name" class="block text-sm font-medium mb-1">Nama</label>
            <input id="reg-name" type="text" wire:model="name" class="w-full border rounded px-3 py-2">
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="reg-username" class="block text-sm font-medium mb-1">Username</label>
            <input id="reg-username" type="text" wire:model="username" class="w-full border rounded px-3 py-2">
            @error('username') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="reg-email" class="block text-sm font-medium mb-1">Email</label>
            <input id="reg-email" type="email" wire:model="email" class="w-full border rounded px-3 py-2">
            @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="reg-password" class="block text-sm font-medium mb-1">Password</label>
            <input id="reg-password" type="password" wire:model="password" class="w-full border rounded px-3 py-2">
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="reg-password-confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
            <input id="reg-password-confirmation" type="password" wire:model="password_confirmation"
                   class="w-full border rounded px-3 py-2">
        </div>

        <button id="register-submit" type="submit"
                class="w-full bg-blue-600 text-white rounded py-2 hover:bg-blue-700">Daftar</button>
    </form>

    <p class="text-sm mt-4 text-center">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk</a>
    </p>
</div>