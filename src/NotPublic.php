<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker;

use DraculAid\PhpMocker\Tools\NotPublicProxy;

/**
 * Класс для работы с непубличными элементами классов и объектов
 *
 * Оглавление:
 * @see NotPublic::createObject() - Создаст объект указанного класса. Создаст объект, даже если конструктор private или protected
 * @see NotPublic::instance() - Вернет объект для работы с не публичными элементами
 * @see NotPublic::proxy() - Позволяет получить прокси для работы с непубличными свойствами и методами объекта
 * --- Процедурный стиль
 * @see NotPublic::readConstant() - Чтение значения константы
 * @see NotPublic::readProperty() - Чтение занчения свойства
 * @see NotPublic::writeProperty() - Запись значния свойства (списка свойств)
 * @see NotPublic::callMethod() - Вызов метода
 * --- Объект для взаимодействия с непубличными элементами
 * @see NotPublic::$toObject - Для какого объекта создан объект
 * @see NotPublic::constant() - Вернет значение указанной константы
 * @see NotPublic::get() - Чтение статического свойства
 * @see NotPublic::getStatic() - Чтение статического свойства
 * @see NotPublic::set() - Установка свойства (или списка свойств)
 * @see NotPublic::setStatic() - Установка статического свойства (или списка свойств)
 * @see NotPublic::call() - Вызов метода
 * @see NotPublic::callStatic() - Вызов статического метода
 * @see NotPublic::getProxy() - Позволяет получить прокси для работы с непубличными свойствами и методами объекта
 */
class NotPublic
{
    /**
     * Для какого объекта создан "объект для взаимодействия с непубличными элементами"
     */
    readonly public object $toObject;

    private function __construct() {}

    /**
     * Вернет объект для взаимодействия с непубличными элементами класса и объекта
     *
     * @param   string|object   $objectOrClass   Для какого класса или объекта создается
     *
     * @return  static
     */
    public static function instance(string|object $objectOrClass): self
    {
        /**
         * Массив объектов, для которых создан объект для взаимодействия с непубличными элементами
         * @var NotPublic[]|\SplObjectStorage $_notPublicObjects
         */
        static $_notPublicObjects = new \SplObjectStorage();

        /**
         * Массив классов, для которых создан объект для взаимодействия с непубличными элементами
         * @var NotPublic[] $_notPublicClasses (ключ массива - имена классов)
         */
        static $_notPublicClasses = [];

        // * * *

        if (is_object($objectOrClass))
        {
            if (empty($_notPublicObjects[$objectOrClass]))
            {
                $_notPublicObjects[$objectOrClass] = new self();
                $_notPublicObjects[$objectOrClass]->toObject = $objectOrClass;
            }

            return $_notPublicObjects[$objectOrClass];
        }
        else
        {
            if (empty($_notPublicClasses[$objectOrClass]))
            {
                $_notPublicClasses[$objectOrClass] = new self();
                $_notPublicClasses[$objectOrClass]->toObject = self::createObject($objectOrClass, false);
            }

            return $_notPublicClasses[$objectOrClass];
        }
    }

    /**
     * Позволяет получить прокси для работы с непубличными свойствами и методами объекта
     *
     * @param   object   $object    Объект для которого создается прокси
     *
     * @return  NotPublicProxy
     */
    public static function proxy(object $object): NotPublicProxy
    {
        static $_proxyStorage = new \SplObjectStorage();

        if (empty($_proxyStorage[$object])) $_proxyStorage[$object] = new NotPublicProxy($object);

        return $_proxyStorage[$object];
    }

