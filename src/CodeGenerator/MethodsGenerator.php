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
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\MethodScheme;

/**
 * Создает PHP строку с методами класса из схем методов @see MethodScheme
 *
 * Это класс, для разгрузки кода @see ClassGenerator - Генератора PHP кода класса по схеме класса
 *
 * Оглавление:
 * @see MethodsGenerator::exe() - Генератор PHP кода методов
 */
class MethodsGenerator
{
    /**
     * Схема класса
     */
    private ClassScheme $classScheme;

    /**
     * Схема класса
     */
    private string $result = '';

    /**
     * Создает PHP строку с методами класса
     *
     * @param   ClassScheme  $classScheme   Схема класса
     *
     * @return  string
     */
    public static function exe(ClassScheme $classScheme): string
    {
        $executor = new self($classScheme);

        $executor->run();

        return $executor->result;
    }

    /**
     * @param    ClassScheme  $classScheme   Схема класса
     */
    private function __construct(ClassScheme $classScheme)
    {
        $this->classScheme = $classScheme;
    }

    /**
     * Выполнит генерацию PHP кода со списком методов класса
     *
     * @return void
     */
    private function run(): void
    {
        if (count($this->classScheme->methods) === 0)
        {
            return;
        }

        // * * *

        foreach ($this->classScheme->methods as $method)
        {
            // если этот метод был определен не в этом классе - пропустим ее генерацию
            if (!$method->isDefine)
            {
                continue;
            }

            $this->result .= AttributesGenerator::exe($method);

            $this->result .= ClassGenerator::NEW_LINE_FOR_ELEMENTS;

            if ($method->isAbstract && $this->classScheme->type->canUseAbstractMethods()) $this->result .= "abstract ";
            elseif (PHP_MAJOR_VERSION > 7 && $method->isFinal) $this->result .= "final ";

            $this->result .= "{$method->view->value} ";
            if ($method->isStatic) $this->result .= "static ";

            if ($method->name === '__construct') $this->runIfIsConstruct($method);
            else $this->runIfIsNotConstruct($method);

            if ($this->classScheme->type === ClassSchemeType::INTERFACES() || $method->isAbstract) $this->result .= ";\n";
            elseif ($method->innerPhpCode !== '') $this->result .= "\n" . ClassGenerator::NEW_LINE_FOR_ELEMENTS . "{\n{$method->innerPhpCode}\n" . ClassGenerator::NEW_LINE_FOR_ELEMENTS . "}\n";
            else $this->result .= "{}\n";
        }
    }

    /**
     * Создаст и добавит код конструктора
     *
     * @param   MethodScheme   $methodScheme   Объект-схема метода
     *
     * @return  void
     */
    private function runIfIsConstruct(MethodScheme $methodScheme): void
    {
        $this->result .= "function __construct";

        $this->runArguments($methodScheme);
    }

    /**
     * Создаст и добавит код функции
     *
     * @param   MethodScheme   $methodScheme   Объект-схема метода
     *
     * @return  void
     */
    private function runIfIsNotConstruct(MethodScheme $methodScheme): void
    {
        // ключевое слово
        $this->result .= 'function ';

        if ($methodScheme->isReturnLink) $this->result .= '&';
        $this->result .= "{$methodScheme->name}";

        $this->runArguments($methodScheme);

        if ($methodScheme->returnType) $this->result .= ": {$methodScheme->returnType}";
    }

    /**
     * Создаст и добавит код со списком аргументов
     *
     * @param   MethodScheme   $methodScheme    Объект-схема метода
     *
     * @return  void
     */
    private function runArguments(MethodScheme $methodScheme): void
    {
        if ($methodScheme->argumentsPhpCode !== '')
        {
            $this->result .= "({$methodScheme->argumentsPhpCode})";
            return;
        }
        elseif (count($methodScheme->arguments) === 0)
        {
            $this->result .= '()';
            return;
        }

        // * * *

        $tmpArguments = [];
        foreach ($methodScheme->arguments as $argument)
        {
            $tmpCode = '';

            if ($argument->ifInConstructGetPropertiesScheme() !== null)
            {
                $tmpCode .= PropertiesGenerator::runGetSignatures($argument->ifInConstructGetPropertiesScheme());
            }

            if ($argument->type) $tmpCode .= "{$argument->type} ";
            if ($argument->isVariadic) $tmpCode .= '... ';
            if ($argument->isLink) $tmpCode .= '&';
            $tmpCode .= "\${$argument->name}";

            if ($argument->isValue)
            {
                $tmpCode .= ' = ';
                if ($argument->valueFromConstant) $tmpCode .= $argument->valueFromConstant;
                else $tmpCode .= var_export($argument->value, true);
            }

            $tmpArguments[] = $tmpCode;
        }

        $this->result .= '(' . implode(', ', $tmpArguments) . ')';
    }
}
