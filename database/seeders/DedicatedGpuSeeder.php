<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DedicatedGpuSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\DedicatedGpu::insert([
            ['name' => 'NVIDIA GTX 1650', 'vram_size' => 4],
            ['name' => 'NVIDIA RTX 3050', 'vram_size' => 6],
        ]);
    }
}
