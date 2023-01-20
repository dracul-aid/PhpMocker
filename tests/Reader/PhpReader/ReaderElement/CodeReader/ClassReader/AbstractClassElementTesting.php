<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\AbstractClassElementsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\TmpClassElement;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use PHPUnit\Framework\TestCase;

class AbstractClassElementTesting extends TestCase
{
    protected PhpReader $phpReader;

    protected function searchSchemes(string $phpCode): void
    {
        /**
         * @var null|ClassElementSchemeCreator|AbstractClassElementsReader $runner
         * В ходе корректного выполнения, будет меняться на:
         * 1) @see ClassElementSchemeCreator - В момент создания схемы элементы
         * 2) @see ClassConstantsReader - В момент чтения константы
         * 3) NULL (чтение завершено)
         */
        $runner = $this->createObjectsAndGetRunner(" {$phpCode} ");

        while (true)
        {
            $this->phpReader->codeString->read();
            // "прочитанный символ", может стать пустой строкой в ходе выполнения функции run() ниже
            $charFirstIsEmpty = $this->phpReader->codeString->charFirst === '';

            $runner = $runner->run();

            if ($runner === null) return;
            elseif ($charFirstIsEmpty) $this->fail();
        }
    }

    protected function createObjectsAndGetRunner(string $phpCode): ClassElementSchemeCreator
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->phpReader->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::CLASSES, 'TestClassName');

        return ClassElementSchemeCreator::start($this->phpReader, new TmpClassElement());
    }
}
