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
        Schema::create('master_desa', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_bps', 10);
            $table->string('nama_bps', 255);
            $table->string('kode_pum', 10);
            $table->string('nama_pum', 255);
            $table->string('level', 4);
            $table->string('parent_code', 7);
        });

        Schema::create('master_kec', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_bps', 10);
            $table->string('nama_bps', 255);
            $table->string('kode_pum', 10);
            $table->string('nama_pum', 255);
            $table->string('level', 4);
            $table->string('parent_code', 7);
        });

        Schema::create('master_kako', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_bps', 10);
            $table->string('nama_bps', 255);
            $table->string('kode_pum', 10);
            $table->string('nama_pum', 255);
            $table->string('level', 4);
            $table->string('parent_code', 7);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_desa');
        Schema::dropIfExists('master_kec');
        Schema::dropIfExists('master_kako');
    }
};
