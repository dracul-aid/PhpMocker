<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassPropertiesReader;

/**
 * Test for @see ClassPropertiesReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassPropertiesReaderTest.php
 */
class ClassPropertiesReaderTest extends AbstractClassElementTesting
{
    /**
     * Test for @see ClassPropertiesReader::run()
     */
    public function testRun(): void
    {
        $testCases = [
            '$var_basic;' => ['name' => 'var_basic', 'type' => '', 'innerPhpCode' => ''],
            '$var_value = 123;' => ['name' => 'var_value', 'type' => '', 'innerPhpCode' => '123'],
            'string $var_string;' => ['name' => 'var_string', 'type' => 'string', 'innerPhpCode' => ''],
            'string $var_string_value = "string_value";' => ['name' => 'var_string_value', 'type' => 'string', 'innerPhpCode' => '"string_value"'],
            '?string $var_string_null;' => ['name' => 'var_string_null', 'type' => 'null|string', 'innerPhpCode' => ''],
            "\$var_value_code = CONST_VALUE . '\$var';" => ['name' => 'var_value_code', 'type' => '', 'innerPhpCode' => "CONST_VALUE . '\$var'"],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            $this->searchSchemes("public {$phpCode}");

            self::assertCount(1, $this->phpReader->tmpResult->schemeClass->properties);
            self::assertArrayHasKey($options['name'], $this->phpReader->tmpResult->schemeClass->properties);

            $schemeProperty =  $this->phpReader->tmpResult->schemeClass->properties[$options['name']];
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $schemeProperty->{$name}, "For '{$name}' in code {$phpCode}");
            }
        }
    }
}
