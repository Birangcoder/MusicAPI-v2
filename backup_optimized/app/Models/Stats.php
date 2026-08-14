<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Stats extends Model
{
    public function profile(int $userId): array
    {
        return [
            'favorite_songs' => $this->count('favorites', $userId),
            'playlists'       => $this->count('playlists', $userId),
            'history'         => $this->count('history', $userId),
            'following'       => $this->count('artist_follows', $userId),
        ];
    }

    public function totalSongs(): int
    {
        return $this->scalar("
            SELECT COUNT(*)
            FROM songs
            WHERE is_active=1
            AND deleted_at IS NULL
        ");
    }

    public function totalArtists(): int
    {
        return $this->scalar("
            SELECT COUNT(*)
            FROM artists
            WHERE deleted_at IS NULL
        ");
    }

    public function totalAlbums(): int
    {
        return $this->scalar("
            SELECT COUNT(*)
            FROM albums
            WHERE deleted_at IS NULL
        ");
    }

    public function totalGenres(): int
    {
        return $this->scalar("
            SELECT COUNT(*)
            FROM genres
        ");
    }

    public function totalUsers(): int
    {
        return $this->scalar("
            SELECT COUNT(*)
            FROM users
            WHERE deleted_at IS NULL
        ");
    }

    private function count(string $table, int $userId): int
    {
        $allowed = [
            'favorites',
            'playlists',
            'history',
            'artist_follows'
        ];

        if (!in_array($table, $allowed, true)) {
            return 0;
        }

        return $this->scalar("
            SELECT COUNT(*)
            FROM {$table}
            WHERE user_id={$userId}
        ");
    }

    private function scalar(string $sql): int
    {
        $result = $this->db->query($sql);

        return (int)$result
            ->fetch_row()[0];
    }
}