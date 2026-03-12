<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsls extends Model
{
    use HasFactory;

    protected $table = 'subsls';

    protected $fillable = [
        'id_subsls',
        'nama_sls',
        'nama_ketua_sls',
        'jenis',
        'kode_prov',
        'kode_kab',
        'kode_kec',
        'kode_desa',
        'kode_sls',
        'kode_sub_sls',
        'jumlah_kk',
        'jumlah_bstt',
        'jumlah_bsbtt',
        'jumlah_bsttk',
        'jumlah_bku',
        'jumlah_usaha',
        'jumlah_muatan',
        'se26_selesai',
        'se26_diperiksa',
    ];

    protected $casts = [
        'jumlah_kk' => 'integer',
        'jumlah_bstt' => 'integer',
        'jumlah_bsbtt' => 'integer',
        'jumlah_bsttk' => 'integer',
        'jumlah_bku' => 'integer',
        'jumlah_usaha' => 'integer',
        'jumlah_muatan' => 'integer',
        'se26_selesai' => 'integer',
        'se26_diperiksa' => 'integer',
    ];
}
