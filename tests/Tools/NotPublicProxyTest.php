<?php

namespace DraculAid\PhpMocker\tests\Tools;

use DraculAid\PhpMocker\Tools\NotPublicProxy;
use DraculAid\PhpMocker\NotPublic;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see NotPublicProxy
 *
 * @run php tests/run.php tests/Tools/NotPublicProxyTest.php
 */
class NotPublicProxyTest extends TestCase
{
    /**
     * Test for @see NotPublic::NotPublicProxy
     */
    public function testRun(): void
    {
        $proxy = new NotPublicProxy(
            $this->getObjectForTest()
        );

        $this->runTesting($proxy);
    }

    /**
     * Test for:
     * @see NotPublic::Proxy()
     * @see NotPublic::getProxy()
     */
    public function testForGetProxy(): void
    {
        $proxy = NotPublic::proxy(
            $this->getObjectForTest()
        );

        $this->runTesting($proxy);

        // * * *

        $proxy = NotPublic::instance(
            $this->getObjectForTest()
        )->getProxy();

        $this->runTesting($proxy);
    }

    /**
     * Test for @see NotPublic::createObjectAndReturnProxy()
     */
    public function testForCreateObjectAndReturnProxy(): void
    {
        $proxy = NotPublic::createObjectAndReturnProxy(
            get_class($this->getObjectForTest())
        );

        $this->runTesting($proxy);
    }

    private function runTesting(NotPublicProxy $proxy): void
    {
        self::assertEquals('private_var_value', $proxy->private_var);
        $proxy->private_var = '123';
        self::assertEquals('123', $proxy->private_var);
        self::assertEquals('private_call_123', $proxy->private_function('123'));
    }

    private function getObjectForTest(): object
    {
        return new class() {
            private $private_var = 'private_var_value';
            private function private_function(string $t): string
            {
                return "private_call_{$t}";
            }
        };
    }
}
