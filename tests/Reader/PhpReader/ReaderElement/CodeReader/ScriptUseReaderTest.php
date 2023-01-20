<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ScriptUseReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\UseScheme;
use DraculAid\PhpMocker\Schemes\UseSchemeType;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ScriptUseReader
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ScriptUseReaderTest.php
 */
class ScriptUseReaderTest extends TestCase
{
    private PhpReader $phpReader;
    private ScriptUseReader $scriptUseReader;

    /**
     * Test for @see ScriptUseReader::isStart()
     */
    public function testIsStart(): void
    {
        $this->createObjects(';use');
        $this->phpReader->codeString->read();
        self::assertFalse(ScriptUseReader::isStart($this->phpReader));

        $this->createObjects('uses');
        $this->phpReader->codeString->read();
        self::assertFalse(ScriptUseReader::isStart($this->phpReader));

        $this->createObjects('use ClassName');
        $this->phpReader->codeString->read();
        self::assertTrue(ScriptUseReader::isStart($this->phpReader));

        $this->createObjects("use\nClassName");
        $this->phpReader->codeString->read();
        self::assertTrue(ScriptUseReader::isStart($this->phpReader));
    }

    public function testBasicVariant(): void
    {
        $uses = $this->executeAttributeTest("use ClassName;");
        self::assertCount(1, $uses);
        self::assertEquals("ClassName", $uses[0]->getFullName());
        self::assertEquals("ClassName", $uses[0]->name);
        self::assertEquals("", $uses[0]->namespace);
        self::assertEquals('', $uses[0]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[0]->type);

        $uses = $this->executeAttributeTest("use CatalogName\\ClassName;");
        self::assertCount(1, $uses);
        self::assertEquals("CatalogName\\ClassName", $uses[0]->getFullName());
        self::assertEquals("ClassName", $uses[0]->name);
        self::assertEquals("CatalogName", $uses[0]->namespace);
        self::assertEquals('', $uses[0]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[0]->type);

        $uses = $this->executeAttributeTest("use const CONST_NAME;");
        self::assertCount(1, $uses);
        self::assertEquals("CONST_NAME", $uses[0]->getFullName());
        self::assertEquals("CONST_NAME", $uses[0]->name);
        self::assertEquals("", $uses[0]->namespace);
        self::assertEquals('', $uses[0]->alias);
        self::assertEquals(UseSchemeType::CONSTANTS, $uses[0]->type);

        $uses = $this->executeAttributeTest("use function CatalogName\\function_name;");
        self::assertCount(1, $uses);
        self::assertEquals("CatalogName\\function_name", $uses[0]->getFullName());
        self::assertEquals("function_name", $uses[0]->name);
        self::assertEquals("CatalogName", $uses[0]->namespace);
        self::assertEquals('', $uses[0]->alias);
        self::assertEquals(UseSchemeType::FUNCTIONS, $uses[0]->type);
    }

    public function testList(): void
    {
        $uses = $this->executeAttributeTest("use ClassName as NewClassName, CatalogName\\ClassName, const CatalogName\\CONSTNAME;");

        self::assertCount(3, $uses);

        self::assertEquals("ClassName", $uses[0]->getFullName());
        self::assertEquals("ClassName", $uses[0]->name);
        self::assertEquals("", $uses[0]->namespace);
        self::assertEquals('NewClassName', $uses[0]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[0]->type);

        self::assertEquals("CatalogName\\ClassName", $uses[1]->getFullName());
        self::assertEquals("ClassName", $uses[1]->name);
        self::assertEquals("CatalogName", $uses[1]->namespace);
        self::assertEquals('', $uses[1]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[1]->type);

        self::assertEquals("CatalogName\\CONSTNAME", $uses[2]->getFullName());
        self::assertEquals("CONSTNAME", $uses[2]->name);
        self::assertEquals("CatalogName", $uses[2]->namespace);
        self::assertEquals('', $uses[2]->alias);
        self::assertEquals(UseSchemeType::CONSTANTS, $uses[2]->type);
    }

    public function testListInCatalog(): void
    {
        $uses = $this->executeAttributeTest("use CatalogName\\{ClassName1, ClassName2 as NewClassName, function function_name, Cataloglevel2\\ClassName3};");

        self::assertCount(4, $uses);

        self::assertEquals("CatalogName\\ClassName1", $uses[0]->getFullName());
        self::assertEquals("ClassName1", $uses[0]->name);
        self::assertEquals("CatalogName", $uses[0]->namespace);
        self::assertEquals('', $uses[0]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[0]->type);

        self::assertEquals("CatalogName\\ClassName2", $uses[1]->getFullName());
        self::assertEquals("ClassName2", $uses[1]->name);
        self::assertEquals("CatalogName", $uses[1]->namespace);
        self::assertEquals('NewClassName', $uses[1]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[1]->type);

        self::assertEquals("CatalogName\\function_name", $uses[2]->getFullName());
        self::assertEquals("function_name", $uses[2]->name);
        self::assertEquals("CatalogName", $uses[2]->namespace);
        self::assertEquals('', $uses[2]->alias);
        self::assertEquals(UseSchemeType::FUNCTIONS, $uses[2]->type);

        self::assertEquals("CatalogName\\Cataloglevel2\\ClassName3", $uses[3]->getFullName());
        self::assertEquals("ClassName3", $uses[3]->name);
        self::assertEquals("CatalogName\\Cataloglevel2", $uses[3]->namespace);
        self::assertEquals('', $uses[3]->alias);
        self::assertEquals(UseSchemeType::CLASSES, $uses[3]->type);
    }

    /**
     * @param  string  $phpCode
     *
     * @return UseScheme[]
     */
    private function executeAttributeTest(string $phpCode): array
    {
        $this->createObjects($phpCode);
        $this->phpReader->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::CLASSES, 'TestClassname');
        $this->phpReader->codeString->read();
        $this->scriptUseReader->start();

        while (true)
        {
            $this->phpReader->codeString->read();
            if ($this->scriptUseReader->run() === null)
            {
                return $this->phpReader->tmpResult->uses;
            }
            elseif ($this->phpReader->codeString->charFirst === '') $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->scriptUseReader = NotPublic::createObject(ScriptUseReader::class, [$this->phpReader]);
    }
}
