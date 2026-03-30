<?php

namespace App\Http\Controllers;

use App\Models\MasterKec;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterKecController extends Controller
{
    /** Province code prefix for first 4 digits of kode_bps (e.g. 16 for Sumatera Selatan). */
    private const PROVINCE_CODE = '16';

    /**
     * List records or get single by kode_bps.
     *
     * @OA\Get(
     *     path="/api/master-kec",
     *     tags={"Master Kec"},
     *     summary="List or get Master Kecamatan",
     *     description="List paginated records (optional filter by kode_kab = chars 3-4 of kode_bps), or get single by kode_bps. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="kode_bps", in="query", required=false, description="Get single record by kode_bps (e.g. 1601052)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kode_kab", in="query", required=false, description="Filter list by kab (kode_bps positions 3-4)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-1000)", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", description="Single object or array"),
     *         @OA\Property(property="meta", type="object", description="Pagination meta (when list)")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Data not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
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
            $data = MasterKec::where('kode_bps', $kodeBps)->first();
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
     * Get single record by id.
     *
     * @OA\Get(
     *     path="/api/master-kec/{id}",
     *     tags={"Master Kec"},
     *     summary="Get Master Kecamatan by ID",
     *     description="Returns a single master kecamatan record. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Record ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Data not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $item = MasterKec::find($id);
        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
        }
        if (! $this->userCanAccess($request->user(), $item->kode_bps)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You can only access data for your kabupaten.',
            ], 403);
        }
        return response()->json(['status' => 'success', 'data' => $item]);
    }

    /**
     * Create a new record.
     *
     * @OA\Post(
     *     path="/api/master-kec",
     *     tags={"Master Kec"},
     *     summary="Create Master Kecamatan",
     *     description="Create a new master kecamatan record. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"kode_bps","nama_bps","kode_pum","nama_pum","level","parent_code"},
     *         @OA\Property(property="kode_bps", type="string", maxLength=10),
     *         @OA\Property(property="nama_bps", type="string", maxLength=255),
     *         @OA\Property(property="kode_pum", type="string", maxLength=10),
     *         @OA\Property(property="nama_pum", type="string", maxLength=255),
     *         @OA\Property(property="level", type="string", maxLength=4),
     *         @OA\Property(property="parent_code", type="string", maxLength=7)
     *     )),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="data", type="object")
     *     )),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_bps' => 'required|string|max:10',
            'nama_bps' => 'required|string|max:255',
            'kode_pum' => 'required|string|max:10',
            'nama_pum' => 'required|string|max:255',
            'level' => 'required|string|max:4',
            'parent_code' => 'required|string|max:7',
        ]);
        $item = MasterKec::create($validated);
        return response()->json(['status' => 'success', 'data' => $item], 201);
    }

    /**
     * Update a record by id.
     *
     * @OA\Put(
     *     path="/api/master-kec/{id}",
     *     tags={"Master Kec"},
     *     summary="Update Master Kecamatan",
     *     description="Update a master kecamatan record by ID. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=false, @OA\JsonContent(
     *         @OA\Property(property="kode_bps", type="string"), @OA\Property(property="nama_bps", type="string"),
     *         @OA\Property(property="kode_pum", type="string"), @OA\Property(property="nama_pum", type="string"),
     *         @OA\Property(property="level", type="string"), @OA\Property(property="parent_code", type="string")
     *     )),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not found")
     * )
     * @OA\Patch(
     *     path="/api/master-kec/{id}",
     *     tags={"Master Kec"},
     *     summary="Update Master Kecamatan (PATCH)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = MasterKec::find($id);
        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
        }
        if (! $this->userCanAccess($request->user(), $item->kode_bps)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You can only access data for your kabupaten.',
            ], 403);
        }
        $validated = $request->validate([
            'kode_bps' => 'sometimes|string|max:10',
            'nama_bps' => 'sometimes|string|max:255',
            'kode_pum' => 'sometimes|string|max:10',
            'nama_pum' => 'sometimes|string|max:255',
            'level' => 'sometimes|string|max:4',
            'parent_code' => 'sometimes|string|max:7',
        ]);
        $item->update($validated);
        return response()->json(['status' => 'success', 'data' => $item->fresh()]);
    }

    /**
     * Delete a record by id.
     *
     * @OA\Delete(
     *     path="/api/master-kec/{id}",
     *     tags={"Master Kec"},
     *     summary="Delete Master Kecamatan",
     *     description="Delete a master kecamatan record by ID. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(
     *         @OA\Property(property="status", type="string", example="success"),
     *         @OA\Property(property="message", type="string", example="Deleted.")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Data not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = MasterKec::find($id);
        if (! $item) {
            return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
        }
        if (! $this->userCanAccess($request->user(), $item->kode_bps)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You can only access data for your kabupaten.',
            ], 403);
        }
        $item->delete();
        return response()->json(['status' => 'success', 'message' => 'Deleted.']);
    }

    private function scopeByUser($user, $query)
    {
        $kodeKab = $user?->kode_kab ?? '00';
        if ($kodeKab === '00') {
            return $query;
        }
        $allowedPrefix = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
        return $query->where('kode_bps', 'like', $allowedPrefix . '%');
    }

    private function userCanAccess($user, string $kodeBps): bool
    {
        $kodeKab = $user?->kode_kab ?? '00';
        if ($kodeKab === '00') {
            return true;
        }
        $allowedPrefix = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
        return substr($kodeBps, 0, 4) === $allowedPrefix;
    }
}