    /**
     * Создаст объект указанного класса. Создаст объект, даже если конструктор private или protected
     *
     * @param   string        $class         Полное имя класса
     * @param   false|array   $arguments     Массив аргументов для конструктора:
     *                                       * FALSE: конструктор не будет вызван
     *                                       * array: список аргументов для конструктора
     * @param   array         $properties    Массив свойств необходимых для установки в объекте
     *
     * @return  object  Вернет созданный объект
     */
    public static function createObject(string $class, false|array $arguments = false, array $properties = []): object
    {
        $reflectionClass = new \ReflectionClass($class);
        $object = $reflectionClass->newInstanceWithoutConstructor();

        if ($arguments !== false) self::instance($object)->call('__construct', $arguments);

        if (count($properties) > 0) self::instance($object)->set($properties);

        return $object;
    }

    /**
     * Создаст объект указанного класса (даже если конструктор private или protected) и вернет прокси для взаимодействия с непубличными свойствами и методами объекта
     *
     * @param   string        $class         Полное имя класса
     * @param   false|array   $arguments     Массив аргументов для конструктора:
     *                                       * FALSE: конструктор не будет вызван
     *                                       * array: список аргументов для конструктора
     * @param   array         $properties    Массив свойств необходимых для установки в объекте
     *
     * @return  NotPublicProxy
     */
    public static function createObjectAndReturnProxy(string $class, false|array $arguments = false, array $properties = []): NotPublicProxy
    {
        $object = self::createObject($class, $arguments, $properties);

        return self::proxy($object);
    }

    /**
     * Вернет значение константы
     *
     * @param   string|object   $classOrObject   Класс или объект, из которого будет проводиться чтение
     * @param   string          $name            Имя константы
     *
     * @return  mixed
     */
    public static function readConstant(string|object $classOrObject, string $name): mixed
    {
        return self::instance($classOrObject)->constant($name);
    }

    /**
     * Вернет значение указанной константы
     *
     * @param   string   $name   Имя константы
     *
     * @return  mixed
     */
    public function constant(string $name): mixed
    {
        return $this->getOrCreateFunctionForConstants()($name);
    }

    /**
     * Прочитает значение свойства объекта или статического свойства класса
     *
     * @param   string|object   $classOrObject    Строка с именем класса (для чтения статических свойств) или объект (для чтения свойств объекта)
     * @param   string          $name             Имя свойства
     *
     * @return  mixed
     */
    public static function readProperty(string|object $classOrObject, string $name): mixed
    {
        if (is_object($classOrObject)) return self::instance($classOrObject)->get($name);
        else return self::instance($classOrObject)->getStatic($name);
    }

    /**
     * Вернет значение указанного свойства объекта
     *
     * @param   string   $name   Имя свойства
     *
     * @return  mixed
     */
    public function get(string $name): mixed
    {
        return $this->getOrCreateFunctionForGetProperties()($name);
    }

    /**
     * Вернет значение указанного статического свойства
     *
     * @param   string   $name   Имя свойства
     *
     * @return  mixed
     */
    public function getStatic(string $name): mixed
    {
        return $this->getOrCreateFunctionForGetStaticProperties()($name);
    }

    /**
     * Прочитает значение свойства объекта или статического свойства класса
     *
     * @param   string|object   $classOrObject    Строка с именем класса (для чтения статических свойств) или объект (для чтения свойств объекта)
     * @param   string|array    $name             Имя свойства или массив со списком свойств (имя свойства => значение)
     * @param   mixed           $data             Значение для установки
     *
     * @return  mixed
     */
    public static function writeProperty(string|object $classOrObject, string|array $name, mixed $data = null): mixed
    {
        if (is_object($classOrObject)) return self::instance($classOrObject)->set($name, $data);
        else return self::instance($classOrObject)->setStatic($name, $data);
    }

    /**
     * Установит значение указанному свойству объекта
     *
     * @param   string|array   $var    Имя свойства или массив со списком свойств (имя свойства => значение)
     * @param   mixed          $data   Значение для установки
     *
     * @return  $this
     */
    public function set(string|array $var, mixed $data = null): self
    {
        if (is_string($var)) $this->getOrCreateFunctionForSetProperties()($var, $data);
        else foreach ($var as $name => $data) $this->getOrCreateFunctionForSetProperties()($name, $data);

        return $this;
    }

