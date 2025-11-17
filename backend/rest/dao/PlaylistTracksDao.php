<?php

require_once 'BaseDao.php';

class PlaylistTracksDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "playlist_tracks"; 
        parent::__construct($this->table_name);
    }

    public function getTracksByPlaylistId($playlistId)
    {
        $query = "SELECT t.*, pt.id as playlist_track_id 
                  FROM tracks t 
                  INNER JOIN playlist_tracks pt ON t.id = pt.track_id 
                  WHERE pt.playlist_id = :playlist_id 
                  ORDER BY pt.id ASC";
        
        return $this->query($query, ['playlist_id' => $playlistId]);
    }

    public function getPlaylistsByTrackId($trackId)
    {
        $query = "SELECT p.*, pt.id as playlist_track_id 
                  FROM playlists p 
                  INNER JOIN playlist_tracks pt ON p.id = pt.playlist_id 
                  WHERE pt.track_id = :track_id 
                  ORDER BY pt.id ASC";
        
        return $this->query($query, ['track_id' => $trackId]);
    }

    public function addTrackToPlaylist($playlistId, $trackId)
    {
        // Check if track already exists in playlist
        $existing = $this->query_unique(
            "SELECT * FROM playlist_tracks WHERE playlist_id = :playlist_id AND track_id = :track_id",
            ['playlist_id' => $playlistId, 'track_id' => $trackId]
        );

        if ($existing) {
            return false; // Track already in playlist
        }

        return $this->add([
            'playlist_id' => $playlistId,
            'track_id' => $trackId
        ]);
    }

    public function removeTrackFromPlaylist($playlistId, $trackId)
    {
        $query = "DELETE FROM playlist_tracks WHERE playlist_id = :playlist_id AND track_id = :track_id";
        $stmt = $this->connection->prepare($query);
        return $stmt->execute(['playlist_id' => $playlistId, 'track_id' => $trackId]);
    }

    public function removeAllTracksFromPlaylist($playlistId)
    {
        $query = "DELETE FROM playlist_tracks WHERE playlist_id = :playlist_id";
        $stmt = $this->connection->prepare($query);
        return $stmt->execute(['playlist_id' => $playlistId]);
    }
}

?>
