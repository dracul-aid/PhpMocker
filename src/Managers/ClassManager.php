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

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\Creator\ToolsElementNames;
use DraculAid\PhpMocker\Exceptions\Managers\ClassManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Managers\Tools\AbstractClassAndObjectManager;
use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Менеджер для взаимодействия с мок-классами
 *
 * Оглавление:
 * @see ClassManager::getManager() - Вернет менеджер мок-класа по имени мок-класса
 * --- Свойства класса
 * @see self::$toClass - Имя класса, для которого создан менеджер
 * @see self::getTo() - Вернет имя мок-класса, для которого создан менеджер
 * @see self::$classType - Хранит тип мок-класса (интерфейс, класс, трейт...)
 * @see self::$driverName - Имя класса, с помощью которого создан мок-класс
 * @see self::$index - Уникальный идентификатор мок-класса
 * @see self::$objectManagers - Хранилище менеджеров мок-объектов созданных на основе класса (взаимодействие, как с массивом)
 * @see self::getDriver() - Вернет имя класса, с помощью которого был создан мок-класс
 * @see self::getToClass() - Вернет имя мок-класса, для которого создан менеджер
 * @see self::getToClassScheme() - Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
 * --- Создание объектов
 * @see self::createObject() - Создание мок-объекта на основе мок-класса
 * @see self::createObjectWithoutConstructor() - Создание мок-объекта без вызова конструктора
 * @see self::createObjectAndManager() - Создаст мок-объект, возможно без вызова конструктора, с установкой свойств (в том числе и не public), вернет "Менеджер мок-объекта"
 * --- Работа с мок-методами
 * @see self::$mockMethodNames - Список имен методов класса, для которых можно получить "мок-метод"
 * @see self::$methodManagers - Массив с созданными менеджерами мок-методов
 * @see self::getMethodManager() - Вернет менеджер мок-метода
 * --- Взаимодействие с статическими элементами (в том числе и protected и private)
 * @see self::getConst() - Получение значения константы
 * @see self::getProperty() - Получение значения статического свойства
 * @see self::setProperty() - Установка значения статического свойства
 * @see self::callMethod() - Вызов статического метода
 *
 * Свойства доступные только для чтения @see self::__get()
 * @property string $toClass
 * @property string[] $mockMethodNames
 * @property string $driverName
 * @property string $index
 * @property \SplObjectStorage $objectManagers
 * @property ClassSchemeType $classType
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
    protected static array $managers = [];

    /**
     * Имя класса, для которого создан менеджер
     */
    protected string $toClass;

    /**
     * Список имен методов класса, для которых можно получить "мок-метод"
     *
     * @var string[] $mockMethodNames Ключи и значение - строка с именем метода
     *
     * Для установки @see self::setMockMethodNames()
     */
    private array $mockMethodNames;

    /**
     * Рефлексия класса, для которого создан менеджер
     */
    private ClassScheme $schemeToClass;

    /**
     * Имя класса, с помощью которого создан мок-класс, обычно это:
     * @see \DraculAid\PhpMocker\Creator\SoftMocker Мок-классы созданные с помощью наследования
     * @see \DraculAid\PhpMocker\Creator\HurdMocker Мок-классы созданные с помощью изменения PHP кода
     */
    protected string $driverName;

    /**
     * Уникальный идентификатор мок-класса
     * (Последовательность символов, которую можно использовать, для создания уникальных имен связанных с мок-классом)
     */
    protected string $index;

    /**
     * Хранилище менеджеров мок-объектов созданных на основе класса (взаимодействие, как с массивом)
     *
     * Ключи [object]: мок-объекты для которых создан менеджер
     * Значения: объекты менеджеры мок-объектов
     *
     * @var \SplObjectStorage|ObjectManager[] $objectManagers
     */
    protected \SplObjectStorage $objectManagers;

    /**
     * Хранит тип мок-класса (интерфейс, класс, трейт...)
     */
    protected ClassSchemeType $classType;

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
    public static function getManager(string $mockClass, bool $throw = false): ?ClassManager
    {
        if (empty(self::$managers[$mockClass]))
        {
            if ($throw) throw new ClassManagerNotFoundException($mockClass);
            else return null;
        }
        else return self::$managers[$mockClass];
    }

    /**
     * @param   ClassSchemeType   $classType    Тип класса (класс, перечисление, интерфейс...)
     * @param   string            $className    Имя мок-класса, для которого создан менеджер
     * @param   string            $driverName   Имя класса, создавшего менеджер
     * @param   string            $index        Уникальный идентификатор мок-класса
     */
    public function __construct(ClassSchemeType $classType, string $className, string $driverName, ?string $index = null)
    {
        $this->toClass = $className;
        $this->driverName = $driverName;

        $this->index = $index ?? uniqid();
        $this->objectManagers = new \SplObjectStorage();

        $this->classType = $classType;

        $this->registerInManagerList();
    }

    public function __get(string $name)
    {
        return $this->{$name};
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
    public function createObject(... $arguments): object
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
    public function createObjectWithoutConstructor(array $setProperties = [], ?ObjectManager &$objectManager = null): object
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
    public function createObjectAndManager($constructorArguments = false, array $setProperties = [], ?object &$newObject = null): ObjectManager
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
     * Код функции создается в @see GeneratorNoPublicMethods::runConstGet()
     *
     * @param   string   $name    Имя константы
     *
     * @return  mixed    Значение константы
     */
    public function getConst(string $name)
    {
        return [$this->toClass, ToolsElementNames::methodConstGet($this->index)]($name);
    }

    /**
     * Получение значения статического свойства (в том числе и protected и private)
     *
     * Код функции создается в @see GeneratorNoPublicMethods::runStaticPropertyGet()
     *
     * @param   string   $name    Имя статического свойства
     *
     * @return  mixed   Значение свойства
     */
    public function getProperty(string $name)
    {
        return [$this->toClass, ToolsElementNames::methodStaticPropertyGet($this->index)]($name);
    }

    /**
     * Установка значения статического свойства или списка свойств (в том числе и protected и private)
     *
     * Код функции создается в @see GeneratorNoPublicMethods::runStaticPropertySet()
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
    public function setProperty($nameOrList, $value = null): self
    {
        if (is_array($nameOrList))
        {
            foreach ($nameOrList as $name => $value) $this->setProperty($name, $value);
        }
        else
        {
            [$this->toClass, ToolsElementNames::methodStaticPropertySet($this->index)]($nameOrList, $value);
        }

        return $this;
    }

    /**
     * Вызов статического метода (в том числе и protected и private)
     *
     * Код функции создается в @see GeneratorNoPublicMethods::runStaticMethodCall()
     *
     * @param   string    $name         Имя вызываемого метода
     * @param   mixed  ...$arguments    Аргументы вызываемого метода
     *
     * @return  mixed    Вернет результат работы функции
     */
    public function callMethod(string $name, ... $arguments)
    {
        return [$this->toClass, ToolsElementNames::methodStaticCall($this->index)]($name, $arguments);
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

    /**
     * Регистрирует созданный "менеджер мок-классов" в списке всех созданных менеджеров
     *
     * @return void
     */
    protected function registerInManagerList(): void
    {
        $this->registerInManagerListExecuting();
    }

    /**
     * Проводит "реальную регистрацию созданного менеджера в списке всех менеджеров мок-классов"
     *
     * Метод создан, для того, что бы @see self::registerInManagerList() мог быть переопределен в потомках
     * (для случаев, когда созданный объект не должен попадать в список всех менеджеров, т.е. когда реальное создание
     * менеджера происходит отложено. Такой механиз используется, например, при автозагрузке классов @see Autoloader )
     *
     * @return void
     */
    protected function registerInManagerListExecuting(): void
    {
        self::$managers[$this->toClass] = $this;
    }
}
