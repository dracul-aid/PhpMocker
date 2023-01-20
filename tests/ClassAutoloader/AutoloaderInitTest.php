<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader;

use DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit;
use DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AutoloaderInit
 *
 * @run php tests/run.php tests/ClassAutoloader/AutoloaderInitTest.php
 */
class AutoloaderInitTest extends TestCase
{
    public function testIncludeAndCreateAutoloaderInit(): void
    {
        $autoloaderInit = include(dirname(__DIR__, 2) . '/src/autoloader.php');

        self::assertTrue($autoloaderInit::class === AutoloaderInit::class);
    }

    public function testCreateWithDriver(): void
    {
        $autoloaderInit = new AutoloaderInit();
        $autoloaderInit->setAutoloaderDriver(new class() implements AutoloaderDriverInterface {
            public bool $unregisterCall = false;
            public function getPath(string $class): string {return 'ABC' . $class;}
            public function unregister(): void {$this->unregisterCall = true;}
        });

        $autoloader = $autoloaderInit->create(false);
        self::assertTrue($autoloader->autoloaderDriver->unregisterCall);
        self::assertEquals('ABCtest', $autoloader->getPath('test'));
    }

    public function testCreateDefaultDriver(): void
    {
        $autoloaderInit = new AutoloaderInit();
        $autoloaderInit->setDriverAutoloaderUnregister(false);

        $autoloader = $autoloaderInit->create(false);
        self::assertTrue((bool) $autoloader->getPath(AutoloaderInitTest::class));
        self::assertEquals(realpath(__FILE__), realpath($autoloader->getPath(AutoloaderInitTest::class)));
    }
}
