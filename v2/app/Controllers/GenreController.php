<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Genre;
use App\Core\Controller;

class GenreController extends Controller
{
    private Genre $genre;

    public function __construct()
    {
        $this->genre = new Genre();
    }

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->genre->all($page);

        $this->success($result);
    }

    public function show(int $id): void
    {
        $genre = $this->genre->find($id);

        if (!$genre) {
            Response::notFound('Genre not found.');
        }

        $this->success($genre);
    }

    public function search(): void
    {
        $keyword = trim($_GET['q'] ?? '');

        if ($keyword === '') {
            $this->success([]);
        }

        $this->success(
            $this->genre->search($keyword)
        );
    }
}