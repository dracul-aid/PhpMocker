<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ScriptNamespaceReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ScriptNamespaceReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ScriptNamespaceReaderTest.php
 */
class ScriptNamespaceReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private ScriptNamespaceReader $scriptNamespaceReader;

    /**
     * Test for @see ScriptNamespaceReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('name space ');
        $this->phpReader->codeString->read();
        self::assertFalse(ScriptNamespaceReader::isStart($this->phpReader));

        $this->createObjects('namespace catalog\\Class');
        $this->phpReader->codeString->read();
        self::assertTrue(ScriptNamespaceReader::isStart($this->phpReader));
    }

    public function testWithoutCodeBlock(): void
    {
        $testNamespace = $this->executeAttributeTest('namespace catalog\\Class;');
        self::assertEquals('catalog\\Class', $testNamespace);
    }

    public function testWithCodeBlock(): void
    {
        $testNamespace = $this->executeAttributeTest("namespace catalog\\Class\n{");
        self::assertEquals('catalog\\Class', $testNamespace);
    }

    /**
     * @param  string  $phpCode
     *
     * @return string
     */
    private function executeAttributeTest(string $phpCode): string
    {
        $this->createObjects($phpCode);
        $this->phpReader->codeString->read();
        $this->scriptNamespaceReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->scriptNamespaceReader->run() === null)
            {
                return $this->phpReader->tmpResult->namespace;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->scriptNamespaceReader = NotPublic::createObject(ScriptNamespaceReader::class, [$this->phpReader]);
    }

}
