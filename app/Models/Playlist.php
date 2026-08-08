<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Playlist extends Model
{
    public function all(int $userId, int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM playlists
        WHERE user_id = ?
    ");

        $countStmt->bind_param('i', $userId);
        $countStmt->execute();

        $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

        $countStmt->close();

        $stmt = $db->prepare("
        SELECT
            id,
            name,
            description,
            cover_url,
            total_songs,
            created_at,
            updated_at
        FROM playlists
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $playlists = [];

        while ($row = $result->fetch_assoc()) {
            $playlists[] = $row;
        }

        $stmt->close();

        return [
            'items' => $playlists,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $total > 0
                    ? (int)ceil($total / $limit)
                    : 0
            ]
        ];
    }

    public function findWithTracks(int $id): ?array
    {
        $db = Database::getInstance()->connection();

        // Get playlist
        $stmt = $db->prepare("
        SELECT
            id,
            user_id,
            title,
            description,
            cover_url,
            is_public,
            total_songs,
            created_at,
            updated_at
        FROM playlists
        WHERE id = ?
        LIMIT 1
    ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $playlist = $result->fetch_assoc();

        $stmt->close();

        if (!$playlist) {
            return null;
        }

        // Get playlist songs
        $stmt = $db->prepare("
        SELECT
            song_id,
            position,
            added_at
        FROM playlist_songs
        WHERE playlist_id = ?
        ORDER BY position ASC
    ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $songModel = new Song();
        $tracks = [];

        while ($row = $result->fetch_assoc()) {

            $song = $songModel->find((int) $row['song_id']);

            if ($song) {
                $song['position'] = (int) $row['position'];
                $song['added_at'] = $row['added_at'];

                $tracks[] = $song;
            }
        }

        $stmt->close();

        return [
            'id' => (int) $playlist['id'],
            'user_id' => (int) $playlist['user_id'],
            'title' => $playlist['title'],
            'description' => $playlist['description'],
            'cover_url' => $playlist['cover_url'],
            'is_public' => (bool) $playlist['is_public'],
            'total_songs' => (int) $playlist['total_songs'],
            'created_at' => $playlist['created_at'],
            'updated_at' => $playlist['updated_at'],
            'tracks' => $tracks
        ];
    }

    public function find(int $playlistId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM playlists
            WHERE id=?
            AND user_id=?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $playlistId,
            $userId
        );

        $stmt->execute();

        $playlist = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$playlist) {
            return null;
        }

        $playlist['songs'] = $this->songs($playlistId);

        return $playlist;
    }

    public function create(
        int $userId,
        array $data
    ): int {

        $stmt = $this->db->prepare("
            INSERT INTO playlists
            (
                user_id,
                title,
                description,
                cover_url,
                is_public
            )
            VALUES
            (
                ?,?,?,?,?
            )
        ");

        $stmt->bind_param(
            "isssi",
            $userId,
            $data['title'],
            $data['description'],
            $data['cover_url'],
            $data['is_public']
        );

        $stmt->execute();

        $id = $stmt->insert_id;

        $stmt->close();

        return $id;
    }

    public function update(
        int $playlistId,
        int $userId,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE playlists
            SET
                title=?,
                description=?,
                cover_url=?,
                is_public=?
            WHERE
                id=?
            AND
                user_id=?
        ");

        $stmt->bind_param(
            "sssiii",
            $data['title'],
            $data['description'],
            $data['cover_url'],
            $data['is_public'],
            $playlistId,
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function delete(
        int $playlistId,
        int $userId
    ): bool {

        $stmt = $this->db->prepare("
            DELETE FROM playlists
            WHERE id=?
            AND user_id=?
        ");

        $stmt->bind_param(
            "ii",
            $playlistId,
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function songs(int $playlistId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                ps.position
            FROM playlist_songs ps

            INNER JOIN songs s
                ON s.id=ps.song_id

            WHERE
                ps.playlist_id=?
            ORDER BY
                ps.position ASC
        ");

        $stmt->bind_param(
            "i",
            $playlistId
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        $stmt->close();

        return $songs;
    }

    public function addSong(
        int $playlistId,
        int $songId
    ): bool {

        $position = 1;

        $stmt = $this->db->prepare("
            SELECT
                IFNULL(MAX(position),0)+1 position
            FROM playlist_songs
            WHERE playlist_id=?
        ");

        $stmt->bind_param(
            "i",
            $playlistId
        );

        $stmt->execute();

        $position = (int)$stmt
            ->get_result()
            ->fetch_assoc()['position'];

        $stmt->close();

        $stmt = $this->db->prepare("
            INSERT INTO playlist_songs
            (
                playlist_id,
                song_id,
                position
            )
            VALUES
            (
                ?,?,?
            )
        ");

        $stmt->bind_param(
            "iii",
            $playlistId,
            $songId,
            $position
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function removeSong(
        int $playlistId,
        int $songId
    ): bool {

        $stmt = $this->db->prepare("
            DELETE
            FROM playlist_songs
            WHERE
                playlist_id=?
            AND
                song_id=?
        ");

        $stmt->bind_param(
            "ii",
            $playlistId,
            $songId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }
}