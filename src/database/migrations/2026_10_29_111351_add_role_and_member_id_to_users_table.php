<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('fan')->after('password');
            $table->foreignId('member_id')->nullable()->unique()->after('role')
              ->constrained()->nullOnDelete();   // メンバー削除時はNULLに
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->dropColumn('role');
        });
    }
};
