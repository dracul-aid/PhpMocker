<?php

namespace DraculAid\PhpMocker\tests\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\AbstractClassElementsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassConstantsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassMethodsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassPropertiesReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\ClassTraitsReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\EnumCasesReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader\TmpClassElement;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\PropertyScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassElementSchemeCreator
 *
 * @run php tests/run.php tests/Reader/PhpReader/ReaderElement/CodeReader/ClassReader/ClassElementSchemeCreatorTest.php
 */
class ClassElementSchemeCreatorTest extends TestCase
{
    private PhpReader $phpReader;
    private TmpClassElement $tmpClassElement;
    private ClassElementSchemeCreator $classElementSchemeCreator;

    public function testProperty(): void
    {
        $testCases = [
            'public $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => false],
            'public static $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isReadonly' => false],
            'readonly public string $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => true],
            'static public $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isReadonly' => false],
            '$var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => false],
            'var $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => false],
            'protected $var;' => ['view' => ViewScheme::PROTECTED(), 'isStatic' => false, 'isReadonly' => false],
            'private $var;' => ['view' => ViewScheme::PRIVATE(), 'isStatic' => false, 'isReadonly' => false],
            'public string|int $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => false],
            'public Alpha&Beta $var;' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isReadonly' => false],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            self::assertTrue(
                get_class($this->getSchemes($phpCode)) === ClassPropertiesReader::class
            );
            self::assertTrue(
                get_class($this->tmpClassElement->scheme) === PropertyScheme::class
            );

            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $this->tmpClassElement->scheme->{$name}, "For '{$name}' in code: {$phpCode}");
            }
        }
    }

    public function testMethods(): void
    {
        $testCases = [
            'public function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isFinal' => false, 'isAbstract' => false],
            'public static function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => false],
            'static public function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => false],
            'final public function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isFinal' => true, 'isAbstract' => false],
            'final static public function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => true, 'isAbstract' => false],
            'final public static function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => true, 'isAbstract' => false],
            'abstract public static function f();' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => true],
            'abstract static public function f();' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => true],
            'protected static function f() {}' => ['view' => ViewScheme::PROTECTED(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => false],
            'private static function f() {}' => ['view' => ViewScheme::PRIVATE(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => false],
            'function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isFinal' => false, 'isAbstract' => false],
            'static function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => false, 'isAbstract' => false],
            'abstract function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => false, 'isFinal' => false, 'isAbstract' => true],
            'final static function f() {}' => ['view' => ViewScheme::PUBLIC(), 'isStatic' => true, 'isFinal' => true, 'isAbstract' => false],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            self::assertTrue(
                get_class($this->getSchemes($phpCode)) === ClassMethodsReader::class
            );
            self::assertTrue(
                get_class($this->tmpClassElement->scheme) === MethodScheme::class
            );

            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $this->tmpClassElement->scheme->{$name}, "For '{$name}' in code: {$phpCode}");
            }
        }
    }

    public function testConstants(): void
    {
        $testCases = [
            'public const NAME' => ['view' => ViewScheme::PUBLIC(), 'isFinal' => false],
            'protected const NAME' => ['view' => ViewScheme::PROTECTED(), 'isFinal' => false],
            'private const NAME' => ['view' => ViewScheme::PRIVATE(), 'isFinal' => false],
            'final public const NAME' => ['view' => ViewScheme::PUBLIC(), 'isFinal' => true],
            'final const NAME' => ['view' => ViewScheme::PUBLIC(), 'isFinal' => true],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            self::assertTrue(
                get_class($this->getSchemes($phpCode)) === ClassConstantsReader::class
            );
            self::assertTrue(
                get_class($this->tmpClassElement->scheme) === ConstantScheme::class
            );

            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $this->tmpClassElement->scheme->{$name}, "For '{$name}' in code: {$phpCode}");
            }
        }
    }

    /*
    public function testEnumCase(): void
    {
        $testCases = [
            'case ONE;' => ['name' => 'ONE', 'innerPhpCode' => ''],
            'case TWO = 132;' => ['name' => 'TWO', 'innerPhpCode' => '123'],
        ];

        foreach ($testCases as $phpCode => $options)
        {
            self::assertTrue(
                get_class($this->getSchemes($phpCode)) === EnumCasesReader::class
            );
            self::assertTrue(
                get_class($this->tmpClassElement->scheme) === Constants::class
            );

            self::assertTrue($this->tmpClassElement->scheme->isEnumCase);
            foreach ($options as $name => $value)
            {
                self::assertEquals($value, $this->tmpClassElement->scheme->{$name}, "For '{$name}' in code: {$phpCode}");
            }
        }
    }
    */

    public function testUse(): void
    {
        self::assertTrue(
            get_class($this->getSchemes('use blablabla;')) === ClassTraitsReader::class
        );
        self::assertNull($this->tmpClassElement->scheme);
    }

    private function getSchemes(string $phpCode): AbstractClassElementsReader
    {
        $this->createObjects(" {$phpCode} ");

        while (true)
        {
            $this->phpReader->codeString->read();
            // "прочитанный символ", может стать пустой строкой в ходе выполнения функции run() ниже
            $charFirstIsEmpty = $this->phpReader->codeString->charFirst === '';

            $runReturn = $this->classElementSchemeCreator->run();

            if ($runReturn === null) $this->fail();
            elseif ($runReturn !== $this->classElementSchemeCreator) return $runReturn;
            elseif ($charFirstIsEmpty) $this->fail();
        }
    }

    private function createObjects(string $phpCode): void
    {
        $this->phpReader = NotPublic::createObject(PhpReader::class, [$phpCode]);
        $this->phpReader->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::CLASSES(), 'TestClassName');
        $this->tmpClassElement = new TmpClassElement();

        $this->classElementSchemeCreator = ClassElementSchemeCreator::start($this->phpReader, $this->tmpClassElement);
    }
}
