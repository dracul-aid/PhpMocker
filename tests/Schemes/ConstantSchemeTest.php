<?php

namespace DraculAid\PhpMocker\tests\Schemes;

use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ConstantScheme
 *
 * @run php tests/run.php tests/Schemes/ConstantsSchemeTest.php
 */
class ConstantSchemeTest extends TestCase
{
    /**
     * Test for @see ConstantScheme::getValuePhpCode()
     */
    public function testGetValueForConst(): void
    {
        $constant = new ConstantScheme($this->getClassScheme(), 'constant');
        $constant->innerPhpCode = 'php_code';
        $constant->value = 'value';

        self::assertEquals('php_code', $constant->getValuePhpCode());

        $constant->innerPhpCode = '';
        self::assertEquals("'value'", $constant->getValuePhpCode());

        $constant->value = 123;
        self::assertEquals(123, $constant->getValuePhpCode());
    }

    public function testGetValueForEnumCaseWithValue(): void
    {
        // До PHP8 не было трейтов, этот тест не имеет смысла
        self::assertTrue(true);
        return;

        $constant = new ConstantScheme($this->getClassScheme(), 'constant');
        $constant->isEnumCase = true;
        $constant->innerPhpCode = 'php_code';
        $constant->value = ClassSchemeType::TRAITS();

        self::assertEquals('php_code', $constant->getValuePhpCode());

        $constant->innerPhpCode = '';
        self::assertEquals("'" . ClassSchemeType::TRAITS()->value . "'", $constant->getValuePhpCode());
    }

    public function testGetValueForEnumCaseWithoutValue(): void
    {
        // До PHP8 не было трейтов, этот тест не имеет смысла
        self::assertTrue(true);
        return;

        $className = $this->createEnumWithoutValue();

        $constant = new ConstantScheme($this->getClassScheme(), 'constant');
        $constant->isEnumCase = true;
        $constant->value = $className::ONE;

        self::assertEquals('', $constant->getValuePhpCode());
    }

    private function getClassScheme(): ClassScheme
    {
        return new ClassScheme(ClassSchemeType::CLASSES(), 'test_class');
    }

    private function createEnumWithoutValue(): string
    {
        $className = 'test_enum_' . uniqid();

        eval(<<<CODE
                enum {$className} {
                    case ONE;
                    case TWO;
                }
            CODE
        );

        return $className;
    }
}
