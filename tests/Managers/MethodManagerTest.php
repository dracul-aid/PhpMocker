<?php

namespace DraculAid\PhpMocker\tests\Managers;

use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerIncorrectForObjectException;
use DraculAid\PhpMocker\Exceptions\PhpMockerLogicException;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Tools\CallableObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\MethodManager
 *
 * @run php tests/run.php tests/Managers/MethodManagerTest.php
 */
class MethodManagerTest extends TestCase
{
    private const CLASS_FOR_CLOSE_ELEMENTS_VALUES = [
        'f_protected_static' => 'f_protected_static_return_',
        'f_protected' => 'f_protected_return_',
        'f_protected_final' => 'f_protected_final_return_',
    ];

    /**
     * Test for @see MethodManager::call()
     *
     * Метод тестируется для случаев создания мок-класса с помощью наследования, т.е. @see SoftMocker
     *
     * @todo Реализовать для способа создания с помощью "изменения PHP кода" - \sf\tests\mocker\Hard
     */
    public function testSoftCall(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();

        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['f_protected_static'] . 'A1', $classManager->getMethodManager('f_protected_static')->call(['A1']));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['f_protected'] . 'A2', $objectManager->getMethodManager('f_protected')->call(['A2']));
    }

    /**
     * Test for @see MethodManager::call()
     */
    public function testCallNoStaticFailed(): void
    {
        $this->expectException(\Error::class);

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());

        $classManager->getMethodManager('f_protected')->call(['A2']);
    }

    /**
     * Test for @see MethodManager::call()
     */
    public function testCallStaticFailed(): void
    {
        $this->expectException(MethodManagerIncorrectForObjectException::class);

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();

        $objectManager->getMethodManager('f_protected_static')->call(['A1']);
    }

    /**
     * Test for:
     * @see MethodManager::defaultCase()
     * @see MethodManager::getOrCreateCase()
     */
    public function testGetCaseFailed(): void
    {
        $this->expectException(PhpMockerLogicException::class);

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();

        $objectManager->getMethodManager('f_protected_final')->defaultCase();
    }

    /**
     * Test for @see MethodManager::clearCases()
     */
    public function testClearCases(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();
        $methodManager = $objectManager->getMethodManager('f_protected');

        $methodManager->defaultCase()->setWillReturn('');
        $methodManager->case('1')->setWillReturn('');
        $methodManager->userFunction = new CallableObject(static function() {});
        self::assertEquals('', $objectManager->getMethodManager('f_protected')->call(['1']));

        $methodManager->clearCases();
        self::assertNull($methodManager->userFunction);
        self::assertCount(0, $methodManager->cases);
        self::assertEquals(0, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallUserFunctionReturn);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['f_protected'] . '1', $methodManager->call(['1']));
    }

    private function generateClass(): string
    {
        $className = 'generateClassForCloseElements' . uniqid();
        $values = self::CLASS_FOR_CLOSE_ELEMENTS_VALUES;

        eval(
        <<<END
                class {$className}
                {
                    protected static function f_protected_static(string \$t)
                    {
                        return "{$values['f_protected_static']}{\$t}";
                    }
                    protected function f_protected(string \$t)
                    {
                        return "{$values['f_protected']}{\$t}";
                    }
                    final protected function f_protected_final(string \$t)
                    {
                        return "{$values['f_protected_final']}{\$t}";
                    }
                }
            END
        );

        return $className;
    }
}
