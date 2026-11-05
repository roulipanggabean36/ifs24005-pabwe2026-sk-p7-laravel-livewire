<?php

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AdminUserStockList extends Component
{
    use WithPagination;

    public User $user;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(AdminUserService $service)
    {
        return view('livewire.admin.admin-user-stock-list', [
            'stocks'   => $service->paginateStocksOf(
                $this->user,
                $this->search !== '' ? $this->search : null,
                $this->statusFilter !== '' ? $this->statusFilter : null,
            ),
            'statuses' => StockStatus::cases(),
        ]);
    }
}