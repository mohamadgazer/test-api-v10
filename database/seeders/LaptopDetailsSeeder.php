<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaptopDetail;
use App\Models\Storage;
use App\Models\Ram;

class LaptopDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $laptops = [
            [
                'product_model_id' => 1,
                'brand_id' => 1,
                'cpu_id' => 1,
                'gpu_id' => 1,
                'dedicated_gpu_id' => 1,
                'base_price' => 25000,
                'ram_type_id' => 1,
                'storage_types' => [1, 2],
                'rams' => [1, 2],
            ],
            [
                'product_model_id' => 2,
                'brand_id' => 2,
                'cpu_id' => 2,
                'gpu_id' => 2,
                'dedicated_gpu_id' => 2,
                'base_price' => 31000,
                'ram_type_id' => 2,
                'storage_types' => [2],
                'rams' => [3],
            ],
            [
                'product_model_id' => 3,
                'brand_id' => 3,
                'cpu_id' => 3,
                'gpu_id' => 2,
                'dedicated_gpu_id' => 2,
                'base_price' => 40000,
                'ram_type_id' => 1,
                'storage_types' => [1, 3],
                'rams' => [2, 3],
            ],
        ];

        foreach ($laptops as $data) {
            $laptop = LaptopDetail::create([
                'product_model_id' => $data['product_model_id'],
                'brand_id' => $data['brand_id'],
                'cpu_id' => $data['cpu_id'],
                'gpu_id' => $data['gpu_id'],
                'dedicated_gpu_id' => $data['dedicated_gpu_id'],
                'base_price' => $data['base_price'],
                'ram_type_id' => $data['ram_type_id'],
            ]);

            // ربط أنواع التخزين
            $laptop->storageTypes()->sync($data['storage_types']);

            // ربط الرامات
            $laptop->rams()->sync($data['rams']);

            // اختيار الأرخص تلقائياً
            $cheapestStorageId = Storage::whereIn('id', $data['storage_types'])->orderBy('price')->value('id');
            $cheapestRamId = Ram::whereIn('id', $data['rams'])->orderBy('price')->value('id');

            $laptop->default_storage_id = $cheapestStorageId;
            $laptop->default_ram_id = $cheapestRamId;

            $laptop->save();
        }
    }
}
