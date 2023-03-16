<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\AbstractClassElementsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassTraitsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\TmpClassElement;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassTraitsReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassTraitsReaderTest.php
 */
class ClassTraitsReaderTest extends TestCase
{
    private PhpReader $phpReader;

    /**
     * Test for @see ClassTraitsReader::run()
     */
    public function testType(): void
    {
        $testCases = [
            'TraitName',
            'TraitName1, TraitName2',
            'TraitName1, TraitName2 {TraitName2::smallTalk insteadof TraitName1; TraitName1::bigTalk insteadof TraitName2;}',
            'TraitName1, TraitName2 {TraitName2::bigTalk as talk;}',
        ];

        foreach ($testCases as $phpCode => $testPhpCode)
        {
            $this->searchSchemes("use {$phpCode};");
            self::assertEquals("use {$phpCode};", $this->phpReader->tmpResult->schemeClass->traitsPhpCode);
        }
    }

    private function searchSchemes(string $phpCode): void
    {
        /**
         * @var null|ClassElementSchemeCreator|AbstractClassElementsReader $runner
         * В ходе корректного выполнения, будет меняться на:
         * 1) @see ClassElementSchemeCreator - В момент создания схемы элементы
         * 2) @see ClassTraitsReader - В момент чтения свойства
         * 3) NULL (чтение завершено)
         */
        $runner = $this->createObjectsAndGetRunner(" {$phpCode} ");

        while (true)
        {
            $this->phpReader->codeString->read();
            // "прочитанный символ", может стать пустой строкой в ходе выполнения функции run() ниже
            $charFirstIsEmpty = $this->phpReader->codeString->charFirst === '';

            if ($this->phpReader->codeString->charFirst === '{') $this->phpReader->tmpResult->codeBlockDeep++;
            if ($this->phpReader->codeString->charFirst === '}') $this->phpReader->tmpResult->codeBlockDeep--;

            $runner = $runner->run();
            if ($runner === null) return;
            elseif ($charFirstIsEmpty) $this->fail();
        }
    }

    private function createObjectsAndGetRunner(string $phpCode): ClassElementSchemeCreator
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->phpReader->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::CLASSES(), 'TestClassName');

        return ClassElementSchemeCreator::start($this->phpReader, new TmpClassElement());
    }
}
