<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CommentLineReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CommentLineReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CommentLineReaderTest.php
 */
class CommentLineReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private CommentLineReader $commentLineReader;

    /**
     * Test for @see CommentLineReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('#/');
        $this->phpReader->codeString->read();
        self::assertFalse(CommentLineReader::isStart($this->phpReader));

        $this->createObjects('/ /');
        $this->phpReader->codeString->read();
        self::assertFalse(CommentLineReader::isStart($this->phpReader));

        $this->createObjects('#');
        $this->phpReader->codeString->read();
        self::assertFalse(CommentLineReader::isStart($this->phpReader));

        $this->createObjects('//');
        $this->phpReader->codeString->read();
        self::assertTrue(CommentLineReader::isStart($this->phpReader));
    }

    /**
     * Test for @see CommentLineReader::Run()
     */
    public function testRun(): void
    {
        $this->createObjects("//comment\n456;");
        $this->phpReader->codeString->read();
        $this->commentLineReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->commentLineReader->run() === null)
            {
                self::assertEquals('comment', NotPublic::instance($this->commentLineReader)->get('result'));
                break;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->commentLineReader = NotPublic::createObject(CommentLineReader::class, [$this->phpReader]);
    }
}
