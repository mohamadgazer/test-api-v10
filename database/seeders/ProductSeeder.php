<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductModelSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\ProductModel::insert([
            ['name' => 'ZBook 15 G3', 'description' => 'Workstation Laptop'],
            ['name' => 'Legion 5 Pro', 'description' => 'Gaming Laptop'],
        ]);
    }
}
