<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\Services;

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Creator\ToolsElementNames;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\MethodScheme;

/**
 * Генератор методов, для взаимодействия с мок-классами {@see GeneratorNoPublicMethods::exe()}
 * (вызов не публичных методов, получения значения непубличных свойств и констант)
 */
class GeneratorNoPublicMethods
{
    /**
     * Хранит схему класса
     */
    readonly private ClassScheme $classScheme;

    /**
     * Уникальный идентификатор мок-класса
     */
    readonly private string $index;

    /**
     * Создаем методы для взаимодействия с менеджером мок-объектов
     *
     * @param   ClassScheme   $scheme   Схема создаваемого класса
     * @param   string        $index    Идентификатор создаваемого мок-класса
     *
     * $index см @see ClassManager::$index
     */
    public static function exe(ClassScheme $scheme, string $index): void
    {
        $executor = new self();
        $executor->classScheme = $scheme;
        $executor->index = $index;

        $executor->runConstGet();
        $executor->runStaticPropertyGet();
        $executor->runStaticPropertySet();
        $executor->runStaticMethodCall();

        $executor->runPropertyGet();
        $executor->runPropertySet();
        $executor->runMethodCall();
    }

    private function __construct() {}

    /**
     * Создаем метод для чтения свойства (в том числе и закрытых)
     *
     * @return void
     */
    private function runPropertyGet(): void
    {
        $methodName = ToolsElementNames::methodPropertyGet($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'return $this->{ $name } ?? null;';
    }

    /**
     * Создаем метод для записи в свойства (в том числе и закрытые)
     *
     * @return void
     */
    private function runPropertySet(): void
    {
        $methodName = ToolsElementNames::methodPropertySet($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name, mixed $value';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . '$this->{ $name } = $value;';
    }

    /**
     * Создаем метод для вызова методов (в том числе и закрытых)
     *
     * @return void
     */
    private function runMethodCall(): void
    {
        $methodName = ToolsElementNames::methodCall($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name, array $arguments';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'return $this->{ $name }( ... $arguments );';
    }

    /**
     * Создаем метод, для чтения значения константы (в том числе и закрытых)
     *
     * @return void
     */
    private function runConstGet(): void
    {
        $methodName = ToolsElementNames::methodConstGet($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->isStatic = true;
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'return constant( "static::{$name}" );';
    }

    /**
     * Создаем метод, для чтения статического свойства (в том числе и закрытого)
     *
     * @return void
     */
    private function runStaticPropertyGet(): void
    {
        $methodName = ToolsElementNames::methodStaticPropertyGet($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->isStatic = true;
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'return static::$$name ?? null;';
    }

    /**
     * Создаем метод для записи в статическое свойство (в том числе и закрытое)
     *
     * @return void
     */
    private function runStaticPropertySet(): void
    {
        $methodName = ToolsElementNames::methodStaticPropertySet($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->isStatic = true;
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name, mixed $value';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'static::$$name = $value;';
    }

    /**
     * Создаем метод для вызова статического метода (в том числе и закрытого)
     *
     * @return void
     */
    private function runStaticMethodCall(): void
    {
        $methodName = ToolsElementNames::methodStaticCall($this->index);
        $this->classScheme->methods[$methodName] = new MethodScheme($this->classScheme, $methodName);
        $this->classScheme->methods[$methodName]->isStatic = true;
        $this->classScheme->methods[$methodName]->argumentsPhpCode = 'string $name, array $arguments';
        $this->classScheme->methods[$methodName]->innerPhpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . 'return (static::class)::$name( ... $arguments );';
    }
}
