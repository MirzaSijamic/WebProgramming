<?php

require_once 'BaseService.php';
require_once __DIR__ . '/../dao/TrackDao.php';
require_once __DIR__ . '/../dao/PlaylistTracksDao.php';

class TrackService extends BaseService {
    private $playlistTracksDao;

    public function __construct()
    {
        parent::__construct(new TrackDao());
        $this->playlistTracksDao = new PlaylistTracksDao();
    }

    public function get_all()
    {
        return $this->dao->getAll();
    }

    public function get_by_id($id)
    {
        $track = $this->dao->getById($id);
        if (!$track) {
            return ['success' => false, 'error' => 'Track not found'];
        }

        // Get playlists that contain this track
        $track['playlists'] = $this->playlistTracksDao->getPlaylistsByTrackId($id);

        return ['success' => true, 'data' => $track];
    }

    public function get_by_spotify_id($spotifyTrackId)
    {
        $track = $this->dao->getTrackBySpotifyId($spotifyTrackId);
        if (!$track) {
            return ['success' => false, 'error' => 'Track not found'];
        }
        return ['success' => true, 'data' => $track];
    }

    public function add($entity)
    {
        // Validation
        $validation = $this->validate($entity);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Check if track with same spotify_track_id already exists
        $existing = $this->dao->getTrackBySpotifyId($entity['spotify_track_id']);
        if ($existing) {
            return ['success' => false, 'error' => 'Track with this Spotify ID already exists'];
        }

        $result = $this->dao->add($entity);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to create track'];
    }

    public function update($entity, $id, $id_column = "id")
    {
        // Check if track exists
        $existing = $this->dao->getById($id);
        if (!$existing) {
            return ['success' => false, 'error' => 'Track not found'];
        }

        // Validation (only validate provided fields)
        if (isset($entity['spotify_track_id']) || isset($entity['name'])) {
            $validation = $this->validate($entity, true);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
        }

        // If spotify_track_id is being updated, check for duplicates
        if (isset($entity['spotify_track_id']) && $entity['spotify_track_id'] !== $existing['spotify_track_id']) {
            $duplicate = $this->dao->getTrackBySpotifyId($entity['spotify_track_id']);
            if ($duplicate) {
                return ['success' => false, 'error' => 'Track with this Spotify ID already exists'];
            }
        }

        $result = $this->dao->update($entity, $id, $id_column);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to update track'];
    }

    public function delete($id)
    {
        $track = $this->dao->getById($id);
        if (!$track) {
            return ['success' => false, 'error' => 'Track not found'];
        }

        // Cascade delete will handle playlist_tracks
        $result = $this->dao->delete($id);
        if ($result) {
            return ['success' => true, 'message' => 'Track deleted successfully'];
        }
        return ['success' => false, 'error' => 'Failed to delete track'];
    }

    private function validate($entity, $partial = false)
    {
        if (!$partial) {
            if (empty($entity['spotify_track_id'])) {
                return ['valid' => false, 'error' => 'Spotify track ID is required'];
            }
            if (empty($entity['name'])) {
                return ['valid' => false, 'error' => 'Track name is required'];
            }
        }

        if (isset($entity['spotify_track_id']) && (strlen($entity['spotify_track_id']) < 1 || strlen($entity['spotify_track_id']) > 100)) {
            return ['valid' => false, 'error' => 'Spotify track ID must be between 1 and 100 characters'];
        }

        if (isset($entity['name']) && (strlen($entity['name']) < 1 || strlen($entity['name']) > 255)) {
            return ['valid' => false, 'error' => 'Track name must be between 1 and 255 characters'];
        }

        if (isset($entity['artists']) && strlen($entity['artists']) > 500) {
            return ['valid' => false, 'error' => 'Artists field is too long (max 500 characters)'];
        }

        if (isset($entity['album']) && strlen($entity['album']) > 255) {
            return ['valid' => false, 'error' => 'Album name is too long (max 255 characters)'];
        }

        if (isset($entity['duration_ms']) && (!is_numeric($entity['duration_ms']) || $entity['duration_ms'] < 0)) {
            return ['valid' => false, 'error' => 'Duration must be a positive number'];
        }

        if (isset($entity['preview_url']) && strlen($entity['preview_url']) > 500) {
            return ['valid' => false, 'error' => 'Preview URL is too long (max 500 characters)'];
        }

        if (isset($entity['external_url']) && strlen($entity['external_url']) > 500) {
            return ['valid' => false, 'error' => 'External URL is too long (max 500 characters)'];
        }

        return ['valid' => true];
    }
}

?>
