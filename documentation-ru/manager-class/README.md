# PhpMocker - Менеджер Мок-Классов
[<< Оглавление](../README.md)

_Менеджер Мок-классов - это объекты, для управления созданными мок-классами_

* [Получение менеджера](class-managers.md)
* [Взаимодействие с непубличными элементами](nopublic.md) (методами, константами, свойствами)
* [Создание объектов управляемого мок-класса](create-object.md)
* [Менеджер мок-метода для мок-класса](methods.md)

Также смотрите [Что такое мок-классы и как их создать](../mock-classes/README.md) и [Автозагрузка классов, с преобразованием в мок-классы](../autoloader/README.md)

Класс "менеджеров мок-классов" `\DraculAid\PhpMocker\Managers\ClassManager`

## Свойства "менеджера мок-класса"

`ClassManager::$index`: Уникальный идентификатор мок-класса, используется при работе PhpMocker. Является уникальной
строкой (PHP функция `uniqid()`).

`ClassManager::getDriver()`: Вернет строку с именем драйвера с помощью которого был создан мок-класс (т.е. имя класса-создателя мока)

`ClassManager::getToClass()` и `ClassManager::$toClass`: Вернет имя мок-класса, которым управляет менеджер

`ClassManager::$classType` - Хранит тип мок-класса (интерфейс, класс, трейт...)

```php
use DraculAid\PhpMocker\MockCreator;

// Создание мок-класса из PHP кода
$classManager = MockCreator::hardFromPhpCode("class TestClassName {}");

// Выведет что-то вида '63ff0a02e4af9'
echo $classManager->index . "\n";

// Выведет 'DraculAid\PhpMocker\Creator\HardMocker'
echo $classManager->getDriver() . "\n";

// в обоих случаях выведет 'TestClassName'
echo $classManager->getToClass() . "\n";
echo $classManager->toClass . "\n";

// Работает с PHP 8.1: Вернет 'class' 
echo $classManager->classType->value . "\n";
// Работает ДО PHP 8.1: Вернет 'class'
echo $classManager->classType . "\n";
```

---

[<< Оглавление](../README.md)
