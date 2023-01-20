<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\AbstractClassElementsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassMethodsReader;

/**
 * Test for @see ClassMethodsReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassMethodsReaderTest.php
 */
class ClassMethodsReaderTest extends AbstractClassElementTesting
{
    /**
     * Test for @see ClassMethodsReader::run()
     */
    public function testPhpCode(): void
    {
        $testCases = [
            'f_abstract();' => ['name' => 'f_abstract', 'argumentsPhpCode' => '', 'innerPhpCode' => ''],
            'f_empty() {}' => ['name' => 'f_empty', 'argumentsPhpCode' => '', 'innerPhpCode' => ''],
            'f_nonempty_1() {echo "123";}' => ['name' => 'f_nonempty_1', 'argumentsPhpCode' => '', 'innerPhpCode' => 'echo "123";'],
            'f_nonempty_2() {echo $this->{0};}' => ['name' => 'f_nonempty_2', 'argumentsPhpCode' => '', 'innerPhpCode' => 'echo $this->{0};'],
            'f_with_arguments_1(string $var = "string") {}' => ['name' => 'f_with_arguments_1', 'argumentsPhpCode' => 'string $var = "string"', 'innerPhpCode' => ''],
            'f_with_arguments_2(array &$array = array([123])) {}' => ['name' => 'f_with_arguments_2', 'argumentsPhpCode' => 'array &$array = array([123])', 'innerPhpCode' => ''],
            'f_attributes(#[Attribute()] $var1) {}' => ['name' => 'f_attributes', 'argumentsPhpCode' => '#[Attribute()] $var1', 'innerPhpCode' => ''],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            $this->searchSchemes("public function {$phpCode}");

            self::assertCount(1, $this->phpReader->tmpResult->schemeClass->methods);
            self::assertArrayHasKey($options['name'], $this->phpReader->tmpResult->schemeClass->methods);

            $schemeMethod =  $this->phpReader->tmpResult->schemeClass->methods[$options['name']];
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $schemeMethod->{$name}, "For '{$name}' in code {$phpCode}");
            }
        }
    }

    /**
     * Test for @see ClassMethodsReader::run()
     */
    public function testType(): void
    {
        $testCases = [
            'f1(): string;' => ['returnType' => 'string', 'isReturnLink' => false],
            '&f1(): int;' => ['returnType' => 'int', 'isReturnLink' => true],
            'f1(): null|object {};' => ['returnType' => 'null|object', 'isReturnLink' => false],
            '&f1(): Alpha&Beta {};' => ['returnType' => 'Alpha&Beta', 'isReturnLink' => true],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            $this->searchSchemes("public function {$phpCode}");

            self::assertCount(1, $this->phpReader->tmpResult->schemeClass->methods);
            self::assertArrayHasKey('f1', $this->phpReader->tmpResult->schemeClass->methods);

            $schemeMethod =  $this->phpReader->tmpResult->schemeClass->methods['f1'];
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $schemeMethod->{$name}, "For '{$name}' in code {$phpCode}");
            }
        }
    }

    protected function searchSchemes(string $phpCode): void
    {
        /**
         * @var null|ClassElementSchemeCreator|AbstractClassElementsReader $runner
         * В ходе корректного выполнения, будет меняться на:
         * 1) @see ClassElementSchemeCreator - В момент создания схемы элементы
         * 2) @see ClassMethodsReader - В момент чтения свойства
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
}
