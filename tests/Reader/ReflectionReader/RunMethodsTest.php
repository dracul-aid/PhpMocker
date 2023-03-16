<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runMethods()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunMethodsTest.php
 */
class RunMethodsTest extends TestCase
{
    private ClassScheme $classScheme;

    public function testClassWithoutMethods(): void
    {
        $this->classScheme = ReflectionReader::exe(get_class(
            new class {}
        ));

        self::assertIsArray($this->classScheme->methods);
        self::assertCount(0, $this->classScheme->methods);
    }

    public function testClassWithMethods(): void
    {
        $this->createClassWithMethods();

        $this->classScheme = ReflectionReader::exe('RunMethodsTestClass1');

        self::assertIsArray($this->classScheme->methods);
        self::assertCount(9, $this->classScheme->methods);
        self::assertIsArray($this->classScheme->constants);
        self::assertCount(0, $this->classScheme->constants);
        self::assertIsArray($this->classScheme->properties);
        self::assertCount(0, $this->classScheme->properties);

        self::assertArrayHasKey('f', $this->classScheme->methods);
        self::assertArrayHasKey('__construct', $this->classScheme->methods);
        self::assertArrayHasKey('f_static', $this->classScheme->methods);
        self::assertArrayHasKey('f_abstract', $this->classScheme->methods);
        self::assertArrayHasKey('f_link', $this->classScheme->methods);
        self::assertArrayHasKey('f_final', $this->classScheme->methods);
        self::assertArrayHasKey('f_string', $this->classScheme->methods);
        self::assertArrayHasKey('f_string_null', $this->classScheme->methods);
        self::assertArrayHasKey('f_bool_null', $this->classScheme->methods);

        self::assertEquals('__construct', $this->classScheme->getConstructor()->name);

        self::assertTrue($this->classScheme->methods['f_static']->isStatic);
        self::assertFalse($this->classScheme->methods['f']->isStatic);

        self::assertTrue($this->classScheme->methods['f_abstract']->isAbstract);
        self::assertFalse($this->classScheme->methods['f']->isAbstract);

        self::assertTrue($this->classScheme->methods['f_link']->isReturnLink);
        self::assertFalse($this->classScheme->methods['f']->isReturnLink);

        self::assertTrue($this->classScheme->methods['f_final']->isFinal);
        self::assertFalse($this->classScheme->methods['f']->isFinal);

        self::assertEquals('', $this->classScheme->methods['f']->returnType);
        self::assertEquals('string', $this->classScheme->methods['f_string']->returnType);
        self::assertEquals('?string', $this->classScheme->methods['f_string_null']->returnType);
        self::assertEquals('?bool', $this->classScheme->methods['f_bool_null']->returnType);

        self::assertEquals('', $this->classScheme->methods['f']->argumentsPhpCode);
        self::assertIsArray($this->classScheme->methods['f']->arguments);
        self::assertCount(0, $this->classScheme->methods['f']->arguments);
    }

