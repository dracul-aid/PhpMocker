<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\CodeGenerator\ConstantsGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ConstantsGenerator::exe()
 *
 * @run php tests/run.php tests/CodeGenerator/ConstantsGeneratorTest.php
 */
class ConstantsGeneratorTest extends TestCase
{
    public function testGeneral(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES(), 'TestGeneral' . uniqid());
        foreach (self::CONSTANTS() as $name => $data)
        {
            $scheme->constants[$name] = new ConstantScheme($scheme, $name, $data['value']);
            $scheme->constants[$name]->isDefine = true;
            $scheme->constants[$name]->view = $data['view'];
        }

        $scheme->methods['getConst'] = new MethodScheme($scheme, 'getConst');
        $scheme->methods['getConst']->isStatic = true;
        $scheme->methods['getConst']->argumentsPhpCode = 'string $name';
        $scheme->methods['getConst']->innerPhpCode = 'return constant(self::class . "::" . $name);';

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(4, $newScheme->constants);

        foreach (self::CONSTANTS() as $const => $data)
        {
            self::assertArrayHasKey($const, $newScheme->constants);
            self::assertEquals('', $newScheme->constants[$const]->innerPhpCode);
            foreach ($data as $name => $value)
            {
                self::assertEquals($value, $newScheme->constants[$const]->{$name});
            }
            self::assertEquals($data['value'], $scheme->getFullName()::getConst($const));
        }
    }

    public function testInnerPhpCode(): void
    {
        $scheme = new ClassScheme(ClassSchemeType::CLASSES(), 'testInnerPhpCode' . uniqid());
        $scheme->constants['TEST'] = new ConstantScheme($scheme, 'TEST');
        $scheme->constants['TEST']->innerPhpCode = 'PHP_VERSION . PHP_EOL';

        ClassGenerator::generateCodeAndEval($scheme);

        $newScheme = ReflectionReader::exe($scheme->getFullName());
        self::assertCount(1, $newScheme->constants);
        self::assertArrayHasKey('TEST', $newScheme->constants);

        self::assertEquals(PHP_VERSION . PHP_EOL, constant($scheme->getFullName() . '::TEST'));
    }

    private static function CONSTANTS(): array
    {
        return [
            'C_PUBLIC' => ['view' => ViewScheme::PUBLIC(), 'value' => '123'],
            'C_PROTECTED' => ['view' => ViewScheme::PROTECTED(), 'value' => 123],
            'C_PRIVATE' => ['view' => ViewScheme::PRIVATE(), 'value' => false],
            'C_FINAL' => ['view' => ViewScheme::PUBLIC(), 'value' => null],
        ];
    }
}
