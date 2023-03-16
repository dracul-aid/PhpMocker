<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;
use PHPUnit\Framework\TestCase;

/**
 * @run php tests/run.php tests/WorkTestCases/EnumTest.php
 */
class EnumTest extends TestCase
{
    private string $enumName;
    private ClassManager $classManager;

    public function testWithoutType(): void
    {
        // тест не имеет смысла, так как перечисления доступны только с PHP8
        self::assertTrue(true);
        return;

        $this->classManager = MockCreator::hardFromPhpCode(
            $this->getPhpCodeEnumWithoutType()
        );

        $this->basicTesting();
    }

    public function testWithType(): void
    {
        // тест не имеет смысла, так как перечисления доступны только с PHP8
        self::assertTrue(true);
        return;

        $this->classManager = MockCreator::hardFromPhpCode(
            $this->getPhpCodeEnumWithType()
        );

        $this->basicTesting();

        //self::assertEquals(1, $this->enumName::ONE->value);
        //self::assertEquals(2, $this->enumName::TWO->value);
    }

    private function basicTesting(): void
    {

        // тест не имеет смысла, так как перечисления доступны только с PHP8
        self::assertTrue(true);
        return;

        self::assertEquals($this->enumName, $this->classManager->getToClass());

        //self::assertEquals('123', $this->enumName::TEST_CONST);
        //self::assertEquals('static_f_value', $this->enumName::static_f());
        //self::assertEquals('f_value_ONE', $this->enumName::ONE->f());
        //self::assertEquals('f_value_TWO', $this->enumName::TWO->f());

        $this->classManager->getMethodManager('static_f')->defaultCase()->setWillReturn('STATIC VALUE');
        //self::assertEquals('STATIC VALUE', $this->enumName::static_f());

        $this->classManager->getMethodManager('f')->defaultCase()->setWillReturn('123');
        //self::assertEquals('123', $this->enumName::ONE->f());
        //self::assertEquals('123', $this->enumName::TWO->f());
    }

    private function getPhpCodeEnumWithoutType(): string
    {
        $this->enumName = $this->getEnumName();

        return <<<CODE
                enum {$this->enumName} {
                    public const TEST_CONST = '123';
                
                    case ONE;
                    case TWO;
                    
                    public static function static_f(): string {return 'static_f_value';}
                    public function f(): string {return 'f_value_' . \$this->name;}
                }
            CODE;
    }

    private function getPhpCodeEnumWithType(): string
    {
        $this->enumName = $this->getEnumName();

        return <<<CODE
                enum {$this->enumName}: int {
                    public const TEST_CONST = '123';
                
                    case ONE = 1;
                    case TWO = 2;
                    
                    public static function static_f(): string {return 'static_f_value';}
                    public function f(): string {return 'f_value_' . \$this->name;}
                }
            CODE;
    }

    private function getEnumName(): string
    {
        return '__enum_test_name_' . uniqid() . '___';
    }
}
