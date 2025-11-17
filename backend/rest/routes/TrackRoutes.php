<?php

class TrackRoutes {
    /**
     * @OA\Get(
     *     path="/api/tracks",
     *     summary="Get all tracks",
     *     description="Retrieve a list of all tracks",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of tracks",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Track")
     *         )
     *     )
     * )
     */
    public static function getAll() {
        $tracks = Flight::track_service()->get_all();
        Flight::json($tracks);
    }

    /**
     * @OA\Get(
     *     path="/api/tracks/{id}",
     *     summary="Get track by ID",
     *     description="Retrieve a specific track with playlists that contain it",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Track ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track details",
     *         @OA\JsonContent(ref="#/components/schemas/Track")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Track not found"
     *     )
     * )
     */
    public static function getById($id) {
        $response = Flight::track_service()->get_by_id($id);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tracks/spotify/{spotifyTrackId}",
     *     summary="Get track by Spotify ID",
     *     description="Retrieve a track by its Spotify track ID",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="spotifyTrackId",
     *         in="path",
     *         required=true,
     *         description="Spotify Track ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track details",
     *         @OA\JsonContent(ref="#/components/schemas/Track")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Track not found"
     *     )
     * )
     */
    public static function getBySpotifyId($spotifyTrackId) {
        $response = Flight::track_service()->get_by_spotify_id($spotifyTrackId);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tracks",
     *     summary="Create a new track",
     *     description="Create a new track",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"spotify_track_id", "name"},
     *             @OA\Property(property="spotify_track_id", type="string", example="4iV5W9uYEdYUVa79Axb7Rh", description="Spotify track ID"),
     *             @OA\Property(property="name", type="string", example="Bohemian Rhapsody", description="Track name"),
     *             @OA\Property(property="artists", type="string", example="Queen", description="Artist names"),
     *             @OA\Property(property="album", type="string", example="A Night at the Opera", description="Album name"),
     *             @OA\Property(property="duration_ms", type="integer", example=355000, description="Duration in milliseconds"),
     *             @OA\Property(property="preview_url", type="string", example="https://...", description="Preview URL"),
     *             @OA\Property(property="external_url", type="string", example="https://open.spotify.com/track/...", description="External URL")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Track created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Track")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or duplicate Spotify ID"
     *     )
     * )
     */
    public static function create() {
        $data = Flight::request()->data->getData();
        $response = Flight::track_service()->add($data);
        
        if ($response['success']) {
            Flight::json($response['data'], 201);
        } else {
            Flight::halt(400, $response['error']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tracks/{id}",
     *     summary="Update a track",
     *     description="Update an existing track",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Track ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Track Name"),
     *             @OA\Property(property="artists", type="string", example="Updated Artist"),
     *             @OA\Property(property="album", type="string", example="Updated Album"),
     *             @OA\Property(property="duration_ms", type="integer", example=360000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Track")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Track not found"
     *     )
     * )
     */
    public static function update($id) {
        $data = Flight::request()->data->getData();
        $response = Flight::track_service()->update($data, $id);
        
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tracks/{id}",
     *     summary="Delete a track",
     *     description="Delete a track by ID",
     *     tags={"tracks"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Track ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Track not found"
     *     )
     * )
     */
    public static function delete($id) {
        $response = Flight::track_service()->delete($id);
        
        if ($response['success']) {
            Flight::json(['message' => $response['message']]);
        } else {
            Flight::halt(404, $response['error']);
        }
    }
}

Flight::group('/api/tracks', function() {
    Flight::route("GET /", [TrackRoutes::class, 'getAll']);
    Flight::route("GET /@id", [TrackRoutes::class, 'getById']);
    Flight::route("GET /spotify/@spotifyTrackId", [TrackRoutes::class, 'getBySpotifyId']);
    Flight::route("POST /", [TrackRoutes::class, 'create']);
    Flight::route("PUT /@id", [TrackRoutes::class, 'update']);
    Flight::route("DELETE /@id", [TrackRoutes::class, 'delete']);
});

?>
