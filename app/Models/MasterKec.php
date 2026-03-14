<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKec extends Model
{
    use HasFactory;

    protected $table = 'master_kec';

    protected $fillable = [
        'kode_bps', 'nama_bps', 'kode_pum', 'nama_pum', 'level', 'parent_code'
    ];


    protected $appends = ['encId', 'kab', 'kode_kec', 'nama_kec', 'kode_kab', 'kode_prov'];

    public function getEncIdAttribute(){
        return Crypt::encryptString($this->id);
    }

    public function getKabAttribute(){
        $data = MasterKako::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        return $data->nama_bps ?? $data->nama_pum ?? '';
    }

    // Backward compatibility accessors
    public function getKodeKecAttribute(){
        // Extract last 2 digits from kode_bps (e.g., "1601052" -> "52")
        $code = $this->kode_bps ?? $this->kode_pum ?? '';
        if (strlen($code) >= 2) {
            return substr($code, -2);
        }
        return $code;
    }

    public function getNamaKecAttribute(){
        return $this->nama_bps ?? $this->nama_pum ?? '';
    }

    public function getKodeKabAttribute(){
        // Extract last 2 digits from parent_code (e.g., "1601" -> "01")
        $code = $this->parent_code ?? '';
        if (strlen($code) >= 2) {
            return substr($code, -2);
        }
        return $code;
    }

    public function getKodeProvAttribute(){
        // Get parent kab to find its parent (prov)
        $kab = MasterKako::where('kode_bps', $this->parent_code)
                ->orWhere('kode_pum', $this->parent_code)
                ->first();
        return $kab->parent_code ?? '';
    }
}