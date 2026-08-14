<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\History;
use App\Core\Controller;

class HistoryController extends Controller
{
    private History $history;

    public function __construct()
    {
        $this->history = new History();
    }

    public function index(): void
    {
        $userId = $this->userId();

        $this->success(
            $this->history->all(
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

        $this->history->add(
            $userId,
            (int)$body['song_id'],
            (int)($body['play_duration'] ?? 0),
            (bool)($body['completed'] ?? false)
        );

        Response::created(
            null,
            "History added."
        );
    }

    public function destroy(int $historyId): void
    {
        $userId = $this->userId();

        $this->history->remove(
            $userId,
            $historyId
        );

        $this->success(
            null,
            "History deleted."
        );
    }

    public function clear(): void
    {
        $userId = $this->userId();

        $this->history->clear($userId);

        $this->success(
            null,
            "History cleared."
        );
    }
}