<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubslsResource;
use App\Models\MasterDesa;
use App\Models\MasterKako;
use App\Models\MasterKec;
use App\Models\Subsls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubslsController extends Controller
{
    /**
     * Web: Blade table of subsls with kab/kec/desa names from master tables.
     */
    public function indexPage(Request $request): View
    {
        $perPage = 20;
        $page = max((int) $request->get('page', 1), 1);

        $total = $this->applySubslsFilters(Subsls::query(), $request)->count('subsls.id');
        $rows = $this->buildSubslsRowsQuery($request)
            ->forPage($page, $perPage)
            ->get();

        $items = new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('subsls.index', ['items' => $items]);
    }

    public function listData(Request $request): JsonResponse
    {
        $perPage = 20;
        $page = max((int) $request->get('page', 1), 1);

        $total = $this->applySubslsFilters(Subsls::query(), $request)->count('subsls.id');
        $rows = $this->buildSubslsRowsQuery($request)
            ->forPage($page, $perPage)
            ->get();

        $items = new LengthAwarePaginator($rows, $total, $perPage, $page);

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    public function kabupatenOptions(Request $request): JsonResponse
    {
        $kodeKab = $request->user()?->kode_kab ?? '00';

        $options = MasterKako::query()
            ->selectRaw('RIGHT(kode_bps, 2) as value, kode_bps, nama_bps as label')
            ->where('kode_bps', 'like', '16__')
            ->when($kodeKab !== '00', fn ($q) => $q->whereRaw('RIGHT(kode_bps, 2) = ?', [$kodeKab]))
            ->orderBy('kode_bps')
            ->get();

        return response()->json(['data' => $options]);
    }

    public function kecamatanOptions(Request $request): JsonResponse
    {
        $kodeKab = (string) $request->get('kode_kab', '');
        if ($kodeKab === '') {
            return response()->json(['data' => []]);
        }

        $options = MasterKec::query()
            ->selectRaw('RIGHT(kode_bps, 3) as value, kode_bps, nama_bps as label')
            ->whereRaw('CHAR_LENGTH(kode_bps) = 7')
            ->whereRaw("kode_bps LIKE CONCAT('16', ?, '%')", [$kodeKab])
            ->orderBy('kode_bps')
            ->get();

        return response()->json(['data' => $options]);
    }

    public function desaOptions(Request $request): JsonResponse
    {
        $kodeKab = (string) $request->get('kode_kab', '');
        $kodeKec = (string) $request->get('kode_kec', '');
        if ($kodeKab === '' || $kodeKec === '') {
            return response()->json(['data' => []]);
        }

        $options = MasterDesa::query()
            ->selectRaw('RIGHT(kode_bps, 3) as value, kode_bps, nama_bps as label')
            ->whereRaw('CHAR_LENGTH(kode_bps) = 10')
            ->whereRaw("kode_bps LIKE CONCAT('16', ?, ?, '%')", [$kodeKab, $kodeKec])
            ->orderBy('kode_bps')
            ->get();

        return response()->json(['data' => $options]);
    }

    /**
     * Display a listing of subsls with optional filters.
     *
     * @OA\Get(
     *     path="/api/subsls",
     *     tags={"Subsls"},
     *     summary="List Subsls",
     *     description="Paginated list of subsls. Filter by kode_kab, kode_kec, kode_desa.",
     *
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-1000)", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="kode_kab", in="query", required=false, description="Filter by kode kabupaten", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kode_kec", in="query", required=false, description="Filter by kode kecamatan", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kode_desa", in="query", required=false, description="Filter by kode desa", @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $kodeKab = $user?->kode_kab ?? '00';

        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 1000);

        $query = Subsls::query();

        if ($kodeKab !== '00') {
            $query->where('kode_kab', $kodeKab);
        }

        $items = $query
            ->when($kodeKab === '00' && $request->filled('kode_kab'), fn ($q) => $q->where('kode_kab', $request->get('kode_kab')))
            ->when($request->filled('kode_kec'), fn ($q) => $q->where('kode_kec', $request->get('kode_kec')))
            ->when($request->filled('kode_desa'), fn ($q) => $q->where('kode_desa', $request->get('kode_desa')))
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'data' => SubslsResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * Display the specified subsls.
     *
     * @OA\Get(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Get Subsls by ID",
     *     description="Returns a single subsls record",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Subsls not found.")))
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $item = Subsls::find($id);

        if (! $item) {
            return response()->json(['message' => 'Subsls not found.'], 404);
        }

        if (! $this->userCanAccessSubsls($request->user(), $item)) {
            return response()->json(['message' => 'Forbidden. You can only access data for your kabupaten.'], 403);
        }

        return response()->json(['data' => new SubslsResource($item)]);
    }

    /**
     * Update se26_selesai, se26_diperiksa, and se2026_is_finish.
     *
     * @OA\Patch(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Update Subsls (allowed fields only)",
     *     description="Update only se26_selesai, se26_diperiksa, and se2026_is_finish (0/1). Other fields are ignored.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="se26_selesai", type="integer", minimum=0),
     *             @OA\Property(property="se26_diperiksa", type="integer", minimum=0),
     *             @OA\Property(property="se2026_is_finish", type="integer", description="0 or 1", enum={0,1})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string")))
     * )
     *
     * @OA\Put(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Update Subsls (allowed fields only)",
     *     description="Same as PATCH.",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="se26_selesai", type="integer", minimum=0),
     *             @OA\Property(property="se26_diperiksa", type="integer", minimum=0),
     *             @OA\Property(property="se2026_is_finish", type="integer", enum={0,1})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $item = Subsls::find($id);

        if (! $item) {
            return response()->json(['message' => 'Subsls not found.'], 404);
        }

        if (! $this->userCanAccessSubsls($request->user(), $item)) {
            return response()->json(['message' => 'Forbidden. You can only access data for your kabupaten.'], 403);
        }

        $validated = $request->validate([
            'se26_selesai' => 'sometimes|integer|min:0',
            'se26_diperiksa' => 'sometimes|integer|min:0',
            'se2026_is_finish' => 'sometimes|integer|in:0,1',
        ]);

        $item->update($validated);

        return response()->json(['data' => new SubslsResource($item->fresh())]);
    }

    /**
     * Remove the specified subsls.
     *
     * @OA\Delete(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Delete Subsls",
     *     description="Delete a subsls record by ID",
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Deleted."))),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string")))
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Subsls::find($id);

        if (! $item) {
            return response()->json(['message' => 'Subsls not found.'], 404);
        }

        if (! $this->userCanAccessSubsls($request->user(), $item)) {
            return response()->json(['message' => 'Forbidden. You can only access data for your kabupaten.'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * Check if the authenticated user can access the given Subsls record based on kode_kab.
     */
    private function userCanAccessSubsls($user, Subsls $item): bool
    {
        $kodeKab = $user?->kode_kab ?? '00';

        if ($kodeKab === '00') {
            return true;
        }

        return $item->kode_kab === $kodeKab;
    }

    private function buildSubslsRowsQuery(Request $request): Builder
    {
        $query = Subsls::query()
            ->select([
                'subsls.id',
                'subsls.nama_sls',
                'subsls.se26_selesai',
                'subsls.se26_diperiksa',
                'subsls.se2026_is_finish',
                'subsls.kode_kab',
                'subsls.kode_kec',
                'subsls.kode_desa',
            ])
            ->orderBy('subsls.id');

        return $this->applySubslsFilters($query, $request);
    }

    private function applySubslsFilters(Builder $query, Request $request): Builder
    {
        $kodeKabUser = $request->user()?->kode_kab ?? '00';
        $selectedKab = (string) $request->get('kode_kab', '');
        $selectedKec = (string) $request->get('kode_kec', '');
        $selectedDesa = (string) $request->get('kode_desa', '');

        if ($kodeKabUser !== '00') {
            $query->where('subsls.kode_kab', $kodeKabUser);
        }

        return $query
            ->when($selectedKab !== '', fn ($q) => $q->where('subsls.kode_kab', $selectedKab))
            ->when($selectedKec !== '', fn ($q) => $q->where('subsls.kode_kec', $selectedKec))
            ->when($selectedDesa !== '', fn ($q) => $q->where('subsls.kode_desa', $selectedDesa));
    }
}
