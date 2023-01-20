<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CommentBlockReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CommentBlockReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CommentBlockReaderTest.php
 */
class CommentBlockReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private CommentBlockReader $commentBlockReader;

    /**
     * Test for @see CommentBlockReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('//');
        $this->phpReader->codeString->read();
        self::assertFalse(CommentBlockReader::isStart($this->phpReader));

        $this->createObjects('#');
        $this->phpReader->codeString->read();
        self::assertFalse(CommentBlockReader::isStart($this->phpReader));

        $this->createObjects('/*');
        $this->phpReader->codeString->read();
        self::assertTrue(CommentBlockReader::isStart($this->phpReader));
    }

    /**
     * Test for @see CommentBlockReader::Run()
     */
    public function testRun(): void
    {
        $this->createObjects("/*comment-1\ncomment-2*/456;");
        $this->phpReader->codeString->read();
        $this->commentBlockReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->commentBlockReader->run() === null)
            {
                self::assertEquals("comment-1\ncomment-2", NotPublic::instance($this->commentBlockReader)->get('result'));
                break;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->commentBlockReader = NotPublic::createObject(CommentBlockReader::class, [$this->phpReader]);
    }
}
