# PhpMocker - Менеджер мок-объектов
[<< Оглавление](../README.md)

_Объект "Менеджер мок-объектов" используется для управления мок-методами в мок-объекте_

* [Получение менеджера](object-managers.md)
* [Взаимодействие с непубличными элементами](nopublic.md) (методами, константами, свойствами)
* [Менеджер мок-метода для мок-объекта](methods.md)

Также смотрите [Менеджер мок-классов](../manager-class/README.md) и [Менеджер методов](../manager-method/README.md)

`DraculAid\PhpMocker\Managers\ObjectManager` Класс "менеджер мок-объектов"

## Объект, на который ссылается менеджер

`ObjectManager::$toObject` Хранит мок-объект, на который ссылается "менеджер мок-объекта"

```php
use DraculAid\PhpMocker\Managers\ObjectManager;

/** @car object $mockObject Полученный ранее мок-объект */
$mockObject;

// Получение менеджера для мок-объекта
$objectManager = ObjectManager::getManager($mockObject);

// вернет TRUE
$mockObject === $objectManager->toObject;
```

## Свойства мок-класса

Это блок актуален только для **Полных Мок-Объектов** (т.е. экземплярах мок-классов). Если мок-объект был не полным,
то перечисленные методы вернут NULL

* `ObjectManager::getClassManager()` Вернет [менеджер мок-класса](../manager-class/README.md)
* `ObjectManager::getToClass()` Вернет имя мок-класса объекта
* `ObjectManager::getDriver()` Вернет имя драйвера, на основе которого был создан мок-класс

```php
use DraculAid\PhpMocker\MockCreator;

$parentClassManager = MockCreator::hardFromPhpCode('class TestClassName {}');

$parentObject = $parentClassManager->createObject()
```

---

[<< Оглавление](../README.md)
