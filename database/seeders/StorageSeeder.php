<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Storage::insert([
            ['size' => 256, 'type' => 'SSD', 'price' => 600],
            ['size' => 512, 'type' => 'SSD', 'price' => 1000],
            ['size' => 1024, 'type' => 'HDD', 'price' => 800],
        ]);
    }
}
