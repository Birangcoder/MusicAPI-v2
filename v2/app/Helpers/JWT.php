<?php

declare(strict_types=1);

namespace App\Helpers;

class JWT
{
    public static function encode(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRE;

        $header = self::base64UrlEncode(json_encode($header));
        $payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$header.$payload",
            JWT_SECRET,
            true
        );

        $signature = self::base64UrlEncode($signature);

        return "$header.$payload.$signature";
    }

    public static function decode(string $jwt): array|false
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $expected = self::base64UrlEncode(
            hash_hmac(
                'sha256',
                "$header.$payload",
                JWT_SECRET,
                true
            )
        );

        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $payload = json_decode(
            self::base64UrlDecode($payload),
            true
        );

        if (!$payload) {
            return false;
        }

        if (
            isset($payload['exp']) &&
            time() >= $payload['exp']
        ) {
            return false;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(
            strtr($data, '-_', '+/')
        );
    }
}