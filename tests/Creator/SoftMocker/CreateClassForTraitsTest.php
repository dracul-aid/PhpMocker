<?php

namespace DraculAid\PhpMocker\tests\Creator\SoftMocker;

use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see SoftMocker::createClassForTraits()
 *
 * @run php tests/run.php tests/Creator/SoftMocker/CreateClassForTraitsTest.php
 */
class CreateClassForTraitsTest extends TestCase
{
    private string $traitName1;
    private string $traitName2;

    public function testRun(): void
    {
        $this->createTraits();

        $mockClassManager = SoftMocker::createClassForTraits([$this->traitName1, $this->traitName2]);
        $mockObjectManager = $mockClassManager->createObjectAndManager();

        $classScheme = ReflectionReader::exe($mockClassManager->toClass);
        $classParentScheme = ReflectionReader::exe($classScheme->parent);
        self::assertCount(2, $classParentScheme->traits);
        self::assertArrayHasKey("\\{$this->traitName1}", $classParentScheme->traits);
        self::assertArrayHasKey("\\{$this->traitName1}", $classParentScheme->traits);

        self::assertEquals('111', $mockObjectManager->toObject->f1());
        self::assertEquals('222', $mockObjectManager->toObject->f2());

        $mockObjectManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $mockObjectManager->toObject->f1());
        $mockObjectManager->getMethodManager('f2')->defaultCase()->setWillReturn('BBB');
        self::assertEquals('BBB', $mockObjectManager->toObject->f2());
    }

    private function createTraits(): void
    {
        $this->traitName1 = '__test_trait_name_1_' . uniqid() . '___';
        $this->traitName2 = '__test_trait_name_2_' . uniqid() . '___';

        eval(<<<CODE
                trait {$this->traitName1} {
                    public function f1()
                    {
                        return '111';
                    }
                }
                trait {$this->traitName2} {
                    public function f2()
                    {
                        return '222';
                    }
                }
            CODE
        );
    }
}
