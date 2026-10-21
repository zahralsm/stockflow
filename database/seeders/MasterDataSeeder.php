<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->error(
                'Seeder data dummy hanya boleh dijalankan di local atau testing.'
            );

            return;
        }

        DB::transaction(function () {
            $categoryNames = [
                'Alat Tulis',
                'Perlengkapan Kantor',
                'Aksesori Komputer',
            ];

            $categories = [];

            foreach ($categoryNames as $name) {
                $categories[$name] = Category::firstOrCreate([
                    'name' => $name,
                ]);
            }

            $suppliers = [
                [
                    'name' => 'PT Sumber Alat Tulis',
                    'phone' => '0215550101',
                    'email' => 'sales@sumberatk.test',
                ],
                [
                    'name' => 'CV Mitra Kantor',
                    'phone' => '0225550102',
                    'email' => 'order@mitrakantor.test',
                ],
                [
                    'name' => 'PT Solusi Digital',
                    'phone' => '0215550103',
                    'email' => 'sales@solusidigital.test',
                ],
            ];

            foreach ($suppliers as $supplier) {
                Supplier::firstOrCreate(
                    ['email' => $supplier['email']],
                    [
                        'name' => $supplier['name'],
                        'phone' => $supplier['phone'],
                    ]
                );
            }

            $products = [
                [
                    'category' => 'Alat Tulis',
                    'sku' => 'ATK-001',
                    'name' => 'Pulpen Gel Hitam 0.5 mm',
                    'purchase_price' => 3500,
                    'selling_price' => 5000,
                    'minimum_stock' => 10,
                ],
                [
                    'category' => 'Alat Tulis',
                    'sku' => 'ATK-002',
                    'name' => 'Buku Tulis A5 58 Lembar',
                    'purchase_price' => 4500,
                    'selling_price' => 7000,
                    'minimum_stock' => 10,
                ],
                [
                    'category' => 'Alat Tulis',
                    'sku' => 'ATK-003',
                    'name' => 'Spidol Whiteboard Hitam',
                    'purchase_price' => 6500,
                    'selling_price' => 10000,
                    'minimum_stock' => 5,
                ],
                [
                    'category' => 'Perlengkapan Kantor',
                    'sku' => 'KTR-001',
                    'name' => 'Kertas A4 80 gsm 1 Rim',
                    'purchase_price' => 48000,
                    'selling_price' => 58000,
                    'minimum_stock' => 5,
                ],
                [
                    'category' => 'Perlengkapan Kantor',
                    'sku' => 'KTR-002',
                    'name' => 'Stapler Ukuran Sedang',
                    'purchase_price' => 18000,
                    'selling_price' => 27000,
                    'minimum_stock' => 3,
                ],
                [
                    'category' => 'Perlengkapan Kantor',
                    'sku' => 'KTR-003',
                    'name' => 'Map Ordner A4',
                    'purchase_price' => 22000,
                    'selling_price' => 32000,
                    'minimum_stock' => 5,
                ],
                [
                    'category' => 'Aksesori Komputer',
                    'sku' => 'KMP-001',
                    'name' => 'Mouse Wireless',
                    'purchase_price' => 65000,
                    'selling_price' => 95000,
                    'minimum_stock' => 5,
                ],
                [
                    'category' => 'Aksesori Komputer',
                    'sku' => 'KMP-002',
                    'name' => 'Keyboard USB',
                    'purchase_price' => 85000,
                    'selling_price' => 125000,
                    'minimum_stock' => 3,
                ],
                [
                    'category' => 'Aksesori Komputer',
                    'sku' => 'KMP-003',
                    'name' => 'Flashdisk USB 32 GB',
                    'purchase_price' => 45000,
                    'selling_price' => 65000,
                    'minimum_stock' => 5,
                ],
            ];

            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['sku' => $product['sku']],
                    [
                        'category_id' => $categories[
                            $product['category']
                        ]->id,
                        'name' => $product['name'],
                        'purchase_price' => $product['purchase_price'],
                        'selling_price' => $product['selling_price'],
                        'minimum_stock' => $product['minimum_stock'],
                    ]
                );
            }
        });

        $this->command->info(
            'Data master dummy berhasil disiapkan.'
        );
    }
}