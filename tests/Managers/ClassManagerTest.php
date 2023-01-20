<?php

namespace DraculAid\PhpMocker\tests\Managers;

use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\ClassManager
 *
 * @run php tests/run.php tests/Managers/ClassManagerTest.php
 */
class ClassManagerTest extends TestCase
{
    private const CLASS_FOR_CLOSE_ELEMENTS_VALUES = [
        'PROTECTED_CONST' => 'protected_const_value',
        'protected_var' => 'protected_var_value',
        'f_protected' => 'f_protected_return_',
        'set_list_1' => '111',
        'set_list_2' => '222',
        'in_construct_1' => 'A-',
        'in_construct_2' => 'B-',
    ];

    /**
     * Test for: @see ClassManager::getManager()
     */
    public function testGetClassManager(): void
    {
        self::assertNull(ClassManager::getManager('ClassIdNotInClassManagerList' . uniqid()));

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());

        self::assertTrue($classManager == ClassManager::getManager($classManager->toClass));
    }

    /**
     * Test for:
     * @see ClassManager::createObject()
     * @see ClassManager::createObjectWithoutConstructor()
     * @see ClassManager::createObjectAndManager()
     *
     * And test methods inside @see ClassManager:
     * @see ObjectManager::getProperty()
     * @see ObjectManager::setProperty()
     */
    public function testCreateObjects(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());

        $object0 = new $classManager->toClass('A0', 'B0');
        self::assertEquals('A0', $object0->in_construct_1);
        self::assertEquals('B0', $object0->in_construct_2);
        self::assertTrue($object0 === $classManager->objectManagers[$object0]->toObject);

        $object1 = $classManager->createObject('A1', 'B2');
        self::assertEquals('A1', $object1->in_construct_1);
        self::assertEquals('B2', $object1->in_construct_2);
        self::assertTrue($object1 === $classManager->objectManagers[$object1]->toObject);

        /** @var ObjectManager $objectManager2 */
        $classManager->createObjectWithoutConstructor(['is_not_in_constructor' => 'ABC123'], $objectManager2);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_1'], $objectManager2->toObject->in_construct_1);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_2'], $objectManager2->toObject->in_construct_2);
        self::assertEquals('ABC123', $objectManager2->getProperty('is_not_in_constructor'));

        /** @var ObjectManager $objectManager3 */
        $object3 = $classManager->createObjectWithoutConstructor(['is_not_in_constructor' => 'ZXC098'], $objectManager3);
        self::assertTrue($object3 === $objectManager3->toObject);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_1'], $object3->in_construct_1);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_2'], $object3->in_construct_2);
        self::assertEquals('ZXC098', $object3->is_not_in_constructor);
        self::assertTrue($object3 === $classManager->objectManagers[$object3]->toObject);

        $objectManager4 = $classManager->createObjectAndManager(false, ['is_not_in_constructor' => 'ASD456']);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_1'], $objectManager4->toObject->in_construct_1);
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['in_construct_2'], $objectManager4->toObject->in_construct_2);
        self::assertEquals('ASD456', $objectManager4->getProperty('is_not_in_constructor'));

        $objectManager5 = $classManager->createObjectAndManager(['A11', 'B22'], ['is_not_in_constructor' => '123ABC'], $object5);
        self::assertTrue($object5 === $objectManager5->toObject);
        self::assertEquals('A11', $object5->in_construct_1);
        self::assertEquals('B22', $object5->in_construct_2);
        self::assertEquals('123ABC', $object5->is_not_in_constructor);
        self::assertTrue($object5 === $classManager->objectManagers[$object5]->toObject);
    }

    /**
     * Test for:
     * @see ClassManager::$methodManagers
     * @see ClassManager::getMethodManager()
     */
    public function testGetMethodManager(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());

        self::assertEquals(MethodManager::class, get_class($classManager->getMethodManager('f_protected')));
        self::assertTrue($classManager->methodManagers['f_protected'] === $classManager->getMethodManager('f_protected'));
    }

    /**
     * Test for:
     * @see ClassManager::getMethodManager()
     */
    public function testGetMethodManagerNotFound(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());

        $this->expectException(\RuntimeException::class);

        $classManager->getMethodManager('f_protected_not_method');
    }

    /**
     * Test for:
     * @see ClassManager::getConst()
     * @see ClassManager::getProperty()
     * @see ClassManager::setProperty()
     * @see ClassManager::callMethod()
     */
    public function testForCloseElements(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );

        $classManager = SoftMocker::createClass($scheme->getFullName());

        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['PROTECTED_CONST'], $classManager->getConst('PROTECTED_CONST'));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['protected_var'], $classManager->getProperty('protected_var'));
        $classManager->setProperty('protected_var', '123');
        self::assertEquals('123', $classManager->getProperty('protected_var'));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['f_protected'] . 'ABC', $classManager->callMethod('f_protected', 'ABC'));

        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['set_list_1'], $classManager->getProperty('set_list_1'));
        self::assertEquals(self::CLASS_FOR_CLOSE_ELEMENTS_VALUES['set_list_2'], $classManager->getProperty('set_list_2'));
        $classManager->setProperty(['set_list_1' => 'AAA', 'set_list_2' => 'BBB']);
        self::assertEquals('AAA', $classManager->getProperty('set_list_1'));
        self::assertEquals('BBB', $classManager->getProperty('set_list_2'));

    }

    private function generateClass(): string
    {
        $className = 'generateClassForCloseElements' . uniqid();
        $values = self::CLASS_FOR_CLOSE_ELEMENTS_VALUES;

        eval(
            <<<END
                class {$className}
                {
                    protected const PROTECTED_CONST = '{$values['PROTECTED_CONST']}';
                    protected static string \$protected_var = '{$values['protected_var']}';
                    protected static function f_protected(string \$t)
                    {
                        return "{$values['f_protected']}{\$t}";
                    }
                    
                    protected static string \$set_list_1 = '{$values['set_list_1']}';
                    protected static string \$set_list_2 = '{$values['set_list_2']}';
                    
                    public string \$in_construct_1 = '{$values['in_construct_1']}';
                    public string \$in_construct_2 = '{$values['in_construct_2']}';
                    public string \$is_not_in_constructor = 'XXX';
                    public function __construct(string \$in_construct_1, string \$in_construct_2)
                    {
                        \$this->in_construct_1 = \$in_construct_1;
                        \$this->in_construct_2 = \$in_construct_2;
                    }
                }
            END
        );

        return $className;
    }
}
