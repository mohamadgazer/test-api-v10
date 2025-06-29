<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_item_configurations', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('cart_item_configurations', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};
