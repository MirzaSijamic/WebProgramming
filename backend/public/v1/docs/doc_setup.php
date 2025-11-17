<?php
/*
 * @OA\OpenApi(
 * * // --- 1. Mandatory @OA\Info ---
 * @OA\Info(
 * title="Playlist Manager API", // Use a unique name
 * description="Playlist Manager",
 * version="1.0",
 * @OA\Contact(
 * email="mirza.sijamica@stu.ibu.edu.ba",
 * name="Mirza Sijamic"
 * )
 * ),
 * * // --- 2. Mandatory @OA\Server ---
 * @OA\Server(
 * url="http://localhost/WebProgramming/WebProgramming/backend",
 * description="API server"
 * ),
 * * // --- 3. @OA\SecurityScheme (optional, but needed for documentation) ---
 * @OA\SecurityScheme(
 * securityScheme="ApiKey",
 * type="apiKey",
 * in="header",
 * name="Authentication"
 * )
 * )
 */