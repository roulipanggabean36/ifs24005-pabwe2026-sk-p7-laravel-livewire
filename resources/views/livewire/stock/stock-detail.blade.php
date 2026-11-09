<div class="space-y-4">
    @if (session('status'))
        <div class="bg-green-100 text-green-800 rounded px-4 py-2 text-sm">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <a href="{{ route('stocks.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Kembali</a>
        <button wire:click="delete" wire:confirm="Hapus stok ini?" class="text-red-600 hover:underline text-sm">Hapus stok</button>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        {{-- Kolom kiri: cover + quantity --}}
        <div class="space-y-4">
            <div class="bg-white rounded shadow p-4 space-y-3">
                <h2 class="font-bold">Cover</h2>
                @if ($stock->cover_image_path)
                    <img src="{{ asset('storage/'.$stock->cover_image_path) }}" alt="Cover" class="w-full rounded">
                    <button wire:click="removeCover" class="text-sm text-red-600 hover:underline">Hapus cover</button>
                @else
                    <p class="text-sm text-gray-500">Belum ada cover.</p>
                @endif
                <input aria-label="Foto sampul" type="file" wire:model="cover" accept="image/*" class="text-sm">
                @error('cover') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <button wire:click="uploadCover" class="bg-gray-800 text-white rounded px-3 py-1 text-sm">Unggah</button>
            </div>

            <div class="bg-white rounded shadow p-4 space-y-3 text-center">
                <h2 class="font-bold text-left">Quantity</h2>
                <div class="flex items-center justify-center gap-4">
                    <button aria-label="Kurangi stok" wire:click="adjust(-1)" class="px-3 py-1 border rounded hover:bg-gray-100 text-lg">-</button>
                    <span class="text-2xl font-bold">{{ $stock->quantity }} {{ $stock->unit }}</span>
                    <button aria-label="Tambah stok" wire:click="adjust(1)" class="px-3 py-1 border rounded hover:bg-gray-100 text-lg">+</button>
                </div>
                <span class="inline-block px-2 py-1 rounded text-xs {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
            </div>
        </div>

        {{-- Kolom kanan: form --}}
        <form wire:submit="save" class="md:col-span-2 bg-white rounded shadow p-4 space-y-3">
            <h1 class="text-xl font-bold">Detail Stok</h1>

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
                <div wire:ignore>
                    <div id="stock-editor"></div>
                </div>
                @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>

    @if ($stock->description)
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-bold mb-2">Pratinjau Deskripsi</h2>
            <div class="prose max-w-none text-sm space-y-2">
                {!! \Illuminate\Support\Str::markdown($stock->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            </div>
        </div>
    @endif
</div>

@assets
<script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
@endassets

@script
<script>
    const editor = new toastui.Editor({
        el: $wire.$el.querySelector('#stock-editor'),
        initialValue: $wire.description ?? '',
        height: '280px',
        initialEditType: 'markdown',
        previewStyle: 'tab',
        events: {
            change: () => { $wire.description = editor.getMarkdown(); },
        },
    });
</script>
@endscript