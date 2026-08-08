<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class History extends Model
{
    public function all(int $userId, int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM history
        WHERE user_id = ?
    ");

        $countStmt->bind_param('i', $userId);
        $countStmt->execute();

        $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

        $countStmt->close();

        $stmt = $db->prepare("
        SELECT
            h.id,
            h.song_id,
            h.played_at,
            s.title,
            s.slug,
            s.cover_url,
            s.audio_url,
            s.duration_seconds
        FROM history h
        INNER JOIN songs s ON s.id = h.song_id
        WHERE h.user_id = ?
        ORDER BY h.played_at DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $history = [];

        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        $stmt->close();

        return [
            'items' => $history,
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