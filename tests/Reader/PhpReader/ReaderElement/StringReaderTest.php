<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\StringReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see StringReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/StringReaderTest.php
 */
class StringReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private StringReader $stringReader;

    /**
     * Test for @see StringReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('0123456789');
        $this->phpReader->codeString->read();
        self::assertFalse(StringReader::isStart($this->phpReader));

        $this->createObjects('"123');
        $this->phpReader->codeString->read();
        self::assertTrue(StringReader::isStart($this->phpReader));

        $this->createObjects("'123");
        $this->phpReader->codeString->read();
        self::assertTrue(StringReader::isStart($this->phpReader));

        $this->createObjects('`123');
        $this->phpReader->codeString->read();
        self::assertFalse(StringReader::isStart($this->phpReader));
    }

    public function testForQuote1(): void
    {
        $this->createObjects("'012\'3'4");
        $this->phpReader->readWithStrings = true;

        $this->testing("'012\'3'");

        // * * *

        $slash = '\\';
        $testString = "'{$slash}{$slash}'";

        $this->createObjects($testString);
        $this->phpReader->readWithStrings = true;

        $this->testing($testString);
    }

    public function testForQuote2(): void
    {
        $this->createObjects('"012\"3"45');
        $this->phpReader->readWithStrings = true;

        $this->testing('"012\"3"');

        // * * *

        $slash = '\\';
        $testString = '"' . $slash . $slash . '"';

        $this->createObjects($testString);
        $this->phpReader->readWithStrings = true;

        $this->testing($testString);
    }

    public function testForNotSaveString(): void
    {
        $this->createObjects('"012\"3"45');
        $this->phpReader->readWithStrings = false;

        $this->testing('');
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->stringReader = NotPublic::createObject(StringReader::class, [$this->phpReader]);
    }

    private function testing(string $assertEqualsString): void
    {
        $this->phpReader->codeString->read();
        $this->stringReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();

            if ($this->stringReader->run() === null)
            {
                self::assertEquals($assertEqualsString, $this->phpReader->codeTmp->result);
                break;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail('Stop, because end string');
        }
    }
}
