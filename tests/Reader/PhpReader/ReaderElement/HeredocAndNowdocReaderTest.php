<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\HeredocAndNowdocReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HeredocAndNowdocReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/HeredocAndNowdocReaderTest.php
 */
class HeredocAndNowdocReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private HeredocAndNowdocReader $heredocAndNowdocReader;

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->heredocAndNowdocReader = NotPublic::createObject(HeredocAndNowdocReader::class, [$this->phpReader]);
    }

    /**
     * Test for @see HeredocAndNowdocReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('<<START');
        $this->phpReader->codeString->read();
        self::assertFalse(HeredocAndNowdocReader::isStart($this->phpReader));

        $this->createObjects('<<<"START"');
        $this->phpReader->codeString->read();
        self::assertFalse(HeredocAndNowdocReader::isStart($this->phpReader));

        $this->createObjects("<<<START\n");
        $this->phpReader->codeString->read();
        self::assertTRUE(HeredocAndNowdocReader::isStart($this->phpReader));

        $this->createObjects("<<<'START'\n");
        $this->phpReader->codeString->read();
        self::assertTRUE(HeredocAndNowdocReader::isStart($this->phpReader));
    }

    public function testForHeredoc(): void
    {
        $this->createObjects("<<<START
            123
        START;Not_string");
        $this->phpReader->readWithStrings = true;

        $this->phpReader->codeString->read();
        $this->heredocAndNowdocReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->heredocAndNowdocReader->run() === null)
            {
                self::assertEquals("<<<START
            123
        START", $this->phpReader->codeTmp->result);
                break;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    public function testForNowdoc(): void
    {
        $this->createObjects("<<<'START'
            123
        START;Not_string");
        $this->phpReader->readWithStrings = true;

        $this->phpReader->codeString->read();
        $this->heredocAndNowdocReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->heredocAndNowdocReader->run() === null)
            {
                self::assertEquals("<<<'START'
            123
        START", $this->phpReader->codeTmp->result);
                break;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }
}
