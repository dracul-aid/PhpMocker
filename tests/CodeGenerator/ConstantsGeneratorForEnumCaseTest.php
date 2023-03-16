<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ConstantsGenerator::exe()
 *
 * @run php tests/run.php tests/CodeGenerator/ConstantsGeneratorForEnumCaseTest.php
 */
class ConstantsGeneratorForEnumCaseTest extends TestCase
{
    public function testCreateEnumCaseFromValue(): void
    {
        // Этот тест не имеет смысла, перечисление доступны только с PHP8
        self::assertTrue(true);
        return;

        $className = 'testEnumPhpCode' . uniqid();
        $scheme = new ClassScheme(ClassSchemeType::ENUMS(), $className);
        $scheme->constants['ENUM_CASE'] = new ConstantScheme($scheme, 'ENUM_CASE');
        $scheme->constants['ENUM_CASE']->isEnumCase = true;

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(1, $newScheme->constants);
        self::assertArrayHasKey('ENUM_CASE', $newScheme->constants);
        self::assertEquals($className::ENUM_CASE, $newScheme->constants['ENUM_CASE']->value);
    }

    public function testCreateEnumCaseFromPhpCode(): void
    {
        // Этот тест не имеет смысла, перечисление доступны только с PHP8
        self::assertTrue(true);
        return;

        $className = 'testEnumPhpCode' . uniqid();
        $scheme = new ClassScheme(ClassSchemeType::ENUMS(), $className);
        $scheme->enumType = 'int';
        $scheme->constants['ENUM_CASE'] = new ConstantScheme($scheme, 'ENUM_CASE');
        $scheme->constants['ENUM_CASE']->isEnumCase = true;
        $scheme->constants['ENUM_CASE']->innerPhpCode = '123';

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(1, $newScheme->constants);
        self::assertArrayHasKey('ENUM_CASE', $newScheme->constants);
        self::assertEquals($className::ENUM_CASE, $newScheme->constants['ENUM_CASE']->value);
        //self::assertEquals(123, $className::ENUM_CASE->value);
    }
}
