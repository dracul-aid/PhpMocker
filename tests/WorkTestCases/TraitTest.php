<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\MockManager;
use DraculAid\PhpMocker\Tools\CreateClassImplementsTraits;
use PHPUnit\Framework\TestCase;

/**
 * @run php tests/run.php tests/WorkTestCases/TraitTest.php
 */
class TraitTest extends TestCase
{
    private ClassManager $traitManager;
    private string $testClassName;

    public function testRun(): void
    {
        $this->createMockForTrait();

        $this->testClassName = CreateClassImplementsTraits::exe(
            $this->traitManager->getToClass()
        );

        self::assertEquals('111', $this->testClassName::f1());
        $this->traitManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $this->testClassName::f1());

        // $testObject = new ($this->testClassName)(); - Такое создание объекта невозможно до PHP8
        $testObject = eval("return new {$this->testClassName}();");

        self::assertEquals('222', $testObject->f2());
        $this->traitManager->getMethodManager('f2')->defaultCase()->setWillReturn('BBB');
        self::assertEquals('BBB', $testObject->f2());

        MockManager::getForObject($testObject)->getMethodManager('f2')->defaultCase()->setWillReturn('CCC');
        self::assertEquals('CCC', $testObject->f2());
    }

    private function createMockForTrait(): void
    {
        $traitName = $this->getNewClassName();

        $traitPhpCode = "trait {$traitName} {public static function f1() {return '111';} public function f2() {return '222';}}";

        $this->traitManager = MockCreator::hardFromPhpCode($traitPhpCode);
    }

    private function getNewClassName(): string
    {
        return '___test_class_name__' . uniqid();
    }
}
