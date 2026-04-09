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
        Schema::create('product_image', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT
            $table->unsignedInteger('product_id'); // Not Null
            $table->string('image'); // Not Null
            $table->string('alt')->nullable(); // Null
            $table->string('title')->nullable(); // Null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_image');
    }
};
