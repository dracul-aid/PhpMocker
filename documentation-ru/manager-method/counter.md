# PhpMocker - Менеджер методов (мок-методов) - Счетчики вызовов
[<< Оглавление](../README.md) |[Менеджер методов](README.md)

Менеджер мок-методов предоставляет счетчики вызова методов. `Все счетчики, для НЕ мок-методов будут всегда отображать 0.`
Также, каждый [кейс-вызова](../mock-cases/README.md) имеет свой счетчик

Пример, как работают счетчики, можно найти ниже (см [Как происходит вызов мок-метода](../call-method.md))

```php
use DraculAid\PhpMocker\Managers\MethodManager

/** @var MethodManager $methodManager Полученный каким-то образом менеджер метода */
$methodManager;

/** Хранит кол-во всех вызовов мок-метода. */ 
$methodManager->countCall;

/** Хранит кол-во вызовов метода, для которых @see MethodManager::$userFunction Вернула результат работы функции*/
$methodManager->countCallUserFunctionReturn;

/**
 * Хранит кол-во вызовов с отработанным кодом метода
 * (т.е. для метода не было сработавшего кейса-вызова или @see MethodManager::$userFunction Вернувшей ответ для функции)
*/
$methodManager->countCallWithoutCase;
```

---

[<< Оглавление](../README.md) |[Менеджер методов](README.md)
