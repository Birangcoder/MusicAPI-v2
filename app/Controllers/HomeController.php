<?php

declare(strict_types=1);

namespace App\Controllers;

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
        $totalStart = microtime(true);

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

        $times = [];

        $start = microtime(true);
        $trending = $this->song->trending(1, 5);
        $times['trending_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $popular = $this->song->popular(1, 5);
        $times['popular_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $latest = $this->song->latest(1, 5);
        $times['latest_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $recommended = $userId > 0
            ? $this->song->recommended($userId, 1, 5)
            : ['tracks' => []];

        $times['recommended_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $topArtists = $this->artist->homeCards(5);
        $times['artists_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $topAlbums = $this->album->homeCards(5);
        $times['albums_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $start = microtime(true);
        $continueListening = $this->continueListening($userId, 5);
        $times['continue_listening_ms'] = round(
            (microtime(true) - $start) * 1000,
            2
        );

        $times['total_ms'] = round(
            (microtime(true) - $totalStart) * 1000,
            2
        );

        $this->success([
            'trending' => $trending['tracks'],
            'popular' => $popular['tracks'],
            'new_release' => $latest['tracks'],
            'recommended' => $recommended['tracks'],
            'top_artists' => $topArtists,
            'top_albums' => $topAlbums,
            'continue_listening' => $continueListening,
            '_performance' => $times,
        ]);
    }

    private function continueListening(
        int $userId,
        int $limit = 10
    ): array {
        if ($userId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, MAX_LIMIT));

        $db = \App\Core\Database::getInstance()->connection();

        $stmt = $db->prepare("
        SELECT
            song_id,
            MAX(played_at) AS last_played
        FROM history
        WHERE user_id = ?
        GROUP BY song_id
        ORDER BY last_played DESC
        LIMIT ?
    ");

        $stmt->bind_param(
            "ii",
            $userId,
            $limit
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $rows = [];
        $songIds = [];

        while ($row = $result->fetch_assoc()) {
            $songId = (int) $row['song_id'];

            $songIds[] = $songId;
            $rows[$songId] = $row['last_played'];
        }

        $stmt->close();

        if (empty($songIds)) {
            return [];
        }

        $songs = $this->song->cardsByIds($songIds);

        foreach ($songs as &$song) {
            $song['last_played'] =
                $rows[$song['id']] ?? null;
        }

        unset($song);

        return $songs;
    }
}