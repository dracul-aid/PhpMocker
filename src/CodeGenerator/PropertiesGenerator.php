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
use DraculAid\PhpMocker\Schemes\PropertyScheme;

/**
 * Создает PHP строку со свойствами класса из схем свойств @see PropertyScheme
 *
 * Это класс, для разгрузки кода @see ClassGenerator - Генератора PHP кода класса по схеме класса
 *
 * Оглавление:
 * @see PropertiesGenerator::exe() - Генератор PHP кода свойств
 * @see PropertiesGenerator::runGetSignatures() - Создает строку с описанием свойства класса (уровень видимости, финальность...)
 */
class PropertiesGenerator
{
    /**
     * Схема класса
     */
    readonly private ClassScheme $classScheme;

    /**
     * Схема класса
     */
    private string $result = '';

    /**
     * Создает PHP строку со свойствами класса
     *
     * @param    ClassScheme  $classScheme   Схема класса
     */
    public static function exe(ClassScheme $classScheme): string
    {
        $executor = new self($classScheme);

        $executor->run();

        return $executor->result;
    }

    /**
     * Создает строку с описанием свойства класса (уровень видимости, финальность...)
     *
     * @param   PropertyScheme   $propertyScheme   Схема свойства класса
     *
     * @return  string
     */
    public static function runGetSignatures(PropertyScheme $propertyScheme): string
    {
        $_return = '';

        // только для чтения
        if ($propertyScheme->isReadonly) $_return .= "readonly ";

        // видимость
        $_return .= "{$propertyScheme->view->value} ";

        // статичное
        if ($propertyScheme->isStatic) $_return .= "static ";

        return $_return;
    }

    /**
     * @param    ClassScheme  $classScheme   Схема класса
     */
    private function __construct(ClassScheme $classScheme)
    {
        $this->classScheme = $classScheme;
    }

    /**
     * Выполнит генерацию PHP кода со списком свойств класса
     *
     * @return void
     */
    private function run(): void
    {
        // если данный тип классов не поддерживает свойства
        if (!$this->classScheme->type->canUseProperties() || count($this->classScheme->properties) === 0)
        {
            return;
        }

        // * * *

        foreach ($this->classScheme->properties as $property)
        {
            if (!$property->isDefine || $property->isInConstruct)
            {
                continue;
            }

            $this->result .= AttributesGenerator::exe($property);

            $this->result .= ClassGenerator::NEW_LINE_FOR_ELEMENTS;
            $this->result .= self::runGetSignatures($property);
            $this->result .= "{$property->type} ";
            $this->result .= "\${$property->name}";
            if ($property->isValue) $this->result .= " = {$property->getValuePhpCode()}";
            $this->result .= ";\n";
        }
    }
}
