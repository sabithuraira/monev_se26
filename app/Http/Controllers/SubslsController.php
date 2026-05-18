<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubslsResource;
use App\Models\MasterDesa;
use App\Models\MasterKako;
use App\Models\MasterKec;
use App\Models\Subsls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

    public function rekap(Request $request): JsonResponse
    {
        $resolved = $this->resolveRekapFilters($request);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        $kodeProv = $resolved['kode_prov'];
        $kodeKab = $resolved['kode_kab'];
        $kodeKec = $resolved['kode_kec'];
        $kodeDesa = $resolved['kode_desa'];

        $baseQuery = $this->buildRekapFilteredSubslsQuery($kodeProv, $kodeKab, $kodeKec, $kodeDesa);
        $summary = $this->computeRekapProgressSummary($baseQuery);

        if ($kodeKab === '') {
            $rows = Subsls::query()
                ->select([
                    'subsls.kode_kab',
                    DB::raw('COALESCE(master_kako.nama_bps, master_kako.nama_pum, "") as nama_wilayah'),
                    DB::raw('COUNT(*) as total_data'),
                    DB::raw('SUM(subsls.se26_selesai) as total_se26_selesai'),
                    DB::raw('SUM(subsls.se26_diperiksa) as total_se26_diperiksa'),
                    DB::raw('SUM(subsls.se2026_is_finish) as total_se26_is_finish'),
                ])
                ->leftJoin('master_kako', 'master_kako.kode_bps', '=', DB::raw('CONCAT(subsls.kode_prov, subsls.kode_kab)'))
                ->where('subsls.kode_prov', $kodeProv)
                ->groupBy('subsls.kode_kab', 'master_kako.nama_bps', 'master_kako.nama_pum')
                ->orderBy('subsls.kode_kab')
                ->get()
                ->map(fn ($row) => [
                    'kode_prov' => $kodeProv,
                    'kode_kab' => $row->kode_kab,
                    'nama_wilayah' => $row->nama_wilayah,
                    'total_data' => (int) $row->total_data,
                    'total_se26_selesai' => (int) $row->total_se26_selesai,
                    'total_se26_diperiksa' => (int) $row->total_se26_diperiksa,
                    'total_se26_is_finish' => (int) $row->total_se26_is_finish,
                ]);

            return response()->json(['level' => 'kabupaten', 'data' => $rows, 'summary' => $summary]);
        }

        if ($kodeKec === '') {
            $rows = Subsls::query()
                ->select([
                    'subsls.kode_kec',
                    DB::raw('COALESCE(master_kec.nama_bps, master_kec.nama_pum, "") as nama_wilayah'),
                    DB::raw('COUNT(*) as total_data'),
                    DB::raw('SUM(subsls.se26_selesai) as total_se26_selesai'),
                    DB::raw('SUM(subsls.se26_diperiksa) as total_se26_diperiksa'),
                    DB::raw('SUM(subsls.se2026_is_finish) as total_se26_is_finish'),
                ])
                ->leftJoin('master_kec', 'master_kec.kode_bps', '=', DB::raw('CONCAT(subsls.kode_prov, subsls.kode_kab, subsls.kode_kec)'))
                ->where('subsls.kode_prov', $kodeProv)
                ->where('subsls.kode_kab', $kodeKab)
                ->groupBy('subsls.kode_kec', 'master_kec.nama_bps', 'master_kec.nama_pum')
                ->orderBy('subsls.kode_kec')
                ->get()
                ->map(fn ($row) => [
                    'kode_prov' => $kodeProv,
                    'kode_kab' => $kodeKab,
                    'kode_kec' => $row->kode_kec,
                    'nama_wilayah' => $row->nama_wilayah,
                    'total_data' => (int) $row->total_data,
                    'total_se26_selesai' => (int) $row->total_se26_selesai,
                    'total_se26_diperiksa' => (int) $row->total_se26_diperiksa,
                    'total_se26_is_finish' => (int) $row->total_se26_is_finish,
                ]);

            return response()->json(['level' => 'kecamatan', 'data' => $rows, 'summary' => $summary]);
        }

        if ($kodeDesa === '') {
            $rows = Subsls::query()
                ->select([
                    'subsls.kode_desa',
                    DB::raw('COALESCE(master_desa.nama_bps, master_desa.nama_pum, "") as nama_wilayah'),
                    DB::raw('COUNT(*) as total_data'),
                    DB::raw('SUM(subsls.se26_selesai) as total_se26_selesai'),
                    DB::raw('SUM(subsls.se26_diperiksa) as total_se26_diperiksa'),
                    DB::raw('SUM(subsls.se2026_is_finish) as total_se26_is_finish'),
                ])
                ->leftJoin('master_desa', 'master_desa.kode_bps', '=', DB::raw('CONCAT(subsls.kode_prov, subsls.kode_kab, subsls.kode_kec, subsls.kode_desa)'))
                ->where('subsls.kode_prov', $kodeProv)
                ->where('subsls.kode_kab', $kodeKab)
                ->where('subsls.kode_kec', $kodeKec)
                ->groupBy('subsls.kode_desa', 'master_desa.nama_bps', 'master_desa.nama_pum')
                ->orderBy('subsls.kode_desa')
                ->get()
                ->map(fn ($row) => [
                    'kode_prov' => $kodeProv,
                    'kode_kab' => $kodeKab,
                    'kode_kec' => $kodeKec,
                    'kode_desa' => $row->kode_desa,
                    'nama_wilayah' => $row->nama_wilayah,
                    'total_data' => (int) $row->total_data,
                    'total_se26_selesai' => (int) $row->total_se26_selesai,
                    'total_se26_diperiksa' => (int) $row->total_se26_diperiksa,
                    'total_se26_is_finish' => (int) $row->total_se26_is_finish,
                ]);

            return response()->json(['level' => 'desa', 'data' => $rows, 'summary' => $summary]);
        }

        $rows = Subsls::query()
            ->select([
                'subsls.kode_sls',
                DB::raw('MAX(subsls.nama_sls) as nama_sls'),
                DB::raw('COUNT(*) as total_data'),
                DB::raw('SUM(subsls.se26_selesai) as total_se26_selesai'),
                DB::raw('SUM(subsls.se26_diperiksa) as total_se26_diperiksa'),
                DB::raw('SUM(subsls.se2026_is_finish) as total_se26_is_finish'),
            ])
            ->where('subsls.kode_prov', $kodeProv)
            ->where('subsls.kode_kab', $kodeKab)
            ->where('subsls.kode_kec', $kodeKec)
            ->where('subsls.kode_desa', $kodeDesa)
            ->groupBy('subsls.kode_sls')
            ->orderBy('subsls.kode_sls')
            ->get()
            ->map(fn ($row) => [
                'kode_prov' => $kodeProv,
                'kode_kab' => $kodeKab,
                'kode_kec' => $kodeKec,
                'kode_desa' => $kodeDesa,
                'kode_sls' => $row->kode_sls,
                'nama_sls' => $row->nama_sls,
                'total_data' => (int) $row->total_data,
                'total_se26_selesai' => (int) $row->total_se26_selesai,
                'total_se26_diperiksa' => (int) $row->total_se26_diperiksa,
                'total_se26_is_finish' => (int) $row->total_se26_is_finish,
            ]);

        return response()->json(['level' => 'sls', 'data' => $rows, 'summary' => $summary]);
    }

    /**
     * @return JsonResponse|array{kode_prov: string, kode_kab: string, kode_kec: string, kode_desa: string}
     */
    private function resolveRekapFilters(Request $request): JsonResponse|array
    {
        $validated = $request->validate([
            'kode_prov' => 'required|string|size:2',
            'kode_kab' => 'nullable|string|size:2',
            'kode_kec' => 'nullable|string|size:3',
            'kode_desa' => 'nullable|string|size:3',
        ]);

        $kodeProv = (string) $validated['kode_prov'];
        $kodeKab = (string) ($validated['kode_kab'] ?? '');
        $kodeKec = (string) ($validated['kode_kec'] ?? '');
        $kodeDesa = (string) ($validated['kode_desa'] ?? '');

        if ($kodeKab === '' && ($kodeKec !== '' || $kodeDesa !== '')) {
            return response()->json(['message' => 'Parameter hierarchy invalid. kode_kec/kode_desa requires kode_kab.'], 422);
        }
        if ($kodeKec === '' && $kodeDesa !== '') {
            return response()->json(['message' => 'Parameter hierarchy invalid. kode_desa requires kode_kec.'], 422);
        }

        $userKodeKab = $request->user()?->kode_kab ?? '00';
        if ($userKodeKab !== '00') {
            if ($kodeKab !== '' && $kodeKab !== $userKodeKab) {
                return response()->json(['message' => 'Forbidden. You can only access data for your kabupaten.'], 403);
            }
            $kodeKab = $userKodeKab;
        }

        return [
            'kode_prov' => $kodeProv,
            'kode_kab' => $kodeKab,
            'kode_kec' => $kodeKec,
            'kode_desa' => $kodeDesa,
        ];
    }

    private function buildRekapFilteredSubslsQuery(string $kodeProv, string $kodeKab, string $kodeKec, string $kodeDesa): Builder
    {
        $query = Subsls::query()->where('kode_prov', $kodeProv);

        if ($kodeKab !== '') {
            $query->where('kode_kab', $kodeKab);
        }
        if ($kodeKec !== '') {
            $query->where('kode_kec', $kodeKec);
        }
        if ($kodeDesa !== '') {
            $query->where('kode_desa', $kodeDesa);
        }

        return $query;
    }

    /**
     * Percentages are share of total SLS rows in the current filter scope.
     *
     * @return array{
     *     total_sls: int,
     *     persen_total_progres_muatan: float,
     *     persen_selesai: float,
     *     persen_sedang_dikerjakan: float,
     *     persen_belum_dikerjakan: float
     * }
     */
    private function computeRekapProgressSummary(Builder $base): array
    {
        $total = (clone $base)->count();

        if ($total === 0) {
            return [
                'total_sls' => 0,
                'persen_total_progres_muatan' => 0.0,
                'persen_selesai' => 0.0,
                'persen_sedang_dikerjakan' => 0.0,
                'persen_belum_dikerjakan' => 0.0,
            ];
        }

        $progresMuatan = (clone $base)->where(function ($q) {
            $q->where('se2026_is_finish', 1)
                ->orWhereRaw('COALESCE(se26_selesai, 0) <> 0');
        })->count();

        $selesai = (clone $base)->where('se2026_is_finish', 1)->count();

        $sedangDikerjakan = (clone $base)->whereRaw('COALESCE(se26_selesai, 0) <> 0')->count();

        $belumDikerjakan = (clone $base)->whereRaw('COALESCE(se2026_is_finish, 0) = 0 AND COALESCE(se26_selesai, 0) = 0')->count();

        $pct = fn (int $n): float => round(($n / $total) * 100, 2);

        return [
            'total_sls' => $total,
            'persen_total_progres_muatan' => $pct($progresMuatan),
            'persen_selesai' => $pct($selesai),
            'persen_sedang_dikerjakan' => $pct($sedangDikerjakan),
            'persen_belum_dikerjakan' => $pct($belumDikerjakan),
        ];
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
