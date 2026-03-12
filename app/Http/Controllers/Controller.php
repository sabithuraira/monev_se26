<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Monev SE26 API",
 *     version="1.0.0",
 *     description="API documentation for Monev SE26 application"
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Enter your Bearer token from login"
 * )
 */
abstract class Controller
{
    //
}
