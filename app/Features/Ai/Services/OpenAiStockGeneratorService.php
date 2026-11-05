<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiStockGeneratorService implements AiClientInterface
{
    public function generate(string $prompt, int $count = 5): array
    {
        $apiKey = config('openai.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY belum diatur.');
        }

        $system = 'Kamu asisten inventaris. Balas HANYA JSON dengan bentuk '
            .'{"items":[{"name":string,"sku":string,"description":string,"quantity":int,'
            .'"unit":string,"price":number,"min_stock":int}]}. '
            ."Buat maksimal {$count} item stok barang yang realistis dalam bahasa Indonesia. "
            .'Description boleh memakai Markdown.';

        $response = Http::baseUrl(rtrim((string) config('openai.base_url'), '/'))
            ->withToken($apiKey)
            ->timeout((int) config('openai.timeout'))
            ->post('/chat/completions', [
                'model'           => config('openai.model'),
                'temperature'     => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal menghubungi layanan AI.');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $items   = is_array($decoded) ? ($decoded['items'] ?? []) : [];

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item) => GeneratedStockItem::fromArray($item))
            ->filter(fn (GeneratedStockItem $item) => $item->name !== '')
            ->take($count)
            ->values()
            ->all();
    }
}