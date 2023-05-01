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

/**
 * Для хранения временного результата при обработке PHP кода в {@see PhpReader} и дочерних классов
 *
 * Оглавление:
 * @see self::$result - Строка с накопленным временным результатом
 * @see self::$lastChar - Последний символ в накопленных результатах
 * @see self::set() - Заменит значение для результата
 * @see self::addChar() - Добавит к результату новый символ
 * @see self::addString() - Добавит к результату строку и вычислит последний символ
 * @see self::resultClear() - Очистит накопленные результаты
 * @see self::resultClearAndSetSpase() - Очистит накопленные результаты и в результаты запишет пустую строку (но не в последний символ)
 */
class CodeTmp
{
    /**
     * Строка с накопленным временным результатом
     *
     * @see self::$lastChar - Хранит последний символ из результата (символ также есть и в результате)
     */
    public string $result = '';
    /**
     * Последний символ в накопленных результатах (т.е. $this->result[-1])
     */
    public string $lastChar = '';

    /**
     * Объект "читатель кода", для которого идет накопление временных результатов
     */
    readonly private PhpReader $phpReader;

    public function __construct(PhpReader $phpReader)
    {
        $this->phpReader = $phpReader;
    }

    /**
     * Заменит значение для результата
     *
     * @param   string         $string     Новое значение для "результата"
     * @param   null|string    $lastChar   Последний символ "результата" (если NULL - будет вычислен автоматически)
     *
     * @return void
     */
    public function set(string $string, null|string $lastChar = null): void
    {
        $this->result = $string;

        if ($lastChar === null) $this->lastChar = mb_substr($this->result, -1);
        else $this->lastChar = $lastChar;
    }

    /**
     * Добавит к результату новый символ
     *
     * @param   null|string    $char   Добавляемый символ (если NULL - добавит последний прочитанный символ)
     *
     * @return void
     */
    public function addChar(null|string $char = null): void
    {
        if ($char === null) $char = $this->phpReader->codeString->charFirst;

        $this->lastChar = $char;
        $this->result .= $char;
    }

    /**
     * Добавит к результату строку и вычислит последний символ
     *
     * @param   string         $string     Новое значение для "результата"
     * @param   null|string    $lastChar   Последний символ "результата" (если NULL - будет вычислен автоматически)
     *
     * @return void
     */
    public function addString(string $string, null|string $lastChar = null): void
    {
        $this->result .= $string;

        if ($lastChar === null) $this->lastChar = mb_substr($this->result, -1);
        else $this->lastChar = $lastChar;
    }

    /**
     * Очистит накопленные результаты
     *
     * @return void
     */
    public function resultClear(): void
    {
        $this->result = '';
        $this->lastChar = '';
    }

    /**
     * Очистит накопленные результаты и в результаты запишет пустую строку (но не в последний символ)
     *
     * (!) Запись пробела позволит в проверках вида "в начале строки или после пробела" избегать регулярных выражений
     *
     * @return void
     */
    public function resultClearAndSetSpase(): void
    {
        $this->result = ' ';
        $this->lastChar = ' ';
    }
}
