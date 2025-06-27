<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RamSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Ram::insert([
            // ['ram_type_id' => 1, 'size_gb' => 8, 'price' => 400],
            // ['ram_type_id' => 2, 'size_gb' => 2, 'price' => 200],
            // ['ram_type_id' => 1, 'size_gb' => 16, 'price' => 1000],
          

            
    [
        
        'size_gb'=> 8,
        'ram_type_id'=> 1,
        'price'=> '450.00',
        
        

    ],

    [
        
        'size_gb'=> 16,
        'ram_type_id'=> 1,
        'price'=> '1000.00',
        
        
 
    ],
    [
        
        'size_gb'=> 8,
        'ram_type_id'=> 2,
        'price'=> '700.00',
        
        

    ],
    [
        
        'size_gb'=> 8,
        'ram_type_id'=> 3,
        'price'=> '650.00',
        
        

    ],
    [
        
        'size_gb'=> 16,
        'ram_type_id'=> 2,
        'price'=> '1500.00',
        
        

    ],
    [
        
        'size_gb'=> 16,
        'ram_type_id'=> 3,
        'price'=> '2500.00',
        
        

    ],
    [
        
        'size_gb'=> 32,
        'ram_type_id'=> 1,
        'price'=> '2500.00',
        
        

    ],
    [
        
        'size_gb'=> 32,
        'ram_type_id'=> 2,
        'price'=> '3000.00',
        
        

    ],
    [
        
        'size_gb'=> 32,
        'ram_type_id'=> 3,
        'price'=> '2500.00',
        
        

    ],
    [
        
        'size_gb'=> 4,
        'ram_type_id'=> 3,
        'price'=> '250.00',
        
        

    ],
    [
        
        'size_gb'=> 4,
        'ram_type_id'=> 2,
        'price'=> '300.00',
        
        

    ],
    [
        
        'size_gb'=> 4,
        'ram_type_id'=> 1,
        'price'=> '250.00',
        
        

    ]
        ]);
    }
}
