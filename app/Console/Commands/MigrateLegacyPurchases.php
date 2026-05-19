<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\Brand;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class MigrateLegacyPurchases extends Command
{
    // The terminal command name
    protected $signature = 'migrate:legacy-purchases';

    // The description of the command
    protected $description = 'Migrate legacy purchase data into clean normalized tables safely.';

    public function handle()
    {
        $this->info('Starting legacy data migration...');

        // Your provided legacy dataset
        $legacyPurchases = [
            [
                'item_name'  => 'Sugar',
                'brand_name' => 'ABC',
                'qty'        => 10,
                'price'      => 100,
            ],

        ];

        foreach ($legacyPurchases as $data) {
            DB::transaction(function () use ($data) {
                // 1. Get or Create the Item (Idempotent)
                $item = Item::firstOrCreate(
                    ['name' => trim($data['item_name'])]
                );

                // 2. Get or Create the Brand (Idempotent)
                $brand = Brand::firstOrCreate(
                    ['name' => trim($data['brand_name'])]
                );

                // 3. Calculate Total for this specific entry
                $calculatedTotal = $data['qty'] * $data['price'];

                // 4. Create or Find the Purchase record
                // To maintain idempotency, we check if a matching purchase structure already exists
                $purchase = Purchase::whereHas('items', function ($query) use ($item, $brand, $data) {
                    $query->where('item_id', $item->id)
                        ->where('brand_id', $brand->id)
                        ->where('qty', $data['qty'])
                        ->where('price', $data['price']);
                })->first();

                if (!$purchase) {
                    // Create parent purchase row
                    $purchase = Purchase::create([
                        'total' => $calculatedTotal,
                    ]);

                    // Create child purchase item row
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id'     => $item->id,
                        'brand_id'    => $brand->id,
                        'qty'         => $data['qty'],
                        'price'       => $data['price'],
                    ]);

                    $this->info("Successfully migrated: {$data['item_name']} ({$data['brand_name']})");
                } else {
                    $this->line("Skipped (Duplicate found): {$data['item_name']} ({$data['brand_name']})");
                }
            });
        }

        $this->info('Migration process finished complete!');
    }
}
