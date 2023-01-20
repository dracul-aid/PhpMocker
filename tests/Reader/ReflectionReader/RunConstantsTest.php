<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runConstants()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunConstantsTest.php
 */
class RunConstantsTest extends TestCase
{
    private ClassScheme $classScheme;

    public function testClassWithoutConstants(): void
    {
        $this->classScheme = ReflectionReader::exe(get_class(
            new class {}
        ));

        self::assertIsArray($this->classScheme->constants);
        self::assertCount(0, $this->classScheme->constants);
    }

    public function testClassWithConstants(): void
    {
        $className = $this->createClassWithConstants();

        $this->classScheme = ReflectionReader::exe($className);

        self::assertIsArray($this->classScheme->constants);
        self::assertCount(6, $this->classScheme->constants);
        self::assertIsArray($this->classScheme->properties);
        self::assertCount(0, $this->classScheme->properties);
        self::assertIsArray($this->classScheme->methods);
        self::assertCount(0, $this->classScheme->methods);

        for ($i = 1; $i < 7; $i++)
        {
            $constName = "CONST_{$i}";
            self::assertArrayHasKey($constName, $this->classScheme->constants, "Not found {$constName}");
            self::assertFalse($this->classScheme->constants[$constName]->isEnumCase, "{$constName} is Enum Case");
        }

        self::assertTrue($this->classScheme->constants['CONST_1']->isFinal);
        self::assertFalse($this->classScheme->constants['CONST_2']->isFinal);

        self::assertEquals('111', $this->classScheme->constants['CONST_1']->value);
        self::assertEquals(222, $this->classScheme->constants['CONST_2']->value);
        self::assertEquals($this->classScheme->constants['CONST_1']->value, $this->classScheme->constants['CONST_3']->value);
        self::assertEquals(
            $this->classScheme->constants['CONST_1']->value . $this->classScheme->constants['CONST_2']->value,
            $this->classScheme->constants['CONST_4']->value
        );
        self::assertEquals(false, $this->classScheme->constants['CONST_5']->value);
        self::assertEquals(null, $this->classScheme->constants['CONST_6']->value);
    }

    public function testClassesWithConstants(): void
    {
        $className = $this->createClassesWithConstants();

        $this->classScheme = ReflectionReader::exe($className);

        self::assertIsArray($this->classScheme->constants);
        self::assertCount(5, $this->classScheme->constants);

        self::assertArrayHasKey('CONST_1_PUBLIC', $this->classScheme->constants);
        self::assertArrayHasKey('CONST_1_PROTECTED', $this->classScheme->constants);
        self::assertArrayHasKey('CONST_2_PUBLIC', $this->classScheme->constants);
        self::assertArrayHasKey('CONST_2_PROTECTED', $this->classScheme->constants);
        self::assertArrayHasKey('CONST_2_PRIVATE', $this->classScheme->constants);

        $this->assertConstSchemes('CONST_1_PUBLIC', '1_public', ViewScheme::PUBLIC, false);
        $this->assertConstSchemes('CONST_1_PROTECTED', '1_protected', ViewScheme::PROTECTED, false);
        $this->assertConstSchemes('CONST_2_PUBLIC', '2_public', ViewScheme::PUBLIC, true);
        $this->assertConstSchemes('CONST_2_PROTECTED', '2_protected', ViewScheme::PROTECTED, true);
        $this->assertConstSchemes('CONST_2_PRIVATE', '2_private', ViewScheme::PRIVATE, true);
    }

    private function assertConstSchemes(string $name, string $value, ViewScheme $view, bool $inClass): void
    {
        self::assertEquals($name, $this->classScheme->constants[$name]->name);
        self::assertEquals('', $this->classScheme->constants[$name]->innerPhpCode);
        self::assertEquals($value, $this->classScheme->constants[$name]->value);
        self::assertEquals($view, $this->classScheme->constants[$name]->view);
        self::assertEquals($inClass, $this->classScheme->constants[$name]->isDefine);
    }

    private function createClassWithConstants(): string
    {
        $className = 'RunConstantsTestClass1_' . uniqid();

        eval(
        <<<END
                class {$className} {
                    final public const CONST_1 = '111';
                    public const CONST_2 = 222;
                    public const CONST_3 = self::CONST_1;
                    public const CONST_4 = self::CONST_1 . self::CONST_2;
                    public const CONST_5 = false;
                    public const CONST_6 = null;
                }
            END
        );

        return $className;
    }

    private function createClassesWithConstants(): string
    {
        $className1 = 'RunConstantsTestClass2_' . uniqid();
        $className2 = 'RunConstantsTestClass3_' . uniqid();

        eval(
        <<<END
                class {$className1} {
                    public const CONST_1_PUBLIC = '1_public';
                    protected const CONST_1_PROTECTED = '1_protected';
                    private const CONST_1_PRIVATE = '1_private';
                }
                class {$className2} extends {$className1} {
                    public const CONST_2_PUBLIC = '2_public';
                    protected const CONST_2_PROTECTED = '2_protected';
                    private const CONST_2_PRIVATE = '2_private';
                }
            END
        );

        return $className2;
    }
}
