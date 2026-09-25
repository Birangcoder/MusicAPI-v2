<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Genre extends Model
{
    public function all(int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $totalResult = $db->query("
        SELECT COUNT(*) AS total
        FROM genres
    ");

        $total = (int)$totalResult->fetch_assoc()['total'];

        $stmt = $db->prepare("
        SELECT
            id,
            name,
            slug
        FROM genres
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();

        $result = $stmt->get_result();

        $genres = [];

        while ($row = $result->fetch_assoc()) {
            $genres[] = $row;
        }

        $stmt->close();

        return [
            'items' => $genres,
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

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                slug,
                image_url
            FROM genres
            WHERE id=?
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $genre = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$genre) {
            return null;
        }

        $genre['songs'] = $this->songs($id);

        return $genre;
    }

    public function songs(int $genreId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                s.play_count
            FROM songs s
            INNER JOIN song_genres sg
                ON sg.song_id = s.id
            WHERE
                sg.genre_id = ?
            AND
                s.deleted_at IS NULL
            ORDER BY
                s.play_count DESC
        ");

        $stmt->bind_param("i", $genreId);

        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        $stmt->close();

        return $songs;
    }

    public function search(string $keyword): array
    {
        $keyword = "%{$keyword}%";

        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                slug,
                image_url
            FROM genres
            WHERE
                name LIKE ?
            ORDER BY name ASC
        ");

        $stmt->bind_param("s", $keyword);

        $stmt->execute();

        $result = $stmt->get_result();

        $genres = [];

        while ($row = $result->fetch_assoc()) {
            $genres[] = $row;
        }

        $stmt->close();

        return $genres;
    }
}