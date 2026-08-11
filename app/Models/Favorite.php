<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Models\Song;

class Favorite extends Model
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
        // Total favorites
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT COUNT(*) AS total
        FROM favorites
        WHERE user_id = ?
    ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $total = (int)$stmt
            ->get_result()
            ->fetch_assoc()['total'];

        $stmt->close();

        // -----------------------------------------
        // Favorite records
        // -----------------------------------------

        $stmt = $this->db->prepare("
        SELECT
            id,
            song_id,
            created_at
        FROM favorites
        WHERE user_id = ?
        ORDER BY created_at DESC
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
        $favorites = [];

        foreach ($rows as $row) {
            if (!isset($songsById[$row['song_id']])) {
                continue;
            }
            $favorites[] = [
                'id' => $row['id'],
                'created_at' => $row['created_at'],
                'song' => $songsById[$row['song_id']]
            ];
        }

        $totalPages = $total > 0
            ? (int)ceil($total / $limit)
            : 0;

        return [
            'data' => $favorites,

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