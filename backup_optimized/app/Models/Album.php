<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Album extends Model
{
    /*
    |--------------------------------------------------------------------------
    | All Albums - Pagination
    |--------------------------------------------------------------------------
    */

    public function allPaginated(
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Total Albums
    |--------------------------------------------------------------------------
    */

        $totalResult = $this->db->query("
        SELECT COUNT(*) AS total
        FROM albums
        WHERE deleted_at IS NULL
    ");

        $total = (int)$totalResult->fetch_assoc()['total'];

        /*
    |--------------------------------------------------------------------------
    | Albums
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
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
        WHERE deleted_at IS NULL
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            "ii",
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $albums = [];

        while ($row = $result->fetch_assoc()) {

            /*
        |--------------------------------------------------------------------------
        | Album Artists
        |--------------------------------------------------------------------------
        */

            $artistStmt = $this->db->prepare("
            SELECT DISTINCT
                ar.id,
                ar.name,
                ar.slug,
                ar.image_url,
                ar.verified
            FROM song_albums sal

            INNER JOIN song_artists sa
                ON sa.song_id = sal.song_id

            INNER JOIN artists ar
                ON ar.id = sa.artist_id

            WHERE sal.album_id = ?
            AND ar.deleted_at IS NULL

            ORDER BY ar.name ASC
        ");

            $albumId = (int)$row['id'];

            $artistStmt->bind_param(
                "i",
                $albumId
            );

            $artistStmt->execute();

            $artistResult = $artistStmt->get_result();

            $artists = [];

            while ($artist = $artistResult->fetch_assoc()) {

                $artists[] = [
                    'id' => (int)$artist['id'],
                    'name' => $artist['name'],
                    'slug' => $artist['slug'],
                    'image_url' => $artist['image_url'],
                    'verified' => (bool)$artist['verified']
                ];
            }

            $artistStmt->close();

            /*
        |--------------------------------------------------------------------------
        | Album Response
        |--------------------------------------------------------------------------
        */

            $albums[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],

                'metadata' => [
                    'description' => $row['description'],
                    'release_date' => $row['release_date'],
                    'album_type' => $row['album_type'],
                    'label' => $row['label'],
                    'copyright' => $row['copyright'],
                    'total_tracks' => (int)$row['total_tracks']
                ],

                'artists' => $artists
            ];
        }

        $stmt->close();

        return [
            'data' => $albums,

            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $total > 0
                    ? (int)ceil($total / $limit)
                    : 0,
                'has_next' => $page < (
                    $total > 0
                    ? (int)ceil($total / $limit)
                    : 0
                ),
                'has_previous' => $page > 1
            ]
        ];
    }


    /** Lightweight album cards for home/list sections. */
    public function homeCards(int $limit = 5): array
    {
        $limit = max(1, min($limit, MAX_LIMIT));
        $stmt = $this->db->prepare("
            SELECT id, title, slug, cover_url, release_date, album_type, total_tracks
            FROM albums
            WHERE deleted_at IS NULL
            ORDER BY release_date DESC, id DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $albums = [];
        while ($row = $result->fetch_assoc()) {
            $albums[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],
                'metadata' => [
                    'release_date' => $row['release_date'],
                    'album_type' => $row['album_type'],
                    'total_tracks' => (int)$row['total_tracks']
                ]
            ];
        }
        $stmt->close();
        return $albums;
    }

    /*
    |--------------------------------------------------------------------------
    | Album Details + Tracks
    |--------------------------------------------------------------------------
    */

    public function findWithTracks(int $id): ?array
    {
        /*
        |--------------------------------------------------------------------------
        | Album
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
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

        /*
        |--------------------------------------------------------------------------
        | Tracks
        |--------------------------------------------------------------------------
        */

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.language,
                s.duration_seconds,
                s.release_date,
                s.play_count,
                s.like_count,
                s.download_count

            FROM song_albums sa

            INNER JOIN songs s
                ON s.id = sa.song_id

            WHERE sa.album_id = ?
            AND s.is_active = 1
            AND s.deleted_at IS NULL

            ORDER BY
                s.disc_number ASC,
                s.track_number ASC,
                s.id ASC
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $result = $stmt->get_result();

        $tracks = [];

        while ($track = $result->fetch_assoc()) {

            $tracks[] = [
                'id' => (int)$track['id'],
                'title' => $track['title'],
                'slug' => $track['slug'],

                'media' => [
                    'cover_url' => $track['cover_url'],
                    'audio_url' => $track['audio_url'],
                    'duration_seconds' => (int)$track['duration_seconds'],
                    'duration' => $this->formatDuration(
                        (int)$track['duration_seconds']
                    )
                ],

                'metadata' => [
                    'language' => $track['language'],
                    'release_date' => $track['release_date']
                ],

                'statistics' => [
                    'play_count' => (int)$track['play_count'],
                    'like_count' => (int)$track['like_count'],
                    'download_count' => (int)$track['download_count']
                ]
            ];
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        $album['id'] = (int)$album['id'];
        $album['total_tracks'] = (int)$album['total_tracks'];

        $album['tracks'] = $tracks;

        return $album;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Album
    |--------------------------------------------------------------------------
    */

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
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
                total_tracks,
                created_at,
                updated_at

            FROM albums

            WHERE id = ?
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

        $album['id'] = (int)$album['id'];
        $album['total_tracks'] = (int)$album['total_tracks'];

        return $album;
    }


    /*
    |--------------------------------------------------------------------------
    | Search Albums
    |--------------------------------------------------------------------------
    */

    public function search(
        string $keyword,
        int $limit = 20
    ): array {

        $limit = max(1, min($limit, MAX_LIMIT));

        $keyword = "%{$keyword}%";

        $stmt = $this->db->prepare("
            SELECT
                id,
                title,
                slug,
                description,
                cover_url,
                release_date,
                album_type,
                label,
                total_tracks

            FROM albums

            WHERE deleted_at IS NULL
            AND title LIKE ?

            ORDER BY title ASC

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

            $albums[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'cover_url' => $row['cover_url'],
                'release_date' => $row['release_date'],
                'album_type' => $row['album_type'],
                'label' => $row['label'],
                'total_tracks' => (int)$row['total_tracks']
            ];
        }

        $stmt->close();

        return $albums;
    }


    /*
    |--------------------------------------------------------------------------
    | Album Songs
    |--------------------------------------------------------------------------
    */

    public function songs(int $albumId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.language,
                s.duration_seconds,
                s.play_count,
                s.like_count,
                s.download_count

            FROM songs s

            INNER JOIN song_albums sa
                ON sa.song_id = s.id

            WHERE sa.album_id = ?
            AND s.is_active = 1
            AND s.deleted_at IS NULL

            ORDER BY
                s.disc_number ASC,
                s.track_number ASC,
                s.id ASC
        ");

        $stmt->bind_param("i", $albumId);

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
                    'duration_seconds' => (int)$row['duration_seconds'],
                    'duration' => $this->formatDuration(
                        (int)$row['duration_seconds']
                    )
                ],

                'metadata' => [
                    'language' => $row['language']
                ],

                'statistics' => [
                    'play_count' => (int)$row['play_count'],
                    'like_count' => (int)$row['like_count'],
                    'download_count' => (int)$row['download_count']
                ]
            ];
        }

        $stmt->close();

        return $songs;
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


    /*
    |--------------------------------------------------------------------------
    | Format Duration
    |--------------------------------------------------------------------------
    */

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf(
            "%02d:%02d",
            $minutes,
            $remainingSeconds
        );
    }
}