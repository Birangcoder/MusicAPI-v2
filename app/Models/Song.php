<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Song extends Model
{
    public function all(
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $db = Database::getInstance()->connection();

        // Total
        $result = $db->query("
        SELECT COUNT(*) AS total
        FROM songs
        WHERE is_active = 1
        AND deleted_at IS NULL
    ");

        $total = (int)$result->fetch_assoc()['total'];

        // IDs for this page
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
        SELECT id
        FROM songs
        WHERE is_active = 1
        AND deleted_at IS NULL
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['id'];
        }

        $stmt->close();

        $songs = $this->cardsByIds($songIds);

        return [
            'tracks' => $songs,
            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    public function find(int $id): ?array
    {
        $db = \App\Core\Database::getInstance()->connection();

        /*
    |--------------------------------------------------------------------------
    | SONG
    |--------------------------------------------------------------------------
    */

        $stmt = $db->prepare("
        SELECT
            id,
            title,
            slug,
            cover_url,
            audio_url,
            language,
            duration_seconds,
            release_date,
            play_count,
            like_count,
            download_count,
            is_active
        FROM songs
        WHERE id = ?
        LIMIT 1
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $song = $result->fetch_assoc();

        $stmt->close();

        if (!$song) {
            return null;
        }

        /*
    |--------------------------------------------------------------------------
    | DURATION
    |--------------------------------------------------------------------------
    */

        $durationSeconds = (int)$song['duration_seconds'];

        /*
    |--------------------------------------------------------------------------
    | ARTISTS
    |--------------------------------------------------------------------------
    */

        $stmt = $db->prepare("
        SELECT
            a.id,
            a.name,
            a.slug,
            a.image_url,
            a.verified,

            COALESCE(sa.role, 'primary') AS role

        FROM song_artists sa

        INNER JOIN artists a
            ON a.id = sa.artist_id

        WHERE sa.song_id = ?

        ORDER BY
            CASE
                WHEN sa.role = 'primary' THEN 1
                WHEN sa.role = 'featured' THEN 2
                ELSE 3
            END,
            a.name ASC
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $artists = [];

        while ($artist = $result->fetch_assoc()) {

            $artist['verified'] = (bool)$artist['verified'];

            $artists[] = $artist;
        }

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | ALBUM
    |--------------------------------------------------------------------------
    */

        $stmt = $db->prepare("
        SELECT
            a.id,
            a.title,
            a.slug,
            a.cover_url,
            a.release_date,
            a.album_type,
            a.label
        FROM song_albums sa

        INNER JOIN albums a
            ON a.id = sa.album_id

        WHERE sa.song_id = ?

        ORDER BY a.release_date DESC

        LIMIT 1
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $album = $result->fetch_assoc();

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | GENRES
    |--------------------------------------------------------------------------
    */

        $stmt = $db->prepare("
        SELECT
            g.id,
            g.name,
            g.slug
        FROM song_genres sg

        INNER JOIN genres g
            ON g.id = sg.genre_id

        WHERE sg.song_id = ?

        ORDER BY g.name ASC
    ");

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        $genres = [];

        while ($genre = $result->fetch_assoc()) {
            $genres[] = $genre;
        }

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | BUILD RESPONSE
    |--------------------------------------------------------------------------
    */

        return [
            'id' => (int)$song['id'],
            'title' => $song['title'],
            'slug' => $song['slug'],

            'media' => [
                'audio_url' => $song['audio_url'],
                'cover_url' => $song['cover_url'],
                'duration_seconds' => $durationSeconds,
            ],

            'metadata' => [
                'language' => $song['language'],
                'release_date' => $song['release_date']
            ],

            'statistics' => [
                'play_count' => (int)$song['play_count'],
                'like_count' => (int)$song['like_count'],
                'download_count' => (int)$song['download_count']
            ],

            'artists' => $artists,

            'album' => $album ?: null,

            'genres' => $genres
        ];
    }

    public function filterByTags(
        string $tags,
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $db = Database::getInstance()->connection();

        $tagsArray = array_filter(
            array_map(
                'trim',
                explode(',', strtolower($tags))
            )
        );

        if (empty($tagsArray)) {
            return [
                'tracks' => [],
                'pagination' => $this->pagination(
                    $page,
                    $limit,
                    0
                )
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Build placeholders
    |--------------------------------------------------------------------------
    */

        $placeholders = implode(
            ',',
            array_fill(0, count($tagsArray), '?')
        );

        $types = str_repeat('s', count($tagsArray));

        /*
    |--------------------------------------------------------------------------
    | Count
    |--------------------------------------------------------------------------
    */

        $sql = "
        SELECT COUNT(DISTINCT s.id) AS total

        FROM songs s

        INNER JOIN song_genres sg
            ON sg.song_id = s.id

        INNER JOIN genres g
            ON g.id = sg.genre_id

        WHERE s.is_active = 1
        AND s.deleted_at IS NULL

        AND (
            LOWER(g.slug) IN ($placeholders)
            OR LOWER(g.name) IN ($placeholders)
        )
    ";

        $params = array_merge(
            $tagsArray,
            $tagsArray
        );

        $countTypes = $types . $types;

        $stmt = $db->prepare($sql);

        $stmt->bind_param(
            $countTypes,
            ...$params
        );

        $stmt->execute();

        $total = (int)$stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | Song IDs
    |--------------------------------------------------------------------------
    */

        $offset = ($page - 1) * $limit;

        $sql = "
        SELECT DISTINCT s.id

        FROM songs s

        INNER JOIN song_genres sg
            ON sg.song_id = s.id

        INNER JOIN genres g
            ON g.id = sg.genre_id

        WHERE s.is_active = 1
        AND s.deleted_at IS NULL

        AND (
            LOWER(g.slug) IN ($placeholders)
            OR LOWER(g.name) IN ($placeholders)
        )

        ORDER BY s.release_date DESC, s.id DESC

        LIMIT ? OFFSET ?
    ";

        $params[] = $limit;
        $params[] = $offset;

        $types = $countTypes . 'ii';

        $stmt = $db->prepare($sql);

        $stmt->bind_param(
            $types,
            ...$params
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['id'];
        }

        $stmt->close();

        $songs = $this->cardsByIds($songIds);

        return [
            'tracks' => $songs,

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    public function search(
        string $keyword,
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {

        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        $keyword = "%{$keyword}%";

        // -----------------------------------------
        // Total songs
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT COUNT(DISTINCT s.id) AS total

        FROM songs s

        LEFT JOIN song_artists sa
            ON sa.song_id = s.id

        LEFT JOIN artists a
            ON a.id = sa.artist_id

        WHERE
            s.is_active = 1
        AND
            s.deleted_at IS NULL
        AND
        (
            s.title LIKE ?
            OR s.slug LIKE ?
            OR a.name LIKE ?
            OR a.slug LIKE ?
        )
    ");

        $stmt->bind_param(
            "ssss",
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );

        $stmt->execute();

        $total = (int)$stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        // -----------------------------------------
        // Songs
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT DISTINCT

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

        FROM songs s

        LEFT JOIN song_artists sa
            ON sa.song_id = s.id

        LEFT JOIN artists a
            ON a.id = sa.artist_id

        WHERE
            s.is_active = 1
        AND
            s.deleted_at IS NULL
        AND
        (
            s.title LIKE ?
            OR s.slug LIKE ?
            OR a.name LIKE ?
            OR a.slug LIKE ?
        )

        ORDER BY
            s.title ASC

        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            "ssssii",
            $keyword,
            $keyword,
            $keyword,
            $keyword,
            $limit,
            $offset
        );

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
                'release_date' => $row['release_date'],

                'statistics' => [
                    'play_count' => (int)$row['play_count'],
                    'like_count' => (int)$row['like_count'],
                    'download_count' => (int)$row['download_count']
                ]
            ];
        }

        $stmt->close();

        $totalPages = $total > 0
            ? (int)ceil($total / $limit)
            : 0;

        return [
            'data' => $songs,

            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_previous' => $page > 1
            ]
        ];
    }

    public function latest(
        int $page = 1,
        int $limit = 20
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM songs
            WHERE is_active = 1
            AND deleted_at IS NULL
        ");

        $countStmt->execute();

        $total = (int) $countStmt
            ->get_result()
            ->fetch_assoc()['total'];

        $countStmt->close();

        /*
    |--------------------------------------------------------------------------
    | Latest Songs
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
            SELECT id
            FROM songs
            WHERE is_active = 1
            AND deleted_at IS NULL
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

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['id'];
        }

        $stmt->close();

        $tracks = $this->cardsByIds($songIds);

        return [
            'tracks' => $tracks,

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    public function popular(
        int $page = 1,
        int $limit = 20
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM songs
            WHERE is_active = 1
            AND deleted_at IS NULL
        ");

        $countStmt->execute();

        $total = (int) $countStmt
            ->get_result()
            ->fetch_assoc()['total'];

        $countStmt->close();

        /*
    |--------------------------------------------------------------------------
    | Popular
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
            SELECT id
            FROM songs
            WHERE is_active = 1
            AND deleted_at IS NULL
            ORDER BY
                play_count DESC,
                like_count DESC,
                download_count DESC,
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

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['id'];
        }

        $stmt->close();

        $tracks = $this->cardsByIds($songIds);

        return [
            'tracks' => $tracks,

            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    public function trending(
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $db = Database::getInstance()->connection();

        /*
    |--------------------------------------------------------------------------
    | Total trending songs
    |--------------------------------------------------------------------------
    */

        $result = $db->query("
        SELECT COUNT(DISTINCT h.song_id) AS total
        FROM history h
        INNER JOIN songs s
            ON s.id = h.song_id
        WHERE s.is_active = 1
        AND s.deleted_at IS NULL
        AND h.played_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");

        $total = (int)$result->fetch_assoc()['total'];

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Trending IDs
    |--------------------------------------------------------------------------
    */

        $stmt = $db->prepare("
        SELECT
            s.id,

            COUNT(h.id) AS recent_play_count,

            COALESCE(
                SUM(
                    CASE
                        WHEN h.completed = 1
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS completed_play_count

        FROM history h

        INNER JOIN songs s
            ON s.id = h.song_id

        WHERE s.is_active = 1
        AND s.deleted_at IS NULL

        AND h.played_at >= DATE_SUB(
            NOW(),
            INTERVAL 7 DAY
        )

        GROUP BY s.id

        ORDER BY
            recent_play_count DESC,
            completed_play_count DESC,
            s.release_date DESC,
            s.id DESC

        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            "ii",
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $id = (int)$row['id'];
            $songIds[] = $id;
        }

        $stmt->close();

        $songs = $this->cardsByIds($songIds);

        unset($song);

        return [
            'tracks' => $songs,
            'pagination' => $this->pagination(
                $page,
                $limit,
                $total
            )
        ];
    }

    public function increasePlayCount(int $songId): void
    {
        $stmt = $this->db->prepare("
            UPDATE songs
            SET play_count = play_count + 1
            WHERE id=?
        ");

        $stmt->bind_param("i", $songId);

        $stmt->execute();

        $stmt->close();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO songs
            (
                title,
                slug,
                description,
                lyrics,
                audio_url,
                cover_url,
                duration_seconds,
                language,
                release_date,
                track_number,
                disc_number
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->bind_param(
            "sssssssiiii",
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['lyrics'],
            $data['audio_url'],
            $data['cover_url'],
            $data['duration_seconds'],
            $data['language'],
            $data['release_date'],
            $data['track_number'],
            $data['disc_number']
        );

        $stmt->execute();

        $id = $stmt->insert_id;

        $stmt->close();

        return $id;
    }

    public function recommended(
        int $userId,
        int $limit = 10
    ): array {

        $limit = max(1, min($limit, MAX_LIMIT));

        /*
    |--------------------------------------------------------------------------
    | Recommended Songs
    |--------------------------------------------------------------------------
    |
    | Score:
    |   Followed artist  = +5
    |   Favorite artist  = +3
    |   History artist   = +2 per listen
    |
    */

        $stmt = $this->db->prepare("
        SELECT
            s.id,

            (
                COALESCE(follow_score.score, 0)
                +
                COALESCE(favorite_score.score, 0)
                +
                COALESCE(history_score.score, 0)
            ) AS recommendation_score

        FROM songs s

        INNER JOIN song_artists sa
            ON sa.song_id = s.id


        /*
        |--------------------------------------------------------------------------
        | Followed artists
        |--------------------------------------------------------------------------
        */

        LEFT JOIN (
            SELECT
                artist_id,
                5 AS score

            FROM artist_follows

            WHERE user_id = ?
        ) follow_score

            ON follow_score.artist_id = sa.artist_id


        /*
        |--------------------------------------------------------------------------
        | Favorite artists
        |--------------------------------------------------------------------------
        */

        LEFT JOIN (
            SELECT
                sa2.artist_id,
                3 AS score

            FROM favorites f

            INNER JOIN song_artists sa2
                ON sa2.song_id = f.song_id

            WHERE f.user_id = ?

            GROUP BY sa2.artist_id
        ) favorite_score

            ON favorite_score.artist_id = sa.artist_id


        /*
        |--------------------------------------------------------------------------
        | History artists
        |--------------------------------------------------------------------------
        */

        LEFT JOIN (
            SELECT
                sa3.artist_id,
                COUNT(*) * 2 AS score

            FROM history h

            INNER JOIN song_artists sa3
                ON sa3.song_id = h.song_id

            WHERE h.user_id = ?

            GROUP BY sa3.artist_id
        ) history_score

            ON history_score.artist_id = sa.artist_id


        WHERE
            s.is_active = 1
            AND s.deleted_at IS NULL


        /*
        |--------------------------------------------------------------------------
        | Must have some recommendation score
        |--------------------------------------------------------------------------
        */

        AND (
            COALESCE(follow_score.score, 0)
            +
            COALESCE(favorite_score.score, 0)
            +
            COALESCE(history_score.score, 0)
        ) > 0


        GROUP BY s.id


        ORDER BY
            recommendation_score DESC,
            s.play_count DESC,
            s.release_date DESC,
            s.id DESC


        LIMIT ?
    ");

        $stmt->bind_param(
            "iiii",
            $userId,
            $userId,
            $userId,
            $limit
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $tracks = [];

        while ($row = $result->fetch_assoc()) {

            $song = $this->find(
                (int)$row['id']
            );

            if ($song !== null) {
                $tracks[] = $song;
            }
        }

        $stmt->close();

        return [
            'tracks' => $tracks
        ];
    }

    public function addHistory(
        int $userId,
        int $songId,
        int $playDuration = 0,
        bool $completed = false
    ): void {

        $completed = $completed ? 1 : 0;

        $stmt = $this->db->prepare("
        INSERT INTO history
        (
            user_id,
            song_id,
            play_duration,
            completed
        )
        VALUES
        (
            ?,?,?,?
        )
    ");

        $stmt->bind_param(
            "iiii",
            $userId,
            $songId,
            $playDuration,
            $completed
        );

        $stmt->execute();

        $stmt->close();
    }

    public function addView(
        ?int $userId,
        int $songId
    ): void {

        $stmt = $this->db->prepare("
        INSERT INTO song_views
        (
            song_id,
            user_id,
            ip_address,
            device,
            platform
        )
        VALUES
        (
            ?,?,?,?,?
        )
    ");

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        $device = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $platform = 'Flutter';

        $stmt->bind_param(
            "iisss",
            $songId,
            $userId,
            $ip,
            $device,
            $platform
        );

        $stmt->execute();

        $stmt->close();
    }

    /**
     * Lightweight song representation for list/home endpoints.
     * This deliberately excludes lyrics, description, genres, album,
     * statistics and timestamps. Full details remain available through find().
     */
    public function cardsByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn(int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                s.language,
                s.release_date,
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        a.id, '::', a.name, '::', a.slug
                    )
                    ORDER BY
                        CASE WHEN sa.role = 'primary' THEN 0 ELSE 1 END,
                        a.name ASC
                    SEPARATOR '||'
                ) AS artist_data
            FROM songs s
            LEFT JOIN song_artists sa
                ON sa.song_id = s.id
            LEFT JOIN artists a
                ON a.id = sa.artist_id
                AND a.deleted_at IS NULL
            WHERE s.id IN ($placeholders)
            GROUP BY
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                s.language,
                s.release_date
        ");

        $params = [$types];
        foreach ($ids as $id) {
            $params[] = $id;
        }
        $this->bindDynamic($stmt, $params);
        $stmt->execute();

        $result = $stmt->get_result();
        $byId = [];

        while ($row = $result->fetch_assoc()) {
            $artists = [];
            if (!empty($row['artist_data'])) {
                foreach (explode('||', $row['artist_data']) as $artist) {
                    $parts = explode('::', $artist, 3);
                    if (count($parts) === 3) {
                        $artists[] = [
                            'id' => (int)$parts[0],
                            'name' => $parts[1],
                            'slug' => $parts[2]
                        ];
                    }
                }
            }

            $seconds = (int)$row['duration_seconds'];
            $byId[(int)$row['id']] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'media' => [
                    'cover_url' => $row['cover_url'],
                    'audio_url' => $row['audio_url'],
                    'duration_seconds' => $seconds,
                ],
                'metadata' => [
                    'language' => $row['language'],
                    'release_date' => $row['release_date']
                ],
                'artists' => $artists
            ];
        }

        $stmt->close();

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

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

    private function bindDynamic(
        \mysqli_stmt $stmt,
        array $params
    ): void {
        $refs = [];

        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }

        call_user_func_array(
            [$stmt, 'bind_param'],
            $refs
        );
    }
}