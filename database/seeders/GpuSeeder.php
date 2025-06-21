<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GpuSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Gpu::insert([
            ['name' => 'Intel Iris Xe'],
            ['name' => 'AMD Radeon Vega 8'],
        ]);
    }
}
