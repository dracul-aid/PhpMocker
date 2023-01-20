<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\AttributesReader;
use DraculAid\PhpMocker\Schemes\AttributeScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AttributesReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/AttributesReaderTest.php
 */
class AttributesReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private AttributesReader $attributesReader;

    /**
     * Test for @see AttributesReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects('#A');
        $this->phpReader->codeString->read();
        self::assertFalse(AttributesReader::isStart($this->phpReader));

        $this->createObjects('#[');
        $this->phpReader->codeString->read();
        self::assertTrue(AttributesReader::isStart($this->phpReader));
    }

    public function testAttributeOnlyName(): void
    {
        $attributes = $this->executeAttributeTest("#[AttrName]");

        self::assertCount(1, $attributes);
        self::assertEquals('AttrName', $attributes[0]->getFullName());
        self::assertEquals('AttrName', $attributes[0]->name);
        self::assertEquals('', $attributes[0]->namespace);
        self::assertEquals('', $attributes[0]->innerPhpCode);
    }

    public function testAttributeOnlyNameWithNamespace(): void
    {
        $attributes = $this->executeAttributeTest("#[catalog\AttrName]");

        self::assertCount(1, $attributes);
        self::assertEquals('catalog\AttrName', $attributes[0]->getFullName());
        self::assertEquals('AttrName', $attributes[0]->name);
        self::assertEquals('catalog', $attributes[0]->namespace);
        self::assertEquals('', $attributes[0]->innerPhpCode);
    }

    public function testAttributeWithArguments(): void
    {
        $testCases = ['1+2', "'string'", '"string"', '$GLOBALS["abc"]', 'trim(" 123 ")'];

        foreach ($testCases as $number => $testValue)
        {
            $attributes = $this->executeAttributeTest("#[AttrName{$number}({$testValue})]");

            self::assertCount(1, $attributes);
            self::assertEquals("AttrName{$number}", $attributes[0]->getFullName());
            self::assertEquals("AttrName{$number}", $attributes[0]->name);
            self::assertEquals('', $attributes[0]->namespace);
            self::assertEquals($testValue, $attributes[0]->innerPhpCode, "For Attribute Arguments: {$testValue}");
        }
    }

    public function testAttributeList(): void
    {
        $attributes = $this->executeAttributeTest("#[Attr_1,Attr_2(11, array(0,1))]");

        self::assertCount(2, $attributes);

        self::assertEquals("Attr_1", $attributes[0]->getFullName());
        self::assertEquals("Attr_1", $attributes[0]->name);
        self::assertEquals('', $attributes[0]->namespace);
        self::assertEquals('', $attributes[0]->innerPhpCode);

        self::assertEquals("Attr_2", $attributes[1]->getFullName());
        self::assertEquals("Attr_2", $attributes[1]->name);
        self::assertEquals('', $attributes[1]->namespace);
        self::assertEquals('11, array(0,1)', $attributes[1]->innerPhpCode);
    }

    /**
     * @param  string  $phpCode
     *
     * @return AttributeScheme[]
     */
    private function executeAttributeTest(string $phpCode): array
    {
        $this->createObjects($phpCode);
        $this->phpReader->codeString->read();
        $this->attributesReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->attributesReader->run() === null)
            {
                return $this->phpReader->tmpResult->attributes;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->attributesReader = NotPublic::createObject(AttributesReader::class, [$this->phpReader]);
    }
}
