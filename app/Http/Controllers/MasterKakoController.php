<?php

namespace App\Http\Controllers;

use App\Models\MasterKako;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterKakoController extends Controller
{
    /** Province code prefix for building kode_bps (e.g. 16 for Sumatera Selatan). */
    private const PROVINCE_CODE = '16';

    /**
     * List records or get single by kode_bps.
     * GET /api/master-kako?kode_bps=1601 (single) or GET /api/master-kako?per_page=15 (list).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $kodeKab = $user?->kode_kab ?? '00';

        if ($request->filled('kode_bps')) {
            $kodeBps = $request->get('kode_bps');
            if ($kodeKab !== '00') {
                $allowedKodeBps = self::PROVINCE_CODE . str_pad($kodeKab, 2, '0', STR_PAD_LEFT);
                if ($kodeBps !== $allowedKodeBps) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Forbidden. You can only access data for your kabupaten.',
                    ], 403);
                }
            }
            $data = MasterKako::where('kode_bps', $kodeBps)->first();
            if (! $data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        $query = $this->scopeByUser($request->user(), MasterKako::query());
        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
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
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $item = MasterKako::find($id);
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
        $item = MasterKako::create($validated);
        return response()->json(['status' => 'success', 'data' => $item], 201);
    }

    /**
     * Update a record by id.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = MasterKako::find($id);
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
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = MasterKako::find($id);
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
