<?php

namespace DraculAid\PhpMocker\tests;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\tests\Reader\PhpReader\Tools\NotPublicProxyTest;
use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see NotPublic
 *
 * @run php tests/run.php tests/NotPublicTest.php
 *
 * @see NotPublicProxyTest for test methods:
 * @see NotPublic::createObjectAndReturnProxy()
 * @see NotPublic::Proxy()
 * @see NotPublic::getProxy()
 */
class NotPublicTest extends TestCase
{
    /**
     * Test for:
     * @see NotPublic::constant()
     * @see NotPublic::get()
     * @see NotPublic::getStatic()
     * @see NotPublic::set()
     * @see NotPublic::setStatic()
     * @see NotPublic::call()
     * @see NotPublic::callStatic()
     */
    public function testObject(): void
    {
        $testNotPublic = NotPublic::instance($this->createObject());

        self::assertEquals('private_const_value', $testNotPublic->constant('PRIVATE_CONST'));

        self::assertEquals('private_var_value', $testNotPublic->get('private_var'));
        self::assertEquals('private_static_var_value', $testNotPublic->getStatic('private_static_var'));

        $testNotPublic->set('private_var', '111')->setStatic('private_static_var', '222');
        self::assertEquals('111', $testNotPublic->get('private_var'));
        self::assertEquals('222', $testNotPublic->getStatic('private_static_var'));

        $testNotPublic->set(['private_var' => 'AAA'])->setStatic(['private_static_var' => 'BBB']);
        self::assertEquals('AAA', $testNotPublic->get('private_var'));
        self::assertEquals('BBB', $testNotPublic->getStatic('private_static_var'));

        $f_t1 = 'A111';
        self::assertEquals("private_function_return_[A111]_B222", $testNotPublic->call('private_function', [&$f_t1, 'B222']));
        self::assertEquals("[A111]", $f_t1);
        $f_t1 = 'С111';
        self::assertEquals("private_static_function_return_[С111]_D222", $testNotPublic->callStatic('private_static_function', [&$f_t1, 'D222']));
        self::assertEquals('[С111]', $f_t1);
    }

    /**
     * Test for:
     * @see NotPublic::readConstant()
     * @see NotPublic::readProperty()
     * @see NotPublic::writeProperty()
     * @see NotPublic::callMethod()
     */
    public function testEasyObject(): void
    {
        $testObject = $this->createObject();
        $testClass = get_class($testObject);

        self::assertEquals('private_const_value', NotPublic::readConstant($testObject, 'PRIVATE_CONST'));
        self::assertEquals('private_const_value', NotPublic::readConstant($testClass, 'PRIVATE_CONST'));

        self::assertEquals('private_var_value', NotPublic::readProperty($testObject,'private_var'));
        self::assertEquals('private_static_var_value', NotPublic::readProperty($testClass, 'private_static_var'));

        NotPublic::writeProperty($testObject, 'private_var', '111');
        NotPublic::writeProperty($testClass, 'private_static_var', '222');
        self::assertEquals('111', NotPublic::readProperty($testObject,'private_var'));
        self::assertEquals('222', NotPublic::readProperty($testClass, 'private_static_var'));

        NotPublic::writeProperty($testObject, ['private_var' => 'AAA']);
        NotPublic::writeProperty($testClass, ['private_static_var' => 'BBB']);
        self::assertEquals('AAA', NotPublic::readProperty($testObject,'private_var'));
        self::assertEquals('BBB', NotPublic::readProperty($testClass, 'private_static_var'));

        $f_t1 = 'A111';
        self::assertEquals("private_function_return_[A111]_B222", NotPublic::callMethod($testObject, 'private_function', [&$f_t1, 'B222']));
        self::assertEquals("[A111]", $f_t1);
        $f_t1 = 'С111';
        self::assertEquals("private_static_function_return_[С111]_D222", NotPublic::callMethod($testClass, 'private_static_function', [&$f_t1, 'D222']));
        self::assertEquals('[С111]', $f_t1);

        // * * *

        $f_t1 = 'A111';
        self::assertEquals("private_function_return_[A111]_B222", NotPublic::callMethod([$testObject, 'private_function'], [&$f_t1, 'B222']));
        self::assertEquals("[A111]", $f_t1);
        $f_t1 = 'С111';
        self::assertEquals("private_static_function_return_[С111]_D222", NotPublic::callMethod([$testClass, 'private_static_function'], [&$f_t1, 'D222']));
        self::assertEquals('[С111]', $f_t1);

        // * * *

        self::assertTrue(TestTools::waitThrow([NotPublic::class, 'callMethod'], [[]], \TypeError::class));
        self::assertTrue(TestTools::waitThrow([NotPublic::class, 'callMethod'], [['class']], \TypeError::class));
        self::assertTrue(TestTools::waitThrow([NotPublic::class, 'callMethod'], [['class', 'method', 'error']], \TypeError::class));

        self::assertTrue(TestTools::waitThrow([NotPublic::class, 'callMethod'], ['class', []], \TypeError::class));
    }

    /**
     * Test for @see NotPublic::createObject()
     */
    public function testCreateObjectWithoutConstructor(): void
    {
        $className = get_class($this->createObject());
        $testObject = NotPublic::createObject($className, false, ['public_var' => '123', 'private_var' => 'ABC']);

        self::assertEquals("123", $testObject->public_var);
        self::assertEquals("construct_var_not_set", $testObject->construct_var);
        self::assertEquals("construct_argument_var_not_set", $testObject->construct_argument_var);
        self::assertEquals("ABC", NotPublic::instance($testObject)->get('private_var'));
    }

    /**
     * Test for @see NotPublic::createObject()
     */
    public function testCreateObjectWithConstructor(): void
    {
        $className = get_class($this->createObject());
        $testObject = NotPublic::createObject($className, ['XXX'], ['public_var' => '123', 'private_var' => 'ABC']);

        self::assertEquals("123", $testObject->public_var);
        self::assertEquals("construct_var_set_ok", $testObject->construct_var);
        self::assertEquals("construct_argument_var_set_XXX", $testObject->construct_argument_var);
        self::assertEquals("ABC", NotPublic::instance($testObject)->get('private_var'));
    }

    /**
     * Создает объект для тестирования взаимодействия с непубличными элементами
     *
     * @return object
     */
    private function createObject(string $set_var = 'null'): object
    {
        $className = '___Test_Class_Name_' . uniqid() . '___';

        $classInner = <<<'CODE'
            private const PRIVATE_CONST = 'private_const_value';

            public string $public_var = 'public_var_value';
            public string $construct_var = 'construct_var_not_set';
            public string $construct_argument_var = 'construct_argument_var_not_set';

            private string $private_var = 'private_var_value';
            private static string $private_static_var = 'private_static_var_value';

            public function __construct(string $set_var = 'null')
            {
                $this->construct_var = 'construct_var_set_ok';
                $this->construct_argument_var = "construct_argument_var_set_{$set_var}";
            }

            private function private_function(string &$t1, string $t2): string
            {
                $t1 = "[{$t1}]";
                return "private_function_return_{$t1}_{$t2}";
            }
            private static function private_static_function(string &$t1, string $t2): string
            {
                $t1 = "[{$t1}]";
                return "private_static_function_return_{$t1}_{$t2}";
            }
        CODE;

        eval("class {$className} {{$classInner}}");

        return new $className($set_var);
    }
}
