<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDesa extends Model
{
    use HasFactory;

    protected $table = 'master_desa';

    protected $fillable = [
        'kode_bps', 'nama_bps', 'kode_pum', 'nama_pum', 'level', 'parent_code'
    ];

    protected $appends = ['encId', 'kecamatan', 'kab', 'id_desa', 'nama_desa', 'id_kec', 'id_kab', 'id_prov'];

    public function getEncIdAttribute(){
        return Crypt::encryptString($this->id);
    }

    public function getKecamatanAttribute(){
        $data = MasterKec::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        return $data->nama_bps ?? $data->nama_pum ?? '';
    }

    public function getKabAttribute(){
        // First get the kecamatan to find its parent (kab)
        $kec = MasterKec::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        
        if ($kec) {
            $data = MasterKako::where('kode_bps', $kec->parent_code)
                    ->orWhere('kode_pum', $kec->parent_code)
                    ->first();
            return $data->nama_bps ?? $data->nama_pum ?? '';
        }
        
        return '';
    }

    // Backward compatibility accessors
    public function getIdDesaAttribute(){
        return $this->kode_bps ?? $this->kode_pum ?? '';
    }

    public function getNamaDesaAttribute(){
        return $this->nama_bps ?? $this->nama_pum ?? '';
    }

    public function getIdKecAttribute(){
        // Extract last 2 digits from parent_code (e.g., "1601052" -> "52")
        $code = $this->parent_code ?? '';
        if (strlen($code) >= 2) {
            return substr($code, -2);
        }
        return $code;
    }

    public function getIdKabAttribute(){
        // Get parent kec to find its parent (kab), then extract last 2 digits
        $kec = MasterKec::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        
        if ($kec && $kec->parent_code) {
            $code = $kec->parent_code;
            if (strlen($code) >= 2) {
                return substr($code, -2);
            }
            return $code;
        }
        
        return '';
    }

    public function getIdProvAttribute(){
        // Get parent kec, then its parent kab, then its parent prov
        $kec = MasterKec::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        
        if ($kec) {
            $kab = MasterKako::where('kode_bps', $kec->parent_code)
                    ->orWhere('kode_pum', $kec->parent_code)
                    ->first();
            return $kab->parent_code ?? '';
        }
        
        return '';
    }
}