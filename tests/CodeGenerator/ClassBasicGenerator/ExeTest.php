<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator\ClassBasicGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassBasicGenerator::exeForClassWords()
 *
 * @run php tests/run.php tests/CodeGenerator/ClassBasicGenerator/ExeTest.php
 *
 * @todo TODO-PHP8.2: добавить проверку на isReadonly для классов
 */
class ExeTest extends TestCase
{
    public function testCreateClassType(): void
    {
        $types = [
            ClassSchemeType::INTERFACES,
            ClassSchemeType::CLASSES,
            ClassSchemeType::ABSTRACT_CLASSES,
            ClassSchemeType::TRAITS,
            ClassSchemeType::ENUMS,
        ];

        foreach ($types as $classType) {
            $scheme = new ClassScheme($classType, 'testCreateClassType' . uniqid());
            ClassGenerator::generateCodeAndEval($scheme);

            $newScheme = ReflectionReader::exe($scheme->getFullName());
            self::assertEquals($scheme->getFullName(), $newScheme->getFullName());
            self::assertEquals($scheme->type, $newScheme->type);
        }
    }

    public function testCreateInterfaceWithParent(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::INTERFACES, 'TestCreateInterfaceWithParentInterface' . uniqid());
        $scheme->interfaces['\Stringable'] = '\Stringable';
        $scheme->interfaces['\Countable'] = '\Countable';
        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(2, $newScheme->interfaces);
        self::assertArrayHasKey('\Stringable', $newScheme->interfaces);
        self::assertArrayHasKey('\Countable', $newScheme->interfaces);
    }

    public function testCreateClassWithParentAndInterface(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES, 'TestCreateClassWithParentAndInterface' . uniqid());
        $scheme->interfaces['\Stringable'] = '\Stringable';
        $scheme->methods['__toString'] = new MethodScheme($scheme, '__toString');
        $scheme->methods['__toString']->innerPhpCode = "return '';";
        $scheme->parent = '\stdClass';
        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(1, $newScheme->interfaces);
        self::assertArrayHasKey('\Stringable', $newScheme->interfaces);

        self::assertEquals('\stdClass', $newScheme->parent);
    }

    public function testCreateFinalClass(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES, 'TestCreateInterfaceWithParentInterface' . uniqid());
        $scheme->isFinal = false;
        ClassGenerator::generateCodeAndEval($scheme);
        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertFalse($newScheme->isFinal);

        $scheme = new ClassScheme(ClassSchemeType::CLASSES, 'TestCreateInterfaceWithParentInterface' . uniqid());
        $scheme->isFinal = true;
        ClassGenerator::generateCodeAndEval($scheme);
        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertTrue($newScheme->isFinal);
    }

    public function testCreateEnum(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::ENUMS, 'TestCreateEnumWithoutType' . uniqid());
        ClassGenerator::generateCodeAndEval($scheme);
        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertEquals($scheme->getFullName(), $newScheme->getFullName());
        self::assertEquals('', $newScheme->enumType);

        // * * *

        $scheme = new ClassScheme(ClassSchemeType::ENUMS, 'TestCreateEnumWithType' . uniqid());
        $scheme->enumType = 'int';
        ClassGenerator::generateCodeAndEval($scheme);
        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertEquals($scheme->getFullName(), $newScheme->getFullName());
        self::assertEquals('int', $newScheme->enumType);
    }
}
