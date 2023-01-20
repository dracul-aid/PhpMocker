# PhpMocker - Менеджер мок-объектов - Взаимодействие с непубличными элементами
[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)

Для объектов на основе мок-классов созданных с помощью наследования возможно взаимодействие с `public` и `protected` элементами.

Для объектов на основе мок-классов созданных с помощью изменения PHP кода, также возможно взаимодействие с `private` элементами.

```php
use DraculAid\PhpMocker\Managers\ObjectManager;

$objectManager = ObjectManager::getManager($mockObject);

// Получение значения свойства $propertyName
$objectManager->getProperty($ropertyName);

// Установка значения свойства $propertyName
// В свойство будет записано $setData
$objectManager->setProperty($ropertyName, $setData);

// Вызов метода $methodName
// С передачей аргументов $argument_1, $argument_2, ..
$objectManager->callMethod($methodName, $argument_1, $argument_2, ... $argument_N);
```

---

[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)
