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
            $table->char('kode_kab', 2)->default('')->after('remember_token');
            $table->char('kode_kec', 3)->nullable()->after('kode_kab');
            $table->char('kode_desa', 3)->nullable()->after('kode_kec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kode_kab', 'kode_kec', 'kode_desa']);
        });
    }
};
