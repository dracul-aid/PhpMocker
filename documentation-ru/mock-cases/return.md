# PhpMocker - Кейс вызова мок-метода - Назначение результата работы методу
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)

Для установки возвращаемого значения используется `MethodCase::setWillReturn()`, для сброса ранее установленного значения
`MethodCase::setWillReturnClear()`, обе функции, также могут сбросить и счетчик `MethodCase::$countCall`

```php
use DraculAid\PhpMocker\Managers\MethodCase;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

// Установит ответ для мок-метода в $returnData
$methodCase->setWillReturn($returnData);
// Установит ответ для мок-метода в $returnData и сбросит счетчик вызова для кейса
$methodCase->setWillReturn($returnData, true);

// кейс ничего не возвращает, функция будет работать как обычно
$methodCase->setWillReturnClear();
// также сбросит счетчик вызовов кейса
$methodCase->setWillReturnClear(true);
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)
