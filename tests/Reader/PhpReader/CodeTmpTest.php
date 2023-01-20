<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\CodeString;
use DraculAid\PhpMocker\Reader\PhpReader\CodeTmp;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CodeTmp
 *
 * @run php tests/run.php tests/Reader/PhpReader/CodeTmpTest.php
 */
class CodeTmpTest extends TestCase
{
    private PhpReader $phpReader;
    private CodeTmp $codeTmp;

    /**
     * Test for:
     * @see CodeTmp::addChar()
     * @see CodeTmp::addString()
     * @see CodeTmp::resultClear()
     * @see CodeTmp::resultClearAndSetSpase()
     * @see CodeTmp::set()
     */
    public function testRun(): void
    {
        $this->createCodeTmp();

        self::assertEquals('', $this->codeTmp->result);
        self::assertEquals('', $this->codeTmp->lastChar);

        $this->phpReader->codeString->read();
        self::assertEquals('', $this->codeTmp->result);
        self::assertEquals('', $this->codeTmp->lastChar);

        $this->codeTmp->addChar();
        self::assertEquals('0', $this->codeTmp->result);
        self::assertEquals('0', $this->codeTmp->lastChar);
        $this->codeTmp->addChar();
        self::assertEquals('00', $this->codeTmp->result);
        self::assertEquals('0', $this->codeTmp->lastChar);
        $this->phpReader->codeString->read();
        $this->codeTmp->addChar();
        self::assertEquals('001', $this->codeTmp->result);
        self::assertEquals('1', $this->codeTmp->lastChar);

        $this->codeTmp->addChar('A');
        self::assertEquals('001A', $this->codeTmp->result);
        self::assertEquals('A', $this->codeTmp->lastChar);

        $this->codeTmp->resultClear();
        self::assertEquals('', $this->codeTmp->result);
        self::assertEquals('', $this->codeTmp->lastChar);

        $this->codeTmp->addString('ABC');
        self::assertEquals('ABC', $this->codeTmp->result);
        self::assertEquals('C', $this->codeTmp->lastChar);
        $this->codeTmp->addString('123');
        self::assertEquals('ABC123', $this->codeTmp->result);
        self::assertEquals('3', $this->codeTmp->lastChar);
        $this->codeTmp->addString('987', 'Z');
        self::assertEquals('ABC123987', $this->codeTmp->result);
        self::assertEquals('Z', $this->codeTmp->lastChar);

        $this->codeTmp->resultClearAndSetSpase();
        self::assertEquals(' ', $this->codeTmp->result);
        self::assertEquals(' ', $this->codeTmp->lastChar);

        $this->codeTmp->set('ASD');
        self::assertEquals('ASD', $this->codeTmp->result);
        self::assertEquals('D', $this->codeTmp->lastChar);

        $this->codeTmp->set('ZXC', '1');
        self::assertEquals('ZXC', $this->codeTmp->result);
        self::assertEquals('1', $this->codeTmp->lastChar);
    }

    private function createCodeTmp(): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class);
        $this->codeTmp = new CodeTmp($this->phpReader);

        NotPublic::instance($this->phpReader)->set('codeString', new CodeString('01234567890'));
        NotPublic::instance($this->phpReader)->set('codeTmp', $this->codeTmp);
    }
}
