<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\CodeGenerator;

use DraculAid\PhpMocker\Schemes\ClassScheme;
use PHPUnit\TextUI\RuntimeException;

/**
 * Генератор кода класса по схеме класса, @see \DraculAid\PhpMocker\Schemes\ClassScheme
 *
 * Оглавление:
 * @see ClassGenerator::generatePhpCode() - Создаст PHP код для переданной схемы класса
 * @see ClassGenerator::generateCodeAndEval() - Создаст PHP класс на основе переданной схемы
 * --- Константы, для осуществления форматирования
 * @see ClassGenerator::NEW_LINE_FOR_ELEMENTS - Строка, с которой начинается генерация методов, констант и свойств
 * @see ClassGenerator::NEW_LINE_FOR_METHOD_CODE - Строка, с которой начинается вывод кода (и генерация кода) для методов класса
 */
class ClassGenerator
{
    /**
     * Строка, с которой начинается генерация методов, констант и свойств
     * (используется, для создания форматирования)
     */
    public const NEW_LINE_FOR_ELEMENTS = "\t\t";

    /**
     * Строка, с которой начинается вывод кода (и генерация кода) для методов класса
     * (используется, для создания форматирования)
     */
    public const NEW_LINE_FOR_METHOD_CODE = "\t\t\t";

    /**
     * Схема класса для которого создается код
     */
    private ClassScheme $classScheme;

    /**
     * Для накопления генерируемого кода
     * $this->code
     */
    private string $code = '';

    /**
     * Создаст PHP код для переданной схемы класса
     *
     * @param   ClassScheme   $classScheme   Объект-схема класса
     *
     * @return  string  Вернет готовый к использованию PHP код (без тегов открытия и закрытия PHP)
     */
    public static function generatePhpCode(ClassScheme $classScheme): string
    {
        $generator = new static($classScheme);

        $generator->run();

        return $generator->code;
    }

    /**
     * Создаст PHP класс на основе переданной схемы
     *
     * @param   ClassScheme   $classScheme   Объект-схема класса
     *
     * @return  string  Вернет готовый к использованию PHP код (без тегов открытия и закрытия PHP)
     *
     * @throws  \ParseError  В случае возникновения синтаксических ошибок в коде
     */
    public static function generateCodeAndEval(ClassScheme $classScheme): void
    {
        $phpCode = static::generatePhpCode($classScheme);

        // echo $phpCode; die();
        eval($phpCode);
    }

    /**
     * @param   ClassScheme   $classScheme   Объект-схема класса
     *
     * @see ClassGenerator::generateCodeAndEval() - Создаст PHP класс на основе переданной схемы
     * @see ClassGenerator::generatePhpCode() - Создаст PHP код для переданной схемы класса
     */
    private function __construct(ClassScheme $classScheme)
    {
        $this->classScheme = $classScheme;
    }

    /**
     * Выполнит генерацию PHP кода
     *
     * @return void
     */
    private function run(): void
    {
        $this->code .= AttributesGenerator::exe($this->classScheme);
        $this->code .= ClassBasicGenerator::exeForClassWords($this->classScheme);

        $this->code .= "\n\t{\n";

        $this->code .= ClassBasicGenerator::exeForTraits($this->classScheme);

        if ($this->classScheme->innerPhpCode !== '')
        {
            $this->code .= $this->classScheme->innerPhpCode;
        }
        else
        {
            $this->code .= ConstantsGenerator::exe($this->classScheme);
            $this->code .= PropertiesGenerator::exe($this->classScheme);
            $this->code .= MethodsGenerator::exe($this->classScheme);
        }

        $this->code .= "\t}\n";

        $this->runResultInNamespace();
    }

    /**
     * Поместит созданный код класса в пространство имен
     *
     * @return void
     */
    private function runResultInNamespace(): void
    {
        $namespaceAndUse = '';

        if ($this->classScheme->namespace) $namespaceAndUse .= "namespace {$this->classScheme->namespace};\n\n";

        if (count($this->classScheme->uses)) foreach ($this->classScheme->uses as $use)
        {
            $namespaceAndUse .= "\t{$use->generatePhpCode()}\n";
        }

        $this->code = "{$namespaceAndUse}\n{$this->code}\n";
    }
}
