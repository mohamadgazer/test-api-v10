<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Storage::insert([
            ['size' => 256, 'storage_type_id' => 1, 'price' => 600],
            ['size' => 512, 'storage_type_id' => 2, 'price' => 1000],
            ['size' => 1024, 'storage_type_id' => 3, 'price' => 800],
        ]);
    }
}
