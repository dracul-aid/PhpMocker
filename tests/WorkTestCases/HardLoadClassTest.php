<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;
use PHPUnit\Framework\TestCase;

/**
 * @run php tests/run.php tests/WorkTestCases/HardLoadClassTest.php
 */
class HardLoadClassTest extends TestCase
{
    private Autoloader $classAutoloader;

    public function testRun(): void
    {
        $this->autoloaderInit();

        $classManager = MockCreator::hardLoadClass(\DraculAidPhpMockerExamples\BasicClass::class);

        self::assertTrue(is_a($classManager, ClassManager::class));
    }

    private function autoloaderInit(): void
    {
        /** @var AutoloaderInit $autoloaderInit */
        $autoloaderInit = require(dirname(__DIR__, 2) . '/src/autoloader.php');

        $this->classAutoloader = $autoloaderInit->setComposerVendorPath(dirname(__DIR__, 2) . '/vendor')->create();
        $this->classAutoloader->autoMockerEnabled = false;
    }
}
