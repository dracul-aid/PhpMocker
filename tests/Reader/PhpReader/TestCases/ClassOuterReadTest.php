<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\TestCases;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PhpReader::CodeToSchemes()
 *
 * @run php tests/run.php tests/Reader/PhpReader/TestCases/ClassOuterReadTest.php
 */
class ClassOuterReadTest extends TestCase
{
    public function testSchemesClassTypes(): void
    {
        $schemes = $this->createSchemesForTestSchemesClassTypes();

        self::assertCount(7, $schemes);

        self::assertEquals('MyAbstractClass', $schemes[0]->getFullName());
        self::assertEquals(ClassSchemeType::ABSTRACT_CLASSES(), $schemes[0]->type);
        self::assertFalse($schemes[0]->isReadonly);
        self::assertFalse($schemes[0]->isFinal);

        self::assertEquals('MyInterface', $schemes[1]->getFullName());
        self::assertEquals(ClassSchemeType::INTERFACES(), $schemes[1]->type);
        self::assertFalse($schemes[1]->isReadonly);
        self::assertFalse($schemes[1]->isFinal);

        self::assertEquals('MyTrait', $schemes[2]->getFullName());
        self::assertEquals(ClassSchemeType::TRAITS(), $schemes[2]->type);
        self::assertFalse($schemes[2]->isReadonly);
        self::assertFalse($schemes[2]->isFinal);

        self::assertEquals('MyEnum', $schemes[3]->getFullName());
        self::assertEquals(ClassSchemeType::ENUMS(), $schemes[3]->type);
        self::assertFalse($schemes[3]->isReadonly);
        self::assertFalse($schemes[3]->isFinal);

        self::assertEquals('MyClassReadonly', $schemes[4]->getFullName());
        self::assertEquals(ClassSchemeType::CLASSES(), $schemes[4]->type);
        self::assertTrue($schemes[4]->isReadonly);
        self::assertFalse($schemes[4]->isFinal);

        self::assertEquals('MyClassFinal', $schemes[5]->getFullName());
        self::assertEquals(ClassSchemeType::CLASSES(), $schemes[5]->type);
        self::assertFalse($schemes[5]->isReadonly);
        self::assertTrue($schemes[5]->isFinal);

        self::assertEquals('MyClassReadonlyFinal', $schemes[6]->getFullName());
        self::assertEquals(ClassSchemeType::CLASSES(), $schemes[6]->type);
        self::assertTrue($schemes[6]->isReadonly);
        self::assertTrue($schemes[6]->isFinal);
    }

    public function testOneNamespaceStyle1(): void
    {
        $schemes = $this->createSchemesForTestOneNamespaceStyle1();

        self::assertCount(2, $schemes);
        self::assertEquals('myNamespace\Catalog\MyClass1', $schemes[0]->getFullName());
        self::assertEquals('myNamespace\Catalog\MyClass2', $schemes[1]->getFullName());

        self::assertCount(2, $schemes[0]->uses);
        self::assertCount(2, $schemes[1]->uses);
        self::assertEquals($schemes[0]->uses, $schemes[1]->uses);
        self::assertEquals('use StdClass;', $schemes[1]->uses[0]->generatePhpCode());
        self::assertEquals('use Catalog\ClassName as AliasName;', $schemes[1]->uses[1]->generatePhpCode());
    }

    public function testOneNamespaceStyle2(): void
    {
        $schemes = $this->createSchemesForTestOneNamespaceStyle2();

        self::assertCount(2, $schemes);
        self::assertEquals('myNamespace\Catalog\MyClass1', $schemes[0]->getFullName());
        self::assertEquals('myNamespace\Catalog\MyClass2', $schemes[1]->getFullName());

        self::assertCount(3, $schemes[0]->uses);
        self::assertCount(3, $schemes[1]->uses);
        self::assertEquals($schemes[0]->uses, $schemes[1]->uses);
        self::assertEquals('use UseLink1;', $schemes[1]->uses[0]->generatePhpCode());
        self::assertEquals('use UseLink2;', $schemes[1]->uses[1]->generatePhpCode());
        self::assertEquals('use UseCatalog\UseLink3 as alias3;', $schemes[1]->uses[2]->generatePhpCode());
    }

    public function testListOfNamespaceStyle1(): void
    {
        $schemes = $this->createSchemesForTestListOfNamespaceStyle1();

        self::assertCount(2, $schemes);

        self::assertEquals('myNamespace1\Catalog\MyClass1', $schemes[0]->getFullName());
        self::assertCount(2, $schemes[0]->uses);
        self::assertEquals('use StdClass;', $schemes[0]->uses[0]->generatePhpCode());
        self::assertEquals('use Catalog1\ClassName as AliasName;', $schemes[0]->uses[1]->generatePhpCode());

        self::assertEquals('myNamespace2\Catalog\MyClass2', $schemes[1]->getFullName());
        self::assertCount(2, $schemes[1]->uses);
        self::assertEquals('use StdClass;', $schemes[1]->uses[0]->generatePhpCode());
        self::assertEquals('use Catalog2\ClassName as AliasName;', $schemes[1]->uses[1]->generatePhpCode());
    }

    /**
     * @return  ClassScheme[]
     */
    private function createSchemesForTestSchemesClassTypes(): array
    {
        $phpCode = <<<'CODE'
            // test line comment
            abstract class MyAbstractClass {}
            interface MyInterface {}
            trait MyTrait {}
            enum MyEnum {} 
            readonly class MyClassReadonly {}
            final class MyClassFinal {}
            /* test block comment */
            readonly final class MyClassReadonlyFinal {}
CODE;

        return PhpReader::CodeToSchemes($phpCode);
    }

    /**
     * @return  ClassScheme[]
     */
    private function createSchemesForTestOneNamespaceStyle1(): array
    {
        $phpCode = <<<'CODE'
            namespace myNamespace\Catalog;
            use StdClass;
            use Catalog\ClassName as AliasName;
            class MyClass1 {}
            class MyClass2 {}
CODE;

        return PhpReader::CodeToSchemes($phpCode);
    }

    /**
     * @return  ClassScheme[]
     */
    private function createSchemesForTestOneNamespaceStyle2(): array
    {

        $phpCode = <<<'CODE'
            namespace myNamespace\Catalog {
                use UseLink1, UseLink2;
                use UseCatalog\{UseLink3 as alias3};
                class MyClass1 {}
                class MyClass2 {}
            }
CODE;

        return PhpReader::CodeToSchemes($phpCode);
    }

    /**
     * @return  ClassScheme[]
     */
    private function createSchemesForTestListOfNamespaceStyle1(): array
    {
        $phpCode = <<<'CODE'
            namespace myNamespace1\Catalog;
            use StdClass;
            use Catalog1\ClassName as AliasName;
            class MyClass1 {}
            namespace myNamespace2\Catalog;
            use StdClass;
            use Catalog2\ClassName as AliasName;
            class MyClass2 {}
CODE;

        return PhpReader::CodeToSchemes($phpCode);
    }
}
