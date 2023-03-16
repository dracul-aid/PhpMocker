<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface;
use DraculAid\PhpMocker\ClassAutoloader\Drivers\ComposerAutoloaderDriver;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderExceptionInterface;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathException;
use DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see Autoloader
 *
 * @run php tests/run.php tests/ClassAutoloader/AutoloaderTest.php
 */
class AutoloaderTest extends TestCase
{
    /**
     * Test for @see Autoloader::getPath()
     */
    public function testPathNotFound(): void
    {
        $autoloader = new Autoloader(
            new class () implements AutoloaderDriverInterface {
                public function getPath(string $class): string {return '';}
                public function unregister(): void {}
            },
            new DefaultAutoloaderFilter(),
            ''
        );

        self::assertEquals('', $autoloader->getPath('class_name'));

        try {
            $autoloader->getPath('class_name', true);
            $this->fail();
        }
        catch (PhpMockerAutoloaderPathException $error) {}
    }

    /**
     * Test for @see Autoloader::canClassLoadAsMock()
     */
    public function testCanClassLoadAsMock(): void
    {
        $autoloader = new class() extends Autoloader {
            public function __construct() {
                $this->autoloaderFilter = new DefaultAutoloaderFilter();
            }
            public function canClassLoadAsMock(string $class, string $classPath): bool
            {
                return parent::canClassLoadAsMock($class, $classPath);
            }
        };

        self::assertTrue($autoloader->canClassLoadAsMock('test_class', ''));
        self::assertTrue($autoloader->canClassLoadAsMock('DraculAid\\test_class', ''));
        self::assertFalse($autoloader->canClassLoadAsMock('DraculAid\\PhpMocker\\ClassName', ''));
        self::assertFalse($autoloader->canClassLoadAsMock('DraculAid\\PhpMocker\\Catalog\\ClassName', ''));
    }

    /**
     * Test for @see Autoloader::load()
     */
    public function testExecutingClassLoad(): void
    {
        $autoloader = $this->createMockAutoloaderForTestExecutingClassLoad('');
        self::assertFalse($autoloader->load('test_class'));

        // * * *

        $autoloader = $this->createMockAutoloaderForTestExecutingClassLoad('class_path');
        self::assertTrue($autoloader->load('test_class'));
        self::assertFalse($autoloader->callRequireClassFile);
        self::assertTrue($autoloader->callExecutingClassLoadCreateMock);

        // * * *

        $autoloader = $this->createMockAutoloaderForTestExecutingClassLoad('class_path');
        $autoloader->autoMockerEnabled = false;
        self::assertTrue($autoloader->load('test_class'));
        self::assertTrue($autoloader->callRequireClassFile);
        self::assertFalse($autoloader->callExecutingClassLoadCreateMock);
    }

    /**
     * Test for @see Autoloader::executingClassLoadCreateMock()
     */
    public function testExecutingClassLoadCreateMockOk(): void
    {
        $autoloader = $this->createMockAutoloaderForTestExecutingClassLoadCreateMock();

        self::assertTrue($autoloader->executingClassLoadCreateMock('DraculAidPhpMockerExamples\\AbstractClass',dirname(__DIR__, 2) . '/examples-ru/classes/AbstractClass.php', false));
        self::assertTrue(class_exists('DraculAidPhpMockerExamples\\AbstractClass', false));

        self::assertTrue($autoloader->executingClassLoadCreateMock('DraculAidPhpMockerExamples\\BasicClass', dirname(__DIR__, 2) . '/examples-ru/classes/BasicClass.php', true));
        self::assertTrue(class_exists('DraculAidPhpMockerExamples\\BasicClass', false));

        self::assertTrue($autoloader->executingClassLoadCreateMock('DraculAidPhpMockerExamples\\ClassInterface', dirname(__DIR__, 2) . '/examples-ru/classes/ClassInterface.php', true));
        self::assertTrue(interface_exists('DraculAidPhpMockerExamples\\ClassInterface', false));
    }

    /**
     * Test for @see Autoloader::executingClassLoadCreateMock()
     */
    public function testExecutingClassLoadCreateMockFail(): void
    {
        $autoloader = $this->createMockAutoloaderForTestExecutingClassLoadCreateMock();

        self::assertFalse($autoloader->executingClassLoadCreateMock('not-class','path_not_found', false));

        $this->expectException(PhpMockerAutoloaderExceptionInterface::class);

        $autoloader->executingClassLoadCreateMock('not-class','path_not_found', true);
    }

    private function createMockAutoloaderForTestExecutingClassLoad(string $driverReturnPath): Autoloader
    {
        return new class($driverReturnPath) extends Autoloader {
            public bool $callRequireClassFile = false;
            public bool $callExecutingClassLoadCreateMock = false;
            public string $mockClassCachePath;
            public string $driverReturnPath;
            public function __construct(string $driverReturnPath)
            {
                $this->driverReturnPath = $driverReturnPath;
                $this->autoloaderFilter = new DefaultAutoloaderFilter();
                $this->mockClassCachePath = '';
            }
            public function getPath(string $class, bool $ifFailToException = false): string
            {
                return $this->driverReturnPath;
            }
            public function requireClassFile(string $classPath, bool $ifFailToException): bool
            {
                $this->callRequireClassFile = true;
                return true;
            }
            public function executingClassLoadCreateMock(string $class, string $classPath, bool $ifFailToException): bool
            {
                $this->callExecutingClassLoadCreateMock = true;
                return true;
            }

        };
    }

    private function createMockAutoloaderForTestExecutingClassLoadCreateMock(): Autoloader
    {
        return new class() extends Autoloader {
            public string $mockClassCachePath;
            public function __construct()
            {
                $this->mockClassCachePath = '';
            }
            public function executingClassLoadCreateMock(string $class, string $classPath, bool $ifFailToException): bool
            {
                return parent::executingClassLoadCreateMock($class, $classPath, $ifFailToException);
            }
        };
    }
}
