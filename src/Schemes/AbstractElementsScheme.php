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

/**
 * Схемы для ООП элементов: базовые элементы для свойств, методов и констант
 *
 * Оглавление:
 * @see self::$view - Уровень видимости (public, protected...)
 * @see self::$isDefine - Элемент определен (переопределен) в этом текущем классе схемаПроверит, является ли элемент public
 * @see self::isPublic() - Проверит, является ли элемент public
 * @see self::isProtected() - Проверит, является ли элемент protected
 * @see self::isPrivate() - Проверит, является ли элемент private
 */
abstract class AbstractElementsScheme extends AbstractBasicScheme
{
    /**
     * Уровень видимости
     */
    public ViewScheme $view = ViewScheme::PUBLIC;

    /**
     * Элемент определен (переопределен) в этом текущем классе схемы
     */
    public bool $isDefine = true;

    /**
     * Объект "схема класса" для которой создана константа
     */
    protected ClassScheme $classScheme;

    /**
     * @param   ClassScheme   $schemesClass   Объект "схема класса" для которой создана константа
     * @param   string    $name           Имя элемента
     */
    public function __construct(ClassScheme $schemesClass, string $name)
    {
        $this->name = $name;
        $this->classScheme = $schemesClass;
    }

    /**
     * Вернет схему класса, которому принадлежит элемент
     *
     * @return  ClassScheme
     */
    public function getClassScheme(): ClassScheme
    {
        return $this->classScheme;
    }

    /**
     * Проверит, является ли элемент public
     *
     * @return  bool
     */
    public function isPublic(): bool
    {
        return $this->view === ViewScheme::PUBLIC;
    }

    /**
     * Проверит, является ли элемент protected
     *
     * @return  bool
     */
    public function isProtected(): bool
    {
        return $this->view === ViewScheme::PROTECTED;
    }

    /**
     * Проверит, является ли элемент private
     *
     * @return  bool
     */
    public function isPrivate(): bool
    {
        return $this->view === ViewScheme::PRIVATE;
    }
}
