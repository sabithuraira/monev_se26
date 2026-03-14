<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login: validate credentials and issue a Sanctum token.
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     description="Authenticate with email and password, returns Bearer token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Logged in successfully."),
     *         @OA\Property(property="token", type="string"),
     *         @OA\Property(property="token_type", type="string", example="Bearer"),
     *         @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"), @OA\Property(property="email", type="string"))
     *     )),
     *     @OA\Response(response=422, description="Validation error / Invalid credentials")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => ['email' => [__('auth.failed')]],
            ], 401);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();
        $user->tokens()->delete(); // revoke previous tokens (single device) or remove this for multiple devices

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'kode_kab' => $user->kode_kab,
                'kode_kec' => $user->kode_kec,
                'kode_desa' => $user->kode_desa,
            ],
        ]);
    }

    /**
     * Logout: revoke the current access token.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     description="Revoke the current access token. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out", @OA\JsonContent(@OA\Property(property="message", type="string", example="Logged out successfully."))),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get the authenticated user.
     *
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Auth"},
     *     summary="Current user",
     *     description="Get the currently authenticated user. Requires Bearer token.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Authenticated user", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }
}
