<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            CpuSeeder::class,
            GpuSeeder::class,
            DedicatedGpuSeeder::class,
            StorageTypeSeeder::class,
            StorageSeeder::class,
            ProductModelSeeder::class,
            ProductImageSeeder::class,
            RamTypeSeeder::class,
            RamSeeder::class,
            LaptopStorageSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
    
    
}
