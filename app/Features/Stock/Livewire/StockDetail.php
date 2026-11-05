<?php

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class StockDetail extends Component
{
    use WithFileUploads;

    public Stock $stock;

    public string $name = '';
    public ?string $sku = null;
    public ?string $description = null;
    public $quantity = 0;
    public string $unit = 'pcs';
    public $price = null;
    public $min_stock = 5;

    public $cover = null;

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'sku'         => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:20000'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'unit'        => ['required', 'string', 'max:30'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'min_stock'   => ['required', 'integer', 'min:0'],
        ];
    }

    public function mount(Stock $stock): void
    {
        Gate::authorize('view', $stock);

        $this->stock = $stock;
        $this->loadForm();
    }

    private function loadForm(): void
    {
        $this->name        = $this->stock->name;
        $this->sku         = $this->stock->sku;
        $this->description = $this->stock->description;
        $this->quantity    = $this->stock->quantity;
        $this->unit        = $this->stock->unit;
        $this->price       = $this->stock->price;
        $this->min_stock   = $this->stock->min_stock;
    }

    public function save(StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $data        = $this->validate();
        $this->stock = $service->update($this->stock, $data);
        $this->loadForm();

        session()->flash('status', 'Stok berhasil disimpan.');
    }

    public function adjust(int $delta, StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $this->stock    = $service->adjustQuantity($this->stock, $delta);
        $this->quantity = $this->stock->quantity;
    }

    public function uploadCover(StockService $service): void
    {
        Gate::authorize('update', $this->stock);
        $this->validate(['cover' => ['required', 'image', 'max:2048']]);

        $this->stock = $service->setCover($this->stock, $this->cover);
        $this->cover = null;

        session()->flash('status', 'Cover diperbarui.');
    }

    public function removeCover(StockService $service): void
    {
        Gate::authorize('update', $this->stock);

        $this->stock = $service->removeCover($this->stock);
    }

    public function delete(StockService $service)
    {
        Gate::authorize('delete', $this->stock);
        $service->delete($this->stock);

        session()->flash('status', 'Stok dihapus.');

        return redirect()->route('stocks.index');
    }

    public function render()
    {
        return view('livewire.stock.stock-detail');
    }
}