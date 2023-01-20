<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker\CreateClassExecuting;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Tools\CreateClassImplementsTraits;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::createClassExecuting() for trait {}
 *
 * @run php tests/run.php tests/Creator/HardMocker/CreateClassExecuting/TraitTest.php
 */
class TraitTest extends TestCase
{
    public function testRun(): void
    {
        $traitName = $this->getClassName();
        $phpCode = "trait {$traitName} {public static function f1(){return '111';}}";
        $traitManager = MockCreator::hardFromPhpCode($phpCode);

        $className = $this->getClassName();
        CreateClassImplementsTraits::exe($traitName, $className);

        self::assertEquals('111', $className::f1());
        $traitManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $className::f1());
    }

    private function getClassName(): string
    {
        return '___test_trait_name_' . uniqid() . '___';
    }
}
