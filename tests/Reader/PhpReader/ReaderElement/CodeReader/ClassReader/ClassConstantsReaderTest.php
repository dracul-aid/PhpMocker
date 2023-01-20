<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassConstantsReader;

/**
 * Test for @see ClassConstantsReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassConstantsReaderTest.php
 */
class ClassConstantsReaderTest extends AbstractClassElementTesting
{
    /**
     * Test for @see ClassConstantsReader::run()
     */
    public function testRun(): void
    {
        $testCases = [
            'My_CONST_STRING = "string";' => ['name' => 'My_CONST_STRING', 'innerPhpCode' => '"string"'],
            'My_CONST_INT = 123;' => ['name' => 'My_CONST_INT', 'innerPhpCode' => '123'],
            "My_CONST_CODE=CONST_VALUE . '\$var';" => ['name' => 'My_CONST_CODE', 'innerPhpCode' => "CONST_VALUE . '\$var'"],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            $this->searchSchemes("public const {$phpCode}");

            self::assertCount(1, $this->phpReader->tmpResult->schemeClass->constants);
            self::assertArrayHasKey($options['name'], $this->phpReader->tmpResult->schemeClass->constants);

            $schemeConstant = $this->phpReader->tmpResult->schemeClass->constants[$options['name']];
            self::assertFalse($schemeConstant->isEnumCase);
            self::assertEquals('', $schemeConstant->value, "For 'value' in code {$phpCode}");
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $schemeConstant->{$name}, "For '{$name}' in code {$phpCode}");
            }
        }
    }
}
