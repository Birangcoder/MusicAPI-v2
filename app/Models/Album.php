<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Album extends Model
{
    public function all(int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $totalResult = $db->query("
        SELECT COUNT(*) AS total
        FROM albums
    ");

        $total = (int)$totalResult->fetch_assoc()['total'];

        $stmt = $db->prepare("
        SELECT
            id,
            title,
            slug,
            description,
            cover_url,
            release_date,
            album_type,
            copyright,
            label,
            total_tracks
        FROM albums
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $albums = [];

        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }

        $stmt->close();

        return [
            'items' => $albums,
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
        $db = \App\Core\Database::getInstance()->connection();

        // Album
        $stmt = $db->prepare("
        SELECT
            id,
            title,
            slug,
            description,
            cover_url,
            release_date,
            album_type,
            copyright,
            label,
            total_tracks
        FROM albums
        WHERE id = ?
        LIMIT 1
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $album = $result->fetch_assoc();

        $stmt->close();

        if (!$album) {
            return null;
        }

        // Tracks
        $stmt = $db->prepare("
        SELECT
            s.id,
            s.title,
            s.slug,
            s.cover_url,
            s.audio_url,
            s.language,
            s.duration_seconds,
            s.release_date
        FROM song_albums sa

        INNER JOIN songs s
            ON s.id = sa.song_id

        WHERE sa.album_id = ?
          AND s.is_active = 1

        ORDER BY s.id ASC
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $tracks = [];

        while ($track = $result->fetch_assoc()) {
            $tracks[] = $track;
        }

        $stmt->close();

        $album['tracks'] = $tracks;

        return $album;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM albums
            WHERE id=?
            AND deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $album = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$album) {
            return null;
        }

        $album['songs'] = $this->songs($id);

        return $album;
    }

    public function search(
        string $keyword,
        int $limit = 20
    ): array {

        $keyword = "%{$keyword}%";

        $stmt = $this->db->prepare("
            SELECT
                id,
                title,
                slug,
                cover_url,
                release_date,
                album_type
            FROM albums
            WHERE
                deleted_at IS NULL
            AND
                title LIKE ?
            LIMIT ?
        ");

        $stmt->bind_param(
            "si",
            $keyword,
            $limit
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $albums = [];

        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }

        $stmt->close();

        return $albums;
    }

    public function songs(int $albumId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                s.play_count
            FROM songs s
            INNER JOIN song_albums sa
                ON sa.song_id = s.id
            WHERE
                sa.album_id = ?
            AND
                s.deleted_at IS NULL
            ORDER BY
                s.track_number ASC,
                s.disc_number ASC
        ");

        $stmt->bind_param("i", $albumId);

        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        $stmt->close();

        return $songs;
    }
}