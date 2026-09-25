<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\UserSettings;
use App\Core\Controller;

class SettingsController extends Controller
{
    private UserSettings $settings;

    public function __construct()
    {
        $this->settings = new UserSettings();
    }

    public function show(): void
    {
        $userId = $this->userId();

        $settings = $this->settings->get($userId);

        if (!$settings) {
            Response::notFound(
                "Settings not found."
            );
        }

        $this->success($settings);
    }

    public function update(): void
    {
        $userId = $this->userId();

        $body = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        $this->settings->update(
            $userId,
            [
                'theme' => $body['theme'] ?? 'system',
                'language' => $body['language'] ?? 'en',
                'stream_quality' => $body['stream_quality'] ?? 'high',
                'download_quality' => $body['download_quality'] ?? 'high',
                'autoplay' => (int)($body['autoplay'] ?? 1),
                'crossfade_seconds' => (int)($body['crossfade_seconds'] ?? 0),
                'normalize_volume' => (int)($body['normalize_volume'] ?? 1),
                'explicit_content' => (int)($body['explicit_content'] ?? 1),
            ]
        );

        $this->success(
            null,
            "Settings updated successfully."
        );
    }
}