<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DedicatedGpuSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\DedicatedGpu::insert([
         ['name' => 'NVIDIA RTX 3060', 'vram_size' => 6],
    ['name' => 'NVIDIA RTX 3070', 'vram_size' => 8],
    ['name' => 'AMD Radeon RX 6600M', 'vram_size' => 8],
        ]);
    }
}