    public function testClassWithMethodWithArguments(): void
    {
        $this->createClassWithMethodWithArguments();

        $this->classScheme = ReflectionReader::exe('RunMethodsTestClass2');

        self::assertIsArray($this->classScheme->methods);
        self::assertCount(1, $this->classScheme->methods);
        self::assertArrayHasKey('f', $this->classScheme->methods);

        self::assertIsArray($this->classScheme->methods['f']->arguments);
        self::assertCount(6, $this->classScheme->methods['f']->arguments);

        $arguments = $this->classScheme->methods['f']->arguments;

        self::assertArrayHasKey('var', $arguments);
        self::assertArrayHasKey('var_int', $arguments);
        self::assertArrayHasKey('var_bool_null', $arguments);
        self::assertArrayHasKey('var_string_null', $arguments);
        self::assertArrayHasKey('var_link', $arguments);
        self::assertArrayHasKey('var_list', $arguments);

        self::assertEquals('', $arguments['var']->type);
        self::assertEquals('', $arguments['var']->value);

        self::assertEquals('int', $arguments['var_int']->type);
        self::assertEquals('', $arguments['var_int']->value);

        if (PHP_MAJOR_VERSION>7)
        {
            self::assertCount(2, $arguments['var_int']->attributes);
            self::assertEquals('Attribute1', $arguments['var_int']->attributes[0]->name);
            self::assertEquals('Attribute2', $arguments['var_int']->attributes[1]->name);
        }

        self::assertEquals('?bool', $arguments['var_bool_null']->type);
        self::assertEquals('', $arguments['var_bool_null']->value);

        self::assertEquals('?string', $arguments['var_string_null']->type);
        self::assertEquals(null, $arguments['var_string_null']->value);

        self::assertEquals('', $arguments['var_link']->type);
        self::assertEquals('', $arguments['var_link']->value);
        self::assertTrue($arguments['var_link']->isLink);
        self::assertFalse($arguments['var']->isLink);

        self::assertEquals('self', $arguments['var_list']->type);
        self::assertEquals('', $arguments['var_list']->value);
        self::assertTrue($arguments['var_list']->isVariadic);
        self::assertFalse($arguments['var']->isVariadic);
    }

    public function testClassesWithMethods(): void
    {
        $this->createClassesWithMethods();

        $this->classScheme = ReflectionReader::exe('RunMethodsTestClass4');

        self::assertIsArray($this->classScheme->methods);
        self::assertCount(5, $this->classScheme->methods);

        self::assertNull($this->classScheme->getConstructor());

        self::assertArrayHasKey('f_1_public', $this->classScheme->methods);
        self::assertArrayHasKey('f_1_protected', $this->classScheme->methods);
        self::assertArrayHasKey('f_2_public', $this->classScheme->methods);
        self::assertArrayHasKey('f_2_protected', $this->classScheme->methods);
        self::assertArrayHasKey('f_2_private', $this->classScheme->methods);

        $this->assertConstSchemes('f_1_public', ViewScheme::PUBLIC(), false);
        $this->assertConstSchemes('f_1_protected', ViewScheme::PROTECTED(), false);
        $this->assertConstSchemes('f_2_public', ViewScheme::PUBLIC(), true);
        $this->assertConstSchemes('f_2_protected', ViewScheme::PROTECTED(), true);
        $this->assertConstSchemes('f_2_private', ViewScheme::PRIVATE(), true);
    }

    private function assertConstSchemes(string $name, ViewScheme $view, bool $inClass): void
    {
        self::assertEquals($name, $this->classScheme->methods[$name]->name);
        self::assertEquals('', $this->classScheme->methods[$name]->innerPhpCode);
        self::assertEquals($view, $this->classScheme->methods[$name]->view);
        self::assertEquals($inClass, $this->classScheme->methods[$name]->isDefine);
    }

    private function createClassWithMethods(): void
    {
        eval(
        <<<END
                abstract class RunMethodsTestClass1 {
                    public function f() {}
                    public function __construct() {}
                    public static function f_static() {}
                    abstract public function f_abstract();
                    public function &f_link() {}
                    final public function f_final() {}
                    public function f_string(): string {}
                    public function f_string_null(): ?string {}
                    public function f_bool_null(): ?bool {}
                }
            END
        );
    }

    private function createClassWithMethodWithArguments(): void {
        eval(
        <<<'END'
                abstract class RunMethodsTestClass2 {
                    public function f(
                        $var,
                        #[Attribute1]
                        #[Attribute2]
                        int $var_int,
                        ?bool $var_bool_null,
                        string $var_string_null = null,
                        &$var_link = null,
                        self ...$var_list
                    ) {}
                }
            END
        );
    }

    private function createClassesWithMethods(): void
    {
        eval(
        <<<END
                class RunMethodsTestClass3 {
                    public function f_1_public() {}
                    protected function f_1_protected() {}
                    private function f_1_private() {}
                }
                class RunMethodsTestClass4 extends RunMethodsTestClass3 {
                    public function f_2_public() {}
                    protected function f_2_protected() {}
                    private function f_2_private() {}
                }
            END
        );
    }
}
