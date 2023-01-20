<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\EnumCasesReader;

/**
 * Test for @see EnumCasesReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/EnumCasesReaderTest.php
 */
class EnumCasesReaderTest extends AbstractClassElementTesting
{
    /**
     * Test for @see EnumCasesReader::run()
     */
    public function testRun(): void
    {
        $testCases = [
            'case WITHOUT_VALUE;' => ['name' => 'WITHOUT_VALUE', 'innerPhpCode' => ''],
            'case WITH_VALUE = 123;' => ['name' => 'WITH_VALUE', 'innerPhpCode' => '123'],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            $this->searchSchemes(" {$phpCode} ");

            self::assertCount(1, $this->phpReader->tmpResult->schemeClass->constants);
            self::assertArrayHasKey($options['name'], $this->phpReader->tmpResult->schemeClass->constants);

            $schemeConstant = $this->phpReader->tmpResult->schemeClass->constants[$options['name']];
            self::assertTrue($schemeConstant->isEnumCase);
            self::assertEquals('', $schemeConstant->value, "For 'value' in code {$phpCode}");
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $schemeConstant->{$name}, "For '{$name}' in code {$phpCode}");
            }
        }
    }
}
