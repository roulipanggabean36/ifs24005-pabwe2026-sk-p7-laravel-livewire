<div class="max-w-2xl mx-auto space-y-4">
    @if (session('status'))
        <div class="bg-green-100 text-green-800 rounded px-4 py-2 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded shadow p-4 space-y-3">
        <h1 class="sr-only">Profil</h1>
        <h2 class="font-bold">Foto Profil</h2>
        <div class="flex items-center gap-4">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Foto" class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-gray-300 flex items-center justify-center text-2xl font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div class="space-y-2 text-sm">
                <input aria-label="Foto" type="file" wire:model="photo" accept="image/*">
                @error('photo') <p class="text-red-700">{{ $message }}</p> @enderror
                <div class="space-x-3">
                    <button wire:click="savePhoto" class="bg-gray-800 text-white rounded px-3 py-1">Unggah</button>
                    @if ($user->profile_photo_path)
                        <button wire:click="removePhoto" class="text-red-700 hover:underline">Hapus foto</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form wire:submit="saveProfile" class="bg-white rounded shadow p-4 space-y-3">
        <h2 class="font-bold">Data Profil</h2>
        <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input aria-label="Nama" type="text" wire:model="name" class="w-full border rounded px-3 py-2">
            @error('name') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Username</label>
            <input aria-label="Username" type="text" wire:model="username" class="w-full border rounded px-3 py-2">
            @error('username') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input aria-label="Email" type="email" wire:model="email" class="w-full border rounded px-3 py-2">
            @error('email') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700">Simpan</button>
    </form>

    <form wire:submit="savePassword" class="bg-white rounded shadow p-4 space-y-3">
        <h2 class="font-bold">Ubah Password</h2>
        <div>
            <label class="block text-sm font-medium mb-1">Password saat ini</label>
            <input aria-label="Password saat ini" type="password" wire:model="current_password" class="w-full border rounded px-3 py-2">
            @error('current_password') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password baru</label>
            <input aria-label="Password baru" type="password" wire:model="password" class="w-full border rounded px-3 py-2">
            @error('password') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Konfirmasi password baru</label>
            <input aria-label="Konfirmasi password" type="password" wire:model="password_confirmation" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700">Ubah Password</button>
    </form>
</div>