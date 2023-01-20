# PhpMocker - Кейс вызова мок-метода - Счетчик вызовов
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)

`MethodCase::$countCall` Считает кол-во выполнений кейса вызова и сбрасывается при вызове `MethodCase::setWillReturnClear()`
`MethodCase::$canReturnData` также проводит подсчет кол-ва вызовов, но не подлежит сбросу

Счетчики доступны только для чтения

```php
use DraculAid\PhpMocker\Managers\MethodCase;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

/** Счетчики вызовов */
$methodCase->countAllCall;
$methodCase->countCall;
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)
