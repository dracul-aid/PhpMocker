<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\CodeTmp;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\TmpResult;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassSchemeCreator
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassSchemeCreatorTest.php
 */
class ClassSchemeCreatorTest extends TestCase
{
    private PhpReader $phpReader;

    public function testClassType(): void
    {
        $testCases = [
            'final class MyFinalClass' => ['type' => ClassSchemeType::CLASSES(), 'isFinal' => true, 'isReadonly' => false, 'name' => 'MyFinalClass', 'enumType' => ''],
            'readonly final class MyFinalReadonlyClass' => ['type' => ClassSchemeType::CLASSES(), 'isFinal' => true, 'isReadonly' => true, 'name' => 'MyFinalReadonlyClass', 'enumType' => ''],
            'class MyClass' => ['type' => ClassSchemeType::CLASSES(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyClass', 'enumType' => ''],
            'abstract class MyAbstractClass' => ['type' => ClassSchemeType::ABSTRACT_CLASSES(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyAbstractClass', 'enumType' => ''],
            'enum MyEnumWithoutValue' => ['type' => ClassSchemeType::ENUMS(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyEnumWithoutValue', 'enumType' => ''],
            'enum MyEnumWithValue: int' => ['type' => ClassSchemeType::ENUMS(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyEnumWithValue', 'enumType' => 'int'],
            'interface MyInterface' => ['type' => ClassSchemeType::INTERFACES(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyInterface', 'enumType' => ''],
            'trait MyTrait' => ['type' => ClassSchemeType::TRAITS(), 'isFinal' => false, 'isReadonly' => false, 'name' => 'MyTrait', 'enumType' => ''],
        ];

        foreach ($testCases as $phpCode => $classOptions)
        {
            $this->createObjectsAndParseCode(" {$phpCode} ");

            foreach ($classOptions as $name => $value)
            {
                self::assertEquals($value, $this->phpReader->tmpResult->schemeClass->{$name}, "option '{$name}' for code>>>{$phpCode}<<<");
            }
            self::assertEquals('', $this->phpReader->tmpResult->schemeClass->namespace);
            self::assertEquals('', $this->phpReader->tmpResult->schemeClass->innerPhpCode);
            self::assertEquals('', $this->phpReader->tmpResult->schemeClass->parent);
            self::assertEquals([], $this->phpReader->tmpResult->schemeClass->interfaces);
        }
    }

    public function testParents(): void
    {
        $testCases = [
            'class MyClass' => ['parent' => '', 'interfaces' => []],
            'class MyClass extends MyParent' => ['parent' => 'MyParent', 'interfaces' => []],
            'class MyClass implements InterfaceA, InterfaceB' => ['parent' => '', 'interfaces' => ['InterfaceA', 'InterfaceB']],
            'class MyClass extends MyParent implements InterfaceA, InterfaceB' => ['parent' => 'MyParent', 'interfaces' => ['InterfaceA', 'InterfaceB']],
            'interface MyClass extends InterfaceA, InterfaceB' => ['parent' => '', 'interfaces' => ['InterfaceA', 'InterfaceB']],
        ];

        foreach ($testCases as $phpCode => $classOptions)
        {
            $this->createObjectsAndParseCode(" {$phpCode} ");

            self::assertEquals('MyClass', $this->phpReader->tmpResult->schemeClass->name, "option 'name' for code: {$phpCode}");
            self::assertEquals('', $this->phpReader->tmpResult->schemeClass->namespace, "option 'namespace' for code: {$phpCode}");
            self::assertEquals($classOptions['parent'], $this->phpReader->tmpResult->schemeClass->parent, "option 'parent' for code: {$phpCode}");
            self::assertEquals($classOptions['interfaces'], $this->phpReader->tmpResult->schemeClass->interfaces, "option 'interfaces' for code: {$phpCode}");
        }
    }

    private function createObjectsAndParseCode(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->phpReader->codeTmp->result = $this->phpReader->codeString->phpCode;

        ClassSchemeCreator::exe($this->phpReader->codeTmp, $this->phpReader->tmpResult);
    }
}
