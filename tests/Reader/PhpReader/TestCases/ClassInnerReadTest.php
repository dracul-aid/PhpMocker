<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\TestCases;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassConstantsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassPropertiesReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassMethodsReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see PhpReader::CodeToSchemes()
 *
 * @run php tests/run.php tests/Reader/PhpReader/TestCases/ClassInnerReadTest.php
 */
class ClassInnerReadTest extends TestCase
{
    public function testWithoutTraits(): void
    {
        $schemes = $this->getSchemesForTestWithoutTraits();

        self::assertCount(1, $schemes);

        self::assertEquals('myClassName', $schemes[0]->getFullName());
        self::assertEquals(ClassSchemeType::ABSTRACT_CLASSES(), $schemes[0]->type);
        self::assertFalse($schemes[0]->isReadonly);

        // * * *

        /**
         * @see ClassConstantsReader::run()
         * @see ClassConstantsReader::ReadElementWithValueFinish()
         */
        self::assertCount(2, $schemes[0]->constants);

        $const = $schemes[0]->constants['OLD_STYLE'];
        self::assertEquals('OLD_STYLE', $const->name);
        self::assertEquals("'old_constant'", $const->innerPhpCode);
        self::assertEquals(ViewScheme::PUBLIC(), $const->view);
        self::assertFalse($const->isFinal);

        $const = $schemes[0]->constants['FINAL_PROTECTED'];
        self::assertEquals('FINAL_PROTECTED', $const->name);
        self::assertEquals('123', $const->innerPhpCode);
        self::assertEquals(ViewScheme::PROTECTED(), $const->view);
        self::assertTrue($const->isFinal);


        // * * *

        /**
         * @see ClassPropertiesReader::run()
         * @see ClassPropertiesReader::ReadElementWithValueFinish()
         */
        self::assertCount(4, $schemes[0]->properties);

        $var = $schemes[0]->properties['old_style_var'];
        self::assertEquals('old_style_var', $var->name);
        self::assertEquals('', $var->type);
        self::assertEquals('false', $var->innerPhpCode);
        self::assertEquals(ViewScheme::PUBLIC(), $var->view);
        self::assertTrue($var->isValue);
        self::assertFalse($var->isStatic);
        self::assertFalse($var->isReadonly);

        $var = $schemes[0]->properties['static_var'];
        self::assertEquals('static_var', $var->name);
        self::assertEquals('', $var->type);
        self::assertEquals("'}static_var{'", $var->innerPhpCode);
        self::assertEquals(ViewScheme::PUBLIC(), $var->view);
        self::assertTrue($var->isValue);
        self::assertTrue($var->isStatic);
        self::assertFalse($var->isReadonly);

        $var = $schemes[0]->properties['protectedVar'];
        self::assertEquals('protectedVar', $var->name);
        self::assertEquals('', $var->type);
        self::assertEquals('132', $var->innerPhpCode);
        self::assertEquals(ViewScheme::PROTECTED(), $var->view);
        self::assertTrue($var->isValue);
        self::assertFalse($var->isStatic);
        self::assertFalse($var->isReadonly);

        $var = $schemes[0]->properties['readonlyVar'];
        self::assertEquals('readonlyVar', $var->name);
        self::assertEquals('null|string', $var->type);
        self::assertEquals('', $var->innerPhpCode);
        self::assertEquals(ViewScheme::PUBLIC(), $var->view);
        self::assertFalse($var->isValue);
        self::assertFalse($var->isStatic);
        self::assertTrue($var->isReadonly);

        // * * *

        /**
         * @see ClassMethodsReader::run()
         */
        self::assertCount(2, $schemes[0]->methods);

        $function = $schemes[0]->methods['f_public'];
        self::assertFalse($function->isAbstract);
        self::assertFalse($function->isStatic);
        self::assertTrue($function->isFinal);
        self::assertEquals('f_public', $function->name);
        self::assertEquals('int $v, ... $chars', $function->argumentsPhpCode);
        self::assertEquals('return "public_value";', $function->innerPhpCode);

        $function = $schemes[0]->methods['f_private'];
        self::assertTrue($function->isAbstract);
        self::assertTrue($function->isStatic);
        self::assertFalse($function->isFinal);
        self::assertEquals('f_private', $function->name);
        self::assertEquals('', $function->argumentsPhpCode);
        self::assertEquals('return "private_value";', $function->innerPhpCode);
    }

    /**
     * @return  ClassScheme[]
     */
    private function getSchemesForTestWithoutTraits(): array
    {
        $phpCode = <<<'CODE'
            abstract class myClassName {
                const OLD_STYLE = 'old_constant';
                final protected const FINAL_PROTECTED = 123;
                
                var $old_style_var = false;
                public static $static_var = '}static_var{';
                protected $protectedVar = 132;
                readonly public null|string $readonlyVar;
                
                final public function f_public(int $v, ... $chars): string
                {
                    return "public_value";                
                }
                abstract private static function f_private(): string
                {
                    return "private_value";                
                }
            }
CODE;

        return PhpReader::CodeToSchemes($phpCode);
    }
}
