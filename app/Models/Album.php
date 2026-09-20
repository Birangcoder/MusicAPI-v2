<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Album extends Model
{
    /**
     * Get all albums with pagination.
     *
     * Lightweight response:
     * - id
     * - title
     * - slug
     * - cover_url
     * - artists
     *
     * No metadata.
     */
    public function allPaginated(
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        // Total albums
        $countResult = $this->db->query("
            SELECT COUNT(*) AS total
            FROM albums
            WHERE deleted_at IS NULL
        ");

        $total = (int) $countResult->fetch_assoc()['total'];

        // Albums
        $stmt = $this->db->prepare("
            SELECT
                id,
                title,
                slug,
                cover_url
            FROM albums
            WHERE deleted_at IS NULL
            ORDER BY
                release_date DESC,
                id DESC
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
        $albumIds = [];

        while ($row = $result->fetch_assoc()) {
            $albumId = (int) $row['id'];

            $albumIds[] = $albumId;

            $albums[$albumId] = [
                'id' => $albumId,
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],
                'artists' => []
            ];
        }

        $stmt->close();

        /*
         * Get artists for all albums in ONE query.
         */
        if ($albumIds !== []) {
            $placeholders = implode(
                ',',
                array_fill(0, count($albumIds), '?')
            );

            $types = str_repeat(
                'i',
                count($albumIds)
            );

            $artistStmt = $this->db->prepare("
                SELECT DISTINCT
                    sal.album_id,
                    ar.id,
                    ar.name,
                    ar.slug
                FROM song_albums sal
                INNER JOIN song_artists sa
                    ON sa.song_id = sal.song_id
                INNER JOIN artists ar
                    ON ar.id = sa.artist_id
                WHERE sal.album_id IN ($placeholders)
                  AND ar.deleted_at IS NULL
                ORDER BY
                    sal.album_id ASC,
                    ar.name ASC
            ");

            $params = [$types];

            foreach ($albumIds as $albumId) {
                $params[] = $albumId;
            }

            $this->bindDynamic(
                $artistStmt,
                $params
            );

            $artistStmt->execute();

            $artistResult = $artistStmt->get_result();

            $seen = [];

            while ($artist = $artistResult->fetch_assoc()) {
                $albumId = (int) $artist['album_id'];
                $artistId = (int) $artist['id'];

                if (isset($seen[$albumId][$artistId])) {
                    continue;
                }

                $seen[$albumId][$artistId] = true;

                if (!isset($albums[$albumId])) {
                    continue;
                }

                $albums[$albumId]['artists'][] = [
                    'id' => $artistId,
                    'name' => $artist['name'],
                    'slug' => $artist['slug']
                ];
            }

            $artistStmt->close();
        }

        return [
            'data' => array_values($albums),

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    /**
     * Get tracks belonging to an album.
     *
     * Tracks use the common song card format,
     * but artists are removed for album track lists.
     */
    public function tracks(
        int $albumId,
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        // Total tracks
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM song_albums sa
            INNER JOIN songs s
                ON s.id = sa.song_id
            WHERE sa.album_id = ?
              AND s.is_active = 1
              AND s.deleted_at IS NULL
        ");

        $countStmt->bind_param(
            "i",
            $albumId
        );

        $countStmt->execute();

        $total = (int) $countStmt
            ->get_result()
            ->fetch_assoc()['total'];

        $countStmt->close();

        // Track IDs
        $stmt = $this->db->prepare("
            SELECT
                sa.song_id
            FROM song_albums sa
            INNER JOIN songs s
                ON s.id = sa.song_id
            WHERE sa.album_id = ?
              AND s.is_active = 1
              AND s.deleted_at IS NULL
            ORDER BY
                sa.disc_number ASC,
                sa.track_number ASC,
                s.id ASC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param(
            "iii",
            $albumId,
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int) $row['song_id'];
        }

        $stmt->close();

        // Get common song cards
        $tracks = [];

        if ($songIds !== []) {
            $song = new Song();

            $tracks = $song->cardsByIds($songIds);

            // Album track list does not need artist data
            foreach ($tracks as &$track) {
                unset($track['artists']);
            }

            unset($track);
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

    /**
     * Lightweight album cards for home/list sections.
     */
    public function homeCards(
        int $limit = 5
    ): array {
        $limit = max(
            1,
            min($limit, MAX_LIMIT)
        );

        $stmt = $this->db->prepare("
        SELECT
            id,
            title,
            slug,
            cover_url,
            release_date,
            album_type,
            total_tracks
        FROM albums
        WHERE deleted_at IS NULL
        ORDER BY
            release_date DESC,
            id DESC
        LIMIT ?
    ");

        $stmt->bind_param(
            "i",
            $limit
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $albums = [];
        $albumIds = [];

        while ($row = $result->fetch_assoc()) {
            $albumId = (int) $row['id'];

            $albumIds[] = $albumId;

            $albums[$albumId] = [
                'id' => $albumId,
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],

                'metadata' => [
                    'release_date' => $row['release_date'],
                    'album_type' => $row['album_type'],
                    'total_tracks' => (int) $row['total_tracks']
                ],

                'artists' => []
            ];
        }

        $stmt->close();

        /*
     * Get artists for all albums in ONE query.
     */
        if ($albumIds !== []) {
            $placeholders = implode(
                ',',
                array_fill(0, count($albumIds), '?')
            );

            $types = str_repeat(
                'i',
                count($albumIds)
            );

            $artistStmt = $this->db->prepare("
                SELECT DISTINCT
                    sal.album_id,
                    ar.id,
                    ar.name,
                    ar.slug
                FROM song_albums sal

                INNER JOIN song_artists sa
                    ON sa.song_id = sal.song_id

                INNER JOIN artists ar
                    ON ar.id = sa.artist_id

                WHERE sal.album_id IN ($placeholders)
                AND ar.deleted_at IS NULL

                ORDER BY
                    sal.album_id ASC,
                    ar.name ASC
            ");

            $params = [$types];

            foreach ($albumIds as $albumId) {
                $params[] = $albumId;
            }

            $this->bindDynamic(
                $artistStmt,
                $params
            );

            $artistStmt->execute();

            $artistResult = $artistStmt->get_result();

            $seen = [];

            while ($artist = $artistResult->fetch_assoc()) {
                $albumId = (int) $artist['album_id'];
                $artistId = (int) $artist['id'];

                // Prevent duplicate artists
                if (isset($seen[$albumId][$artistId])) {
                    continue;
                }

                $seen[$albumId][$artistId] = true;

                if (!isset($albums[$albumId])) {
                    continue;
                }

                $albums[$albumId]['artists'][] = [
                    'id' => $artistId,
                    'name' => $artist['name'],
                    'slug' => $artist['slug']
                ];
            }

            $artistStmt->close();
        }

        return array_values($albums);
    }

    /**
     * Find album details.
     *
     * Used by:
     * GET /albums/{id}
     * GET /albums/{id}/tracks
     *
     * Metadata included.
     * Artists excluded.
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
                total_tracks
            FROM albums
            WHERE id = ?
              AND deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        $album = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$album) {
            return null;
        }

        return [
            'id' => (int) $album['id'],
            'title' => $album['title'],
            'slug' => $album['slug'],
            'cover_url' => $album['cover_url'],

            'metadata' => [
                'description' => $album['description'],
                'release_date' => $album['release_date'],
                'album_type' => $album['album_type'],
                'label' => $album['label'],
                'copyright' => $album['copyright'],
                'total_tracks' => (int) $album['total_tracks']
            ]
        ];
    }

    /**
     * Search albums.
     */
    public function search(
        string $keyword,
        int $limit = 20
    ): array {
        $limit = max(
            1,
            min($limit, MAX_LIMIT)
        );

        $keyword = "%{$keyword}%";

        $stmt = $this->db->prepare("
            SELECT
                id,
                title,
                slug,
                cover_url,
                release_date,
                album_type,
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
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'cover_url' => $row['cover_url'],
                'metadata' => [
                    'release_date' => $row['release_date'],
                    'album_type' => $row['album_type'],
                    'total_tracks' => (int) $row['total_tracks']
                ]
            ];
        }

        $stmt->close();

        return $albums;
    }

    /**
     * Pagination helper.
     */
    private function pagination(
        int $page,
        int $limit,
        int $total
    ): array {
        $totalPages = $total > 0
            ? (int) ceil($total / $limit)
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

    /**
     * Dynamic bind_param helper.
     */
    private function bindDynamic(
        \mysqli_stmt $stmt,
        array $params
    ): void {
        $types = array_shift($params);

        $refs = [];

        foreach ($params as $key => &$value) {
            $refs[$key] = &$value;
        }

        array_unshift(
            $refs,
            $types
        );

        call_user_func_array(
            [$stmt, 'bind_param'],
            $refs
        );
    }
}