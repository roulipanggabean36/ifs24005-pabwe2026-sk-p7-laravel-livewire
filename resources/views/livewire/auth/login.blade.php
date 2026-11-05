<div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-xl font-bold mb-4">Masuk</h1>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label for="login-username" class="block text-sm font-medium mb-1">Username atau Email</label>
            <input id="login-username" type="text" wire:model="login" autocomplete="username"
                   class="w-full border rounded px-3 py-2">
            @error('login') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="login-password" class="block text-sm font-medium mb-1">Password</label>
            <input id="login-password" type="password" wire:model="password" autocomplete="current-password"
                   class="w-full border rounded px-3 py-2">
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember"> Ingat saya
        </label>

        <button id="login-submit" type="submit"
                class="w-full bg-blue-600 text-white rounded py-2 hover:bg-blue-700">Masuk</button>
    </form>

    <p class="text-sm mt-4 text-center">
        Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Daftar</a>
    </p>
</div>