<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StorageTypeSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\StorageType::insert([
            ['name' => 'HDD'],
            ['name' => 'SSD'],
            ['name' => 'M.2'],
        
        ]);
    }
}
