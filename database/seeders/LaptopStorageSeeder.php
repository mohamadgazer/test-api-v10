<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LaptopStorageSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء جهاز لابتوب جديد
        $laptop = \App\Models\LaptopDetail::create([
            'product_model_id' => 2,
            'brand_id' => 3,
            'cpu_id' => 2,
            'gpu_id' => 2,
            'dedicated_gpu_id' => 2,
            'base_price' => 9500,
            'ram_type_id' => 2,
            // ممكن تحدد default_ram_id و default_storage_id لاحقًا بناء على الأسعار
        ]);

        // ربط أنواع التخزين
        $laptop->storageTypes()->sync([1, 2]);

        // ربط الرامات (اختياري)
        $laptop->rams()->sync([1, 2]);

        // اختيار أرخص وحدة تخزين كافتراضية (اختياري)
        $cheapestStorageId = \App\Models\Storage::whereIn('id', [1, 2])->orderBy('price')->value('id');
        $laptop->default_storage_id = $cheapestStorageId;

        // نفس الفكرة مع الرام
        $cheapestRamId = \App\Models\Ram::whereIn('id', [1, 2])->orderBy('price')->value('id');
        $laptop->default_ram_id = $cheapestRamId;

        $laptop->save();
    }
}
