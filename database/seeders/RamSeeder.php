<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RamSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Ram::insert([
            ['ram_type_id' => 1, 'size_gb' => 8, 'price' => 400],
            ['ram_type_id' => 2, 'size_gb' => 2, 'price' => 200],
            ['ram_type_id' => 1, 'size_gb' => 16, 'price' => 1000],
          
        ]);
    }
}
