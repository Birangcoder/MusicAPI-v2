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
        
        $artists = $this->artist->allPaginated(1, 10);
        $albums = $this->album->allPaginated(1, 10);
        
        $this->success([
            // 'banner' => $this->banners(),
            'trending' => $this->song->trending(),
            'popular' => $this->song->popular(10),
            'new_release' => $this->song->latest(10),
            'recommended' => $this->song->recommended($userId),
            'top_artists' => $artists['data'],
            'top_albums' => $albums['data'],
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
            SELECT
                s.id,
                s.title,
                s.slug,
                s.cover_url,
                s.audio_url,
                s.duration_seconds,
                MAX(h.played_at) AS last_played
            FROM history h

            INNER JOIN songs s
                ON s.id=h.song_id

            WHERE
                h.user_id=?

            GROUP BY s.id

            ORDER BY last_played DESC

            LIMIT 10
        ");

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        $stmt->close();

        return $songs;
    }
}