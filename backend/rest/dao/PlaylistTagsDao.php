<?php

require_once 'BaseDao.php';

class PlaylistTagsDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "playlist_tags"; 
        parent::__construct($this->table_name);
    }

    public function getTagsByPlaylistId($playlistId)
    {
        $query = "SELECT t.*, pt.id as playlist_tag_id 
                  FROM tags t 
                  INNER JOIN playlist_tags pt ON t.id = pt.tag_id 
                  WHERE pt.playlist_id = :playlist_id 
                  ORDER BY t.name ASC";
        
        return $this->query($query, ['playlist_id' => $playlistId]);
    }

    public function getPlaylistsByTagId($tagId)
    {
        $query = "SELECT p.*, pt.id as playlist_tag_id 
                  FROM playlists p 
                  INNER JOIN playlist_tags pt ON p.id = pt.playlist_id 
                  WHERE pt.tag_id = :tag_id 
                  ORDER BY p.created_at DESC";
        
        return $this->query($query, ['tag_id' => $tagId]);
    }

    public function addTagToPlaylist($playlistId, $tagId)
    {
        // Check if tag already exists in playlist
        $existing = $this->query_unique(
            "SELECT * FROM playlist_tags WHERE playlist_id = :playlist_id AND tag_id = :tag_id",
            ['playlist_id' => $playlistId, 'tag_id' => $tagId]
        );

        if ($existing) {
            return false; // Tag already in playlist
        }

        return $this->add([
            'playlist_id' => $playlistId,
            'tag_id' => $tagId
        ]);
    }

    public function removeTagFromPlaylist($playlistId, $tagId)
    {
        $query = "DELETE FROM playlist_tags WHERE playlist_id = :playlist_id AND tag_id = :tag_id";
        $stmt = $this->connection->prepare($query);
        return $stmt->execute(['playlist_id' => $playlistId, 'tag_id' => $tagId]);
    }

    public function removeAllTagsFromPlaylist($playlistId)
    {
        $query = "DELETE FROM playlist_tags WHERE playlist_id = :playlist_id";
        $stmt = $this->connection->prepare($query);
        return $stmt->execute(['playlist_id' => $playlistId]);
    }
}

?>
