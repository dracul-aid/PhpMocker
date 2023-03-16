<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runProperties()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunPropertiesTest.php
 */
class RunPropertiesTest extends TestCase
{
    private ClassScheme $classScheme;

    public function testClassWithoutProperties(): void
    {
        $this->classScheme = ReflectionReader::exe(get_class(
            new class {}
        ));

        self::assertIsArray($this->classScheme->properties);
        self::assertCount(0, $this->classScheme->properties);
    }

    public function testClassWithProperties(): void
    {
        $this->createClassWithProperties();

        $this->classScheme = ReflectionReader::exe('RunPropertiesTestClass1');

        self::assertIsArray($this->classScheme->properties);
        self::assertCount(5, $this->classScheme->properties);
        self::assertIsArray($this->classScheme->constants);
        self::assertCount(0, $this->classScheme->constants);
        self::assertIsArray($this->classScheme->methods);
        self::assertCount(0, $this->classScheme->methods);

        self::assertArrayHasKey('var_static', $this->classScheme->properties);
        self::assertArrayHasKey('var_string', $this->classScheme->properties);
        self::assertArrayHasKey('var_int', $this->classScheme->properties);
        self::assertArrayHasKey('var_false', $this->classScheme->properties);
        self::assertArrayHasKey('var_with_null', $this->classScheme->properties);

        self::assertTrue($this->classScheme->properties['var_static']->isStatic);
        self::assertFalse($this->classScheme->properties['var_static']->isReadonly);

        self::assertEquals('', $this->classScheme->properties['var_static']->value);
        self::assertEquals('', $this->classScheme->properties['var_static']->type);

        self::assertEquals('string', $this->classScheme->properties['var_string']->value);
        self::assertEquals('string', $this->classScheme->properties['var_string']->type);

        self::assertEquals(123, $this->classScheme->properties['var_int']->value);
        self::assertEquals('int', $this->classScheme->properties['var_int']->type);

        self::assertEquals(false, $this->classScheme->properties['var_false']->value);
        self::assertEquals('bool', $this->classScheme->properties['var_false']->type);

        self::assertEquals('', $this->classScheme->properties['var_with_null']->value);
        self::assertEquals('?bool', $this->classScheme->properties['var_with_null']->type);
    }

    public function testClassWithPropertiesInConstruct(): void
    {
        $this->createClassesWithPropertiesInConstruct();

        $this->classScheme = ReflectionReader::exe('RunPropertiesTestClass2');

        self::assertIsArray($this->classScheme->properties);
        self::assertCount(4, $this->classScheme->properties);

        self::assertArrayHasKey('var_1', $this->classScheme->properties);
        self::assertArrayHasKey('var_2', $this->classScheme->properties);
        self::assertArrayHasKey('var_3', $this->classScheme->properties);
        self::assertArrayHasKey('var_object', $this->classScheme->properties);
    }
    
    public function testClassesWithProperties(): void
    {
        $this->createClassesWithProperties();

        $this->classScheme = ReflectionReader::exe('RunPropertiesTestClass4');

        self::assertIsArray($this->classScheme->properties);
        self::assertCount(5, $this->classScheme->properties);

        self::assertArrayHasKey('var_1_public', $this->classScheme->properties);
        self::assertArrayHasKey('var_1_protected', $this->classScheme->properties);
        self::assertArrayHasKey('var_2_public', $this->classScheme->properties);
        self::assertArrayHasKey('var_2_protected', $this->classScheme->properties);
        self::assertArrayHasKey('var_2_private', $this->classScheme->properties);

        $this->assertConstSchemes('var_1_public', '1_public', ViewScheme::PUBLIC(), false);
        $this->assertConstSchemes('var_1_protected', '1_protected', ViewScheme::PROTECTED(), false);
        $this->assertConstSchemes('var_2_public', '2_public', ViewScheme::PUBLIC(), true);
        $this->assertConstSchemes('var_2_protected', '2_protected', ViewScheme::PROTECTED(), true);
        $this->assertConstSchemes('var_2_private', '2_private', ViewScheme::PRIVATE(), true);
    }

    private function assertConstSchemes(string $name, string $value, ViewScheme $view, bool $inClass): void
    {
        self::assertEquals($name, $this->classScheme->properties[$name]->name, "For test property {$name}");
        self::assertEquals('', $this->classScheme->properties[$name]->valueFromConstant, "For test property {$name}");
        self::assertEquals('', $this->classScheme->properties[$name]->innerPhpCode, "For test property {$name}");
        self::assertEquals($value, $this->classScheme->properties[$name]->value, "For test property {$name}");
        self::assertEquals($view, $this->classScheme->properties[$name]->view, "For test property {$name}");
        self::assertEquals($inClass, $this->classScheme->properties[$name]->isDefine, "For test property {$name}");
        self::assertFalse($this->classScheme->properties[$name]->isInConstruct, "For test property {$name}");
    }

    private function createClassWithProperties(): void
    {
        eval(
        <<<'END'
                class RunPropertiesTestClass1 {
                    public static $var_static;
                    public string $var_string = 'string';
                    public int $var_int = 123;
                    public bool $var_false = false;
                    public ?bool $var_with_null;
                }
            END
        );
    }

    private function createClassesWithPropertiesInConstruct(): void
    {
        eval(
        <<<'END'
                class RunPropertiesTestClass2 {
                    public $var_1;
                    protected $var_2;
                    public string $var_3;
                    public \stdClass $var_object;
                    public function __construct($opt_1, $var_2, string $var_3, $opt_2, \stdClass $var_object = null) {
                        $this->var_2 = $var_2;
                        $this->var_3 = $var_3;
                        $this->var_object = $var_object === null ? new \stdClass() : $var_object;
                    }
                }
            END
        );
    }

    private function createClassesWithProperties(): void
    {
        eval(
        <<<'END'
                class RunPropertiesTestClass3 {
                    public $var_1_public = '1_public';
                    protected $var_1_protected = '1_protected';
                    private $var_1_private = '1_private';
                }
                class RunPropertiesTestClass4 extends RunPropertiesTestClass3 {
                    public $var_2_public = '2_public';
                    protected $var_2_protected = '2_protected';
                    private $var_2_private = '2_private';
                }
            END
        );
    }
}