    /**
     * Установит значение статическому свойству
     *
     * @param   string|array   $var    Имя свойства или массив со списком свойств (имя свойства => значение)
     * @param   mixed          $data   Значение для установки
     *
     * @return  $this
     */
    public function setStatic(string|array $var, mixed $data = null): self
    {
        if (is_string($var)) $this->getOrCreateFunctionForSetStaticProperties()($var, $data);
        else foreach ($var as $name => $data) $this->getOrCreateFunctionForSetStaticProperties()($name, $data);

        return $this;
    }

    /**
     * Вызов метода
     *
     * @param   string|object   $classOrObject    Строка с именем класса (для вызова статического метода) или объект (для вызова метода объекта)
     * @param   string|array    $name             Имя метода
     * @param   array           $arguments        Список аргументов
     *
     * @return  mixed
     */
    public static function callMethod(string|object $classOrObject, string|array $name, array $arguments = []): mixed
    {
        if (is_object($classOrObject)) return self::instance($classOrObject)->call($name, $arguments);
        else return self::instance($classOrObject)->callStatic($name, $arguments);
    }

    /**
     * Проведет вызов метода
     *
     * @param   string   $name        Имя метода
     * @param   mixed    $arguments   Список аргументов
     *
     * @return  mixed
     */
    public function call(string $name, array $arguments = []): mixed
    {
        return $this->getOrCreateFunctionForCall()($name, $arguments);
    }

    /**
     * Проведет вызов статического метода
     *
     * @param   string   $name        Имя статического метода
     * @param   mixed    $arguments   Список аргументов
     *
     * @return  mixed
     */
    public function callStatic(string $name, array $arguments = []): mixed
    {
        return $this->getOrCreateFunctionForCallStatic()($name, $arguments);
    }

    /**
     * Вернет прокси для взаимодействия с непубличными свойствами и методами
     *
     * @return  NotPublicProxy
     */
    public function getProxy(): NotPublicProxy
    {
        return self::proxy($this->toObject);
    }

    /**
     * Создание (если надо) функции, для чтения значения константы
     *
     * @return   \Closure
     */
    private function getOrCreateFunctionForConstants(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name) {
                return constant($this::class . "::{$name}");
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для чтения значения свойства
     *
     * @return   \Closure
     */
    private function getOrCreateFunctionForGetProperties(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name) {
                return $this->{$name};
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для чтения значения статического свойства
     *
     * @return   \Closure
     */
    private function getOrCreateFunctionForGetStaticProperties(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name) {
                return ($this::class)::$$name;
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для установки значения свойства
     *
     * @return   \Closure   Вернет функцию для
     */
    private function getOrCreateFunctionForSetProperties(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name, $data) {
                $this->{$name} = $data;
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для установки значения статического свойства
     *
     * @return   \Closure   Вернет функцию для
     */
    private function getOrCreateFunctionForSetStaticProperties(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name, $data) {
                ($this::class)::$$name = $data;
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для вызова метода
     *
     * @return   \Closure   Вернет функцию для
     */
    private function getOrCreateFunctionForCall(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name, $arguments) {
                return $this->{$name}(...$arguments);
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

    /**
     * Создание (если надо) функции, для вызова статического метода
     *
     * @return   \Closure   Вернет функцию для
     */
    private function getOrCreateFunctionForCallStatic(): \Closure
    {
        /**
         * Для хранения созданных анонимных функций для конкретных классов (ключи массива - объект, которому "принадлежит" функция)
         * @var \Closure[]|\SplObjectStorage $_functionInObject
         */
        static $_functionInObject = new \SplObjectStorage();

        if (empty($_functionInObject[$this->toObject]))
        {
            $_functionInObject[$this->toObject] = function($name, $arguments) {
                return [$this::class, $name](...$arguments);
            };
            $_functionInObject[$this->toObject] = $_functionInObject[$this->toObject]->bindTo($this->toObject, $this->toObject);
        }

        return $_functionInObject[$this->toObject];
    }

}
