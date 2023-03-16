<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Tools;

/**
 * Статический класс с набором функций из PHP8
 */
class Php8Functions
{
    public static function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || strpos($haystack, $needle) !== false;
    }

    public static function str_starts_with(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }

    public static function str_ends_with(string $haystack, string $needle): bool
    {
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = strlen($needle);

        return $needleLength <= strlen($haystack) && substr_compare($haystack, $needle, -$needleLength) === 0;
    }
}
