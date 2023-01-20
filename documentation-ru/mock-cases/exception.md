# PhpMocker - Кейс вызова мок-метода - Назначение выбрасывание исключения, в качестве работы метода
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)

В мок-метод можно передать исключение, которое будет выброшено при вызове метода, для этого используется `MethodCase::setWillException()`

```php
use DraculAid\PhpMocker\Managers\MethodCase;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

// При вызове метода будет выброшено исключение \RuntimeException
$methodCase->setWillException( new \RuntimeException('Error text') );

// устанавливая исключение, также можно сбросить ранее накопленные значения счетчика вызова кейса
$methodCase->setWillException($exceptionObject, true);

// "по умолчанию", при установке исключения, будет сброшено ранее установленная "пользовательская функция" и "Возвращаемое кейсом значение"
// это можно отменить передав третьим параметром FALSE
// (в данном примере, 2-ым передается TRUE - сброс счетчика вызова, но счетчик можно и не сбрасывать, для этого нужно передать FALSE)
$methodCase->setWillException($exceptionObject, true, false);
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)
