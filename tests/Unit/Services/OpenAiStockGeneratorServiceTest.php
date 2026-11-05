<?php

namespace Tests\Unit\Services;

use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    private function fakeContent(string $content, int $status = 200): void
    {
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => $content]]]], $status)]);
    }

    public function test_requires_api_key(): void
    {
        config(['openai.api_key' => null]);

        $this->expectException(RuntimeException::class);
        (new OpenAiStockGeneratorService())->generate('x');
    }

    public function test_parses_items_and_filters_invalid(): void
    {
        config(['openai.api_key' => 'test-key']);
        $this->fakeContent(json_encode(['items' => [
            ['name' => 'Lem', 'sku' => 'L-1', 'quantity' => 10, 'unit' => 'pcs', 'price' => 5000, 'min_stock' => 2],
            'bukan-array',
            ['name' => ''],
            ['name' => 'Pulpen'],
        ]]));

        $items = (new OpenAiStockGeneratorService())->generate('alat tulis', 5);

        $this->assertCount(2, $items);
        $this->assertSame('Lem', $items[0]->name);
        $this->assertSame('Pulpen', $items[1]->name);
    }

    public function test_respects_count_limit(): void
    {
        config(['openai.api_key' => 'test-key']);
        $this->fakeContent(json_encode(['items' => [['name' => 'A'], ['name' => 'B'], ['name' => 'C']]]));

        $this->assertCount(2, (new OpenAiStockGeneratorService())->generate('x', 2));
    }

    public function test_invalid_json_returns_empty(): void
    {
        config(['openai.api_key' => 'test-key']);
        $this->fakeContent('bukan json');

        $this->assertSame([], (new OpenAiStockGeneratorService())->generate('x'));
    }

    public function test_http_failure_throws(): void
    {
        config(['openai.api_key' => 'test-key']);
        Http::fake(['*' => Http::response([], 500)]);

        $this->expectException(RuntimeException::class);
        (new OpenAiStockGeneratorService())->generate('x');
    }
}