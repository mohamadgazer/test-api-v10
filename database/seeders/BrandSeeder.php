<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Brand::insert([
            ['name' => 'Lenovo'],
            ['name' => 'HP'],
            ['name' => 'Dell'],
            ['name' => 'Apple'],
        ]);
    }
}
