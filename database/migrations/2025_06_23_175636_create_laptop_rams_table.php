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
        Schema::create('laptop_rams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laptop_id')->constrained('laptop_details')->onDelete('cascade');
            $table->foreignId('ram_id')->constrained('rams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laptop_rams');
    }
};
