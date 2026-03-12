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
        Schema::create('subsls', function (Blueprint $table) {
            $table->id();

            $table->string('id_subsls', 16);
            $table->string('nama_sls');
            $table->string('nama_ketua_sls');
            $table->string('jenis');

            $table->char('kode_prov', 2);
            $table->char('kode_kab', 2);
            $table->char('kode_kec', 3);
            $table->char('kode_desa', 3);
            $table->char('kode_sls', 4);
            $table->char('kode_sub_sls', 2);

            $table->integer('jumlah_kk');
            $table->integer('jumlah_bstt');
            $table->integer('jumlah_bsbtt');
            $table->integer('jumlah_bsttk');
            $table->integer('jumlah_bku');
            $table->integer('jumlah_usaha');
            $table->integer('jumlah_muatan');

            $table->integer('se26_selesai')->default(0);
            $table->integer('se26_diperiksa')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsls');
    }
};
