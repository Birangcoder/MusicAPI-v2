<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Favorite;
use App\Core\Controller;

class FavoriteController extends Controller
{
    private Favorite $favorite;

    public function __construct()
    {
        $this->favorite = new Favorite();
    }

    public function index(): void
    {
        $userId = $this->userId();

        $this->success(
            $this->favorite->all(
                $userId,
                (int)($_GET['page'] ?? 1),
                (int)($_GET['limit'] ?? DEFAULT_LIMIT)
            )
        );
    }

    public function store(): void
    {
        $userId = $this->userId();

        $body = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        if (!isset($body['song_id'])) {
            Response::error(
                "song_id is required.",
                422
            );
        }

        $this->favorite->add(
            $userId,
            (int)$body['song_id']
        );

        Response::created(
            null,
            "Song added to favorites."
        );
    }

    public function destroy(int $songId): void
    {
        $userId = $this->userId();

        $this->favorite->remove(
            $userId,
            $songId
        );

        $this->success(
            null,
            "Song removed from favorites."
        );
    }
}