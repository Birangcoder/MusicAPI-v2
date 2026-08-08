<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Playlist;
use App\Core\Controller;

class PlaylistController extends Controller
{
    private Playlist $playlist;

    public function __construct()
    {
        $this->playlist = new Playlist();
    }

    public function tracks(int $id): void
    {
        $playlist = $this->playlist->findWithTracks($id);

        if (!$playlist) {
            $this->error('Playlist not found.', 404);
            return;
        }

        $this->success($playlist);
    }

    public function index(): void
    {
        $userId = $this->userId();

        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->playlist->all($userId, $page);

        $this->success($result);
    }

    public function show(int $id): void
    {
        $userId = $this->userId();

        $playlist = $this->playlist->find(
            $id,
            $userId
        );

        if (!$playlist) {
            Response::notFound(
                "Playlist not found."
            );
        }

        $this->success($playlist);
    }

    public function store(): void
    {
        $userId = $this->userId();

        $body = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        if (empty($body['title'])) {
            Response::error(
                "title is required.",
                422
            );
        }

        $playlistId = $this->playlist->create(
            $userId,
            [
                'title' => $body['title'],
                'description' => $body['description'] ?? '',
                'cover_url' => $body['cover_url'] ?? '',
                'is_public' => (int)($body['is_public'] ?? 1)
            ]
        );

        Response::created([
            'playlist_id' => $playlistId
        ]);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();

        $body = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        $this->playlist->update(
            $id,
            $userId,
            [
                'title' => $body['title'] ?? '',
                'description' => $body['description'] ?? '',
                'cover_url' => $body['cover_url'] ?? '',
                'is_public' => (int)($body['is_public'] ?? 1)
            ]
        );

        $this->success(
            null,
            "Playlist updated."
        );
    }

    public function destroy(int $id): void
    {
        $userId = $this->userId();

        $this->playlist->delete(
            $id,
            $userId
        );

        $this->success(
            null,
            "Playlist deleted."
        );
    }

    public function addSong(int $id): void
    {
        $userId = $this->userId();

        $playlist = $this->playlist->find(
            $id,
            $userId
        );

        if (!$playlist) {
            Response::notFound(
                "Playlist not found."
            );
        }

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

        $this->playlist->addSong(
            $id,
            (int)$body['song_id']
        );

        $this->success(
            null,
            "Song added to playlist."
        );
    }

    public function removeSong(
        int $id,
        int $songId
    ): void {

        $userId = $this->userId();

        $playlist = $this->playlist->find(
            $id,
            $userId
        );

        if (!$playlist) {
            Response::notFound(
                "Playlist not found."
            );
        }

        $this->playlist->removeSong(
            $id,
            $songId
        );

        $this->success(
            null,
            "Song removed from playlist."
        );
    }
}