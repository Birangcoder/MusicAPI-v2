<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Models\Song;

class History extends Model
{
    public function all(
        int $userId,
        int $page = 1,
        int $limit = DEFAULT_LIMIT
    ): array {
        $page = max(1, $page);
        $limit = max(1, min($limit, MAX_LIMIT));

        $offset = ($page - 1) * $limit;

        /*
    |--------------------------------------------------------------------------
    | Total History
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
        SELECT COUNT(*) AS total
        FROM history
        WHERE user_id = ?
    ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $total = (int)$stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | History Song IDs
    |--------------------------------------------------------------------------
    */

        $stmt = $this->db->prepare("
        SELECT
            song_id
        FROM history
        WHERE user_id = ?
        ORDER BY played_at DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            "iii",
            $userId,
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['song_id'];
        }

        $stmt->close();

        /*
    |--------------------------------------------------------------------------
    | Get Songs Using Same Card Format
    |--------------------------------------------------------------------------
    */

        $songs = empty($songIds)
            ? []
            : (new Song())->cardsByIds($songIds);

        /*
    |--------------------------------------------------------------------------
    | Preserve History Order
    |--------------------------------------------------------------------------
    */

        $songsById = array_column(
            $songs,
            null,
            'id'
        );

        $tracks = [];

        foreach ($songIds as $songId) {
            if (isset($songsById[$songId])) {
                $tracks[] = $songsById[$songId];
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

        $totalPages = $total > 0
            ? (int)ceil($total / $limit)
            : 0;

        return [
            'tracks' => $tracks,

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

    public function add(
        int $userId,
        int $songId,
        int $playDuration = 0,
        bool $completed = false,
        ?string $device = null
    ): bool {

        $stmt = $this->db->prepare("
            INSERT INTO history
            (
                user_id,
                song_id,
                play_duration,
                completed,
                device
            )
            VALUES
            (
                ?,?,?,?,?
            )
        ");

        $completed = $completed ? 1 : 0;

        $stmt->bind_param(
            "iiiis",
            $userId,
            $songId,
            $playDuration,
            $completed,
            $device
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function clear(int $userId): bool
    {
        $stmt = $this->db->prepare("
            DELETE
            FROM history
            WHERE user_id=?
        ");

        $stmt->bind_param(
            "i",
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function remove(
        int $userId,
        int $historyId
    ): bool {

        $stmt = $this->db->prepare("
            DELETE
            FROM history
            WHERE
                id=?
            AND
                user_id=?
        ");

        $stmt->bind_param(
            "ii",
            $historyId,
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function total(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) total
            FROM history
            WHERE user_id=?
        ");

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        $total = $stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        return (int)$total;
    }
}