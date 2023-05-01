# PhpMocker - Кейс вызова мок-метода
[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md)

* [Счетчик вызовов](counter.md)
* [Изменение поведения конструктора](constructor.md)
* [Назначение результата работы методу](return.md)
* [Назначение выбрасывание исключения, в качестве работы метода](exception.md)
* [Пользовательская функция, предваряющая работу метода](user-function.md)
* [Аргументы по ссылке и изменение значений аргументов](arguments.md)

## Свойства кейса вызова

`MethodCase::$methodManager` Объект "менеджер мок-метода" владеющий кейсом вызова

`MethodCase::$arguments` Список аргументов вызова, `MethodCase::$index` Хэш от списка аргументов, используется, как 
"уникальный идентификатор" кейса.

```php
use DraculAid\PhpMocker\Managers\MethodCase;

/** @var MethodCase $methodCase Полученный любым образом кейс вызова */
$methodCase;

/**
 * Список аргументов, что-то вида ['ABC', 123] 
*/
$methodCase->arguments;
// Хэш от массива-списка аргументов
$methodCase->index;

/**
 * "Менеджер мок-метода", который в свою очередь принадлежит:
 * @see \DraculAid\PhpMocker\Managers\ClassManager "Менеджеру мок-класса"
 * @see \DraculAid\PhpMocker\Managers\ObjectManager "Менеджеру мок-объекта"
 */
$methodCase->methodManager;
```

---

[<< Оглавление](../README.md) | [Менеджер мок-метода](../manager-method/README.md)
