<?php

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('components.layouts.app')]
class StockList extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    // Form create/edit (modal)
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public ?string $sku = null;
    public ?string $description = null;
    public $quantity = 0;
    public string $unit = 'pcs';
    public $price = null;
    public $min_stock = 5;

    // Import Excel
    public $importFile = null;

    // AI
    public bool $showAi = false;
    public string $aiPrompt = '';
    public array $aiItems = [];
    public array $aiSelected = [];

    protected function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'sku'         => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'unit'        => ['required', 'string', 'max:30'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'min_stock'   => ['required', 'integer', 'min:0'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    private function ownedStock(int $id): Stock
    {
        return Stock::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Stock::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $stock = $this->ownedStock($id);
        Gate::authorize('update', $stock);

        $this->editingId   = $stock->id;
        $this->name        = $stock->name;
        $this->sku         = $stock->sku;
        $this->description = $stock->description;
        $this->quantity    = $stock->quantity;
        $this->unit        = $stock->unit;
        $this->price       = $stock->price;
        $this->min_stock   = $stock->min_stock;
        $this->showForm    = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'sku', 'description', 'quantity', 'unit', 'price', 'min_stock']);
        $this->resetValidation();
    }

    public function save(StockService $service): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $stock = $this->ownedStock($this->editingId);
            Gate::authorize('update', $stock);
            $service->update($stock, $data);
            session()->flash('status', 'Stok berhasil diperbarui.');
        } else {
            Gate::authorize('create', Stock::class);
            $service->create(auth()->user(), $data);
            session()->flash('status', 'Stok berhasil ditambahkan.');
        }

        $this->closeForm();
    }

    public function delete(int $id, StockService $service): void
    {
        $stock = $this->ownedStock($id);
        Gate::authorize('delete', $stock);
        $service->delete($stock);
        session()->flash('status', 'Stok dihapus.');
    }

    public function adjust(int $id, int $delta, StockService $service): void
    {
        $stock = $this->ownedStock($id);
        Gate::authorize('update', $stock);
        $service->adjustQuantity($stock, $delta);
    }

    // ---------- Excel ----------

    public function export(StockRepositoryInterface $repository, StockExcelService $excel)
    {
        $binary = $excel->export($repository->allForUser(auth()->id()));

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, 'stok-'.now()->format('Ymd-His').'.xlsx');
    }

    public function downloadTemplate(StockExcelService $excel)
    {
        $binary = $excel->template();

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, 'template-stok.xlsx');
    }

    public function import(StockExcelService $excel): void
    {
        Gate::authorize('create', Stock::class);
        $this->validate(['importFile' => ['required', 'file', 'extensions:xlsx,xls', 'max:2048']]);

        $count = $excel->import(auth()->user(), $this->importFile->getRealPath());
        $this->importFile = null;

        session()->flash('status', "{$count} stok berhasil diimpor.");
    }

    // ---------- AI ----------

    public function openAi(): void
    {
        $this->reset(['aiPrompt', 'aiItems', 'aiSelected']);
        $this->resetValidation();
        $this->showAi = true;
    }

    public function closeAi(): void
    {
        $this->showAi = false;
        $this->reset(['aiPrompt', 'aiItems', 'aiSelected']);
    }

    public function generateAi(AiClientInterface $ai): void
    {
        $this->validate(['aiPrompt' => ['required', 'string', 'max:500']]);

        try {
            $items = $ai->generate($this->aiPrompt, 5);
        } catch (RuntimeException $e) {
            $this->addError('aiPrompt', $e->getMessage());

            return;
        }

        $this->aiItems    = array_map(fn (GeneratedStockItem $item) => $item->toArray(), $items);
        $this->aiSelected = array_keys($this->aiItems);
    }

    public function saveAi(StockService $service): void
    {
        Gate::authorize('create', Stock::class);

        $chosen = [];
        foreach ($this->aiSelected as $index) {
            if (isset($this->aiItems[$index])) {
                $chosen[] = GeneratedStockItem::fromArray($this->aiItems[$index]);
            }
        }

        $count = $service->createMany(auth()->user(), $chosen);
        $this->closeAi();

        session()->flash('status', "{$count} stok dari AI disimpan.");
    }

    public function render(StockService $service)
    {
        return view('livewire.stock.stock-list', [
            'stocks'   => $service->paginate(
                auth()->user(),
                $this->search !== '' ? $this->search : null,
                $this->statusFilter !== '' ? $this->statusFilter : null,
            ),
            'statuses' => StockStatus::cases(),
        ]);
    }
}