<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\Profile;
use App\Core\Controller;

class ProfileController extends Controller
{
    private Profile $profile;

    public function __construct()
    {
        $this->profile = new Profile();
    }

    public function show(): void
    {
        $userId = $this->userId();

        $profile = $this->profile->get($userId);

        if (!$profile) {
            Response::notFound(
                "Profile not found."
            );
        }

        $this->success($profile);
    }

    public function update(): void
    {
        $userId = $this->userId();

        $body = json_decode(
            file_get_contents("php://input"),
            true
        ) ?? [];

        $this->profile->update(
            $userId,
            [
                'name' => $body['name'] ?? '',
                'avatar_url' => $body['avatar_url'] ?? '',
                'country' => $body['country'] ?? '',
                'birth_date' => $body['birth_date'] ?? null,
                'gender' => $body['gender'] ?? 'other',
                'bio' => $body['bio'] ?? ''
            ]
        );

        $this->success(
            null,
            "Profile updated successfully."
        );
    }
}