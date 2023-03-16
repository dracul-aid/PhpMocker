<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\Tools;

use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Абстрактный класс для менеджеров мок-классов и мок-объектов
 *
 * Оглавление:
 * @see self::$methodManagers - Массив с менеджерами мок-методов
 * @see self::getMethodManager() - Вернет менеджер мок-метода
 * @see self::$mockMethodNames - Список имен методов класса, для которых можно получить "мок-метод"
 * @see self::getDriver() - Вернет имя класса, с помощью которого был создан мок-класс
 * @see self::getToClass() - Вернет имя мок-класса, для которого создан менеджер
 * @see self::getToClassScheme() - Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
 * @see self::getProperty() - Получение значения свойства (в том числе и protected и private)
 * @see self::setProperty() - Установка значения свойства или списка свойств (в том числе и protected и private)
 * @see self::callMethod() - Вызов метода (в том числе и protected и private)
 *
 * Наследующие классы должны включать:
 * @property string[] $mockMethodNames - Список имен методов класса, для которых можно получить "мок-метод" (Это readonly свойство, должно быть определенно в классах реализующих абстракцию)
 * @method getTo() - Вернет то, чем управляет менеджер ("менеджер мок-класса" вернет класс, а "менеджер мок-объекта" - объект)
 */
abstract class AbstractClassAndObjectManager
{
    /**
     * Массив с созданными менеджерами мок-методов
     *
     * @var MethodManager[] $methodManagers
     */
    public array $methodManagers = [];

    /**
     * Вернет менеджер мок-метода
     *
     * @param   string   $name            Имя метода
     * @param   bool     $ifIsNotCreate   Если менеджера мок-метода нет, нужно ли его создавать (TRUE - нужно, FALSE - нет)
     *
     * @return  null|MethodManager    Объект "менеджер мок-метода" или NULL (если менеджер мок-метода не существует и его нельзя создавать)
     *
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе
     */
    public function getMethodManager(string $name, bool $ifIsNotCreate = true): ?MethodManager
    {
        if (empty($this->methodManagers[$name]) && $ifIsNotCreate) $this->methodCreateManager($name);

        return $this->methodManagers[$name] ?? null;
    }

    /**
     * Вернет имя класса, с помощью которого был создан мок-класс
     *
     * @return  string   Вернет полное имя класса или NULL (если невозможно получить менеджер мок-класса)
     */
    abstract public function getDriver(): ?string;

    /**
     * Вернет имя мок-класса, для которого создан менеджер
     *
     * @return  string   Вернет полное имя класса или NULL (если невозможно получить менеджер мок-класса)
     */
    abstract public function getToClass(): ?string;

    /**
     * Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
     *
     * @return ClassScheme
     */
    abstract public function getToClassScheme(): ClassScheme;

    /**
     * Создаст менеджер мок-метода
     *
     * @param   string   $methodName   Имя метода
     *
     * @return  void
     *
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе или его родителях
     */
    abstract protected function methodCreateManager(string $methodName): void;

    /**
     * Получение значения свойства (в том числе и protected и private)
     *
     * @param   string   $name    Имя статического свойства
     *
     * @return  mixed   Значение свойства
     */
    abstract public function getProperty(string $name);

    /**
     * Установка значения свойства или списка свойств (в том числе и protected и private)
     *
     * @param   string|array   $nameOrList   Имя статического свойства или массив с устанавливаемыми свойствами
     * @param   mixed          $value        Устанавливаемое значение
     *
     * @return  $this
     *
     * Если устанавливается конкретное свойство, то в $nameOrList передается строка с именем свойства
     * Если устанавливается список свойств, то $nameOrList - представляет собой массив, в котором ключи - имена свойств,
     * а значения - устанавливаемые значения для свойства
     */
    abstract public function setProperty($nameOrList, $value = null): self;

    /**
     * Вызов метода (в том числе и protected и private)
     *
     * @param   string    $name         Имя вызываемого метода
     * @param   mixed  ...$arguments    Аргументы вызываемого метода
     *
     * @return  mixed    Вернет результат работы функции
     */
    abstract public function callMethod(string $name, ... $arguments);


    /**
     * Вернет указание, может ли метод выступать, как мок-метод
     *
     * @param   string   $methodName   Имя метода
     *
     * @return  bool
     */
    public function methodIsMock(string $methodName): bool
    {
        return isset($this->mockMethodNames[$methodName]);
    }
}
