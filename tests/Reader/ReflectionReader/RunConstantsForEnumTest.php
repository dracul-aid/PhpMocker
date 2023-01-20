<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runConstants()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunConstantsForEnumTest.php
 */
class RunConstantsForEnumTest extends TestCase
{
    public function testRunWithoutType(): void
    {
        $className = $this->createClassForRunWithoutType();
        $classScheme = ReflectionReader::exe($className);

        self::assertEquals('', $classScheme->enumType);

        $constantsList = [
            'PUBLIC_CONST' => ['view' => ViewScheme::PUBLIC, 'value' => 'public_const', 'name' => 'PUBLIC_CONST', 'isEnumCase' => false],
            'PROTECTED_CONST' => ['view' => ViewScheme::PROTECTED, 'value' => 'protected_const', 'name' => 'PROTECTED_CONST', 'isEnumCase' => false],
            'ONE' => ['view' => ViewScheme::PUBLIC, 'value' => $className::ONE, 'name' => 'ONE', 'isEnumCase' => true],
            'TWO' => ['view' => ViewScheme::PUBLIC, 'value' => $className::TWO, 'name' => 'TWO', 'isEnumCase' => true],
        ];

        $this->testing($classScheme->constants, $constantsList);
    }

    public function testRunWithType(): void
    {
        $className = $this->createClassRunWithType();
        $classScheme = ReflectionReader::exe($className);

        self::assertEquals('int', $classScheme->enumType);

        $constantsList = [
            'ONE' => ['value' => $className::ONE, 'name' => 'ONE', 'isEnumCase' => true],
            'TWO' => ['value' => $className::TWO, 'name' => 'TWO', 'isEnumCase' => true],
        ];

        $this->testing($classScheme->constants, $constantsList);
    }

    /**
     * @param ConstantScheme[] $constants
     * @param array       $equals
     *
     * @return void
     */
    private function testing(array $constants, array $equals): void
    {
        self::assertIsArray($constants);
        self::assertCount(count($equals), $constants);

        foreach ($equals as $name => $options)
        {
            self::assertArrayHasKey($name, $constants, "Not Found {$name}");
            foreach ($options as $optionName => $value)
            {
                self::assertEquals($value, $constants[$name]->{$optionName});
            }
        }
    }

    private function createClassForRunWithoutType(): string
    {
        $className = 'RunWithoutType_' . uniqid();

        eval(
        <<<END
                enum {$className} {
                    public const PUBLIC_CONST = 'public_const';
                    protected const PROTECTED_CONST = 'protected_const';
                    case ONE;
                    case TWO;
                }
            END
        );

        return $className;
    }

    private function createClassRunWithType(): string
    {
        $className = 'RunWithType_' . uniqid();

        eval(
        <<<END
                enum {$className}: int {
                    case ONE = 111;
                    case TWO = 222;
                }
            END
        );

        return $className;
    }
}
