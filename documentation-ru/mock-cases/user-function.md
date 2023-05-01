# PhpMocker - Кейс вызова мок-метода - Предваряющая выполнение метода функция
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)

Кейсу вызова мок-метода можно назначить "пользовательскую функцию", она будет всегда выполнена, даже если кейс-вызова подразумевает
выброс исключения или имеет установленное значение для ответа. Также эта функция, может сформировать ответ работы метода.

Для установки функции используется `MethodCase::setUserFunction()`

В качестве функции также может выступать объект имплементирующий `\DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionInterface`

Функция получит на вход два аргумента:
1) Объект с параметрами вызова `\DraculAid\PhpMocker\Managers\Tools\HasCalled`
2) Менеджер мок-метода, в рамках которого был вызов

Если функция вернет объект `\DraculAid\PhpMocker\Managers\Tools\CallResult` дальнейшее выполнение мок-метода будет прекращено

Также подобную функцию можно назначит [менеджеру мок-метода](../manager-method/user-function.md), в этом случае функция будет
выполняться перед выполнением любого кейса

### Стандартные функции

* `Managers\MethodUserFunctions\MethodUserFunctionsList` Позволяет выполнить список функций
* `Managers\MethodUserFunctions\OverwritePropertyMethodUserFunction` Позволяет перезаписать свойства объекта или статические свойства класса

## Аргументы вызова

Аргументы с которыми был вызван метод хранятся `\DraculAid\PhpMocker\Managers\Tools\HasCalled::$arguments` (объект `HasCalled`
будет передан в пользовательскую функцию первым аргументов)

`HasCalled::$arguments` представляет собой объект, с доступом как к массиву. В качестве ключей могут быть использованы,
имена или позиции аргументов (позиции, начиная с 0-ля). Перезаписав значения аргументов в `HasCalled::$arguments`, вы также
перезапишите значения аргументов в самом методе.

## Примеры

Более подробный [пример](../../examples-ru/new-function-code.php)
```php
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Tools\CallableObject;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

// * * *

/** В этом примере, установленная функция сработает, перед выбрасыванием исключения */

// Установка функции
$methodCase->setUserFunction( new CallableObject(static function () {}));
// При вызове метода будет выброшено исключение \RuntimeException
$methodCase->setWillException( new \RuntimeException('Error text') );

// * * *

/**
 * В этом примере, установленная функция вернет результат ответа для мок-метода,
 * поэтому установленное исключение не будет выброшено 
 */

// Установка функции, при срабатывании кейса вызова, мок-метод вернет 'ABC'
$methodCase->setUserFunction(
    new CallableObject(
        static function () {
            return new CallResult(true, 'ABC')
        }
    )
);

// Установленное исключение не будет выброшено
$methodCase->setWillException( new \RuntimeException('Error text') );
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)
