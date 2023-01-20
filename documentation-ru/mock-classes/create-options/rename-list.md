# PhpMocker - Установка новых имен для списка классов
[<< Оглавление](../../README.md) | [Параметры создания мок-классов](README.md)

Класс `\DraculAid\PhpMocker\CreateOptions\ClassNameList` используется, если при создании мок-классов (с помощью изменения
PHP кода), в случаях, когда в коде находится описание нескольких классов и каким-то из этих классов нужно поменять названия.

При создании объекта `ClassNameList` необходимо передать массив, с всеми именами подлежащими замене:
* Ключи массива - Полные имена классов (которые нужно будет заменить)
* Значения - Новые полные имена

```php
namespace TestNamespace;

use DraculAid\PhpMocker\CreateOptions\ClassNameList;
use DraculAid\PhpMocker\MockCreator;

// PHP код с описанием классов
$phpCode = "
    abstract class MyAbstractClass {/* какой-то код */}
    class MyClass {/* какой-то код */}
";

// Создание мок классов и получение менеджеров, для взаимодействия с мок-классами
$classManagers = MockCreator::hardFromPhpCode(
    $phpCode,
    new ClassNameList([
        'TestNamespace\\MyAbstractClass' => 'new_abstract_class_name',
        'TestNamespace\\MyClass' => 'TestNamespace\\NewClassName',
    ])
);

// Доступ к менеджеру класса, который изначально имел имя TestNamespace\MyAbstractClass
$classManagers['new_abstract_class_name'];
// Доступ к менеджеру класса, который изначально имел имя TestNamespace\MyClass
$classManagers['TestNamespace\\NewClassName'];
```

См также [Класс для смены имени одному классу](rename.md)

---

[<< Оглавление](../../README.md) | [Параметры создания мок-классов](README.md)