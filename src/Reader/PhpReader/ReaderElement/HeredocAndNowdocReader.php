<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\CodeTmp;
use DraculAid\PhpMocker\Tools\Char;

/**
 * Осуществляет чтение Heredoc и Nowdoc строк, разгружает код для @see PhpReader
 *
 * Для чтения обычных строк @see StringReader
 *
 * Оглавление:
 * @see HeredocAndNowdocReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class HeredocAndNowdocReader extends AbstractReader
{
    /**
     * Метка конца и начала Heredoc и Nowdoc строк
     */
    protected string $mark = '';
    /**
     * Указатель, что это Nowdoc строка
     */
    protected bool $isNowdoc = false;
    /**
     * Для накопления "последней строки"
     */
    protected string $lastString = '';
    /**
     * Указатель, что "последняя строка" - точно не конец Heredoc и Nowdoc строки
     */
    protected bool $lastStringIsNotEnd = false;

    /**
     * Для хранения результатов чтения строки (содержимое строки)
     */
    private string $result = '';

    public function clear(): void
    {
        $this->mark = '';
        $this->isNowdoc = false;
        $this->lastStringIsNotEnd  = false;
        $this->lastString = '';
        $this->result = '';
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        return $phpReader->codeString->charFirst === '<'
            && $phpReader->codeString->charSecond === '<'
            && $phpReader->codeString->phpCode[0] === '<'
            && ($phpReader->codeString->phpCode[1] === "'" || Char::canBeStartNameOfVar($phpReader->codeString->phpCode[1]));
    }

    public function start(): void
    {
        $this->clear();
        $this->searchMark();
    }

    public function run(): null|self
    {
        $this->result .= $this->phpReader->codeString->charFirst;

        if ($this->phpReader->codeString->charFirst === "\n")
        {
            $this->lastString = '';
            $this->lastStringIsNotEnd = false;
        }
        elseif ($this->phpReader->codeString->charFirst !== "\r" && !$this->lastStringIsNotEnd)
        {
            if (trim($this->phpReader->codeString->charFirst) !== '')
            {
                $this->lastString .= $this->phpReader->codeString->charFirst;
            }

            if ($this->lastString !== '')
            {
                if ($this->lastString === $this->mark)
                {
                    if ($this->phpReader->readWithStrings) $this->StopAndSaveIntoResult();
                    return null;
                }
                elseif (!str_starts_with($this->mark, $this->lastString))
                {
                    $this->lastStringIsNotEnd = true;
                }
            }
        }

        return $this;
    }

    /**
     * Определит метку начала и конца Heredoc и Nowdoc строк
     *
     * @return void
     */
    protected function searchMark(): void
    {
        // очищаем прочитанный 1 и 2 символы (дальнейшее чтение будет проводиться с конца метки)
        $this->phpReader->codeString->charClear();

        /** Позиция с которой заканчивается метка */
        $endCut = strpos($this->phpReader->codeString->phpCode, "\n");

        // Получим метку для Nowdoc строки
        if ($this->phpReader->codeString->phpCode[1] === "'")
        {
            $this->mark = substr($this->phpReader->codeString->phpCode, 2, $endCut - 3);
            $this->isNowdoc = true;
        }
        // Получим метку для Heredoc строки
        else
        {
            $this->mark = substr($this->phpReader->codeString->phpCode, 1, $endCut - 1);
        }

        $this->phpReader->codeString->phpCode = substr($this->phpReader->codeString->phpCode, $endCut);
    }

    /**
     * Завершение чтения строки - отправляем ее в результат
     *
     * @return void
     */
    protected function StopAndSaveIntoResult(): void
    {
        $_return = '<<<' . ($this->isNowdoc ? "'{$this->mark}'" : $this->mark);
        $_return .= $this->result;

        // запоминаем строку
        $this->phpReader->codeTmp->addString($_return);
    }
}
