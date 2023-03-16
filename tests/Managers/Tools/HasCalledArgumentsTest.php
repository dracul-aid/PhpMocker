<?php

namespace DraculAid\PhpMocker\tests\Managers\Tools;

use DraculAid\PhpMocker\Managers\Tools\HasCalledArguments;
use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\Tools\HasCalledArguments
 *
 * @run php tests/run.php tests/Managers/Tools/HasCalledArgumentsTest.php
 */
class HasCalledArgumentsTest extends TestCase
{
    private const ARG_1_VAL = 123;
    private const ARG_2_VAL = [0 => '000', 1 => '111'];
    
    private HasCalledArguments $argumentObject;

    /**
     * @var int|null
     */
    private $arg1 = self::ARG_1_VAL;

    /**
     * @var int|array|string[]|null
     */
    private $arg2 = self::ARG_2_VAL;

    public function testRun(): void
    {
        $this->arg1 = self::ARG_1_VAL;
        $this->arg2 = self::ARG_2_VAL;

        $this->argumentObject = new HasCalledArguments(['arg1' => &$this->arg1, 'arg2' => &$this->arg2]);

        // * * *

        self::assertCount(2, $this->argumentObject);

        self::assertTrue($this->argumentObject->in(0));
        self::assertTrue($this->argumentObject->in(1));
        self::assertTrue($this->argumentObject->offsetExists(0));
        self::assertTrue($this->argumentObject->offsetExists(1));
        self::assertTrue($this->argumentObject->in('arg1'));
        self::assertTrue($this->argumentObject->in('arg2'));
        self::assertTrue($this->argumentObject->offsetExists('arg1'));
        self::assertTrue($this->argumentObject->offsetExists('arg2'));

        self::assertFalse($this->argumentObject->in(100));
        self::assertFalse($this->argumentObject->in(100, false));
        self::assertFalse($this->argumentObject->offsetExists(100));
        self::assertFalse($this->argumentObject->in('argX'));
        self::assertFalse($this->argumentObject->in('argX', false));
        self::assertFalse($this->argumentObject->offsetExists('argX'));

        self::assertTrue(TestTools::waitThrow([$this->argumentObject, 'in'], [100, true], \TypeError::class));
        self::assertTrue(TestTools::waitThrow([$this->argumentObject, 'in'], ['argX', true], \TypeError::class));

        $this->testingRead(self::ARG_1_VAL, self::ARG_2_VAL);

        // * * *

        $this->argumentObject->update(0, 321)->update(1, [3 => '333']);
        $this->testingRead(321, [3 => '333']);

        $this->argumentObject->update('arg1', 777);
        $this->testingRead(777, [3 => '333']);

        $this->argumentObject[1] = [4 => 'ABC'];
        $this->testingRead(777, [4 => 'ABC']);

        $this->argumentObject[1]['id'] = 'XYZ';
        $this->testingRead(777, [4 => 'ABC', 'id' => 'XYZ']);

        $this->argumentObject->update(['arg1' => self::ARG_1_VAL, 'arg2' => self::ARG_2_VAL]);
        $this->testingRead(self::ARG_1_VAL, self::ARG_2_VAL);

        // * * *

        unset($this->argumentObject[1][0]);
        self::assertEquals([1 => '111'], $this->argumentObject[1]);

        unset($this->argumentObject[0]);
        unset($this->argumentObject['arg2']);
        $this->testingRead(null, null);
    }

    public function testGenerator(): void
    {
        $this->arg1 = self::ARG_1_VAL;
        $this->arg2 = self::ARG_2_VAL;

        $this->argumentObject = new HasCalledArguments(['arg1' => &$this->arg1, 'arg2' => &$this->arg2]);

        // * * *

        $tmp = iterator_to_array($this->argumentObject->for(true));
        self::assertEquals(self::ARG_1_VAL, $tmp['arg1']);
        self::assertEquals(self::ARG_2_VAL, $tmp['arg2']);

        // * * *

        $tmp = iterator_to_array($this->argumentObject->for(false));
        self::assertEquals(self::ARG_1_VAL, $tmp[0]);
        self::assertEquals(self::ARG_2_VAL, $tmp[1]);

        // * * *

        $tmp = iterator_to_array($this->argumentObject);
        self::assertEquals(self::ARG_1_VAL, $tmp['arg1']);
        self::assertEquals(self::ARG_2_VAL, $tmp['arg2']);
    }

    private function testingRead($var1, $var2): void
    {
        self::assertEquals($var1, $this->argumentObject->getValue(0));
        self::assertEquals($var1, $this->argumentObject->getValue('arg1'));
        self::assertEquals($var2, $this->argumentObject->getValue(1));
        self::assertEquals($var2, $this->argumentObject->getValue('arg2'));

        self::assertEquals($var1, $this->argumentObject->getValueOrNull(0));
        self::assertEquals($var1, $this->argumentObject->getValueOrNull('arg1'));
        self::assertEquals($var2, $this->argumentObject->getValueOrNull(1));
        self::assertEquals($var2, $this->argumentObject->getValueOrNull('arg2'));

        self::assertEquals($var1, $this->argumentObject[0]);
        self::assertEquals($var1, $this->argumentObject['arg1']);
        self::assertEquals($var2, $this->argumentObject[1]);
        self::assertEquals($var2, $this->argumentObject['arg2']);

        self::assertEquals($var1, $this->arg1);
        self::assertEquals($var2, $this->arg2);
    }
}