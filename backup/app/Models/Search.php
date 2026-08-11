<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Search extends Model
{
    private Song $song;
    private Artist $artist;
    private Album $album;
    private Genre $genre;

    public function __construct()
    {
        $this->song = new Song();
        $this->artist = new Artist();
        $this->album = new Album();
        $this->genre = new Genre();
    }

    public function search(string $query, int $page = 1): array
    {
        $page = max(1, $page);

        $limit = DEFAULT_LIMIT;
        $offset = ($page - 1) * $limit;

        $db = \App\Core\Database::getInstance()->connection();

        $search = '%' . $query . '%';

        $countStmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM songs
        WHERE is_active = 1
        AND (
            title LIKE ?
            OR slug LIKE ?
        )
    ");

        $countStmt->bind_param('ss', $search, $search);
        $countStmt->execute();

        $total = (int)$countStmt->get_result()->fetch_assoc()['total'];

        $countStmt->close();

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
            download_count
        FROM songs
        WHERE is_active = 1
        AND (
            title LIKE ?
            OR slug LIKE ?
        )
        ORDER BY play_count DESC
        LIMIT ? OFFSET ?
    ");

        $stmt->bind_param(
            'ssii',
            $search,
            $search,
            $limit,
            $offset
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        $stmt->close();

        return [
            'items' => $songs,
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
}