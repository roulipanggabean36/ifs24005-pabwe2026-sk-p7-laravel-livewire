<?php

namespace Database\Seeders;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name'     => 'Administrator',
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'admin',
        ]);

        $member = User::factory()->member()->create([
            'name'     => 'Member Demo',
            'username' => 'member',
            'email'    => 'member@example.com',
            'password' => 'member',
        ]);

        $items = [
            ['Kertas A4 80gsm',   'ATK-0001', 120, 'rim',   55000, 10],
            ['Pulpen Hitam',      'ATK-0002',   4, 'pcs',    3500, 10],
            ['Spidol Whiteboard', 'ATK-0003',   0, 'pcs',   12000,  5],
            ['Map Folder',        'ATK-0004',  35, 'pcs',    2500,  5],
            ['Tinta Printer',     'ATK-0005',   5, 'botol', 85000,  5],
        ];

        foreach ($items as [$name, $sku, $qty, $unit, $price, $min]) {
            Stock::factory()->withQuantity($qty, $min)->create([
                'user_id' => $member->id,
                'name'    => $name,
                'sku'     => $sku,
                'unit'    => $unit,
                'price'   => $price,
            ]);
        }
    }
}