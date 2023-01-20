<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker;

use DraculAid\PhpMocker\Creator\HardMocker;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::getTextWhyMethodIsNotMockMethod()
 *
 * @run php tests/run.php tests/Creator/HardMocker/MethodCanMockTest.php
 */
class MethodCanMockTest extends TestCase
{
    private string $className;
    private string|object $owner;

    public function testToObject(): void
    {
        $this->createClass(false);
        $this->owner = new $this->className();
        $this->testing(false);
    }

    public function testToClass(): void
    {
        $this->createClass(true);
        $this->owner = $this->className;
        $this->testing(true);
    }

    private function createClass(bool $withAbstract): void
    {
        $this->className = '___test_class_' . uniqid() . '___';

        $classWord = $withAbstract ? 'abstract ' : '';
        $abstractFunction = $withAbstract ? 'abstract public function abstract_f(): void;' : '';

        eval(
        <<<CODE
                {$classWord} class {$this->className} {
                    public function f(): void {}
                    {$abstractFunction}
                }
            CODE
        );
    }

    private function testing(bool $withAbstract): void
    {
        self::assertEquals('Method not_f() not found', HardMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'not_f'));
        if ($withAbstract) self::assertEquals('Method not_f() not found', HardMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'not_f'));
        self::assertEquals('', HardMocker::getTextWhyMethodIsNotMockMethod($this->owner, 'f'));
    }
}
