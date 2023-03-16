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

/**
 * Создает PHP строку с базовым описанием класса (основные ключевые слова класса)
 * и генератор PHP кода для вызова трейтов в классах.
 *
 * Это класс, для разгрузки кода @see ClassGenerator - Генератора PHP кода класса по схеме класса
 *
 * Оглавление:
 * @see ClassBasicGenerator::exeForClassWords() - Создает PHP строку с базовым описанием класса
 * @see ClassBasicGenerator::exeForTraits() - Создает PHP строку с вызовом трейтов
 */
class ClassBasicGenerator
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
     * Создает PHP строку с базовым описанием класса (основные ключевые слова класса)
     *
     * @param   ClassScheme  $classScheme   Схема класса
     *
     * @return  string
     */
    public static function exeForClassWords(ClassScheme $classScheme): string
    {
        $executor = new self($classScheme);

        $executor->run();

        return $executor->result;
    }

    /**
     * Создает PHP строку с вызовом трейтов
     *
     * @param   ClassScheme  $classScheme   Схема класса
     *
     * @return  string
     */
    public static function exeForTraits(ClassScheme $classScheme): string
    {
        if ($classScheme->traitsPhpCode !== '') return $classScheme->traitsPhpCode;
        elseif (count($classScheme->traits) === 0) return '';

        // * * *

        $_return = '';

        foreach ($classScheme->traits as $trait)
        {
            $_return .= ClassGenerator::NEW_LINE_FOR_ELEMENTS . "use {$trait};\n";
        }

        return $_return;
    }

    /**
     * @param   ClassScheme  $classScheme   Схема класса
     */
    private function __construct(ClassScheme $classScheme)
    {
        $this->classScheme = $classScheme;
    }

    /**
     * Создает базовую строку с определением класса
     *
     * @return void
     */
    private function run(): void
    {
        $this->result .= "\t";

        if ($this->classScheme->isReadonly && $this->classScheme->type->canUseReadonly()) $this->result .= "readonly ";
        if ($this->classScheme->isFinal && $this->classScheme->type->canUseFinal()) $this->result .= "final ";

        $this->result .= "{$this->classScheme->type->value} ";
        $this->result .= "{$this->classScheme->name} ";

        // для перечислений - тип значений
        if ($this->classScheme->enumType !== '')
        {
            $this->result .= ": {$this->classScheme->enumType} ";
        }

        $this->runParents();
    }

    /**
     * Создает для базовой строки с определением класса - часть отвечающую за "родителе"
     *
     * @return void
     */
    private function runParents(): void
    {
        if ($this->classScheme->type === ClassSchemeType::INTERFACES())
        {
            if (count($this->classScheme->interfaces) > 0)
            {
                $this->result .= 'extends ' . implode(', ', $this->classScheme->interfaces) . ' ';
            }
        }
        else
        {
            if ($this->classScheme->type->canUseExtends() && $this->classScheme->parent !== '')
            {
                $this->result .= "extends {$this->classScheme->parent} ";
            }
            if ($this->classScheme->type->canUseInterfaces() && count($this->classScheme->interfaces) > 0)
            {
                $this->result .= 'implements ' . implode(', ', $this->classScheme->interfaces) . ' ';
            }
        }
    }
}
