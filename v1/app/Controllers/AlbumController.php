<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Album;
use App\Core\Controller;

class AlbumController extends Controller
{
    private Album $album;

    public function __construct()
    {
        $this->album = new Album();
    }

    /**
     * GET /albums
     */
    public function index(): void
    {
        $page = max(
            1,
            (int) ($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int) ($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $result = $this->album->allPaginated(
            $page,
            $limit
        );

        $this->success(
            [
                'albums' => $result['data'],
                'pagination' => $result['pagination']
            ],
            'Success'
        );
    }

    /**
     * GET /albums/{id}
     */
    public function show(int $id): void
    {
        $album = $this->album->find($id);

        if (!$album) {
            Response::notFound('Album not found.');
            return;
        }

        $this->success(
            $album,
            'Success'
        );
    }

    /**
     * GET /albums/{id}/tracks
     */
    public function tracks(int $id): void
    {
        $page = max(
            1,
            (int) ($_GET['page'] ?? 1)
        );

        $limit = max(
            1,
            min(
                (int) ($_GET['limit'] ?? DEFAULT_LIMIT),
                MAX_LIMIT
            )
        );

        $album = $this->album->find($id);

        if (!$album) {
            Response::notFound('Album not found.');
            return;
        }

        $result = $this->album->tracks(
            $id,
            $page,
            $limit
        );

        $this->success(
            [
                'album' => [
                    'id' => (int) $album['id'],
                    'title' => $album['title'],
                    'slug' => $album['slug'],
                    'cover_url' => $album['cover_url'],
                    'metadata' => $album['metadata']
                ],

                'tracks' => $result['data'],

                'pagination' => $result['pagination']
            ],
            'Success'
        );
    }

    /**
     * GET /albums/search?q=
     */
    public function search(): void
    {
        $keyword = trim(
            (string) ($_GET['q'] ?? '')
        );

        if ($keyword === '') {
            $this->success(
                [],
                'Success'
            );

            return;
        }

        $this->success(
            $this->album->search($keyword),
            'Success'
        );
    }
}