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

        Schema::create('laptop_detail_storage_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laptop_detail_id')->constrained('laptop_details')->onDelete('cascade');
            $table->foreignId('storage_type_id')->constrained('storage_types')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laptop_detail_storage_type');
    }
};
