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

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = (int)($_GET['limit'] ?? DEFAULT_LIMIT);

        $result = $this->album->allPaginated($page, $limit);

        $this->success(
            [
                'albums' => $result['data'],
                'pagination' => $result['pagination']
            ],
            'Success'
        );
    }

    public function tracks(int $id): void
    {
        $album = $this->album->findWithTracks($id);

        if (!$album) {
            $this->error('Album not found.', 404);
            return;
        }

        $this->success($album);
    }

    public function show(int $id): void
    {
        $album = $this->album->find($id);

        if (!$album) {
            Response::notFound('Album not found.');
        }

        $this->success($album);
    }

    public function search(): void
    {
        $keyword = trim(
            $_GET['q'] ?? ''
        );

        if ($keyword === '') {
            $this->success([]);
        }

        $this->success(
            $this->album->search($keyword)
        );
    }
}