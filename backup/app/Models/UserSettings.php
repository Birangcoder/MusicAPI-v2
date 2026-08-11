<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class UserSettings extends Model
{
    public function get(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                theme,
                language,
                stream_quality,
                download_quality,
                autoplay,
                crossfade_seconds,
                normalize_volume,
                explicit_content
            FROM user_settings
            WHERE user_id=?
            LIMIT 1
        ");

        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $settings = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        return $settings ?: null;
    }

    public function update(
        int $userId,
        array $data
    ): bool {

        $stmt = $this->db->prepare("
            UPDATE user_settings
            SET
                theme=?,
                language=?,
                stream_quality=?,
                download_quality=?,
                autoplay=?,
                crossfade_seconds=?,
                normalize_volume=?,
                explicit_content=?
            WHERE user_id=?
        ");

        $stmt->bind_param(
            "ssssiiiii",
            $data['theme'],
            $data['language'],
            $data['stream_quality'],
            $data['download_quality'],
            $data['autoplay'],
            $data['crossfade_seconds'],
            $data['normalize_volume'],
            $data['explicit_content'],
            $userId
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }
}