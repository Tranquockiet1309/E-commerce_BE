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
        Schema::create('user', function (Blueprint $table) {
            $table->id(); // unsignedBigInteger, AUTO_INCREMENT, Key
            $table->string('name'); // Not Null
            $table->string('email')->unique(); // Not Null
            $table->string('phone'); // Not Null
            $table->string('username'); // Not Null
            $table->string('password'); // Not Null
            $table->enum('roles', ['admin', 'customer'])->default('customer'); // Default 'customer'
            $table->string('avatar')->nullable(); // Null
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
        Schema::dropIfExists('user');
    }
};
