<?php

declare(strict_types=1);

namespace Application\Utils;

use Adbar\Dot;

class Session
{
    protected static function initGuard(): void
    {
        if (!isset($_SESSION['massif']) || !$_SESSION['massif'] instanceof Dot) {
            $_SESSION['massif'] = new Dot();
        }
    }

    public static function has(string $path): bool
    {
        self::initGuard();

        return $_SESSION['massif']->has($path);
    }

    public static function get(string $path, mixed $defaultValue = null): mixed
    {
        self::initGuard();

        return $_SESSION['massif']->get($path, $defaultValue);
    }

    public static function set(string $path, mixed $value): void
    {
        self::initGuard();

        $_SESSION['massif']->set($path, $value);
    }

    public static function delete(string $path): void
    {
        self::initGuard();

        $_SESSION['massif']->delete($path);
    }
}
