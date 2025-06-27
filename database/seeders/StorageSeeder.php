<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Storage::insert([
            // ['size' => 256, 'storage_type_id' => 1, 'price' => 600],
            // ['size' => 512, 'storage_type_id' => 2, 'price' => 1000],
            // ['size' => 1024, 'storage_type_id' => 3, 'price' => 800],
            [
      
                'size'=> 256,
                'storage_type_id'=> 1,
                'price'=> 700,
                
                
            ],
            [
               
                'size'=> 512,
                'storage_type_id'=> 1,
                'price'=> 1000,
                
                
            ],
            [
               
                'size'=> 1024,
                'storage_type_id'=> 1,
                'price'=> 2000,
                
                
            ],
            [
                
                'size'=> 256,
                'storage_type_id'=> 2,
                'price'=> 900,
                
                
            ],
            [
               
                'size'=> 256,
                'storage_type_id'=> 3,
                'price'=> 1500,
                
                
            ],
            [
               
                'size'=> 512,
                'storage_type_id'=> 2,
                'price'=> 1600,
                
                
            ],
            [
                
                'size'=> 512,
                'storage_type_id'=> 3,
                'price'=> 2000,
                
                
            ],
            [
                
                'size'=> 1024,
                'storage_type_id'=> 2,
                'price'=> 2600,
                
                
            ],
            [
               
                'size'=> 1024,
                'storage_type_id'=> 3,
                'price'=> 2990,
                
                
            ]
        ]);
    }
}
