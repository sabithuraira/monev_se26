<?php

namespace App\Http\Controllers;

use App\Http\Requests\InformationRequest;
use App\Http\Resources\InformationResource;
use App\Models\Information;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    /**
     * Paginated list of information records.
     *
     * @OA\Get(
     *     path="/api/information",
     *     tags={"Information"},
     *     summary="List information",
     *     description="Paginated list ordered by id descending. Optionally filter by is_active.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-1000)", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="is_active", in="query", required=false, description="Filter by active flag (e.g. true, false, 1, 0)", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="created_by", type="integer", nullable=true),
     *             @OA\Property(property="updated_by", type="integer", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )),
     *         @OA\Property(property="meta", type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )),
     *
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = min(max($perPage, 1), 1000);

        $query = Information::query()->orderByDesc('id');

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $items = $query->paginate($perPage);

        return response()->json([
            'data' => InformationResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * Create an information record. Sets created_by and updated_by from the authenticated user.
     *
     * @OA\Post(
     *     path="/api/information",
     *     tags={"Information"},
     *     summary="Create information",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"title","content"},
     *
     *             @OA\Property(property="title", type="string", example="Pengumuman"),
     *             @OA\Property(property="content", type="string", example="Isi informasi..."),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="created_by", type="integer", nullable=true),
     *             @OA\Property(property="updated_by", type="integer", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *     )),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(InformationRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $information = Information::create($data);

        return response()->json(['data' => new InformationResource($information)], 201);
    }

    /**
     * Get a single information record by id.
     *
     * @OA\Get(
     *     path="/api/information/{id}",
     *     tags={"Information"},
     *     summary="Get information by ID",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Information ID", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="created_by", type="integer", nullable=true),
     *             @OA\Property(property="updated_by", type="integer", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *     )),
     *
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Information not found."))),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $information = Information::find($id);

        if (! $information) {
            return response()->json(['message' => 'Information not found.'], 404);
        }

        return response()->json(['data' => new InformationResource($information)]);
    }

    /**
     * Update an information record. Sets updated_by from the authenticated user.
     *
     * @OA\Put(
     *     path="/api/information/{id}",
     *     tags={"Information"},
     *     summary="Update information",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Information ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="created_by", type="integer", nullable=true),
     *             @OA\Property(property="updated_by", type="integer", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *     )),
     *
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Information not found."))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     *
     * @OA\Patch(
     *     path="/api/information/{id}",
     *     tags={"Information"},
     *     summary="Update information (PATCH)",
     *     description="Same as PUT.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Information ID", @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(
     *
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="is_active", type="boolean"),
     *             @OA\Property(property="created_by", type="integer", nullable=true),
     *             @OA\Property(property="updated_by", type="integer", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
     *         )
     *     )),
     *
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(InformationRequest $request, int $id): JsonResponse
    {
        $information = Information::find($id);

        if (! $information) {
            return response()->json(['message' => 'Information not found.'], 404);
        }

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $information->update($data);

        return response()->json(['data' => new InformationResource($information->fresh())]);
    }

    /**
     * Delete an information record.
     *
     * @OA\Delete(
     *     path="/api/information/{id}",
     *     tags={"Information"},
     *     summary="Delete information",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Information ID", @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(@OA\Property(property="message", type="string", example="Deleted."))),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(@OA\Property(property="message", type="string", example="Information not found."))),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $information = Information::find($id);

        if (! $information) {
            return response()->json(['message' => 'Information not found.'], 404);
        }

        $information->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
