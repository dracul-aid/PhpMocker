<?php

namespace DraculAid\PhpMocker\tests\Tools;

use DraculAid\PhpMocker\Tools\CallableObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CallableObject
 *
 * @run php tests/run.php tests/Tools/CallableObjectTest.php
 */
class CallableObjectTest extends TestCase
{
    /**
     * Test for @see CallableObject::call()
     */
    public function testRun(): void
    {
        $object = $this->getTestObject();

        $object->f = new CallableObject('trim');
        self::assertEquals('123', $object->f->call([' 123 ']));

        $object->f = new CallableObject('trim', [' 123 ']);
        self::assertEquals('123', $object->f->call());
        self::assertEquals('456', $object->f->call([' 456 ']));

        $object->f = new CallableObject(
            function ($t1, $t2, $t3) {
                return "{$t1}-{$t2}-{$t3}";
            },
            ['11', '22', '33']
        );
        self::assertEquals('AA-22-CC', $object->f->call(['AA', 2 => 'CC']));

        $t1 = null;
        $object->f = new CallableObject([$this, 'methodForTest'], ['A', &$t1]);
        self::assertEquals('222-A', $object->f->call());
        self::assertEquals('111-A', $t1);

        $t1 = null;
        $object->f = new CallableObject([$this, 'methodForTest']);
        self::assertEquals('222-B', $object->f->call(['B', &$t1]));
        self::assertEquals('111-B', $t1);
    }

    public function methodForTest(string $t1, null|string &$t2): string
    {
        $t2 = "111-{$t1}";
        return "222-{$t1}";
    }

    private function getTestObject(): object
    {
        return new class() {
            public CallableObject $f;
        };
    }
}
