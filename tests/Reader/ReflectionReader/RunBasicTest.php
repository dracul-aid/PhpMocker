<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use PHPUnit\Framework\TestCase;
use DraculAid\PhpMocker\Reader\ReflectionReader;

/**
 * Test for @see ReflectionReader::runBasic()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunBasicTest.php
 *
 * @todo TODO-PHP8.2: добавить проверку на isReadonly для классов
 */
class RunBasicTest extends TestCase
{
    private const CLASS_PARENT_AND_FINAL_NAME = 'FinalClassForTest';

    public function testAnonymousClass(): void
    {
        $classScheme = ReflectionReader::exe(
            get_class( new class() {} )
        );

        self::assertTrue($classScheme->isAnonymous);

        self::assertFalse($classScheme->isFinal);
        self::assertFalse($classScheme->isInternal);
        self::assertFalse($classScheme->isReadonly);

        self::assertSame('', $classScheme->parent);
    }

    public function testParentAndFinal(): void
    {
        $this->createFinalClassWithParent();

        $classScheme = ReflectionReader::exe(self::CLASS_PARENT_AND_FINAL_NAME);

        self::assertTrue($classScheme->isFinal);

        self::assertFalse($classScheme->isAnonymous);
        self::assertFalse($classScheme->isInternal);
        self::assertFalse($classScheme->isReadonly);

        self::assertSame('\stdClass', $classScheme->parent);
        self::assertSame(self::CLASS_PARENT_AND_FINAL_NAME, $classScheme->getFullName());
    }

    public function testInternalClass(): void
    {
        $classScheme = ReflectionReader::exe('\stdClass');

        self::assertTrue($classScheme->isInternal);

        self::assertFalse($classScheme->isAnonymous);
        self::assertFalse($classScheme->isFinal);
        self::assertFalse($classScheme->isReadonly);

        self::assertSame('', $classScheme->parent);
        self::assertSame('stdClass', $classScheme->getFullName());
    }

    private function createFinalClassWithParent(): void
    {
        eval('final class ' . self::CLASS_PARENT_AND_FINAL_NAME . ' extends \stdClass {}');
    }
}
