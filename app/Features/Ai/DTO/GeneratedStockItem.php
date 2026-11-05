<?php

namespace App\Features\Ai\DTO;

final class GeneratedStockItem
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $sku = null,
        public readonly ?string $description = null,
        public readonly int $quantity = 0,
        public readonly string $unit = 'pcs',
        public readonly ?float $price = null,
        public readonly int $minStock = 5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim((string) ($data['name'] ?? '')),
            sku: filled($data['sku'] ?? null) ? (string) $data['sku'] : null,
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            quantity: max(0, (int) ($data['quantity'] ?? 0)),
            unit: filled($data['unit'] ?? null) ? (string) $data['unit'] : 'pcs',
            price: is_numeric($data['price'] ?? null) ? (float) $data['price'] : null,
            minStock: max(0, (int) ($data['min_stock'] ?? 5)),
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'sku'         => $this->sku,
            'description' => $this->description,
            'quantity'    => $this->quantity,
            'unit'        => $this->unit,
            'price'       => $this->price,
            'min_stock'   => $this->minStock,
        ];
    }
}