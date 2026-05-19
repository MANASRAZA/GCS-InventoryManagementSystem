<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        // Seed default items
        $items = ['Sugar', 'Rice', 'Milk', 'Coffee', 'Flour', 'Tea'];
        foreach ($items as $name) {
            Item::firstOrCreate(['name' => $name]);
        }

        // Seed default brands
        $brands = ['ABC', 'Nestlé', 'Premium', 'Choice', 'Organic', 'Generic'];
        foreach ($brands as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }
    }
}
