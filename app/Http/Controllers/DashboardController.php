<?php

namespace App\Http\Controllers;

use App\Models\MasterKako;
use App\Models\MasterKec;
use App\Models\Subsls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Province code prefix for building kode_bps (e.g. 16 for Sumatera Selatan). */
    private const PROVINCE_CODE = '16';


    /**
     * List records or get single by kode_bps.
     *
     * @OA\Get(
     *     path="/api/histogram_kecamatan",
     *     tags={"Dashboard"},
     *     summary="List or get Histogram Kecamatan For Kabupaten/Kota",
     *     description="List paginated records, or get single by kode_bps. Requires Bearer token. Access scoped by user kode_kab.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="kode_bps", in="query", required=false, description="Get single record by kode_bps (e.g. 1601)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-1000) when listing", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", description="Single object or array of items"),
     *         @OA\Property(property="meta", type="object", description="Pagination meta (when list)")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Data not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function histogram_kecamatan(Request $request): JsonResponse
    {
        $user = $request->user();
        $kodeKab = $user?->kode_kab ?? '00';

        if ($request->filled('kode_bps')) {
            $kodeBps = $request->get('kode_bps');
            if ($kodeKab !== '00') {
                $allowedPrefix = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
                if (substr($kodeBps, 0, 4) !== $allowedPrefix) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Forbidden. You can only access data for your kabupaten.',
                    ], 403);
                }
            }

            $data = Subsls::select('kode_kec','master_kec.nama_bps', DB::raw('SUM(se26_selesai) as total_selesai, SUM(se26_diperiksa) as total_diperiksa,SUM(se2026_is_finish) as total_sls_selesai'))
                ->join(
                    'master_kec',
                    'master_kec.kode_bps',
                    '=',
                    DB::raw("CONCAT('16', subsls.kode_kab, subsls.kode_kec)")
                )
                ->where('kode_kab', substr($kodeBps, 2, 2))
                ->groupBy('subsls.kode_kec', 'master_kec.nama_bps')
                ->get();

            if (! $data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        $query = $this->scopeByUser($request->user(), MasterKec::query())
            ->when($request->filled('kode_kab'), function ($q) use ($request) {
                $q->whereRaw('SUBSTRING(kode_bps, 3, 2) = ?', [$request->get('kode_kab')]);
            });
        $perPage = min(max((int) $request->get('per_page', 15), 1), 1000);
        $items = $query->orderBy('kode_bps')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }


    /**
     * List records or get single by kode_bps.
     *
     * @OA\Get(
     *     path="/api/histogram_desa",
     *     tags={"Dashboard"},
     *     summary="List or get Histogram Desa For Kabupaten/Kota",
     *     description="List paginated records, or get single by kode_bps. Requires Bearer token. Access scoped by user kode_kab.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="kode_bps", in="query", required=false, description="Get single record by kode_bps (e.g. 1601010)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-1000) when listing", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", description="Single object or array of items"),
     *         @OA\Property(property="meta", type="object", description="Pagination meta (when list)")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Data not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function histogram_desa(Request $request): JsonResponse
    {
        $user = $request->user();
        $kodeKab = $user?->kode_kab ?? '00';

        if ($request->filled('kode_bps')) {
            $kodeBps = $request->get('kode_bps');
            if ($kodeKab !== '00') {
                $allowedPrefix = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
                if (substr($kodeBps, 0, 4) !== $allowedPrefix) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Forbidden. You can only access data for your kabupaten.',
                    ], 403);
                }
            }
            $data = Subsls::select('kode_kec','kode_desa',
             'master_desa.nama_bps',
             DB::raw('SUM(se26_selesai) as total_selesai, SUM(se26_diperiksa) as total_diperiksa,SUM(se2026_is_finish) as total_sls_selesai'))
                ->join(
                'master_desa',
                'master_desa.kode_bps',
                    '=',
                    DB::raw("CONCAT('16', subsls.kode_kab, subsls.kode_kec, subsls.kode_desa)")
                )
                ->where('kode_kab', substr($kodeBps, 2, 2))
                ->where('kode_kec', substr($kodeBps, 4, 3))
                ->groupBy('subsls.kode_kec', 'subsls.kode_desa',
                 'master_desa.nama_bps'
                 )
                ->get();

            if (! $data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        $query = $this->scopeByUser($request->user(), MasterKec::query())
            ->when($request->filled('kode_kab'), function ($q) use ($request) {
                $q->whereRaw('SUBSTRING(kode_bps, 3, 2) = ?', [$request->get('kode_kab')]);
            });
        $perPage = min(max((int) $request->get('per_page', 15), 1), 1000);
        $items = $query->orderBy('kode_bps')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }


    private function scopeByUser($user, $query)
    {
        $kodeKab = $user?->kode_kab ?? '00';
        if ($kodeKab === '00') {
            return $query;
        }
        $allowedKodeBps = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
        return $query->where('kode_bps', $allowedKodeBps);
    }

    private function userCanAccess($user, string $kodeBps): bool
    {
        $kodeKab = $user?->kode_kab ?? '00';
        if ($kodeKab === '00') {
            return true;
        }
        $allowed = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
        return $kodeBps === $allowed;
    }
}
