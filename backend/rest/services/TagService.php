<?php

require_once 'BaseService.php';
require_once __DIR__ . '/../dao/TagDao.php';
require_once __DIR__ . '/../dao/PlaylistTagsDao.php';

class TagService extends BaseService {
    private $playlistTagsDao;

    public function __construct()
    {
        parent::__construct(new TagDao());
        $this->playlistTagsDao = new PlaylistTagsDao();
    }

    public function get_all()
    {
        return $this->dao->getAll();
    }

    public function get_by_id($id)
    {
        $tag = $this->dao->getById($id);
        if (!$tag) {
            return ['success' => false, 'error' => 'Tag not found'];
        }

        // Get playlists that use this tag
        $tag['playlists'] = $this->playlistTagsDao->getPlaylistsByTagId($id);

        return ['success' => true, 'data' => $tag];
    }

    public function get_by_name($name)
    {
        $tag = $this->dao->getTagByName($name);
        if (!$tag) {
            return ['success' => false, 'error' => 'Tag not found'];
        }
        return ['success' => true, 'data' => $tag];
    }

    public function add($entity)
    {
        // Validation
        $validation = $this->validate($entity);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Normalize tag name (trim and lowercase for consistency)
        $entity['name'] = trim(strtolower($entity['name']));

        // Check if tag with same name already exists
        $existing = $this->dao->getTagByName($entity['name']);
        if ($existing) {
            return ['success' => false, 'error' => 'Tag with this name already exists'];
        }

        $result = $this->dao->add($entity);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to create tag'];
    }

    public function update($entity, $id, $id_column = "id")
    {
        // Check if tag exists
        $existing = $this->dao->getById($id);
        if (!$existing) {
            return ['success' => false, 'error' => 'Tag not found'];
        }

        // Validation
        if (isset($entity['name'])) {
            $validation = $this->validate($entity, true);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }

            // Normalize tag name
            $entity['name'] = trim(strtolower($entity['name']));

            // If name is being updated, check for duplicates
            if ($entity['name'] !== $existing['name']) {
                $duplicate = $this->dao->getTagByName($entity['name']);
                if ($duplicate) {
                    return ['success' => false, 'error' => 'Tag with this name already exists'];
                }
            }
        }

        $result = $this->dao->update($entity, $id, $id_column);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'error' => 'Failed to update tag'];
    }

    public function delete($id)
    {
        $tag = $this->dao->getById($id);
        if (!$tag) {
            return ['success' => false, 'error' => 'Tag not found'];
        }

        // Cascade delete will handle playlist_tags
        $result = $this->dao->delete($id);
        if ($result) {
            return ['success' => true, 'message' => 'Tag deleted successfully'];
        }
        return ['success' => false, 'error' => 'Failed to delete tag'];
    }

    private function validate($entity, $partial = false)
    {
        if (!$partial) {
            if (empty($entity['name'])) {
                return ['valid' => false, 'error' => 'Tag name is required'];
            }
        }

        if (isset($entity['name'])) {
            $name = trim($entity['name']);
            if (strlen($name) < 1 || strlen($name) > 80) {
                return ['valid' => false, 'error' => 'Tag name must be between 1 and 80 characters'];
            }
        }

        return ['valid' => true];
    }
}

?>
