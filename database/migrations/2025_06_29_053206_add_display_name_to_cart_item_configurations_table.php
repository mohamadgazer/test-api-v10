<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_item_configurations', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('value');
            $table->decimal('price', 10, 2)->nullable()->after('display_name'); // إضافة هذا السطر
        });
    }

    public function down(): void
    {
        Schema::table('cart_item_configurations', function (Blueprint $table) {
            $table->dropColumn('display_name');
            $table->dropColumn('price'); // حذف السعر أيضًا في الـ down
        });
    }
};
