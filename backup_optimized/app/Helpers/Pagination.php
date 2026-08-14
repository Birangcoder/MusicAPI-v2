<?php

declare(strict_types=1);

namespace App\Helpers;

class Pagination
{
    public static function page(): int
    {
        $page = (int)($_GET['page'] ?? 1);

        return max($page, 1);
    }

    public static function limit(): int
    {
        $limit = (int)($_GET['limit'] ?? DEFAULT_LIMIT);

        if ($limit < 1) {
            $limit = DEFAULT_LIMIT;
        }

        if ($limit > MAX_LIMIT) {
            $limit = MAX_LIMIT;
        }

        return $limit;
    }

    public static function offset(): int
    {
        return (self::page() - 1) * self::limit();
    }

    public static function meta(
        int $total,
        int $page,
        int $limit
    ): array {

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'last_page' => (int)ceil($total / $limit),
            'has_next' => ($page * $limit) < $total,
            'has_previous' => $page > 1
        ];
    }
}