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
        Schema::create('category', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT
            $table->string('name'); // Not Null
            $table->string('slug'); // Not Null
            $table->string('image')->nullable(); // Null
            $table->unsignedInteger('parent_id')->default(0); // Default 0
            $table->unsignedInteger('sort_order')->default(0); // Default 0
            $table->tinyText('description')->nullable(); // Null
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
        Schema::dropIfExists('category');
    }
};
