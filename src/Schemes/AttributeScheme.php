<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Schemes;

use DraculAid\PhpMocker\Tools\ClassTools;

/**
 * Схемы для ООП элементов: описание атрибутов
 *
 * Оглавление:
 * @see self::$name - Имя атрибута
 * @see self::$namespace - Пространство имен
 * @see self::$innerPhpCode - Код значения атрибута
 * @see self::getFullName() - Вернет полное имя атрибута (т.е. включая пространство имен)
 * @see self::setFullName() - Установит имя атрибута (включая пространство имен)
 */
class AttributeScheme
{
    /**
     * Имя атрибута
     */
    public string $name = '';

    /**
     * Пространство имен
     */
    public string $namespace = '';

    /**
     * Аргументы атрибута
     */
    public array $arguments = [];

    /**
     * Внутренний код элемента (PHP код)
     *
     * Для класса - это содержимое класса (без трейтов)
     * Для констант и свойств - значение
     * Для методов - тело функции
     */
    public mixed $innerPhpCode = '';

    /**
     * Объект-схема для которой был создан атрибут
     */
    protected null|SchemeWithAttributesInterface $ownerScheme;

    /**
     * Создание схемы атрибута
     *
     * @param   null|SchemeWithAttributesInterface   $scheme   Объект-схема для которой был создан атрибут
     * @param   string                               $name     Полное имя атрибута (включая пространство имен)
     */
    public function __construct(null|SchemeWithAttributesInterface $scheme, string $name)
    {
        $this->ownerScheme = $scheme;
        $this->setFullName($name);
    }

    /**
     * Вернет полное имя атрибута (т.е. включая пространство имен)
     *
     * @return string
     */
    public function getFullName(): string
    {
        return ($this->namespace === '' ? '' : "{$this->namespace}\\") . $this->name;
    }

    /**
     * Вернет полное имя атрибута (т.е. включая пространство имен)
     *
     * @param   string    $name    Полное имя атрибута
     *
     * @return  $this
     */
    public function setFullName(string $name): static
    {
        ClassTools::getNameAndNamespace($name, $this->namespace, $this->name);

        return $this;
    }

    /**
     * Сменит схему класса, которой принадлежит атрибут
     *
     * @param   null|AbstractBasicScheme   $ownerScheme   Объект-схема для которой был создан атрибут
     *
     * @return  $this
     */
    public function setOwnerScheme(null|AbstractBasicScheme $ownerScheme): static
    {
        $this->ownerScheme = $ownerScheme;

        return $this;
    }
}
