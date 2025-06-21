<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            // الصورة الرئيسية
            ProductImage::create([
                'product_id' => $product->id,
                'path' => "https://picsum.photos/seed/main{$product->id}/600/600",
                'is_main' => true,
            ]);

            // صور إضافية (2 صور عشوائية)
            for ($i = 1; $i <= 2; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => "https://picsum.photos/seed/extra{$product->id}{$i}/600/600",
                    'is_main' => false,
                ]);
            }
        }
    }
}
