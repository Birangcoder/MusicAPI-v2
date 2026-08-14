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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = (int)($_GET['limit'] ?? DEFAULT_LIMIT);

        $result = $this->artist->allPaginated($page, $limit);

        $this->success(
            [
                'artists' => $result['data'],
                'pagination' => $result['pagination']
            ],
            'Success'
        );
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
            $this->error('Artist not found.', 404);
            return;
        }

        $this->success([
            'artist' => $artist
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /artists/{id}/tracks
    |--------------------------------------------------------------------------
    */

    public function tracks(int $id): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $page = isset($_GET['page'])
            ? max(1, (int)$_GET['page'])
            : 1;

        $limit = isset($_GET['limit'])
            ? (int)$_GET['limit']
            : DEFAULT_LIMIT;

        $limit = max(1, min($limit, MAX_LIMIT));

        /*
        |--------------------------------------------------------------------------
        | Check artist
        |--------------------------------------------------------------------------
        */

        $artist = $this->artist->find($id);

        if (!$artist) {
            $this->error('Artist not found.', 404);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get artist tracks
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
            'artist' => [
                'id' => (int)$artist['id'],
                'name' => $artist['name'],
                'slug' => $artist['slug'],
                'image_url' => $artist['image_url'],
                'verified' => (bool)$artist['verified']
            ],

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
        $page = isset($_GET['page'])
            ? max(1, (int)$_GET['page'])
            : 1;

        $limit = isset($_GET['limit'])
            ? (int)$_GET['limit']
            : DEFAULT_LIMIT;

        $limit = max(1, min($limit, MAX_LIMIT));

        $artist = $this->artist->find($id);

        if (!$artist) {
            $this->error('Artist not found.', 404);
            return;
        }

        $result = $this->artist->albums(
            $id,
            $page,
            $limit
        );

        $this->success([
            'artist' => [
                'id' => (int)$artist['id'],
                'name' => $artist['name'],
                'slug' => $artist['slug'],
                'image_url' => $artist['image_url'],
                'verified' => (bool)$artist['verified']
            ],

            'albums' => $result['data'],

            'pagination' => $result['pagination']
        ]);
    }
}