<?php

namespace DraculAid\PhpMocker\tests\Managers;

use DraculAid\PhpMocker\CreateOptions\ClassName;
use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\ObjectManager
 *
 * @run php tests/run.php tests/Managers/ObjectManagerTest.php
 */
class ObjectManagerTest extends TestCase
{
    private const CLASS_FOR_CLOSE_ELEMENTS_VALUES = [
        'protected_var' => 'protected_var_value',
        'f_protected' => 'f_protected_',
        'set_list_1' => 'A-',
        'set_list_2' => 'B-',
    ];

    /**
     * Test For:
     * @see ObjectManager::getClassManager()
     * @see ObjectManager::getToClass()
     * @see ObjectManager::getDriver()
     */
    public function testClassProperties(): void
    {
        $objectManager = new ObjectManager(new \stdClass());

        self::assertNull($objectManager->getClassManager());
        self::assertNull($objectManager->getToClass());
        self::assertNull($objectManager->getDriver());

        // * * *

        $className = '___TestClassName_' . uniqid() . '___';

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName(), new ClassName($className));
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);

        self::assertTrue($classManager === $objectManager->getClassManager());

        self::assertTrue($classManager->getToClass() === $objectManager->getToClass());
        self::assertEquals($className, $objectManager->getToClass());

        self::assertTrue($classManager->getDriver() === $objectManager->getDriver());
        self::assertEquals(SoftMocker::class, $objectManager->getDriver());
    }

    /**
     * Test For:
     * @see ObjectManager::getClassManager()
     * @see ObjectManager::$methodManagers
     * @see ObjectManager::getMethodManager()
     */
    public function testGetClassAndMethod(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);

        self::assertEquals(get_class($testObject), get_class($objectManager->toObject), 'Class Name Failed');
        self::assertTrue($classManager === $objectManager->getClassManager(), 'getClassManager() Failed');

        self::assertEquals(MethodManager::class, get_class($objectManager->getMethodManager('f_protected')), 'method() Failed');
        self::assertTrue($objectManager->methodManagers['f_protected'] === $objectManager->getMethodManager('f_protected'));
    }

    /**
     * Test for @see ObjectManager::getMethodManager()
     */
    public function testGetMethodManagerNotFound(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();

        $this->expectException(MethodManagerNotFoundException::class);

        $objectManager->getMethodManager('f_protected_not_method');
    }

    /**
     * Test for:
     * @see ObjectManager::getProperty()
     * @see ObjectManager::setProperty()
     * @see ObjectManager::callMethod()
     */
    public function testForCloseElements(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );

        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager();

        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['protected_var'], $objectManager->getProperty('protected_var'));
        $objectManager->setProperty('protected_var', '123');
        self::assertEquals('123', $objectManager->getProperty('protected_var'));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['f_protected'] . 'ABC', $objectManager->callMethod('f_protected', 'ABC'));

        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['set_list_1'], $objectManager->getProperty('set_list_1'));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['set_list_2'], $objectManager->getProperty('set_list_2'));
        $objectManager->setProperty(['set_list_1' => 'AAA', 'set_list_2' => 'BBB']);
        self::assertEquals('AAA', $objectManager->getProperty('set_list_1'));
        self::assertEquals('BBB', $objectManager->getProperty('set_list_2'));
    }

    private function generateClass(): string
    {
        $className = 'generateClassForCloseElements' . uniqid();
        $values = self::CLASS_FOR_CLOSE_ELEMENTS_VALUES;

        eval(
        <<<END
                class {$className}
                {
                    protected string \$protected_var = '{$values['protected_var']}';
                    protected function f_protected(string \$t): string
                    {
                        return '{$values['f_protected']}' . \$t;
                    }
                
                    public string \$set_list_1 = '{$values['set_list_1']}';
                    public string \$set_list_2 = '{$values['set_list_2']}';
                }
            END
        );

        return $className;
    }
}
