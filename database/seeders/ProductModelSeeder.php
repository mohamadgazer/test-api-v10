<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_models')->insert([
            [
                'name' => 'HP Omen 16',
                'description' => 'لابتوب مخصص للألعاب القوية من HP بسعر متوسط وأداء عالي',
            ],
            [
                'name' => 'Lenovo Legion Y540',
                'description' => 'لابتوب مخصص للألعاب بأداء متميز وتصميم احترافي',
            ],
            [
                'name' => 'MacBook Air M1',
                'description' => 'لابتوب خفيف وسريع من Apple بمعالج M1 وتوفير كبير للطاقة',
            ],
        ]);
    }
}
