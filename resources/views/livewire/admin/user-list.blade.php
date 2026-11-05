<div class="space-y-4">
    <h1 class="text-2xl font-bold">Daftar Pengguna</h1>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, username, atau email..."
           class="border rounded px-3 py-2 w-full">

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Username</th>
                    <th class="p-3">Email</th>
                    <th class="p-3 text-center">Jumlah Stok</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr class="border-t" wire:key="user-{{ $u->id }}">
                        <td class="p-3">{{ $u->name }}</td>
                        <td class="p-3">{{ $u->username }}</td>
                        <td class="p-3">{{ $u->email }}</td>
                        <td class="p-3 text-center">{{ $u->stocks_count }}</td>
                        <td class="p-3 text-right">
                            <a href="{{ route('admin.users.stocks', $u) }}" class="text-blue-600 hover:underline">Lihat stok</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</div>