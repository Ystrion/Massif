<?php

declare(strict_types=1);

namespace Application\Utils;

class Cookie
{
    public static function exists(string $name): bool
    {
        return isset($_COOKIE[Settings::getString('cookies.prefix') . $name]);
    }

    public static function get(string $name, ?string $defaultValue = null): ?string
    {
        return $_COOKIE[Settings::getString('cookies.prefix') . $name] ?? $defaultValue;
    }

    public static function set(string $name, string $value, int $expires = 0): void
    {
        /** @phpstan-ignore-next-line */
        setcookie(Settings::getString('cookies.prefix') . $name, $value, [
            'expires' => $expires,
            'path' => Settings::getString('cookies.path'),
            'domain' => Settings::getString('cookies.domain'),
            'secure' => Settings::getBool('cookies.secure'),
            'httponly' => Settings::getBool('cookies.httpOnly'),
            'samesite' => Settings::getString('cookies.sameSite')
        ]);
    }

    public static function delete(string $name): void
    {
        self::set($name, '', 1);
    }
}
