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
        Schema::create('orderdetail', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT
            $table->unsignedInteger('order_id'); // Not Null
            $table->unsignedInteger('product_id'); // Not Null
            $table->decimal('price', 15, 2); // Not Null
            $table->unsignedInteger('qty'); // Not Null
            $table->decimal('amount', 15, 2); // Not Null
            $table->decimal('discount', 15, 2); // Not Null

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderdetail');
    }
};
