<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterDesa;

class MasterDesaController extends Controller
{
    public function index(Request $request){
        return view('master_desa.index', array(
            'field_info'    => (object)MasterDesa::$field_info
        ));
    }

    public function create(Request $request){
        return view('master_desa.form', array(
            'field_info'    => (object)MasterDesa::$field_info, 
            'id'            => ''
        ));
    }

    public function edit(Request $request, $id){
        return view('master_desa.form', array(
            'field_info'    => (object)MasterDesa::$field_info,
            'id'            => $id
        ));
    }

    public function getByKodeBps(Request $request, $kodeBps){
        $data = MasterDesa::where('kode_bps', $kodeBps)->first();
        
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}


