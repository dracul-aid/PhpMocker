<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\AbstractReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\AbstractClassElementsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\TmpClassElement;

/**
 * Осуществляет чтение конструкций с определением классов (интерфейсов, трейтов, перечислений...), разгружает код для:
 * * {@see CodeReader} - непосредственно в анализаторе кода
 * * {@see PhpReader} - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see ClassReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class ClassReader extends AbstractReader
{
    /**
     * Временные данные для "текущего читаемого элемента класса"
     */
    readonly public TmpClassElement $tmpClassElement;

    /**
     * Объект, для чтения вложенных в атрибут элементов (строк)
     * (NULL - объект чтения кода не установлен)
     *
     * Может быть:
     * @see ClassElementSchemeCreator - Анализатор кода на предмет, какая сущьность класса начинает читаться
     * @see AbstractClassElementsReader - Анализатор кода, для коркнертной сущности класса (свойства, константы...)
     */
    private null|AbstractClassElementsReader|ClassElementSchemeCreator $codeReader = null;

    protected function __construct(PhpReader $phpReader)
    {
        parent::__construct($phpReader);
        $this->tmpClassElement = new TmpClassElement();
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        return $phpReader->codeString->charFirst === '{' && $phpReader->tmpResult->codeBlockForClassDeep === $phpReader->tmpResult->codeBlockDeep;
    }

    public function start(): void
    {
        ClassSchemeCreator::exe($this->phpReader->codeTmp, $this->phpReader->tmpResult);

        // сбросим ранее накопленные данные, они больше не нужны
        $this->phpReader->codeTmp->resultClearAndSetSpase();
    }

    public function run(): null|self
    {
        if ($this->phpReader->codeString->charFirst === '}' && $this->phpReader->tmpResult->codeBlockDeep === $this->phpReader->tmpResult->codeBlockForClassDeep - 1)
        {
            $this->classReadFinish();
            return null;
        }
        else
        {
            if ($this->codeReader === null) $this->codeReader = ClassElementSchemeCreator::start($this->phpReader, $this->tmpClassElement);
            $this->codeReader = $this->codeReader->run();
            return $this;
        }
    }

    /**
     * Осуществляет завершение чтения класса
     *
     * @return void
     */
    protected function classReadFinish(): void
    {
        $this->phpReader->result[] = $this->phpReader->tmpResult->schemeClass;

        $this->phpReader->tmpResult->schemeClass = null;
        $this->phpReader->codeTmp->resultClearAndSetSpase();
    }
}
