<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Models\Song;

class Artist
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connection();
    }

    /*
    |--------------------------------------------------------------------------
    | Find Artist
    |--------------------------------------------------------------------------
    */

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                slug,
                image_url,
                verified
            FROM artists
            WHERE id = ?
            AND deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $artist = $result->fetch_assoc();

        $stmt->close();

        return $artist ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | All Artists - Pagination
    |--------------------------------------------------------------------------
    */

    public function allPaginated(
        int $page = 1,
        int $limit = 20
    ): array {

        $offset = ($page - 1) * $limit;

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $countResult = $this->db->query("
            SELECT COUNT(*) AS total
            FROM artists
            WHERE deleted_at IS NULL
        ");

        $total = (int)$countResult->fetch_assoc()['total'];

        /*
        |--------------------------------------------------------------------------
        | Artists
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                slug,
                image_url,
                verified
            FROM artists
            WHERE deleted_at IS NULL
            ORDER BY name ASC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param(
            "ii",
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $artists = [];

        while ($row = $result->fetch_assoc()) {

            $artists[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'image_url' => $row['image_url'],
                'verified' => (bool)$row['verified']
            ];
        }

        $stmt->close();

        return [
            'data' => $artists,
            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Artist Tracks
    |--------------------------------------------------------------------------
    */

    public function tracks(
        int $artistId,
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
        SELECT COUNT(DISTINCT s.id)
        FROM song_artists sa

        INNER JOIN songs s
            ON s.id = sa.song_id

        WHERE sa.artist_id = ?
        AND s.is_active = 1
        AND s.deleted_at IS NULL
    ");

        $stmt->bind_param(
            "i",
            $artistId
        );

        $stmt->execute();

        $stmt->bind_result($total);
        $stmt->fetch();

        $stmt->close();

        $total = (int)$total;

        /*
    |--------------------------------------------------------------------------
    | IDs
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
        SELECT DISTINCT s.id

        FROM song_artists sa

        INNER JOIN songs s
            ON s.id = sa.song_id

        WHERE sa.artist_id = ?
        AND s.is_active = 1
        AND s.deleted_at IS NULL

        ORDER BY s.release_date DESC, s.id DESC

        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            "iii",
            $artistId,
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['id'];
        }

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | Complete songs
    |--------------------------------------------------------------------------
    */

        $songModel = new Song();

        $tracks = [];

        foreach ($songIds as $songId) {

            $song = $songModel->find($songId);

            if ($song !== null) {
                $tracks[] = $song;
            }
        }

        return [
            'data' => $tracks,

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Artist Albums
    |--------------------------------------------------------------------------
    */

    public function albums(
        int $artistId,
        int $page = 1,
        int $limit = 20
    ): array {

        $offset = ($page - 1) * $limit;

        /*
        |--------------------------------------------------------------------------
        | Count
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT a.id)

            FROM song_artists sa

            INNER JOIN song_albums sal
                ON sal.song_id = sa.song_id

            INNER JOIN albums a
                ON a.id = sal.album_id

            WHERE sa.artist_id = ?
            AND a.deleted_at IS NULL
        ");

        $stmt->bind_param("i", $artistId);
        $stmt->execute();

        $stmt->bind_result($total);
        $stmt->fetch();

        $stmt->close();

        $total = (int)$total;

        /*
        |--------------------------------------------------------------------------
        | Albums
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
            SELECT DISTINCT
                a.id,
                a.title,
                a.slug,
                a.cover_url,
                a.release_date,
                a.album_type,
                a.label,
                a.total_tracks

            FROM song_artists sa

            INNER JOIN song_albums sal
                ON sal.song_id = sa.song_id

            INNER JOIN albums a
                ON a.id = sal.album_id

            WHERE sa.artist_id = ?
            AND a.deleted_at IS NULL

            ORDER BY a.release_date DESC

            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param(
            "iii",
            $artistId,
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $albums = [];

        while ($row = $result->fetch_assoc()) {

            $albums[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],
                'release_date' => $row['release_date'],
                'album_type' => $row['album_type'],
                'label' => $row['label'],
                'total_tracks' => (int)$row['total_tracks']
            ];
        }

        $stmt->close();

        return [
            'data' => $albums,

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination Helper
    |--------------------------------------------------------------------------
    */

    private function pagination(
        int $page,
        int $limit,
        int $total
    ): array {

        $totalPages = $total > 0
            ? (int)ceil($total / $limit)
            : 0;

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => $totalPages,

            'has_next' => $page < $totalPages,

            'has_previous' => $page > 1
        ];
    }
}