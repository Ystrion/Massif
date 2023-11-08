<?php

declare(strict_types=1);

namespace Application\Utils;

use Adbar\Dot;
use TypeError;

class Settings
{
    /** @var Dot<string, mixed> */
    private static Dot $settings;

    /**
     * @param array<string, mixed> $settings
     */
    public static function init(array $settings): void
    {
        self::$settings = new Dot($settings);
    }

    public static function isDev(): bool
    {
        return self::getString('env') === 'dev';
    }

    public static function isProd(): bool
    {
        return self::getString('env') === 'prod';
    }

    public static function get(string $path): mixed
    {
        return self::$settings->get($path);
    }

    /**
     * @return array<mixed, mixed>
     */
    public static function getArray(string $path): array
    {
        $value = self::get($path);

        if (!is_array($value)) {
            throw new TypeError('The value associated with this path must be an array.');
        }

        return $value;
    }

    public static function getString(string $path): string
    {
        $value = self::get($path);

        if (!is_string($value)) {
            throw new TypeError('The value associated with this path must be a string.');
        }

        return $value;
    }

    public static function getBool(string $path): bool
    {
        $value = self::get($path);

        if (!is_bool($value)) {
            throw new TypeError('The value associated with this path must be a boolean..');
        }

        return $value;
    }

    public static function getInt(string $path): int
    {
        $value = self::get($path);

        if (!is_int($value)) {
            throw new TypeError('The value associated with this path must be an integer.');
        }

        return $value;
    }

    public static function getFloat(string $path): float
    {
        $value = self::get($path);

        if (!is_float($value)) {
            throw new TypeError('The value associated with this path must be a float.');
        }

        return $value;
    }
}
