<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKako extends Model
{
    use HasFactory;

    protected $table = 'master_kako';

    protected $fillable = [
        'kode_bps', 'nama_bps', 'kode_pum', 'nama_pum', 'level', 'parent_code'
    ];

    protected $appends = ['encId', 'kode_kab', 'nama_kab', 'kode_prov'];

    // Backward compatibility accessors
    public function getKodeKabAttribute(){
        // Extract last 2 digits from kode_bps (e.g., "1601" -> "01")
        $code = $this->kode_bps ?? $this->kode_pum ?? '';
        if (strlen($code) >= 2) {
            return substr($code, -2);
        }
        return $code;
    }

    public function getNamaKabAttribute(){
        return $this->nama_bps ?? $this->nama_pum ?? '';
    }

    public function getKodeProvAttribute(){
        return $this->parent_code ?? '';
    }

    //set this field for automatic set on list & form input
    public static $field_info = [     
        'columns'   => [ //column define field & name of column
            [
                'name'          => 'kode_bps', 'label'         => 'Kode BPS',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
            [
                'name'          => 'nama_bps', 'label'         => 'Nama BPS',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
            [
                'name'          => 'kode_pum', 'label'         => 'Kode PUM',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
            [
                'name'          => 'nama_pum', 'label'         => 'Nama PUM',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
            [
                'name'          => 'level', 'label'         => 'Level',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
            [
                'name'          => 'parent_code', 'label'         => 'Parent Code',
                'table_display' => true, 'form_generate' => true, 'form_store' => true,
            ],
        ],
    ];

    public function getEncIdAttribute(){
        return Crypt::encryptString($this->id);
    }
}