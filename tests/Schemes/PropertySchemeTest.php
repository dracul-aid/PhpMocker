<?php

namespace DraculAid\PhpMocker\tests\Schemes;

use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\PropertyScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PropertyScheme
 *
 * @run php tests/run.php tests/Schemes/PropertySchemeTest.php
 */
class PropertySchemeTest extends TestCase
{
    /**
     * Test for @see PropertyScheme::getValuePhpCode
     */
    public function testGetValuePhpCode(): void
    {
        $schemes = new ClassScheme(ClassSchemeType::CLASSES(), 'testGetValuePhpCode' . uniqid());
        $schemes->properties['test'] = new PropertyScheme($schemes, 'test');
        $schemes->properties['test']->isValue = false;
        $schemes->properties['test']->value = true;
        $schemes->properties['test']->innerPhpCode = '';
        self::assertEquals(null, $schemes->properties['test']->getValuePhpCode());

        $schemes->properties['test']->isValue = true;
        self::assertEquals(true, $schemes->properties['test']->getValuePhpCode());

        $schemes->properties['test']->innerPhpCode = 'XXXYYYZZZ';
        self::assertEquals('XXXYYYZZZ', $schemes->properties['test']->getValuePhpCode());
    }

    /**
     * Test for:
     * @see PropertyScheme::setValue()
     * @see PropertyScheme::clearValue()
     */
    public function testSetValueAndClearValue(): void
    {
        $schemes = new ClassScheme(ClassSchemeType::CLASSES(), 'testGetValuePhpCode' . uniqid());
        $schemes->properties['test'] = new PropertyScheme($schemes, 'test');
        self::assertFalse($schemes->properties['test']->isValue);
        self::assertEquals('', $schemes->properties['test']->value);

        $schemes->properties['test']->setValue('XXXYYYZZZ');
        self::assertTrue($schemes->properties['test']->isValue);
        self::assertEquals('XXXYYYZZZ', $schemes->properties['test']->value);

        $schemes->properties['test']->clearValue();
        self::assertFalse($schemes->properties['test']->isValue);
        self::assertEquals('', $schemes->properties['test']->value);
    }
}
