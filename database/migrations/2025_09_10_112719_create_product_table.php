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
        Schema::create('product', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT
            $table->unsignedInteger('category_id'); // Not Null
            $table->string('name'); // Not Null
            $table->string('slug'); // Not Null
            $table->string('thumbnail'); // Not Null
            $table->longText('content'); // Not Null
            $table->tinyText('description')->nullable(); // Null
            $table->decimal('price_buy', 15, 2); // Not Null
            $table->dateTime('created_at'); // Not Null
            $table->unsignedInteger('created_by')->default(1); // Default 1
            $table->dateTime('updated_at')->nullable(); // Null
            $table->unsignedInteger('updated_by')->nullable(); // Null
            $table->unsignedTinyInteger('status')->default(1); // Default 1

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
