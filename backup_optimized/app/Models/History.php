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

        // -----------------------------------------
        // Total history
        // -----------------------------------------

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

        // -----------------------------------------
        // History records
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT
            id,
            song_id,
            played_at,
            play_duration,
            completed,
            device
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

        $rows = [];
        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int)$row['id'];
            $row['song_id'] = (int)$row['song_id'];
            $rows[] = $row;
            $songIds[] = (int)$row['song_id'];
        }

        $stmt->close();

        $songsById = (new Song())->cardsByIds($songIds);
        $songsById = array_column($songsById, null, 'id');
        $history = [];

        foreach ($rows as $row) {
            if (!isset($songsById[$row['song_id']])) {
                continue;
            }
            $history[] = [
                'id' => $row['id'],
                'played_at' => $row['played_at'],
                'play_duration' => (int)$row['play_duration'],
                'completed' => (bool)$row['completed'],
                'device' => $row['device'],
                'song' => $songsById[$row['song_id']]
            ];
        }

        $totalPages = $total > 0
            ? (int)ceil($total / $limit)
            : 0;

        return [
            'data' => $history,

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
        bool $completed = false
    ): bool {

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

        $completed = $completed ? 1 : 0;

        $stmt->bind_param(
            "iiii",
            $userId,
            $songId,
            $playDuration,
            $completed
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