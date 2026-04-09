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
        Schema::create('post', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT, Key
            $table->unsignedInteger('topic_id')->nullable(); // Null
            $table->string('title'); // Not Null
            $table->string('slug'); // Not Null
            $table->string('image'); // Not Null
            $table->longText('content'); // Not Null
            $table->tinyText('description')->nullable(); // Null
            $table->dateTime('created_at'); // Not Null
            $table->enum('post_type', ['post', 'page'])->default('post'); // Default 'post'
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
        Schema::dropIfExists('post');
    }
};
