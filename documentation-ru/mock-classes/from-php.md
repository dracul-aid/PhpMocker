# PhpMocker - Создание мок-классов с помощью изменения PHP кода
[<< Оглавление](../README.md) | [Мок-классы](README.md)

Механизм "создания мок-классов с помощью изменения PHP кода" основан, как и указанно в названии, на изменении PHP кода.
Благодаря этому преобразованными в мок-методы могут быть `private` и `final` методы. Также этот способ может работать с
финальными классами и перечислениями.

**Главной проблемой при использовании этого способа, является то, что класс может быть уже загружен, а значит, изменить его
будет уже нельзя.** Для избежания этой проблемы, можно воспользоваться [автозагрузчиком классов PhpMocker-а](../autoloader/README.md)
после чего все загружаемые классы могут быть преобразованы в мок-классы.

Благодаря тому, что этот способ преобразует в мок-класс именно нужный класс, а не создает его "двойника" с помощью наследника.
Появляется удобная возможность для использования в тестах `static` методов

Интерфейсы не будут преобразовываться в тестовый двойник, так как в этом нет никакого смысла. Также будут проигнорированы
`abstract` методы.

## Получение "менеджера мок-классов" при автозагрузке

_Ниже описанное подразумевает, что подключен автозагрузчик классов PhpMocker-а и автозагрузка класса разрешена_

Подробнее об [автозагрузчике классов](../autoloader/README.md)

Для получения "менеджера мок-класса" нужно обратиться к `MockManager::getForClass()`, если класс был загружен как мок-класс,
функция вернет "менеджер мок-класса". Если класс не был загружен как мок-класс, то будет выброшено исключение.
```php
/**
 * Вернет "менеджер мок-класса" для мок-класса $className, или выбросит исключение  
 */
$mockClassManager = \DraculAid\PhpMocker\MockManager::getForClass($className);
```

Проблема `MockManager::getForClass()` заключается в том, что эта функция не вызывает автозагрузку класса, решить это
неудобство можно с помощью `MockCreator::hardLoadClass()`. `MockCreator::hardLoadClass()` - также вернет "менеджер мок-класса",
но если класс еще не был загружен - проведет эту загрузку с преобразованием в мок-класс

`MockCreator::hardLoadClass()` Преобразует загружаемый класс в мок-класс, даже если настройки автозагрузчика это запрещают!

Вызов `MockCreator::hardLoadClass()` может вызвать выбрасывание исключения, в случае, если класс уже был загружен без
преобразования в мок-класс.

```php
/**
 * Вернет "менеджер мок-класса" для $className, если класс $className еще не был загружен, также произойдет его загрузка
 * с преобразованием в мок-класс.
 */
$mockClassManager = \DraculAid\PhpMocker\MockCreator::hardLoadClass($className);
```

## Создание мок-класса из PHP кода

Если по какой-то причине вам не подходит автозагрузчик классов PhpMocker-а, создать тестовые двойники можно с помощью
передачи PHP кода в:
* `MockCreator::hardFromPhpCode()` - Создаст мок-класс, изменив переданный PHP код
* `MockCreator::hardFromScript()` - Создаст мок-класс, изменив PHP код найденный в указанном файле-скрипте

Попытка создать мок-класс, для уже определенного класса, встроенного класса или интерфейса закончится ошибкой

В случае успеха функции вернут "менеджер мок-класса". Также, кроме PHP код (или пути к файлу с PHP кодом), функции могут
принять [параметры создания мок-классов](create-options/README.md), таким образом мок-классы могут быть созданы под
любым именем. Создание тестового двойника с явно указанным именем позволяет создавать мок-классы, даже если оригинальный класс
уже был загружен, но функционал для тестирования в таком случае будет очень сильно ограничен.

```php
/**
 * Проанализирует PHP код переданный в $phpCode, преобразует код в код мок-класса и выполнит этот код
 * 
 * В случае успеха создания мок-класса вернет "менеджер мок-класс", в случае, если в коде находятся описания нескольких
 * классов - вернет массив "менеджеров". Индексами массива будут выступать полные имена созданных мок-классов (т.е. включая
 * пространство имен).
 */
$mockClassManager = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCode);

/**
 * Аналогично создаст мок-класс, но принимает не PHP код, а путь к файлу, содержащему PHP код
 */
$mockClassManager = \DraculAid\PhpMocker\MockCreator::hardFromScript($phpScriptPath);
```

### Несколько классов в коде

PHP код также может содержать описание нескольких классов. В таком случае, функции создания мок-классов с помощью изменения
PHP кода вернут не "менеджер мок-класса", а массив, со списком менеджеров. В качестве ключей этого массива будут выступать
имена классов, в качестве значений - объекты-менеджеры мок-классов

См также, [как поменять имена классам](create-options/rename-list.md)

```php
use DraculAid\PhpMocker\MockCreator;

$phpCode = "
    namespace MyTestNamespace;
    abstract class MyAbstractClass {}
    class MyClass {}
";

$classManagers = MockCreator::hardFromPhpCode($phpCode);

// доступ к менеджеру мок-класса MyAbstractClass
$classManagers['MyTestNamespace\\MyAbstractClass']; 
// доступ к менеджеру мок-класса MyClass
$classManagers['MyTestNamespace\\MyClass']; 
```

---

[<< Оглавление](../README.md) | [Мок-классы](README.md)
