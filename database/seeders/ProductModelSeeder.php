<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_models')->insert([
            ['name' => 'HP ZBook 15 G3', 'description' => 'موديل لابتوب قوي متعدد الإصدارات'],
            ['name' => 'Lenovo Legion Y540', 'description' => 'موديل لابتوب مخصص للألعاب'],
        ]);
    }
}
