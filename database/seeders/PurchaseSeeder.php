<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->error(
                'Seeder transaksi dummy hanya boleh dijalankan di local atau testing.'
            );

            return;
        }

        $purchases = [
            [
                'purchase_number' => 'DEMO-PUR-001',
                'supplier_email' => 'sales@sumberatk.test',
                'days_ago' => 7,
                'items' => [
                    'ATK-001' => 100,
                    'ATK-002' => 60,
                    'ATK-003' => 30,
                ],
            ],
            [
                'purchase_number' => 'DEMO-PUR-002',
                'supplier_email' => 'order@mitrakantor.test',
                'days_ago' => 5,
                'items' => [
                    'KTR-001' => 20,
                    'KTR-002' => 12,
                    'KTR-003' => 25,
                ],
            ],
            [
                'purchase_number' => 'DEMO-PUR-003',
                'supplier_email' => 'sales@solusidigital.test',
                'days_ago' => 3,
                'items' => [
                    'KMP-001' => 15,
                    'KMP-002' => 10,
                    'KMP-003' => 20,
                ],
            ],
        ];

        $created = DB::transaction(function () use ($purchases) {
            $admin = User::where('username', 'admin')
                ->where('role', 'admin')
                ->lockForUpdate()
                ->firstOrFail();

            $createdCount = 0;

            foreach ($purchases as $data) {
                $alreadyExists = Purchase::where(
                    'purchase_number',
                    $data['purchase_number']
                )->exists();

                if ($alreadyExists) {
                    continue;
                }

                $supplier = Supplier::where(
                    'email',
                    $data['supplier_email']
                )->firstOrFail();

                $products = Product::whereIn(
                    'sku',
                    array_keys($data['items'])
                )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('sku');

                if ($products->count() !== count($data['items'])) {
                    throw new RuntimeException(
                        'Produk dummy belum lengkap. Jalankan MasterDataSeeder terlebih dahulu.'
                    );
                }

                $details = [];
                $totalAmount = 0;

                foreach ($data['items'] as $sku => $quantity) {
                    $product = $products->get($sku);
                    $unitPrice = $product->purchase_price;
                    $subtotal = $quantity * $unitPrice;

                    $details[] = [
                        'product_id' => $product->id,
                        'product_sku' => $product->sku,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];

                    $totalAmount += $subtotal;
                }

                $purchase = Purchase::create([
                    'purchase_number' => $data['purchase_number'],
                    'supplier_id' => $supplier->id,
                    'user_id' => $admin->id,
                    'purchased_at' => now()
                        ->subDays($data['days_ago'])
                        ->setTime(9, 0),
                    'total_amount' => $totalAmount,
                    'status' => 'confirmed',
                    'notes' => 'Pembelian dummy untuk stok awal praktikum.',
                ]);

                $purchase->items()->createMany($details);

                foreach ($data['items'] as $sku => $quantity) {
                    $products->get($sku)->increment(
                        'stock',
                        $quantity
                    );
                }

                $createdCount++;
            }

            return $createdCount;
        }, 3);

        $this->command->info(
            $created.' transaksi pembelian dummy baru dibuat.'
        );
    }
}