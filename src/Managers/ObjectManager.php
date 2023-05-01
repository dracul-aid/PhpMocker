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

use DraculAid\PhpMocker\Creator\MockClassInterfaces\MockClassInterface;
use DraculAid\PhpMocker\Creator\ToolsElementNames;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerIncorrectForObjectException;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\ObjectManagerNotFoundException;
use DraculAid\PhpMocker\Managers\Events\ObjectManagerCreateHandler;
use DraculAid\PhpMocker\Managers\Tools\AbstractClassAndObjectManager;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Tools\ClassParents;

/**
 * Менеджер для взаимодействия с мок-объектами и объектами, в которых присутствуют мок-методы (полученные путем наследования)
 *
 * Оглавление:
 * @see ObjectManager::getManager() - Вернет "менеджер мок-объекта" для мок-объекта
 * --- Свойства мок-класса
 * @see self::$toObject - Объект для которого создан менеджер
 * @see self::getTo() - Вернет объект для которого создан менеджер
 * @see self::getClassManager() Вернет "менеджер мок-класса", которому принадлежит мок-объект
 * @see self::getDriver() - Вернет имя класса, с помощью которого был создан мок-класс
 * @see self::getToClass() - Вернет имя мок-класса, для которого создан менеджер
 * @see self::getToClassScheme() - Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
 * --- Работа с мок-методами
 * @see self::$mockMethodNames - Список имен методов класса, для которых можно получить "мок-метод"
 * @see self::$methodManagers - Массив с менеджерами мок-методов
 * @see self::getMethodManager() - Вернет менеджер мок-метода
 * @see self::clearMockMethodsCases() - Удалит все установленные кейсы вызовов
 * --- Взаимодействие с элементами (в том числе и protected и private)
 * @see self::getProperty() - Получение значения свойства
 * @see self::setProperty() - Установка значения свойства
 * @see self::callMethod() - Вызов метода
 */
class ObjectManager extends AbstractClassAndObjectManager
{
    /**
     * Хранилище всех менеджеров мок-объектов
     *   * Индексы: мок-объекты
     *   * Значения: "менеджер мок-объекта" или FALSE - если объект не являлся мок-объектом
     *
     * Для чтения {@see ObjectManager::getManager()}
     *
     * @var \WeakMap|ObjectManager[]|false $objectManagers
     */
    private static \WeakMap $objectManagers;

    /**
     * Объект для которого создан менеджер
     */
    readonly public object $toObject;

    /**
     * Список имен методов класса, для которых можно получить "мок-метод"
     *
     * @var string[] $mockMethodNames Ключи и значение - строка с именем метода
     */
    readonly public array $mockMethodNames;

    /**
     * @param   object   $toObject   Объект для которого создается менеджер
     */
    public function __construct(object $toObject)
    {
        $this->toObject = $toObject;

        $this->searchAndSetMockMethodNames();

        // * * *

        // Мок-объекты могут быть "не полными", это значит, что они могут быть созданы не от мок-класса, а от обычного
        // класса, который наследует мок-методы от мок-класса родителя (обычного класса-родителя или трейта)
        $this->getClassManager()?->objectManagers->offsetSet($toObject, $this);

        if (!isset(self::$objectManagers)) self::$objectManagers = new \WeakMap();
        self::$objectManagers[$toObject] = $this;

        // * * *

        ObjectManagerCreateHandler::exe($this);
    }

    /**
     * Вернет "менеджер мок-объекта" по переданному мок-объекту
     *
     * @param   object   $mockObject   Мок-Объект, для которого необходимо вернуть менеджер
     * @param   bool     $throw        TRUE - если в случае провала поиска мок-класса нужно выбросить исключение
     *
     * @return  null|ObjectManager   Вернет менеджер мок-объекта или NULL, если такой менеджер не существует
     *
     * @throws  ObjectManagerNotFoundException   Может быть выброшен, в случае, если не был найден менеджер
     */
    public static function getManager(object $mockObject, bool $throw = false): null|ObjectManager
    {
        if (!isset(self::$objectManagers)) self::$objectManagers = new \WeakMap();

        // * * *

        if (empty(self::$objectManagers[$mockObject]))
        {
            // мок-классы следуют своему интерфейсу
            if (is_a($mockObject, MockClassInterface::class))
            {
                self::$objectManagers[$mockObject] = new self($mockObject);
            }
            // мок-объект мог быть создан из класса, который наследовал мок-класс "трейт"
            else
            {
                self::$objectManagers[$mockObject] = false;
                foreach (ClassParents::getTraits(get_class($mockObject)) as $traitName)
                {
                    if (ClassManager::getManager($traitName) !== null)
                    {
                        self::$objectManagers[$mockObject] = new self($mockObject);
                    }
                }
            }
        }

        // * * *

        if (isset(self::$objectManagers[$mockObject]) && is_object(self::$objectManagers[$mockObject]))
        {
            return self::$objectManagers[$mockObject];
        }
        else
        {
            if ($throw) throw new ObjectManagerNotFoundException($mockObject);
            else return null;
        }
    }

