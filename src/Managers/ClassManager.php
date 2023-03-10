<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers;

use DraculAid\PhpMocker\Creator\Tools;
use DraculAid\PhpMocker\Exceptions\Managers\ClassManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Managers\Tools\AbstractClassAndObjectManager;
use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Менеджер для взаимодействия с мок-классами
 *
 * Оглавление:
 * @see ClassManager::getManager() - Вернет менеджер мок-класа по имени мок-класса
 * --- Свойства класса
 * @see ClassManager::$toClass - Имя класса, для которого создан менеджер
 * @see ClassManager::$driverName - Имя класса, с помощью которого создан мок-класс
 * @see ClassManager::$index - Уникальный идентификатор мок-класса
 * @see ClassManager::$objectManagers - Хранилище менеджеров мок-объектов созданных на основе класса (взаимодействие, как с массивом)
 * @see ClassManager::getDriver() - Вернет имя класса, с помощью которого был создан мок-класс
 * @see ClassManager::getToClass() - Вернет имя мок-класса, для которого создан менеджер
 * @see ClassManager::getToClassScheme() - Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
 * --- Создание объектов
 * @see ClassManager::createObject() - Создание мок-объекта на основе мок-класса
 * @see ClassManager::createObjectWithoutConstructor() - Создание мок-объекта без вызова конструктора
 * @see ClassManager::createObjectAndManager() - Создаст мок-объект, возможно без вызова конструктора, с установкой свойств (в том числе и не public), вернет "Менеджер мок-объекта"
 * --- Работа с мок-методами
 * @see ClassManager::$mockMethodNames - Список имен методов класса, для которых можно получить "мок-метод"
 * @see ClassManager::$methodManagers - Массив с созданными менеджерами мок-методов
 * @see ClassManager::getMethodManager() - Вернет менеджер мок-метода
 * --- Взаимодействие с статическими элементами (в том числе и protected и private)
 * @see ClassManager::getConst() - Получение значения константы
 * @see ClassManager::getProperty() - Получение значения статического свойства
 * @see ClassManager::setProperty() - Установка значения статического свойства
 * @see ClassManager::callMethod() - Вызов статического метода
 */
class ClassManager extends AbstractClassAndObjectManager
{
    /**
     * Список всех менеджеров мок-классов
     *
     * Представляет собой массив:
     *    * ключи [string]: имена мок-классов
     *    * значения: объекты менеджеры мок-классов
     *
     * @var ClassManager[] $managers
     */
    private static array $managers = [];

    /**
     * Имя класса, для которого создан менеджер
     */
    readonly public string $toClass;

    /**
     * Список имен методов класса, для которых можно получить "мок-метод"
     *
     * @var string[] $mockMethodNames Ключи и значение - строка с именем метода
     */
    readonly public array $mockMethodNames;

    /**
     * Рефлексия класса, для которого создан менеджер
     */
    readonly private ClassScheme $schemeToClass;

    /**
     * Имя класса, с помощью которого создан мок-класс, обычно это:
     * @see \DraculAid\PhpMocker\Creator\SoftMocker Мок-классы созданные с помощью наследования
     * @see \DraculAid\PhpMocker\Creator\HurdMocker Мок-классы созданные с помощью изменения PHP кода
     */
    readonly protected string $driverName;

    /**
     * Уникальный идентификатор мок-класса
     * (Последовательность символов, которую можно использовать, для создания уникальных имен связанных с мок-классом)
     */
    readonly public string $index;

    /**
     * Хранилище менеджеров мок-объектов созданных на основе класса (взаимодействие, как с массивом)
     *
     * Ключи [object]: мок-объекты для которых создан менеджер
     * Значения: объекты менеджеры мок-объектов
     *
     * @var \SplObjectStorage|ObjectManager[] $objectManagers
     */
    readonly public \SplObjectStorage $objectManagers;

    /**
     * Вернет "менеджер мок-класса" по имени мок-класса
     *
     * @param   string   $mockClass   Полное имя мок-класса
     * @param   bool     $throw       TRUE - если в случае провала поиска мок-класса нужно выбросить исключение
     *
     * @return  null|ClassManager   Вернет менеджер мок-класса
     *
     * @throws  ClassManagerNotFoundException  Может быть выброшен, в случае, если не был найден менеджер (обычно это значит, что был запрошен менеджер НЕ ДЛЯ мок-класса)
     */
    public static function getManager(string $mockClass, bool $throw = false): null|ClassManager
    {
        if (empty(self::$managers[$mockClass]))
        {
            if ($throw) throw new ClassManagerNotFoundException($mockClass);
            else return null;
        }
        else return self::$managers[$mockClass];
    }

    /**
     * @param   string   $className    Имя мок-класса, для которого создан менеджер
     * @param   string   $driverName   Имя класса, создавшего менеджер
     * @param   string   $index        Уникальный идентификатор мок-класса
     */
    public function __construct(string $className, string $driverName, null|string $index = null)
    {
        $this->toClass = $className;
        $this->driverName = $driverName;

        $this->index = $index ?? uniqid();
        $this->objectManagers = new \SplObjectStorage();

        self::$managers[$className] = $this;
    }

    /**
     * Вернет имя мок-класса, которым управляем менеджер
     *
     * @return string
     */
    public function getTO(): string
    {
        return $this->toClass;
    }

