<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'لابتوبات',
                'image' => 'https://picsum.photos/seed/laptops/600/400',
            ],
            [
                'name' => 'شاشات',
                'image' => 'https://picsum.photos/seed/monitors/600/400',
            ],
            [
                'name' => 'اكسسوارات',
                'image' => 'https://picsum.photos/seed/accessories/600/400',
            ],
        ]);
    }
}
