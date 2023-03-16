<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\PropertyScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PropertiesGenerator::exe()
 *
 * @run php tests/run.php tests/CodeGenerator/PropertiesGeneratorTest.php
 */
class PropertiesGeneratorTest extends TestCase
{
    public function testGeneral(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES(), 'TestGeneral' . uniqid());
        foreach (self::PROPERTIES() as $property => $data)
        {
            $scheme->properties[$property] = new PropertyScheme($scheme, $property);
            foreach ($data as $name => $value)
            {
                $scheme->properties[$property]->{$name} = $value;
            }
            if ($data['value'] !== '') $scheme->properties[$property]->isValue = true;
            $scheme->properties[$property]->isDefine = true;
        }

        $scheme->methods['getProperty'] = new MethodScheme($scheme, 'getProperty');
        $scheme->methods['getProperty']->argumentsPhpCode = 'string $name';
        $scheme->methods['getProperty']->innerPhpCode = 'return $this->{$name} ?? null;';
        $scheme->methods['getStaticProperty'] = new MethodScheme($scheme, 'getStaticProperty');
        $scheme->methods['getStaticProperty']->isStatic = true;
        $scheme->methods['getStaticProperty']->argumentsPhpCode = 'string $name';
        $scheme->methods['getStaticProperty']->innerPhpCode = 'return self::$$name ?? null;';

        ClassGenerator::generateCodeAndEval($scheme);

        //$testObject = new ($scheme->getFullName())(); - Подобный вызов невозможен до PHP8
        $testObject = eval("return new {$scheme->getFullName()}();");

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(4, $newScheme->properties);

        foreach (self::PROPERTIES() as $property => $data)
        {
            self::assertArrayHasKey($property, $newScheme->properties);
            self::assertEquals('', $newScheme->properties[$property]->innerPhpCode);
            foreach ($data as $name => $value)
            {
                self::assertEquals($value, $newScheme->properties[$property]->{$name}, "\${$property} in {$name}");
            }

            if ($newScheme->properties[$property]->isStatic) self::assertEquals($data['value'], $scheme->getFullName()::getStaticProperty($property), "\${$property} call");
            else self::assertEquals($data['value'], $testObject->getProperty($property), "\${$property} call");
        }
    }

    public function testInnerPhpCode(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES(), 'testInnerPhpCode' . uniqid());
        $scheme->properties['property'] = new PropertyScheme($scheme, 'property');
        $scheme->properties['property']->isStatic = true;
        $scheme->properties['property']->isValue = true;
        $scheme->properties['property']->innerPhpCode = 'PHP_VERSION . PHP_EOL';

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(1, $newScheme->properties);
        self::assertArrayHasKey('property', $newScheme->properties);

        self::assertEquals(PHP_VERSION . PHP_EOL, $scheme->getFullName()::$property);
    }

    private static function PROPERTIES(): array
    {
        return [
            'static' => ['value' => [], 'isStatic' => true, 'view' => ViewScheme::PUBLIC(), 'type' => ''],
            'string' => ['value' => '123', 'isStatic' => false, 'view' => ViewScheme::PUBLIC(), 'type' => ''],
            'bool_null' => ['value' => false, 'isStatic' => false, 'view' => ViewScheme::PROTECTED(), 'type' => '?bool'],
            'int' => ['value' => 123, 'isStatic' => false, 'view' => ViewScheme::PRIVATE(), 'type' => ''],
        ];
    }
}
