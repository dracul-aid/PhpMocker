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
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ScriptNamespaceReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ScriptUseReader;

/**
 * Осуществляет чтение PHP кода (конструкции use, классы и так далее), разгружает код для @see PhpReader
 * Используется, если текущий читаемый код, не комментарий и не строка, а именно PHP код
 *
 * Оглавление:
 * @see CodeReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 *
 * Свойства доступные только для чтения @see self::__get()
 * @property PhpReader $phpReader
 */
class CodeReader
{
    /**
     * Объект "читатель кода", для которого идет накопление временных результатов
     */
    protected PhpReader $phpReader;

    /**
     * Объект, для чтения вложенных в атрибут элементов (строк)
     * (NULL - объект чтения кода не установлен)
     */
    protected ?AbstractReader $codeReader = null;

    /**
     * @param   PhpReader   $phpReader   Объект "читатель кода", для которого идет накопление временных результатов
     */
    public function __construct(PhpReader $phpReader)
    {
        $this->phpReader = $phpReader;
    }

    public function __get(string $name)
    {
        return $this->{$name};
    }

    /**
     * Осуществляет чтение кода класса и создание схемы
     *
     * @todo  инструкции declare - https://www.php.net/manual/ru/control-structures.declare.php
     */
    public function run(): ?self
    {
        if (trim($this->phpReader->codeString->charFirst) === '')
        {
            if (trim($this->phpReader->codeString->charSecond) === '') return $this;
            else $this->phpReader->codeString->charFirst = ' ';
        }

        if ($this->phpReader->codeString->charFirst === '{') $this->phpReader->tmpResult->codeBlockDeep++;
        if ($this->phpReader->codeString->charFirst === '}') $this->phpReader->tmpResult->codeBlockDeep--;

        // * * *

        if ($this->codeReader !== null)
        {
            $this->codeReader = $this->codeReader->run();
        }
        else
        {
            // если достигли точки запятой - уничтожаем ранее накопленный результат, он не нужен
            // так как это PHP код который не принадлежит классу
            if ($this->phpReader->codeString->charFirst === ';')
            {
                $this->phpReader->codeTmp->resultClearAndSetSpase();
            }
            else
            {
                if (ScriptUseReader::isStart($this->phpReader)) $readerClass = ScriptUseReader::class;
                elseif (ScriptNamespaceReader::isStart($this->phpReader)) $readerClass = ScriptNamespaceReader::class;
                elseif (ClassReader::isStart($this->phpReader)) $readerClass = ClassReader::class;
                else $readerClass = null;

                if ($readerClass === null) $this->phpReader->codeTmp->addChar();
                else $this->codeReader = AbstractReader::getReaderObjectByClass($readerClass, $this->phpReader);
            }
        }

        return $this;
    }
}
