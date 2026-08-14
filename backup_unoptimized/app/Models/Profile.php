<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Profile extends Model
{
    public function get(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                email,
                avatar_url,
                country,
                birth_date,
                gender,
                bio,
                is_premium,
                status,
                created_at
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$user) {
            return null;
        }

        $user['favorite_count'] = $this->count(
            "favorites",
            $userId
        );

        $user['playlist_count'] = $this->count(
            "playlists",
            $userId
        );

        $user['history_count'] = $this->count(
            "history",
            $userId
        );

        return $user;
    }

    public function update(
        int $userId,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE users
            SET
                name=?,
                avatar_url=?,
                country=?,
                birth_date=?,
                gender=?,
                bio=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssssssi",
            $data['name'],
            $data['avatar_url'],
            $data['country'],
            $data['birth_date'],
            $data['gender'],
            $data['bio'],
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    private function count(
        string $table,
        int $userId
    ): int {

        $allowed = [
            'favorites',
            'playlists',
            'history'
        ];

        if (!in_array($table, $allowed, true)) {
            return 0;
        }

        $result = $this->db->query("
            SELECT COUNT(*) total
            FROM {$table}
            WHERE user_id={$userId}
        ");

        return (int)$result
            ->fetch_assoc()['total'];
    }
}