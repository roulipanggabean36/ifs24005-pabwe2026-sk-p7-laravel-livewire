<div class="space-y-4">
    @if (session('status'))
        <div class="bg-green-100 text-green-800 rounded px-4 py-2 text-sm">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-bold">Daftar Stok</h1>
        <div class="flex flex-wrap gap-2 text-sm">
            <button wire:click="openCreate" class="bg-blue-600 text-white rounded px-3 py-2 hover:bg-blue-700">+ Tambah</button>
            <button wire:click="openAi" class="bg-purple-600 text-white rounded px-3 py-2 hover:bg-purple-700">Generate AI</button>
            <button wire:click="export" class="bg-green-700 text-white rounded px-3 py-2 hover:bg-green-800">Export Excel</button>
            <button wire:click="downloadTemplate" class="bg-gray-600 text-white rounded px-3 py-2 hover:bg-gray-700">Template</button>
        </div>
    </div>

    <form wire:submit="import" class="bg-white rounded shadow p-3 flex flex-wrap items-center gap-2 text-sm">
        <label class="font-medium">Import Excel:</label>
        <input aria-label="File impor Excel" type="file" wire:model="importFile" accept=".xlsx,.xls">
        <button type="submit" class="bg-gray-800 text-white rounded px-3 py-1">Import</button>
        @error('importFile') <span class="text-red-600">{{ $message }}</span> @enderror
    </form>

    <div class="flex flex-wrap gap-2">
        <input aria-label="Cari barang" type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau SKU..."
               class="border rounded px-3 py-2 flex-1 min-w-[200px]">
        <select aria-label="Filter status" wire:model.live="statusFilter" class="border rounded px-3 py-2">
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
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $stock)
                    <tr class="border-t" wire:key="stock-{{ $stock->id }}">
                        <td class="p-3">
                            <a href="{{ route('stocks.show', $stock) }}" class="text-blue-600 hover:underline font-medium">{{ $stock->name }}</a>
                        </td>
                        <td class="p-3">{{ $stock->sku ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-2">
                                <button aria-label="Kurangi stok" wire:click="adjust({{ $stock->id }}, -1)" class="px-2 border rounded hover:bg-gray-100">-</button>
                                <span class="min-w-[3rem]">{{ $stock->quantity }} {{ $stock->unit }}</span>
                                <button aria-label="Tambah stok" wire:click="adjust({{ $stock->id }}, 1)" class="px-2 border rounded hover:bg-gray-100">+</button>
                            </div>
                        </td>
                        <td class="p-3">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
                        </td>
                        <td class="p-3 text-right space-x-2">
                            <button wire:click="openEdit({{ $stock->id }})" class="text-blue-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $stock->id }})" wire:confirm="Hapus stok ini?" class="text-red-600 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $stocks->links() }}

    {{-- Modal create/edit --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <form wire:submit="save" class="bg-white rounded-lg shadow w-full max-w-lg p-6 space-y-3 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold">{{ $editingId ? 'Edit Stok' : 'Tambah Stok' }}</h2>

                <div>
                    <label class="block text-sm font-medium mb-1">Nama</label>
                    <input aria-label="Nama" type="text" wire:model="name" class="w-full border rounded px-3 py-2">
                    @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">SKU</label>
                        <input aria-label="SKU" type="text" wire:model="sku" class="w-full border rounded px-3 py-2">
                        @error('sku') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Satuan</label>
                        <input aria-label="Satuan" type="text" wire:model="unit" class="w-full border rounded px-3 py-2">
                        @error('unit') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input aria-label="Jumlah" type="number" min="0" wire:model="quantity" class="w-full border rounded px-3 py-2">
                        @error('quantity') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Stok Minimum</label>
                        <input aria-label="Stok minimum" type="number" min="0" wire:model="min_stock" class="w-full border rounded px-3 py-2">
                        @error('min_stock') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga</label>
                    <input aria-label="Harga" type="number" min="0" step="0.01" wire:model="price" class="w-full border rounded px-3 py-2">
                    @error('price') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi (Markdown)</label>
                    <textarea aria-label="Deskripsi" wire:model="description" rows="4" class="w-full border rounded px-3 py-2"></textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <p class="text-xs text-gray-500">Status dihitung otomatis dari quantity dan stok minimum.</p>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeForm" class="px-4 py-2 border rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Modal AI --}}
    @if ($showAi)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow w-full max-w-2xl p-6 space-y-3 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold">Generate Stok dengan AI</h2>

                <form wire:submit="generateAi" class="flex gap-2">
                    <input aria-label="Perintah AI" type="text" wire:model="aiPrompt" placeholder="Contoh: perlengkapan kantor untuk startup"
                           class="flex-1 border rounded px-3 py-2">
                    <button type="submit" class="bg-purple-600 text-white rounded px-4 py-2 hover:bg-purple-700">
                        <span wire:loading.remove wire:target="generateAi">Generate</span>
                        <span wire:loading wire:target="generateAi">Memproses...</span>
                    </button>
                </form>
                @error('aiPrompt') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                @if (count($aiItems))
                    <div class="border rounded divide-y">
                        @foreach ($aiItems as $i => $item)
                            <label class="flex gap-3 p-3 cursor-pointer" wire:key="ai-{{ $i }}">
                                <input aria-label="Pilih item" type="checkbox" wire:model="aiSelected" value="{{ $i }}" class="mt-1">
                                <div class="text-sm">
                                    <div class="font-medium">{{ $item['name'] }} <span class="text-gray-500">({{ $item['sku'] ?? '-' }})</span></div>
                                    <div class="text-gray-600">
                                        {{ $item['quantity'] }} {{ $item['unit'] }}
                                        @if ($item['price'] !== null) &middot; Rp {{ number_format($item['price'], 0, ',', '.') }} @endif
                                        &middot; min {{ $item['min_stock'] }}
                                    </div>
                                    @if ($item['description'])
                                        <div class="text-gray-500">{{ \Illuminate\Support\Str::limit($item['description'], 120) }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeAi" class="px-4 py-2 border rounded">Tutup</button>
                    @if (count($aiItems))
                        <button type="button" wire:click="saveAi" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan terpilih</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>