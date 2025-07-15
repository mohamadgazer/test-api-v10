<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
     \App\Models\Brand::insert([
    [
        'name' => 'Lenovo',
        'logo' => 'https://logo.clearbit.com/lenovo.com',
    ],
    [
        'name' => 'HP',
        'logo' => 'https://logo.clearbit.com/hp.com',
    ],
    [
        'name' => 'Dell',
        'logo' => 'https://logo.clearbit.com/dell.com',
    ],
    [
        'name' => 'Apple',
        'logo' => 'https://logo.clearbit.com/apple.com',
    ],
]);

    }
}
