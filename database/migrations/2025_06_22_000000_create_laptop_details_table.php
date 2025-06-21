<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laptop_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('cpu_id')->constrained();
            $table->foreignId('gpu_id')->nullable()->constrained();
            $table->foreignId('dedicated_gpu_id')->nullable()->constrained();
            $table->decimal('base_price', 10, 2);
            $table->foreignId('default_ram_id')->nullable()->constrained('rams');
            $table->foreignId('default_storage_id')->nullable()->constrained('storages');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laptop_details');
    }
};
