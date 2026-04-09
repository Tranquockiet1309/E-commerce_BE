<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->json('colors')->nullable()->after('thumbnail'); // Lưu mảng màu sắc, VD: [{"name":"Đen","code":"#000000"}]
            $table->json('storage')->nullable()->after('colors');   // Lưu mảng bộ nhớ, VD: [{"size":"128GB","extraPrice":0}]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn(['colors', 'storage']);
        });
    }
};
