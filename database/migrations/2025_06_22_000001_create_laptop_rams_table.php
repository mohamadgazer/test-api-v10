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
        Schema::table('rams', function (Blueprint $table) {
            $table->unsignedBigInteger('ram_type_id')->after('size_gb');
            $table->foreign('ram_type_id')->references('id')->on('ram_types')->onDelete('cascade');
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
