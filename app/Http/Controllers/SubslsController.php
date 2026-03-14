<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubslsResource;
use App\Models\Subsls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubslsController extends Controller
{
    /**
     * Display a listing of subsls with optional filters.
     *
     * @OA\Get(
     *     path="/api/subsls",
     *     tags={"Subsls"},
     *     summary="List Subsls",
     *     description="Paginated list of subsls. Filter by kode_kab, kode_kec, kode_desa.",
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="kode_kab", in="query", required=false, description="Filter by kode kabupaten", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kode_kec", in="query", required=false, description="Filter by kode kecamatan", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kode_desa", in="query", required=false, description="Filter by kode desa", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
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
        $perPage = min(max($perPage, 1), 100);

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
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
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
     * Update only se26_selesai and se26_diperiksa.
     *
     * @OA\Patch(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Update Subsls (se26 fields only)",
     *     description="Update only se26_selesai and se26_diperiksa. Other fields are ignored.",
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="se26_selesai", type="integer", minimum=0),
     *             @OA\Property(property="se26_diperiksa", type="integer", minimum=0)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string")))
     * )
     *
     * @OA\Put(
     *     path="/api/subsls/{id}",
     *     tags={"Subsls"},
     *     summary="Update Subsls (se26 fields only)",
     *     description="Same as PATCH - update only se26_selesai and se26_diperiksa.",
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="se26_selesai", type="integer", minimum=0),
     *             @OA\Property(property="se26_diperiksa", type="integer", minimum=0)
     *         )
     *     ),
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
     *     @OA\Parameter(name="id", in="path", required=true, description="Subsls ID", @OA\Schema(type="integer")),
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
}
