<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryMap = ProductCategory::pluck('id', 'slug')->toArray();

        if (empty($categoryMap)) {
            return;
        }

        $productsByCategory = [
            'elektronik' => [
                ['name' => 'Smartphone X100', 'price' => 2499000, 'stock' => 30],
                ['name' => 'Laptop Pro 14', 'price' => 8499000, 'stock' => 15],
                ['name' => 'Tablet M8', 'price' => 3299000, 'stock' => 22],
                ['name' => 'Smartwatch Fit One', 'price' => 1299000, 'stock' => 40],
                ['name' => 'Earbuds Sonic Air', 'price' => 599000, 'stock' => 55],
                ['name' => 'Speaker Mini Boom', 'price' => 399000, 'stock' => 28],
                ['name' => 'Keyboard Mechanical K75', 'price' => 799000, 'stock' => 18],
                ['name' => 'Mouse Wireless M3', 'price' => 259000, 'stock' => 35],
                ['name' => 'Webcam HD 1080P', 'price' => 449000, 'stock' => 20],
                ['name' => 'Powerbank FastCharge 20000mAh', 'price' => 349000, 'stock' => 50],
            ],
            'fashion' => [
                ['name' => 'Kaos Polos Premium', 'price' => 99000, 'stock' => 100],
                ['name' => 'Kemeja Oxford Slim Fit', 'price' => 189000, 'stock' => 45],
                ['name' => 'Celana Jeans Regular', 'price' => 249000, 'stock' => 60],
                ['name' => 'Jaket Hoodie Fleece', 'price' => 279000, 'stock' => 40],
                ['name' => 'Rok Midi Kasual', 'price' => 169000, 'stock' => 35],
                ['name' => 'Dress Floral Santai', 'price' => 229000, 'stock' => 30],
                ['name' => 'Blazer Wanita Basic', 'price' => 319000, 'stock' => 20],
                ['name' => 'Sweater Knit Unisex', 'price' => 199000, 'stock' => 50],
                ['name' => 'Topi Baseball Daily', 'price' => 89000, 'stock' => 70],
                ['name' => 'Hijab Voal Premium', 'price' => 75000, 'stock' => 80],
            ],
            'olahraga' => [
                ['name' => 'Sepatu Lari Sprint 2', 'price' => 499000, 'stock' => 40],
                ['name' => 'Matras Yoga Comfort', 'price' => 159000, 'stock' => 33],
                ['name' => 'Dumbbell Set 10Kg', 'price' => 329000, 'stock' => 25],
                ['name' => 'Skipping Rope Pro', 'price' => 69000, 'stock' => 75],
                ['name' => 'Jersey Futsal Team', 'price' => 149000, 'stock' => 55],
                ['name' => 'Celana Training QuickDry', 'price' => 139000, 'stock' => 50],
                ['name' => 'Botol Minum Sport 1L', 'price' => 79000, 'stock' => 90],
                ['name' => 'Tas Gym Compact', 'price' => 189000, 'stock' => 30],
                ['name' => 'Resistance Band Set', 'price' => 119000, 'stock' => 48],
                ['name' => 'Raket Badminton Lite', 'price' => 289000, 'stock' => 27],
            ],
            'kesehatan' => [
                ['name' => 'Vitamin C 1000mg', 'price' => 85000, 'stock' => 120],
                ['name' => 'Multivitamin Harian', 'price' => 99000, 'stock' => 110],
                ['name' => 'Masker Medis 50pcs', 'price' => 45000, 'stock' => 150],
                ['name' => 'Hand Sanitizer 500ml', 'price' => 39000, 'stock' => 130],
                ['name' => 'Termometer Digital', 'price' => 129000, 'stock' => 35],
                ['name' => 'Tensimeter Digital', 'price' => 349000, 'stock' => 20],
                ['name' => 'Minyak Angin Herbal', 'price' => 27000, 'stock' => 140],
                ['name' => 'Alat Pijat Leher', 'price' => 219000, 'stock' => 25],
                ['name' => 'Sabun Antibakteri', 'price' => 32000, 'stock' => 100],
                ['name' => 'Suplemen Omega 3', 'price' => 119000, 'stock' => 60],
            ],
            'rumah-tangga' => [
                ['name' => 'Set Panci Stainless', 'price' => 399000, 'stock' => 22],
                ['name' => 'Wajan Anti Lengket 28cm', 'price' => 179000, 'stock' => 40],
                ['name' => 'Sapu Lantai Premium', 'price' => 69000, 'stock' => 85],
                ['name' => 'Pel Putar Otomatis', 'price' => 159000, 'stock' => 38],
                ['name' => 'Kotak Penyimpanan 30L', 'price' => 99000, 'stock' => 65],
                ['name' => 'Rak Serbaguna 4 Susun', 'price' => 249000, 'stock' => 28],
                ['name' => 'Lampu LED Hemat Energi', 'price' => 45000, 'stock' => 95],
                ['name' => 'Dispenser Sabun Otomatis', 'price' => 139000, 'stock' => 32],
                ['name' => 'Karpet Ruang Tamu 120x160', 'price' => 219000, 'stock' => 26],
                ['name' => 'Tempat Sampah Injak 20L', 'price' => 109000, 'stock' => 44],
            ],
        ];

        foreach ($productsByCategory as $categorySlug => $items) {
            if (!isset($categoryMap[$categorySlug])) {
                continue;
            }

            foreach ($items as $item) {
                $slug = Str::slug($item['name']);

                Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $item['name'],
                        'description' => 'Produk ' . $item['name'] . ' kategori ' . str_replace('-', ' ', $categorySlug) . '.',
                        'price' => $item['price'],
                        'stock' => $item['stock'],
                        'image' => 'https://via.placeholder.com/600x600?text=' . rawurlencode($item['name']),
                        'product_category_id' => $categoryMap[$categorySlug],
                    ]
                );
            }
        }
    }
}
