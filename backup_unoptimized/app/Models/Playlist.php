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
            user_id,
            title,
            description,
            cover_url,
            is_public,
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
        // -----------------------------------------
        // Get playlist
        // -----------------------------------------

        $stmt = $this->db->prepare("
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

        $playlist = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$playlist) {
            return null;
        }

        // -----------------------------------------
        // Get songs
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT
            s.id,
            s.title,
            s.slug,
            s.cover_url,
            s.audio_url,
            s.language,
            s.duration_seconds,
            ps.position,
            ps.added_at
        FROM playlist_songs ps

        INNER JOIN songs s
            ON s.id = ps.song_id

        WHERE ps.playlist_id = ?
          AND s.is_active = 1
          AND s.deleted_at IS NULL

        ORDER BY ps.position ASC, ps.added_at ASC
    ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {

            $songs[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],

                'media' => [
                    'cover_url' => $row['cover_url'],
                    'audio_url' => $row['audio_url'],
                    'duration_seconds' => (int)$row['duration_seconds']
                ],

                'language' => $row['language'],

                'playlist' => [
                    'position' => (int)$row['position'],
                    'added_at' => $row['added_at']
                ]
            ];
        }

        $stmt->close();

        // Convert database values
        $playlist['id'] = (int)$playlist['id'];
        $playlist['user_id'] = (int)$playlist['user_id'];
        $playlist['is_public'] = (bool)$playlist['is_public'];
        $playlist['total_songs'] = (int)$playlist['total_songs'];

        $playlist['songs'] = $songs;

        return $playlist;
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