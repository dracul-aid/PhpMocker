<?php

namespace DraculAid\PhpMocker\tests\Creator\SoftMocker;

use DraculAid\PhpMocker\Creator\SoftMocker;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see SoftMocker::getTextWhyMethodIsNotMockMethod()
 *
 * @run php tests/run.php tests/Creator/SoftMocker/MethodCanMockTest.php
 */
class MethodCanMockTest extends TestCase
{
    private string $className;

    /**
     * @var string|object $owner
     */
    private $owner;

    public function testToObject(): void
    {
        $this->createClass();
        $this->owner = new $this->className();
        $this->testing();
    }

    public function testToClass(): void
    {
        $this->createClass();
        $this->owner = $this->className;
        $this->testing();
    }

    private function createClass(): void
    {
        $parentClass = '___test_parent_class_' . uniqid() . '___';
        $this->className = '___test_class_' . uniqid() . '___';

        eval(
            <<<CODE
                class {$parentClass} {
                    private function parent_f(): void {}
                }
                class {$this->className} extends {$parentClass} {
                    public function f(): void {}
                    final public function final_f(): void {}
                }
            CODE
        );
    }

    private function testing(): void
    {
        self::assertEquals('Method not_f() not found or it is a private method', SoftMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'not_f'));
        self::assertEquals('Method parent_f() not found or it is a private method', SoftMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'parent_f'));
        self::assertEquals('Method final_f() is a final method', SoftMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'final_f'));
        self::assertEquals('', SoftMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'f'));
    }
}
