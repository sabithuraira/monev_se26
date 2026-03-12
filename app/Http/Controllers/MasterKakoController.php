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

    /**
     * Get master kabupaten/kota by BPS code.
     *
     * @OA\Get(
     *     path="/api/master-kako/{kodeBps}",
     *     tags={"Master Data"},
     *     summary="Get Master Kabupaten/Kota by Kode BPS",
     *     description="Returns a single master kabupaten/kota record by kode_bps",
     *     @OA\Parameter(name="kodeBps", in="path", required=true, description="Kode BPS", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", type="object")
     *     )),
     *     @OA\Response(response=404, description="Data not found", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="error"),
     *         @OA\Property(property="message", type="string", example="Data not found")
     *     ))
     * )
     */
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