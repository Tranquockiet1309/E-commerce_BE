<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột address vào bảng users
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('address')->nullable()->after('email'); 
            // 👈 thêm sau cột email, có thể để null
        });
    }

    /**
     * Rollback (xóa cột address nếu cần)
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
