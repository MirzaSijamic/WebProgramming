<?php

require_once __DIR__ . "/BaseService.php";
require_once __DIR__ . '/../dao/PlaylistDao.php';
require_once __DIR__ . '/../dao/TrackDao.php';
require_once __DIR__ . '/../dao/LogsDao.php';
require_once __DIR__ . '/../dao/TagDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class PlaylistService extends BaseService {
    private $playlistTracksDao;
    private $playlistTagsDao;
    private $userDao;

    public function __construct()
    {
        parent::__construct(new PlaylistDao());
        $this->playlistTracksDao = new TrackDao();
        $this->playlistTagsDao = new TagDao();
        $this->userDao = new UserDao();
    }

    public function get_all()
    {
        return $this->dao->getAll();
    }

    public function get_by_id($id)
    {
        $playlist = $this->dao->getById($id);
        if (!$playlist) {
            return ['success' => false, 'error' => 'Playlist not found'];
        }

        // Get tracks and tags for the playlist
        //$playlist['tracks'] = $this->playlistTracksDao->getTracksByPlaylistId($id);
        //$playlist['tags'] = $this->playlistTagsDao->getTagsByPlaylistId($id);

        return ['success' => true, 'data' => $playlist];
    }

    public function get_by_user_id($userId)
    {
        $playlists = $this->dao->getPlaylistsByUserId($userId);
        return ['success' => true, 'data' => $playlists];
    }


    public function add($entity)
    {
        // Validation
        $validation = $this->validate($entity);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Check if user exists
        $user = $this->userDao->getById($entity['user_id']);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        // Set defaults
        if (!isset($entity['is_public'])) {
            $entity['is_public'] = 0;
        }

        $result = $this->dao->add($entity);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to create playlist'];
    }

    public function update($entity, $id, $id_column = "id")
    {
        // Check if playlist exists
        $existing = $this->dao->getById($id);
        if (!$existing) {
            return ['success' => false, 'error' => 'Playlist not found'];
        }

        // Validation (only validate provided fields)
        if (isset($entity['title']) || isset($entity['description'])) {
            $validation = $this->validate($entity, true);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
        }

        $result = $this->dao->update($entity, $id, $id_column);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to update playlist'];
    }

    public function delete($id)
    {
        $playlist = $this->dao->getById($id);
        if (!$playlist) {
            return ['success' => false, 'error' => 'Playlist not found'];
        }

        // Cascade delete will handle playlist_tracks and playlist_tags
        $result = $this->dao->delete($id);
        if ($result) {
            return ['success' => true, 'message' => 'Playlist deleted successfully'];
        }
        return ['success' => false, 'error' => 'Failed to delete playlist'];
    }

    public function add_track($playlistId, $trackId)
    {
        // Check if playlist exists
        $playlist = $this->dao->getById($playlistId);
        if (!$playlist) {
            return ['success' => false, 'error' => 'Playlist not found'];
        }

        $result = $this->playlistTracksDao->addTrackToPlaylist($playlistId, $trackId);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Track already in playlist or failed to add'];
    }

    public function remove_track($playlistId, $trackId)
    {
        $result = $this->playlistTracksDao->removeTrackFromPlaylist($playlistId, $trackId);
        if ($result) {
            return ['success' => true, 'message' => 'Track removed from playlist'];
        }
        return ['success' => false, 'error' => 'Failed to remove track from playlist'];
    }

    public function add_tag($playlistId, $tagId)
    {
        // Check if playlist exists
        $playlist = $this->dao->getById($playlistId);
        if (!$playlist) {
            return ['success' => false, 'error' => 'Playlist not found'];
        }

        $result = $this->playlistTagsDao->addTagToPlaylist($playlistId, $tagId);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Tag already in playlist or failed to add'];
    }

    public function remove_tag($playlistId, $tagId)
    {
        $result = $this->playlistTagsDao->removeTagFromPlaylist($playlistId, $tagId);
        if ($result) {
            return ['success' => true, 'message' => 'Tag removed from playlist'];
        }
        return ['success' => false, 'error' => 'Failed to remove tag from playlist'];
    }

    private function validate($entity, $partial = false)
    {
        if (!$partial) {
            if (empty($entity['title'])) {
                return ['valid' => false, 'error' => 'Title is required'];
            }
            if (empty($entity['user_id'])) {
                return ['valid' => false, 'error' => 'User ID is required'];
            }
        }

        if (isset($entity['title']) && (strlen($entity['title']) < 1 || strlen($entity['title']) > 255)) {
            return ['valid' => false, 'error' => 'Title must be between 1 and 255 characters'];
        }

        if (isset($entity['description']) && strlen($entity['description']) > 65535) {
            return ['valid' => false, 'error' => 'Description is too long'];
        }

        if (isset($entity['is_public']) && !in_array($entity['is_public'], [0, 1, '0', '1', true, false])) {
            return ['valid' => false, 'error' => 'is_public must be 0 or 1'];
        }

        return ['valid' => true];
    }

}