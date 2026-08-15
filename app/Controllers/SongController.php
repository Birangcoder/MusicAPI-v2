<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Song;
use App\Core\Controller;

class SongController extends Controller
{
    private Song $song;

    public function __construct()
    {
        $this->song = new Song();
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $this->success(
            $this->song->all(
                $page,
                $limit
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs/{id}
    |--------------------------------------------------------------------------
    */

    public function show(int $id): void
    {
        $song = $this->song->find($id);

        if (!$song) {
            $this->error(
                'Song not found.',
                404
            );

            return;
        }

        $this->success($song);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs/new
    |--------------------------------------------------------------------------
    */

    public function latest(): void
    {
        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $this->success(
            $this->song->latest(
                $page,
                $limit
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs/popular
    |--------------------------------------------------------------------------
    */

    public function popular(): void
    {
        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $this->success(
            $this->song->popular(
                $page,
                $limit
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs/trending
    |--------------------------------------------------------------------------
    */

    public function trending(): void
    {
        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $this->success(
            $this->song->trending(
                $page,
                $limit
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /songs/recommended
    |--------------------------------------------------------------------------
    */

    public function recommended(): void
    {
        $userId = 0;

        try {
            $userId = $this->userId();
        } catch (\Throwable $e) {
            // Guest user
        }

        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $this->success(
            $this->song->recommended(
                $userId,
                $page,
                $limit
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /tracks?tags=Hindi
    |--------------------------------------------------------------------------
    */

    public function filter(): void
    {
        $tags = trim(
            (string)($_GET['tags'] ?? '')
        );

        if ($tags === '') {
            $this->error(
                'tags parameter is required.',
                400
            );

            return;
        }

        $page = max(
            1,
            (int)($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int)($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $result = $this->song->filterByTags(
            $tags,
            $page,
            $limit
        );

        $this->success([
            'filter' => [
                'type' => 'genre',
                'value' => $tags
            ],

            'tracks' => $result['tracks'],

            'pagination' =>
            $result['pagination']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /songs/{id}/play
    |--------------------------------------------------------------------------
    */

    public function play(int $id): void
    {
        $userId = $this->userId();
        $body = $this->body();

        $song = $this->song->find($id);

        if (!$song) {
            Response::notFound(
                'Song not found.'
            );

            return;
        }

        $this->song->increasePlayCount($id);

        $this->song->addHistory(
            $userId,
            $id,
            (int)($body['play_duration'] ?? 0),
            (bool)($body['completed'] ?? false),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        $this->song->addView(
            $userId,
            $id
        );

        $this->success(
            $this->song->find($id),
            'Play recorded.'
        );
    }
    
    /*
    |--------------------------------------------------------------------------
    | POST /songs/{id}/progress
    |--------------------------------------------------------------------------
    */

    public function progress(int $id): void
    {
        $userId = $this->userId();

        if ($userId <= 0) {
            $this->error('Authentication required', 401);
            return;
        }

        $body = json_decode(
            file_get_contents('php://input'),
            true
        );

        $playDuration = (int)($body['play_duration'] ?? 0);
        $completed = (bool)($body['completed'] ?? false);

        if ($playDuration < 0) {
            $playDuration = 0;
        }

        $this->song->updateProgress(
            $userId,
            $id,
            $playDuration,
            $completed
        );

        $this->success([
            'song_id' => $id,
            'play_duration' => $playDuration,
            'completed' => $completed,
        ]);
    }
}