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
 * Статический класс с набором функций для работы со символами
 *
 * Оглавление: 
 * --- Константы номеров символов
 * @see Char::CODE_ABC_UPPER - Позиции с которых начинаются и заканчиваются заглавные буквы
 * @see Char::CODE_ABC_UPPER - Позиции с которых начинаются и заканчиваются строчные буквы
 * @see Char::CODE_NUMBER - Позиции с которых начинаются и заканчиваются символы цифр (10-тичных цифр)
 * --- Функции проверки символов
 * @see Char::isABC() - Проверит, является ли указанный символ буквой латинского алфавита
 * @see Char::isNumber() - Проверит, является ли указанный символ цифрой (отрицательное число - провалит проверку)
 * @see Char::canBeStartNameOfVar() - Проверяет, удовлетворяет ли переданный символ правилу начала имен переменных
 * @see Char::canBeInsideNameOfVar() - Проверяет, символ является символом, допустимым внутри имени переменной (т.е. кроме первого символа)
 */
class Char
{
    /**
     * Позиции с которых начинаются и заканчиваются заглавные буквы
     *
     * Массив: [0: начало, 1: конец]
     */
    public const CODE_ABC_UPPER = [65, 90];

    /**
     * Позиции с которых начинаются и заканчиваются строчные буквы
     *
     * Массив: [0: начало, 1: конец]
     */
    public const CODE_ABC_LOWER = [97, 122];

    /**
     * Позиции с которых начинаются и заканчиваются символы цифр (10-тичных цифр)
     *
     * Массив: [0: начало, 1: конец]
     */
    public const CODE_NUMBER = [48, 57];

    /**
     * Проверит, является ли указанный символ буквой латинского алфавита
     *
     * @param   string   $char   Проверяемый символ
     *
     * @return   bool   Вернет TRUE если символ является буквой
     *
     * (!) Если передан не символ, а строка (т.е. длина строки более 1-го символа), вернет FALSE
     *
     * @link https://en.wikipedia.org/wiki/ASCII
     */
    public static function isABC(string $char): bool
    {
        if ($char === '' || !empty($char[1])) return false;

        $charInt = ord($char);
        return ($charInt >= static::CODE_ABC_UPPER[0] && $charInt <= static::CODE_ABC_UPPER[1]) || ($charInt >= static::CODE_ABC_LOWER[0] && $charInt <= static::CODE_ABC_LOWER[1]);
    }

    /**
     * Проверит, является ли указанный символ цифрой (отрицательное число - провалит проверку)
     *
     * @param   string   $char   Проверяемый символ
     *
     * @return   bool   Вернет TRUE если символ является цифрой
     *
     * (!) Если передан не символ, а строка (т.е. длина строки более 1-го символа), вернет FALSE
     *
     * @link https://en.wikipedia.org/wiki/ASCII
     */
    public static function isNumber(string $char): bool
    {
        // если передан не символ
        if ($char === '' || !empty($char[1])) return false;

        $charInt = ord($char);
        return ($charInt >= static::CODE_NUMBER[0] && $charInt <= static::CODE_NUMBER[1]);
    }

    /**
     * Проверяет, удовлетворяет ли переданный символ правилу начала имен переменных
     * (т.е. это должна быть буква или символ подчеркивания)
     *
     * @param   string   $char   Проверяемый символ
     *
     * @return  bool   Вернет TRUE если символ можно использовать в качестве начала имени переменной
     *
     * (!) Если передан не символ, а строка (т.е. длина строки более 1-го символа), вернет FALSE
     */
    public static function canBeStartNameOfVar(string $char): bool
    {
        if ($char === '' || !empty($char[1])) return false;

        return $char === '_' || static::isABC($char);
    }


    /**
     * Проверяет, символ является символом, допустимым внутри имени переменной (т.е. кроме первого символа)
     *
     * @param   string   $char   Проверяемый символ
     *
     * @return  bool   Вернет TRUE если символ можно использовать в качестве начала имени переменной
     *
     * (!) Если передан не символ, а строка (т.е. длина строки более 1-го символа), вернет FALSE
     * (!) Символ должен быть буквой, цифрой или символом подчеркивания
     */
    public static function canBeInsideNameOfVar(string $char): bool
    {
        return static::canBeStartNameOfVar($char) || static::isNumber($char);
    }
}