    /**
     * Вернет мок-объект, которым управляем менеджер
     *
     * @return object
     */
    public function getTo(): object
    {
        return $this->toObject;
    }

    /**
     * Вернет "менеджер мок-класса", которому принадлежит мок-объект
     *
     * @return  null|ClassManager  Вернет "менеджер мок-класса" или NULL, если мок-объект на который ссылается менеджер, не был создан от мок-класса
     */
    public function getClassManager(): null|ClassManager
    {
        return ClassManager::getManager($this->toObject::class);
    }

    /**
     * Вернет имя класса, с помощью которого был создан мок-класс
     *
     * @return  null|string   Вернет полное имя класса или NULL (если невозможно получить менеджер мок-класса)
     */
    public function getDriver(): null|string
    {
        return $this->getClassManager()?->getDriver();
    }

    /**
     * Вернет имя мок-класса, для которого создан менеджер
     *
     * @return  null|string   Вернет полное имя класса или NULL (если невозможно получить менеджер мок-класса)
     */
    public function getToClass(): null|string
    {
        return $this->getClassManager()?->getToClass();
    }

    /**
     * Вернет схему класса, для которого создан менеджер (если ее нет, создаст ее)
     *
     * @return ClassScheme
     */
    public function getToClassScheme(): ClassScheme
    {
        return $this->getClassManager()->getToClassScheme();
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
        return [$this->toObject, ToolsElementNames::methodPropertyGet($this->getClassManager()->index)]($name);
    }

    /**
     * Установка значения статического свойства (в том числе и protected и private)
     *
     * @param   string|array   $nameOrList   Имя статического свойства или массив с устанавливаемыми свойствами
     * @param   mixed          $value        Устанавливаемое значение
     *
     * @return  $this
     */
    public function setProperty(string|array $nameOrList, mixed $value = null): self
    {
        if (is_array($nameOrList))
        {
            foreach ($nameOrList as $name => $value) $this->setProperty($name, $value);
        }
        else
        {
            [$this->toObject, ToolsElementNames::methodPropertySet($this->getClassManager()->index)]($nameOrList, $value);
        }

        return $this;
    }

    /**
     * Вызов статического метода (в том числе и protected и private)
     *
     * @param   string   $name        Имя вызываемого метода
     * @param   array    $arguments   Аргументы вызываемого метода
     *
     * @return  mixed    Вернет результат работы функции
     */
    public function callMethod(string $name, array $arguments = []): mixed
    {
        return [$this->toObject, ToolsElementNames::methodCall($this->getClassManager()->index)]($name, $arguments);
    }

    /**
     * Создаст менеджер мок-метода
     *
     * @param   string   $methodName   Имя метода
     *
     * @return  MethodManager    Объект "менеджер мок-метода"
     *
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе или его родителях
     * @throws  MethodManagerIncorrectForObjectException   Если метод статичный или абстрактный
     */
    protected function methodCreateManager(string $methodName): void
    {
        try {
            $reflectionMethod = new \ReflectionMethod($this->toObject, $methodName);
        }
        catch (\ReflectionException $error) {
            throw new MethodManagerNotFoundException($methodName, 'ReflectionException: ' . $error->getMessage());
        }

        if ($reflectionMethod->isStatic() || $reflectionMethod->isAbstract()) throw new MethodManagerIncorrectForObjectException($reflectionMethod);

        // * * *

        $this->methodManagers[$methodName] = new MethodManager($this, $methodName);
    }

    /**
     * Ищет все мок-методы доступные для мок-объекта
     *
     * @return void
     */
    private function searchAndSetMockMethodNames(): void
    {
        $mockMethods = [];

        $classList = ClassParents::getWithoutInterfaces($this->toObject::class);
        $classList[$this->toObject::class] = $this->toObject::class;

        foreach ($classList as $class)
        {
            if (ClassManager::getManager($class) !== null)
            {
                $mockMethods += ClassManager::getManager($class)->mockMethodNames;
            }
        }

        $this->mockMethodNames = $mockMethods;
    }
}
