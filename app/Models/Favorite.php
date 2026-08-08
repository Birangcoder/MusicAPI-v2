<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Favorite extends Model
{
    public function all(int $userId, int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM favorites
        WHERE user_id = ?
    ");

        $countStmt->bind_param('i', $userId);
        $countStmt->execute();

        $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

        $countStmt->close();

        $stmt = $db->prepare("
        SELECT
            f.id,
            f.song_id,
            f.created_at,
            s.title,
            s.slug,
            s.cover_url,
            s.audio_url,
            s.duration_seconds
        FROM favorites f
        INNER JOIN songs s ON s.id = f.song_id
        WHERE f.user_id = ?
        ORDER BY f.id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param('iii', $userId, $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $favorites = [];

        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }

        $stmt->close();

        return [
            'items' => $favorites,
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

    public function exists(int $userId, int $songId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM favorites
            WHERE user_id=?
            AND song_id=?
            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $userId,
            $songId
        );

        $stmt->execute();

        $stmt->store_result();

        $exists = $stmt->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    public function add(int $userId, int $songId): bool
    {
        if ($this->exists($userId, $songId)) {
            return true;
        }

        $stmt = $this->db->prepare("
            INSERT INTO favorites
            (
                user_id,
                song_id
            )
            VALUES
            (
                ?,
                ?
            )
        ");

        $stmt->bind_param(
            "ii",
            $userId,
            $songId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function remove(int $userId, int $songId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM favorites
            WHERE user_id=?
            AND song_id=?
        ");

        $stmt->bind_param(
            "ii",
            $userId,
            $songId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function total(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) total
            FROM favorites
            WHERE user_id=?
        ");

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $total = $stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        return (int)$total;
    }
}