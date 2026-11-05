<div class="space-y-4">
    <div>
        <a href="{{ route('admin.users') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali</a>
        <h1 class="text-2xl font-bold mt-1">Stok milik {{ $user->name }}</h1>
        <p class="text-sm text-gray-500">Tampilan hanya baca (read-only).</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU..."
               class="border rounded px-3 py-2 flex-1 min-w-[200px]">
        <select wire:model.live="statusFilter" class="border rounded px-3 py-2">
            <option value="">Semua status</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Nama</th>
                    <th class="p-3">SKU</th>
                    <th class="p-3 text-center">Qty</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $stock)
                    <tr class="border-t" wire:key="stock-{{ $stock->id }}">
                        <td class="p-3">{{ $stock->name }}</td>
                        <td class="p-3">{{ $stock->sku ?? '-' }}</td>
                        <td class="p-3 text-center">{{ $stock->quantity }} {{ $stock->unit }}</td>
                        <td class="p-3">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $stocks->links() }}
</div>