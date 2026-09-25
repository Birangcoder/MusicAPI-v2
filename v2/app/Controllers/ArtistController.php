<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Artist;

class ArtistController extends Controller
{
    private Artist $artist;

    public function __construct()
    {
        $this->artist = new Artist();
    }

    /*
    |--------------------------------------------------------------------------
    | GET /artists
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

        $result = $this->artist->allPaginated(
            $page,
            $limit
        );

        $this->success([
            'artists' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /artists/{id}
    |--------------------------------------------------------------------------
    */

    public function show(int $id): void
    {
        $artist = $this->artist->find($id);

        if (!$artist) {
            $this->error(
                'Artist not found.',
                404
            );

            return;
        }

        /*
         * Do NOT wrap it inside:
         *
         * [
         *     'artist' => $artist
         * ]
         *
         * Keep the same artist object format as /artists.
         */

        $this->success($artist);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /artists/{id}/tracks
    |--------------------------------------------------------------------------
    */

    public function tracks(int $id): void
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

        /*
        |--------------------------------------------------------------------------
        | Check artist
        |--------------------------------------------------------------------------
        */

        $artist = $this->artist->find($id);

        if (!$artist) {
            $this->error(
                'Artist not found.',
                404
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get tracks
        |--------------------------------------------------------------------------
        */

        $result = $this->artist->tracks(
            $id,
            $page,
            $limit
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $this->success([
            'tracks' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /artists/{id}/albums
    |--------------------------------------------------------------------------
    */

    public function albums(int $id): void
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

        /*
        |--------------------------------------------------------------------------
        | Check artist
        |--------------------------------------------------------------------------
        */

        $artist = $this->artist->find($id);

        if (!$artist) {
            $this->error(
                'Artist not found.',
                404
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get albums
        |--------------------------------------------------------------------------
        */

        $result = $this->artist->albums(
            $id,
            $page,
            $limit
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $this->success([
            'albums' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }
}