<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CpuSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Cpu::insert([
            ['name' => 'Intel Core i5 8th Gen'],
            ['name' => 'Intel Core i7 10th Gen'],
            ['name' => 'AMD Ryzen 5 5500U'],
        ]);
    }
}
