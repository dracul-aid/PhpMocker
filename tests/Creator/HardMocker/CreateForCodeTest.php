<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Creator\MockClassInterfaces\MockClassInterface;
use DraculAid\PhpMocker\Creator\MockClassInterfaces\HardMockClassInterface;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpCodeWithoutElementsException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockerCreateForInterface;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassIsInternalException;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::createForCode()
 *
 * @run php tests/run.php tests/Creator/HardMocker/CreateForCodeTest.php
 */
class CreateForCodeTest extends TestCase
{
    public function testCreateFail(): void
    {
        try {
            HardMocker::createForCode('$var = 123;');
            $this->fail();
        }
        catch (HardMockClassCreatorPhpCodeWithoutElementsException $err)
        {
            self::assertEquals("PHP code without classes", $err->getMessage());
        }

        // * * *

        try {
            HardMocker::createForCode("class RuntimeException {}");
            $this->fail();
        }
        catch (MockClassCreatorClassIsInternalException $err)
        {
            self::assertEquals("Class RuntimeException is internal class", $err->getMessage());
        }

        // * * *

        $testClassName = '___test_class_name_' . uniqid() . '___';
        eval("class {$testClassName} {}");

        try {
            HardMocker::createForCode("class {$testClassName} {}");
            $this->fail();
        }
        catch (MockClassCreatorClassWasLoadedException $err)
        {
            self::assertEquals("Class {$testClassName} was loaded", $err->getMessage());
        }

        // * * *

        $testClassName = '___test_interface_name_' . uniqid() . '___';
        try {
            HardMocker::createForCode("interface {$testClassName} {}");
            $this->fail();
        }
        catch (HardMockerCreateForInterface $err)
        {
            self::assertEquals("Class {$testClassName} is a Interface", $err->getMessage());
        }
    }

    public function testCreateSuccessful(): void
    {
        $className = $this->getClassName();
        $phpCode = "class {$className} {}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);

        self::assertTrue(is_object($classManager));
        self::assertTrue(get_class($classManager) === ClassManager::class);

        $interfaces = class_implements($classManager->toClass);
        self::assertArrayHasKey(HardMockClassInterface::class, $interfaces);
        self::assertArrayHasKey(MockClassInterface::class, $interfaces);

        // * * *

        $className1 = $this->getClassName();
        $className2 = $this->getClassName();
        $phpCode = "class {$className1} {} class {$className2} {}";

        $classManagers = MockCreator::hardFromPhpCode($phpCode);

        self::assertTrue(is_array($classManagers));
        self::assertCount(2, $classManagers);
        self::assertArrayHasKey($className1, $classManagers);
        self::assertArrayHasKey($className2, $classManagers);
    }

    private function getClassName(): string
    {
        return '___test_class_name_' . uniqid() . '___';
    }
}
