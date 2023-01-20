# PhpMocker - Менеджер Мок-Классов - Взаимодействие с непубличными элементами
[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)

"Менеджер мок-класса" позволяет взаимодействовать с `static` методами, свойствами и константами:
* Для мок-классов созданных с помощью наследования возможно взаимодействие с `public` и `protected` элементами.
* Для мок-классов созданных с помощью изменения PHP кода, также возможно взаимодействие с `private` элементами.

```php
use DraculAid\PhpMocker\Managers\ClassManager;

// получение "менеджера мок-класса" для мок-класса MockClass
$classManager = ClassManager::getManager('MockClass');

// Вернет значение константы $constantName
$classManager->getConst($constantName);

// Получение значения статического свойства $propertyName
$classManager->getProperty($ropertyName);

// Установка значения статического свойства $propertyName
// В свойство будет записано $setData
$classManager->setProperty($ropertyName, $setData);

// Вызов статического метода $methodName
// С передачей аргументов $argument_1, $argument_2, ..
$classManager->callMethod($methodName, $argument_1, $argument_2, ... $argument_N);
```

--- 

[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)
