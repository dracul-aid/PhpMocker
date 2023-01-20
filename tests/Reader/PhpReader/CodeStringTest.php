<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader;

use DraculAid\PhpMocker\Reader\PhpReader\CodeString;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CodeString
 *
 * @run php tests/run.php tests/Reader/PhpReader/CodeStringTest.php
 */
class CodeStringTest extends TestCase
{
    /**
     * Test for:
     * @see CodeString::read()
     * @see CodeString::charClear()
     */
    public function testRead(): void
    {
        $codeString = new CodeString('0123456789');

        $codeString->read();
        self::assertEquals('0', $codeString->charFirst);
        self::assertEquals('1', $codeString->charSecond);
        self::assertEquals('23456789', $codeString->phpCode);

        $codeString->read();
        self::assertEquals('1', $codeString->charFirst);
        self::assertEquals('2', $codeString->charSecond);
        self::assertEquals('3456789', $codeString->phpCode);

        $codeString->charClear();
        $codeString->read();
        self::assertEquals('3', $codeString->charFirst);
        self::assertEquals('4', $codeString->charSecond);
        self::assertEquals('56789', $codeString->phpCode);

        $codeString->read(true);
        self::assertEquals('5', $codeString->charFirst);
        self::assertEquals('6', $codeString->charSecond);
        self::assertEquals('789', $codeString->phpCode);
    }

    /**
     * Test for @see CodeString::isWordStart()
     */
    public function testIsWordStart(): void
    {
        $codeString = (new CodeString('0123456789 ABC'));
        $codeString->read();

        self::assertTrue($codeString->isWordStart(false, false, '23456789'));
        self::assertTrue($codeString->isWordStart(false, '1', '23456789'));
        self::assertTrue($codeString->isWordStart('0', false, '23456789'));
        self::assertTrue($codeString->isWordStart('0', '1', '23456789'));

        self::assertFalse($codeString->isWordStart(false, false, 'ABC23456789'));
        self::assertFalse($codeString->isWordStart(false, 'A', '23456789'));
        self::assertFalse($codeString->isWordStart('A', false, '23456789'));
        self::assertFalse($codeString->isWordStart('A', '1', '23456789'));
        self::assertFalse($codeString->isWordStart('0', 'A', '23456789'));
        self::assertFalse($codeString->isWordStart('0', '1', 'ABC23456789'));
    }
}
