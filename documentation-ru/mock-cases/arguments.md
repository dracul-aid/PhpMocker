# PhpMocker - Кейс вызова мок-метода - Назначение выбрасывание исключения, в качестве работы метода
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)

С помощью `\DraculAid\PhpMocker\Managers\MethodCase::setRewriteArguments()` Вы можете перезаписать значения аргументов,
с которыми был вызван мок-метод

Если вы также установите ответ для вашего кейса (см `MethodCase::setWillReturn()`), то сможете полностью сымитировать работу
функции возвращающую результат своей работы, в том числе и через аргументы переданные по ссылке.

```php
use DraculAid\PhpMocker\Managers\MethodCase;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

$methodCase->setRewriteArguments();
$methodCase->setWillReturn();
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md) | [Кейс вызова](README.md)
