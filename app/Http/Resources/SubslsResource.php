<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubslsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_subsls' => $this->id_subsls,
            'nama_sls' => $this->nama_sls,
            'nama_ketua_sls' => $this->nama_ketua_sls,
            'jenis' => $this->jenis,
            'kode_prov' => $this->kode_prov,
            'kode_kab' => $this->kode_kab,
            'kode_kec' => $this->kode_kec,
            'kode_desa' => $this->kode_desa,
            'kode_sls' => $this->kode_sls,
            'kode_sub_sls' => $this->kode_sub_sls,
            'jumlah_kk' => $this->jumlah_kk,
            'jumlah_bstt' => $this->jumlah_bstt,
            'jumlah_bsbtt' => $this->jumlah_bsbtt,
            'jumlah_bsttk' => $this->jumlah_bsttk,
            'jumlah_bku' => $this->jumlah_bku,
            'jumlah_usaha' => $this->jumlah_usaha,
            'jumlah_muatan' => $this->jumlah_muatan,
            'se26_selesai' => $this->se26_selesai,
            'se26_diperiksa' => $this->se26_diperiksa,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
