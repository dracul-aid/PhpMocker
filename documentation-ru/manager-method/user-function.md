# PhpMocker - Менеджер методов (мок-методов) - Предваряющая функция
[<< Оглавление](../README.md) |[Менеджер методов](README.md)

PhpMocker позволяет назначить всем мок-методам функцию, которая будет выполняться перед поиском подходящего [кейса вызова](../mock-cases/README.md)
и основным кодом метода. Эта функция позволяет:
* Изменить аргументы вызова
* Вернуть результат выполнения функции (без ее выполнения)
* Выполнить любую иную работу

(!) **Благодаря возможности установить пользовательскую функцию, вы можете полностью изменить работу метода**

## Пользовательская функция, как кейс-вызова

`MethodManager::$userFunction` - Функция, которая будет выполнена перед выполнением основного тела мок-метода и проверок
кейсов вызова. Благородя этой функции можно накладывать любою логику на работу мок-методов.

В качестве значения `MethodManager::$userFunction` может выступать любая функция или объект наследующий `\DraculAid\PhpMocker\Managers\Tools\MethodUserFunctionInterface`

Функция получит на вход два аргумента:
1) Объект с параметрами вызова `\DraculAid\PhpMocker\Managers\Tools\HasCalled`
2) Менеджер мок-метода, в рамках которого был вызов

Если функция вернет объект `\DraculAid\PhpMocker\Managers\Tools\CallResult` дальнейшее выполнение мок-метода будет прекращено

Также, подобную функцию можно назначить и для конкретного [Кейса вызова](../mock-cases/README.md)

## Аргументы вызова

Аргументы с которыми был вызван метод хранятся `\DraculAid\PhpMocker\Managers\Tools\HasCalled::$arguments` (объект `HasCalled`
будет передан в пользовательскую функцию первым аргументов)

`HasCalled::$arguments` представляет собой массив ссылок на аргументы, это значит, что если вы поменяете значение в этом массиве,
вы поменяете и значения самого аргумента.

Также есть возможность получить значение аргумента:
* `HasCalled::getArgumentValueByName()` вернет значение аргумента по его имени.
* `HasCalled::getArgumentValueByNumber()` вернет значение аргумента по его номеру.

## Примеры

```php
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\MethodUserFunctionInterface;
use DraculAid\PhpMocker\Tools\CallableObject;

/** @var MethodManager $methodManager Полученный каким-то образом менеджер метода */
$methodManager;

// Вызов метода, которым управляет менеджер
// допустим этот вызов выведет 'ABC' и $result === '777'
$result = $methodManager->call();

// * * *

/**
 * Так как свойств не могут иметь свойство callable, свойство должно быть @see CallableObject
 * Аргументы и ответ функции описаны @see MethodUserFunctionInterface
 * 
 * В данном случае ответ функции будет проигнорирован
 */
$methodManager->userFunction = new CallableObject( static function () {echo 'XYZ'; return false;} );

// Вызов метода, которым управляет менеджер
// допустим этот вызов выведет 'XYZABC' и $result === '777'
// 'XYZ' вывеет установленная пользовательская функция
// 'ABC' выведет тело метода
// Вызов функции вер
$result = $methodManager->call();

// * * *

/**
 * Установка предваряющей функции. Основное тело метода не будет выполнено, так как
 * пользовательская функция вернула @see CallResult
 */
$methodManager->userFunction = new CallableObject( static function () {echo 'XYZ'; return CallResult(true, '999');} );

// Вызов метода, которым управляет менеджер
// допустим этот вызов выведет 'XYZ' и $result === '999'
$methodManager->call();
```

---

[<< Оглавление](../README.md) |[Менеджер методов](README.md)
