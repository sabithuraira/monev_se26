<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterKako;

class MasterKakoController extends Controller
{
    public function index(Request $request){
        return view('master_kako.index', array(
            'field_info'    => (object)MasterKako::$field_info
        ));
    }

    public function create(Request $request){
        return view('master_kako.form', array(
            'field_info'    => (object)MasterKako::$field_info, 
            'id'            => ''
        ));
    }

    public function edit(Request $request, $id){
        return view('master_kako.form', array(
            'field_info'    => (object)MasterKako::$field_info,
            'id'            => $id
        ));
    }

    public function getByKodeBps(Request $request, $kodeBps){
        $data = MasterKako::where('kode_bps', $kodeBps)->first();
        
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