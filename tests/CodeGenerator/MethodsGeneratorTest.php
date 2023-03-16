<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\MethodArgumentScheme;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see MethodsGenerator::exe()
 *
 * @run php tests/run.php tests/CodeGenerator/MethodsGeneratorTest.php
 */
class MethodsGeneratorTest extends TestCase
{
    private const ARGUMENTS_FOR_GENERAL = [
        'not_value' => ['isValue'=>false, 'isVariadic'=>false, 'isLink'=>false, 'type' => 'int', 'value' => ''],
        'link' => ['isValue'=>true, 'isVariadic'=>false, 'isLink'=>true, 'type' => 'string', 'value' => '123'],
        'variadic' => ['isValue'=>false, 'isVariadic'=>true, 'isLink'=>false, 'type' => '', 'value' => ''],
    ];

    /**
     * @todo  Добавить проверку возвращаемых значений
     */
    public function testGeneral(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::ABSTRACT_CLASSES(), 'TestGeneral' . uniqid());
        foreach (self::METHODS_FOR_GENERAL() as $method => $data)
        {
            $scheme->methods[$method] = new MethodScheme($scheme, $method);
            foreach ($data as $name => $value)
            {
                $scheme->methods[$method]->{$name} = $value;
            }
            $scheme->methods[$method]->isDefine = true;
        }

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(count(self::METHODS_FOR_GENERAL()), $newScheme->methods);

        foreach (self::METHODS_FOR_GENERAL() as $method => $data)
        {
            self::assertArrayHasKey($method, $newScheme->methods);
            self::assertEquals('', $newScheme->methods[$method]->innerPhpCode);
            foreach ($data as $name => $value)
            {
                self::assertEquals($value, $newScheme->methods[$method]->{$name}, "\${$method} in {$name}");
            }
        }
    }

    /**
     * @todo На основе этого теста сделать тест, с проверкой создания свойств класса в конструкторе
     */
    public function testArguments(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES(), 'testArguments' . uniqid());
        $scheme->methods['f'] = new MethodScheme($scheme, 'f');
        foreach (self::ARGUMENTS_FOR_GENERAL as $argument => $data) {
            $scheme->methods['f']->arguments[$argument] = new MethodArgumentScheme($scheme->methods['f'], $argument);
            foreach ($data as $name => $value)
            {
                $scheme->methods['f']->arguments[$argument]->{$name} = $value;
            }
        }

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(count(self::ARGUMENTS_FOR_GENERAL), $newScheme->methods['f']->arguments);

        foreach (self::ARGUMENTS_FOR_GENERAL as $argument => $data)
        {
            self::assertArrayHasKey($argument, $newScheme->methods['f']->arguments);
            foreach ($data as $name => $value)
            {
                self::assertEquals($value, $newScheme->methods['f']->arguments[$argument]->{$name}, "\${$argument} in {$name}");
            }
        }
    }

    private static function METHODS_FOR_GENERAL(): array
    {
        return [
            'f_public' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReturnLink' => false, 'isAbstract' => false],
            'f_protected' => ['view' => ViewScheme::PROTECTED(), 'isStatic' => false, 'isReturnLink' => false, 'isAbstract' => false],
            'f_private' => ['view' => ViewScheme::PRIVATE(), 'isStatic' => false, 'isReturnLink' => false, 'isAbstract' => false],
            'f_static' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isReturnLink' => false, 'isAbstract' => false],
            'f_link' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReturnLink' => true, 'isAbstract' => false],
            'f_abstract' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReturnLink' => false, 'isAbstract' => true],
        ];
    }
}
