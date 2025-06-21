<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RamTypeSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\RamType::insert([
            ['name' => 'DDR3'],
            ['name' => 'DDR4'],
            ['name' => 'DDR5'],
        ]);
        
        
    }
}
