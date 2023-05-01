<?php

namespace DraculAid\PhpMocker\tests\Managers\MethodUserFunctions;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\NotPublic;
use PHPUnit\Framework\TestCase;

class AbstractMethodUserFunctionTesting extends TestCase
{
    protected ClassManager $classManager;
    protected null|ObjectManager $objectManager = null;
    protected HasCalled $hasCalled;
    protected MethodManager $methodManager;

    protected function createObjects(bool $withCallObject): void
    {
        $className = '___test_class_name_' . uniqid() . '___';

        $this->classManager = MockCreator::hardFromPhpCode("class {$className} {
                public static \$staticVar = 'not_value';
                public string \$var = 'not_value';
            }");

        if ($withCallObject) $this->objectManager = $this->classManager->createObjectAndManager();

        // * * *

        $this->hasCalled = NotPublic::createObject(
            HasCalled::class,
            false,
            [
                'callClass' => $className,
                'callObject' => $this->objectManager ? $this->objectManager->toObject : null,
            ],
        );

        $this->methodManager = NotPublic::createObject(MethodManager::class);
    }
}
