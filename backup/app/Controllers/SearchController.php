<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Song;
use App\Models\Artist;

class SearchController extends Controller
{
    private Song $song;
    private Artist $artist;

    public function __construct()
    {
        $this->song = new Song();
        $this->artist = new Artist();
    }

    public function index(): void
    {
        $query = trim($_GET['q'] ?? '');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = (int)($_GET['limit'] ?? DEFAULT_LIMIT);

        $limit = max(1, min($limit, MAX_LIMIT));

        if ($query === '') {
            $this->success([
                'query' => '',
                'songs' => [
                    'data' => [],
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => 0,
                        'total_pages' => 0,
                        'has_next' => false,
                        'has_previous' => false
                    ]
                ],
                'artists' => [
                    'data' => [],
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => 0,
                        'total_pages' => 0,
                        'has_next' => false,
                        'has_previous' => false
                    ]
                ]
            ]);

            return;
        }

        $songs = $this->song->search($query, $page, $limit);

        $artists = $this->artist->search($query, $page, $limit);

        $this->success([
            'query' => $query,
            'songs' => $songs,
            'artists' => $artists
        ]);
    }
}