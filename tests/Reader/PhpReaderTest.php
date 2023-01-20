<?php

namespace DraculAid\PhpMocker\tests\Reader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PhpReader
 *
 * @run php tests/run.php tests/Reader/PhpReaderTest.php
 */
class PhpReaderTest extends TestCase
{
    /**
     * @var PhpReader $phpReader
     */
    private object $phpReader;

    /**
     * Test for @see PhpReader::run()
     */
    public function testRunWithoutString(): void
    {
        $this->createObjects("123'string-line'/*comment*/456");
        $this->phpReader->readWithStrings = false;
        $this->phpReader->run();

        self::assertEquals('123456', $this->phpReader->codeTmp->result);
    }

    /**
     * Test for @see PhpReader::run()
     */
    public function testRunWithString(): void
    {
        $this->createObjects("123'string-line'/*comment*/456");
        $this->phpReader->readWithStrings = true;
        $this->phpReader->run();

        self::assertEquals("123'string-line'456", $this->phpReader->codeTmp->result);
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObjectAndReturnProxy(PhpReader::class, [$phpCode]);
    }
}