    /**
     * Вернет имя класса, с помощью которого был создан мок-класс
     *
     * @return  string   Вернет полное имя класса
     */
    public function getDriver(): string
    {
        return $this->driverName;
    }

    /**
     * Вернет имя мок-класса, для которого создан менеджер
     *
     * @return  string   Вернет полное имя класса
     */
    public function getToClass(): string
    {
        return $this->toClass;
    }

    /**
     * Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
     *
     * @return ClassScheme
     */
    public function getToClassScheme(): ClassScheme
    {
        if (empty($this->schemeToClass)) $this->schemeToClass = ReflectionReader::exe($this->toClass);

        return $this->schemeToClass;
    }

    /**
     * Создание мок-объекта на основе мок-класса
     * (вызовет конструктор класса, даже если он protected или private)
     *
     * @param   mixed   ...$arguments   Аргументы для конструктора
     *
     * @return  object   Вернет мок-объект
     */
    public function createObject(mixed ... $arguments): object
    {
        return NotPublic::createObject($this->toClass, $arguments);
    }

    /**
     * Создание мок-объекта без вызова конструктора
     *
     * @param   array                $setProperties   Список устанавливаемых свойств (ключи массива - имена свойств)
     * @param   null|ObjectManager  &$objectManager   Если передан - будет записан менеджер созданного мок-объекта
     *
     * @return  object  Вернет Менеджер мок-объекта
     *
     * @throws  \ReflectionException  Если не удалось создать рефлексию класса
     *
     * В отличие от создания объекта с помощью рефлексии, этот способ также создаст и менеджер мок-объекта
     */
    public function createObjectWithoutConstructor(array $setProperties = [], null|ObjectManager &$objectManager = null): object
    {
        $reflection = new \ReflectionClass($this->toClass);

        $object = $reflection->newInstanceWithoutConstructor();
        $objectManager = new ObjectManager($object);

        if (count($setProperties) > 0) $objectManager->setProperty($setProperties);

        return $object;
    }

    /**
     * Создаст мок-объект, возможно без вызова конструктора, с установкой свойств (в том числе и не public), вернет "Менеджер мок-объекта"
     *
     * @param   false|array   $constructorArguments   Аргументы для конструктора или FALSE если при создании объекта не нужно вызвать конструктор
     * @param   array         $setProperties          Список устанавливаемых свойств (ключи массива - имена свойств)
     * @param   null|object  &$newObject              Если передан - будет записан созданный мок-объект
     *
     * @return  ObjectManager   Менеджер мок-объекта
     */
    public function createObjectAndManager(false|array $constructorArguments = false, array $setProperties = [], null|object &$newObject = null): ObjectManager
    {
        if (is_array($constructorArguments))
        {
            $newObject = $this->createObject(... $constructorArguments);
            $manager = ObjectManager::getManager($newObject);
        }
        else
        {
            $newObject = $this->createObjectWithoutConstructor([], $manager);
        }

        if (count($setProperties) > 0) $manager->setProperty($setProperties);

        return $manager;
    }

    /**
     * Получение значения константы (в том числе и protected и private)
     *
     * @param   string   $name    Имя константы
     *
     * @return  mixed    Значение константы
     */
    public function getConst(string $name): mixed
    {
        return [$this->toClass, Tools::methodConstGet($this->index)]($name);
    }

    /**
     * Получение значения статического свойства (в том числе и protected и private)
     *
     * @param   string   $name    Имя статического свойства
     *
     * @return  mixed   Значение свойства
     */
    public function getProperty(string $name): mixed
    {
        return [$this->toClass, Tools::methodStaticPropertyGet($this->index)]($name);
    }

    /**
     * Установка значения статического свойства или списка свойств (в том числе и protected и private)
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
    public function setProperty(string|array $nameOrList, mixed $value = null): self
    {
        if (is_array($nameOrList))
        {
            foreach ($nameOrList as $name => $value) $this->setProperty($name, $value);
        }
        else
        {
            [$this->toClass, Tools::methodStaticPropertySet($this->index)]($nameOrList, $value);
        }

        return $this;
    }

    /**
     * Вызов статического метода (в том числе и protected и private)
     *
     * @param   string    $name         Имя вызываемого метода
     * @param   mixed  ...$arguments    Аргументы вызываемого метода
     *
     * @return  mixed    Вернет результат работы функции
     */
    public function callMethod(string $name, mixed ... $arguments): mixed
    {
        return [$this->toClass, Tools::methodStaticCall($this->index)]($name, $arguments);
    }

    /**
     * Установит список методов, для которых можно получить мок-методы
     *
     * @param   string[]   $mockMethodNames   Список мок методов (названия методов в индексах и значениях массива)
     *
     * @return  $this
     */
    public function setMockMethodNames(array $mockMethodNames): self
    {
        $this->mockMethodNames = $mockMethodNames;
        return $this;
    }

    /**
     * Создаст менеджер мок-метода
     *
     * @param   string   $methodName   Имя метода
     *
     * @return  void
     *
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе или его родителях
     */
    protected function methodCreateManager(string $methodName): void
    {
        $schemeMethod = $this->getToClassScheme()->methods[$methodName] ?? null;

        if ($schemeMethod === null)
        {
            throw new MethodManagerNotFoundException($methodName);
        }

        $this->methodManagers[$methodName] = new MethodManager($this, $methodName);
    }
}
