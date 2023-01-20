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

/**
 * Осуществляет чтение строки, разгружает код для @see PhpReader
 *
 * Для чтения Heredoc и Nowdoc строк @see HeredocAndNowdocReader
 *
 * Оглавление:
 * @see StringReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class StringReader extends AbstractReader
{
    /**
     * Кавычка используемая в начале и конце строки
     */
    private string $quote = '';

    private bool $openSlash = false;

    /**
     * Для хранения результатов чтения строки (содержимое строки)
     */
    readonly private CodeTmp $stringValue;

    protected function __construct(PhpReader $phpReader)
    {
        parent::__construct($phpReader);
        $this->stringValue = new CodeTmp($phpReader);
    }

    public function clear(): void
    {
        $this->quote = '';
        $this->openSlash = false;
        $this->stringValue->resultClear();
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        return $phpReader->codeString->charFirst === "'" || $phpReader->codeString->charFirst === '"';
    }

    public function start(): void
    {
        $this->clear();
        $this->quote = $this->phpReader->codeString->charFirst;
    }

    public function run(): null|self
    {
        if ($this->stringValue->lastChar === "\\") $this->openSlash = !$this->openSlash;
        else $this->openSlash = false;

        if (!$this->openSlash && $this->phpReader->codeString->charFirst === $this->quote)
        {
            if ($this->phpReader->readWithStrings) $this->phpReader->codeTmp->addString("{$this->quote}{$this->stringValue->result}{$this->quote}", $this->stringValue->lastChar);

            return null;
        }
        else
        {
            $this->stringValue->addChar();

            return $this;
        }
    }
}
