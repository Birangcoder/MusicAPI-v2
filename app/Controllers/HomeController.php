<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Song;
use App\Models\Artist;
use App\Models\Album;
use App\Core\Controller;

class HomeController extends Controller
{
    private Song $song;
    private Artist $artist;
    private Album $album;

    public function __construct()
    {
        $this->song = new Song();
        $this->artist = new Artist();
        $this->album = new Album();
    }

    public function index(): void
    {
        $userId = 0;

        $headers = function_exists('getallheaders')
            ? getallheaders()
            : [];

        if (!empty($headers['Authorization']) || !empty($headers['authorization'])) {
            try {
                $userId = AuthMiddleware::optionalUserId();
            } catch (\Throwable $e) {
                $userId = 0;
            }
        }
        
        $trending = $this->song->trending(1, 5);
        $popular = $this->song->popular(1, 5);
        $latest = $this->song->latest(1, 5);
        $recommended = $this->song->recommended($userId, 5);

        $this->success([
            // 'banner' => $this->banners(),
            'trending' => $trending['tracks'],
            'popular' => $popular['tracks'],
            'new_release' => $latest['tracks'],
            'recommended' => $recommended['tracks'],
            'top_artists' => $this->artist->homeCards(5),
            'top_albums' => $this->album->homeCards(5),
            'continue_listening' => $this->continueListening($userId)
        ]);
    }

    private function banners(): array
    {
        $db = \App\Core\Database::getInstance()->connection();

        $result = $db->query("
            SELECT
                id,
                title,
                subtitle,
                image_url,
                redirect_type,
                redirect_id,
                external_url
            FROM banners
            WHERE
                is_active=1
            AND
                (
                    start_date IS NULL
                    OR start_date<=NOW()
                )
            AND
                (
                    end_date IS NULL
                    OR end_date>=NOW()
                )
            ORDER BY sort_order ASC
        ");

        $banners = [];

        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }

        return $banners;
    }

    private function continueListening(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $db = \App\Core\Database::getInstance()->connection();
        $stmt = $db->prepare("
            SELECT song_id, MAX(played_at) AS last_played
            FROM history
            WHERE user_id = ?
            GROUP BY song_id
            ORDER BY last_played DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        $songIds = [];
        while ($row = $result->fetch_assoc()) {
            $songIds[] = (int)$row['song_id'];
            $rows[(int)$row['song_id']] = $row['last_played'];
        }
        $stmt->close();

        $songs = $this->song->cardsByIds($songIds);
        foreach ($songs as &$song) {
            $song['last_played'] = $rows[$song['id']] ?? null;
        }
        unset($song);

        return $songs;
    }
}