<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CodeReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReaderTest.php
 */
class CodeReaderTest extends TestCase
{
    /**
     * Test for @see CodeReader::run()
     */
    public function testCodeBlockDeepCounter(): void
    {
        /**
         * @var PhpReader $phpReader
         */
        $phpReader = NotPublic::createObjectAndReturnProxy(PhpReader::class, ["code{{{}{"]);
        $phpReader->tmpResult->codeBlockForClassDeep = 10;
        $phpReader->run();
        self::assertEquals(3, $phpReader->tmpResult->codeBlockDeep);
    }

    /**
     * Test for @see CodeReader::run()
     */
    public function testClearCode(): void
    {
        /**
         * @var PhpReader $phpReader
         */
        $phpReader = NotPublic::createObjectAndReturnProxy(PhpReader::class, ["123;456"]);
        $phpReader->run();
        self::assertEquals('456', trim($phpReader->codeTmp->result));
    }
}
