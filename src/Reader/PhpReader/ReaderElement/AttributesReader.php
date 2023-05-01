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
use DraculAid\PhpMocker\Schemes\AttributeScheme;

/**
 * Осуществляет чтение атрибутов для классов или элементов классов, разгружает код для {@see PhpReader}
 *
 * Оглавление:
 * @see AttributesReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class AttributesReader extends AbstractReader
{
    /**
    * В данный момент идет чтение-накопление имени атрибута {@see static::$tmpName}
    * static::READ_FROM_NAME
    */
    private const READ_FROM_NAME = 1;
    /**
     * В данный момент идет чтение-накопление аргументов атрибута {@see static::$tmpArguments}
     * static::READ_FROM_NAME
     */
    private const READ_FROM_ARGUMENTS = 2;

    /**
     * Указатель, в настоящий момент идет чтение накопление, для чего именно (имени, аргументов..)
     *
     * См константы static::READ_FROM_***
     */
    private int $readBodyType = 0;
    /**
     * Для накопления имени атрибута
     */
    private string $tmpName = '';
    /**
     * Для накопления аргументов атрибута
     */
    private string $tmpArguments = '';

    /**
     * Объект, для чтения вложенных в атрибут элементов (строк)
     * (NULL - объект чтения кода не установлен)
     */
    private null|AbstractReader $codeReader = null;

    /**
     * Подсчет глубины круглых скобок
     */
    private int $braceCircularDeep = 0;

    public function clear(): void
    {
        $this->readBodyType = self::READ_FROM_NAME;
        $this->tmpName = '';
        $this->tmpArguments = '';
        $this->braceCircularDeep = 0;
        $this->codeReader = null;
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        return $phpReader->codeString->charFirst === '#' && $phpReader->codeString->charSecond === '[';
    }

    public function start(): void
    {
        $this->clear();

        // очищаем прочитанный 1 и 2 символы (дальнейшее чтение будет проводиться с конца метки)
        $this->phpReader->codeString->charClear();

        $this->phpReader->readWithStrings = true;
    }

    public function run(): null|self
    {
        if ($this->readBodyType != self::READ_FROM_ARGUMENTS && $this->phpReader->codeString->charFirst === ']')
        {
            $this->endReadAttributeLine();
            $this->phpReader->readWithStrings = false;

            return null;
        }
        else
        {
            if ($this->codeReader)
            {
                $this->codeReader = $this->codeReader->run();
            }
            else
            {
                $this->codeReader = self::searchReader($this->phpReader, false);
                if ($this->codeReader === null) $this->readCode();
            }

            return $this;
        }
    }

    /**
     * Непосредственная отработка чтения нового символа
     *
     * @return void
     */
    private function readCode(): void
    {
        if ($this->phpReader->codeString->charFirst === '(') $this->braceCircularDeep++;
        elseif ($this->phpReader->codeString->charFirst === ')') $this->braceCircularDeep--;

        // чтение значения атрибута
        if ($this->readBodyType === static::READ_FROM_ARGUMENTS)
        {
            // если конец чтения аргументов атрибута
            if ($this->phpReader->codeString->charFirst === ')' && $this->braceCircularDeep === 0)
            {
                $this->readCodeEndReadArguments();
                $this->readBodyType = 0;
            }
            else
            {
                $this->phpReader->codeTmp->addChar();
            }
        }
        // чтение НЕ значения атрибута
        else
        {
            // запятая - это конец определения текущего атрибута и начало определения нового атрибута
            if ($this->phpReader->codeString->charFirst === ',')
            {
                $this->readCodeEndReadAttribute();
                $this->clear();
            }
            // если начало чтения аргументов атрибута
            elseif ($this->phpReader->codeString->charFirst === '(')
            {
                $this->readCodeEndReadName();
                $this->readBodyType = static::READ_FROM_ARGUMENTS;
            }
            // если накапливается имя - добавим в него символ
            elseif ($this->readBodyType === static::READ_FROM_NAME)
            {
                $this->phpReader->codeTmp->addChar();
            }
        }
    }
    
    /**
     * Конец накопления имени атрибута
     *
     * @return void
     */
    private function readCodeEndReadName(): void
    {
        // запомним имя
        $this->tmpName = trim($this->phpReader->codeTmp->result);
        $this->phpReader->codeTmp->resultClear();
    }

    /**
     * Конец накопления аргумента атрибута
     *
     * @return void
     */
    private function readCodeEndReadArguments(): void
    {
        $this->tmpArguments = trim($this->phpReader->codeTmp->result);
        $this->phpReader->codeTmp->resultClear();
    }

    /**
     * Конец чтения очередного атрибута
     *
     * @return void
     */
    private function readCodeEndReadAttribute(): void
    {
        // если текущий режим - это режим чтения имени аргумента, получим имя из временных результатов
        if ($this->readBodyType === self::READ_FROM_NAME) $this->readCodeEndReadName();

        // создаем и сохраняем схему атрибута, если только удалось получить имя атрибута
        if ($this->tmpName !== '')
        {
            $schemeAttribute = new AttributeScheme($this->phpReader->tmpResult->schemeClass, $this->tmpName);
            $schemeAttribute->innerPhpCode = trim($this->tmpArguments);

            $this->phpReader->tmpResult->attributes[] = $schemeAttribute;
            $this->phpReader->codeTmp->resultClear();
        }
    }

    /**
     * Завершает чтение строки-атрибута
     *
     * @return void
     */
    private function endReadAttributeLine(): void
    {
        $this->readCodeEndReadAttribute();
        $this->phpReader->codeTmp->resultClear();
    }
}
