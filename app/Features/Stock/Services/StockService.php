<?php

namespace App\Features\Stock\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class StockService
{
    public function __construct(private readonly StockRepositoryInterface $repository)
    {
    }

    public function paginate(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateForUser($user->id, $search, $status, $perPage);
    }

    public function create(User $user, array $data): Stock
    {
        $payload = $this->normalize($data);
        $payload['user_id'] = $user->id;

        return $this->repository->create($payload);
    }

    public function update(Stock $stock, array $data): Stock
    {
        return $this->repository->update($stock, $this->normalize($data));
    }

    public function delete(Stock $stock): bool
    {
        $this->deleteCoverFile($stock->cover_image_path);

        return $this->repository->delete($stock);
    }

    /** Tambah/kurangi quantity (minimal 0) dan sinkronkan status. */
    public function adjustQuantity(Stock $stock, int $delta): Stock
    {
        $quantity = max(0, $stock->quantity + $delta);

        return $this->repository->update($stock, [
            'quantity' => $quantity,
            'status'   => StockStatus::fromQuantity($quantity, $stock->min_stock)->value,
        ]);
    }

    public function setCover(Stock $stock, UploadedFile $file): Stock
    {
        $this->deleteCoverFile($stock->cover_image_path);
        $path = $file->store('stock-covers', 'public');

        return $this->repository->update($stock, ['cover_image_path' => $path]);
    }

    public function removeCover(Stock $stock): Stock
    {
        $this->deleteCoverFile($stock->cover_image_path);

        return $this->repository->update($stock, ['cover_image_path' => null]);
    }

    /** @param array<int, GeneratedStockItem> $items */
    public function createMany(User $user, array $items): int
    {
        foreach ($items as $item) {
            $this->create($user, $item->toArray());
        }

        return count($items);
    }

    private function normalize(array $data): array
    {
        $quantity = max(0, (int) ($data['quantity'] ?? 0));
        $minStock = max(0, (int) ($data['min_stock'] ?? 5));
        $price    = $data['price'] ?? null;

        return [
            'name'        => trim((string) $data['name']),
            'sku'         => filled($data['sku'] ?? null) ? trim((string) $data['sku']) : null,
            'description' => filled($data['description'] ?? null) ? (string) $data['description'] : null,
            'quantity'    => $quantity,
            'unit'        => filled($data['unit'] ?? null) ? trim((string) $data['unit']) : 'pcs',
            'price'       => ($price === null || $price === '') ? null : $price,
            'min_stock'   => $minStock,
            'status'      => StockStatus::fromQuantity($quantity, $minStock)->value,
        ];
    }

    private function deleteCoverFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}