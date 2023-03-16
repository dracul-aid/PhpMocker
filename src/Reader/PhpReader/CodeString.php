<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Tools\Php8Functions;

/**
 * Объект для работы с еще не обработанным PHP кодом в @see PhpReader и дочерних классов
 *
 * Оглавление:
 * @see self::$phpCode - Еще не обработанный PHP код
 * @see self::$charFirst - Первый символ прочитанный из PHP кода
 * @see self::$charSecond - Второй символ прочитанный из PHP кода
 * @see self::read() - Прочтет очередной символ из необработанного кода
 * @see self::charClear() - Очистка первого и второго прочитанного символа неразобранного кода
 * @see self::isWordStart() - Проверит предположение, что в данный момент идет чтение определенной строки (например, ключевого слова)
 */
class CodeString
{
    /**
     * Строка с еще не обработанным PHP кодом
     *
     * Из этой строки при каждом чтении @see self::read() изымаются:
     * @see self::$charFirst - Первый символ прочитанный из PHP кода
     * @see self::$charSecond - Второй символ прочитанный из PHP кода
     */
    public string $phpCode;

    /**
     * Первый символ в еще не обработанном коде (будет взят из $this->phpCode[0])
     */
    public string $charFirst = '';
    /**
     * Второй символ в еще не обработанном коде (будет взят из $this->phpCode[1])
     */
    public string $charSecond = '';

    /**
     * @param   string   $phpCode   PHP код для обработки
     */
    public function __construct(string $phpCode)
    {
        $this->phpCode = trim($phpCode);
    }

    /**
     * Прочтет очередной символ из необработанного кода (уменьшив его)
     *
     * @param    bool   $firstRead    TRUE Это первичное чтение (т.е. "первый" и "второй" символ считаются отсутствующими)
     *
     * @return   bool   Вернет TRUE если удалось прочитать очередной символ и FALSE - если достигли конца строки
     */
    public function read(bool $firstRead = false): bool
    {
        if ($firstRead || ($this->charFirst === '' && $this->charSecond === ''))
        {
            $this->charFirst = mb_substr($this->phpCode, 0, 1);
            $this->charSecond = mb_substr($this->phpCode, 1, 1);
            $this->phpCode = mb_substr($this->phpCode, 2);
        }
        else
        {
            $this->charFirst = $this->charSecond;
            $this->charSecond = mb_substr($this->phpCode, 0, 1);
            $this->phpCode = mb_substr($this->phpCode, 1);
        }

        return $this->charFirst !== '';
    }

    /**
     * Очистка первого и второго прочитанного символа неразобранного кода
     *
     * @return void
     */
    public function charClear(): void
    {
        $this->charFirst = '';
        $this->charSecond = '';
    }

    /**
     * Проверит предположение, что в данный момент идет чтение определенной строки (например, ключевого слова)
     *
     * @param   false|string    $first          "Первый" прочитанный символ (FALSE - если проверку не нужно проводить)
     * @param   false|string    $second         "Второй" прочитанный символ (FALSE - если проверку не нужно проводить)
     * @param   string          $beforeString   С чего должна начинаться еще не обработанная строка
     *
     * @return  bool   Вернет TRUE если идет чтение строки
     *
     * ```php
     * // Для проверки, что читается public
     * $CodeString->isWordStart( 'p', 'u', 'blic' )
     * ```
     */
    public function isWordStart($first, $second, string $beforeString): bool
    {
        if (!is_bool($first) && !is_string($first)) throw new \TypeError('$first is not false|string');
        if (!is_bool($second) && !is_string($second)) throw new \TypeError('$second is not false|string');

        if ($first !== false && $this->charFirst !== $first)
        {
            return false;
        }

        if ($second !== false && $this->charSecond !== $second)
        {
            return false;
        }

        if ($this->phpCode === "{$beforeString}")
        {
            return true;
        }

        foreach ([' ', "\n", '/*', "\t", "\r"] as $test)
        {
            if (Php8Functions::str_starts_with($this->phpCode, "{$beforeString}{$test}"))
            {
                return true;
            }
        }

        return false;
    }
}
