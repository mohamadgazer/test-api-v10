<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'لابتوب MacBook Air - نسخة مخصصة',
                'description' => 'لابتوب محمول بتصميم خفيف يعمل بمعالج M1، إصدار خاص من متجر ألينز',
                'stock' => 10,
                'images' => [
                    'macbook-air.jpg',
                    'macbook-air-2.jpg',
                    'macbook-air-3.jpg',
                ],
                'category_id' => 1,
                'brand_id' => 4,
                'is_composite' => true,
                'composite_type' => 'App\\Models\\LaptopDetail',
                'composite_id' => 4,
            ],
            [
                'name' => 'لابتوب HP Omen - نسخة احترافية',
                'description' => 'جهاز مخصص للألعاب الثقيلة واحتياجات الجرافيك من ألينز ستور',
                'stock' => 10,
                'images' => [
                    'hp-omen.jpg',
                    'hp-omen-2.jpg',
                    'hp-omen-3.jpg',
                ],
                'category_id' => 1,
                'brand_id' => 2,
                'is_composite' => true,
                'composite_type' => 'App\\Models\\LaptopDetail',
                'composite_id' => 2,
            ],
            [
                'name' => 'Lenovo Y540 - لابتوب تجميعي',
                'description' => 'نسخة تجميع خاصة من لينوفو، مثالية للأداء المتوسط العالي',
                'stock' => 10,
                'images' => [
                    'lenovo-y540.jpg',
                    'lenovo-y540-2.jpg',
                    'lenovo-y540-3.jpg',
                ],
                'category_id' => 1,
                'brand_id' => 1,
                'is_composite' => true,
                'composite_type' => 'App\\Models\\LaptopDetail',
                'composite_id' => 1,
            ],
       
            [
                'name' => 'logitechr g502 - ماوس مخصص للألعاب',
                'description' => 'ماوس مخصص للألعاب بتصميم مريح وأداء عالي.',
                'stock' => 15,
                'category_id' => 3,
                'brand_id' => 2,
                'price' => 500,
                'is_composite' => false,
                'images' => [
                    'logitechr-g502-1.jpg',
                    'logitechr-g502-2.jpg',
                    'logitechr-g502-3.jpg', 
                ],
            ],
            [
                'name' => 'لوحة المفاتيح الميكانيكية',
                'description' => 'لوحة مفاتيح ميكانيكية بتصميم مريح وأداء عالي.',
                'stock' => 25,
                'category_id' => 3,
                'brand_id' => 1,
                'price' => 600,
                'is_composite' => false,
                'images' => [
                    'KD-815S-1.jpg',
                    'KD-815S-2.jpg',
                    'KD-815S-3.jpg',
                    
                ],
            ],

        ];

     foreach ($products as $data) {
    $product = Product::create([
        'name' => $data['name'],
        'description' => $data['description'],
        'stock' => $data['stock'],
        'category_id' => $data['category_id'],
        'brand_id' => $data['brand_id'],
        'is_composite' => $data['is_composite'],
        'price' => $data['price'] ?? null,
        'composite_type' => $data['is_composite'] ? $data['composite_type'] : null,
        'composite_id' => $data['is_composite'] ? $data['composite_id'] : null,
    ]);

    foreach ($data['images'] as $index => $imageName) {
        $product->images()->create([
            'path' => "storage/seeders/products/$imageName",
            'is_main' => $index === 0,
        ]);
    }
}

    }
}
