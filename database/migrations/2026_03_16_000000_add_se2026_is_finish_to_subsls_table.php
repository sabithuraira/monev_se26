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
        Schema::table('subsls', function (Blueprint $table) {
            $table->tinyInteger('se2026_is_finish')->default(0)->after('se26_diperiksa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subsls', function (Blueprint $table) {
            $table->dropColumn('se2026_is_finish');
        });
    }
};
