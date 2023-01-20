# Инструменты PhpMocker - Вызов непубличных методов
[<< Оглавление](../README.md) | [Инструменты](README.md)

`DraculAid\PhpMocker\NotPublic::callMethod()`, `DraculAid\PhpMocker\NotPublic::callStatic()`, `DraculAid\PhpMocker\NotPublic::call()`
позволяют вызывать `protected` и `private` методы. При вызове `private` метода можно вызвать только методы определенные в классе,
при вызове метода определенного в родителях будет выброшена критическая ошибка

`DraculAid\PhpMocker\NotPublic::callMethod()` Принимает имя класса (или объект) и имя метода, если передан объект будет вызван
метод объекта, если передан класс - вызовет статический метод

`DraculAid\PhpMocker\NotPublic::call()` Использует объект "читатель непубличных элементов класса" для вызова метода объекта.
`DraculAid\PhpMocker\NotPublic::callStatic()` аналогична, но используется для вызова статического метода 

```php
use DraculAid\PhpMocker\NotPublic;

// создание тестового класса
class TestClassName {
    protected static function protectedStaticMethod(string $a): string
    {
        return 'protected+' . $a;
    }
    private static function privateStaticMethod(string $a): string
    {
        return 'private+' . $a;
    }
    protected function protectedObjectMethod(): string
    {
        return 'object';
    }
}

// Создание потомка тестового класса
class ChildClassName extends TestClassName {}

// * * *

// "процедурный стиль"

// Выведет 'protected+AA'
echo NotPublic::callMethod(TestClassName::class, 'protectedStaticMethod', ['AA']) . "\n";
// Выведет 'private+BB'
echo NotPublic::callMethod(TestClassName::class, 'privateStaticMethod', ['AA']) . "\n";

// Выведет 'protected+AA'
echo NotPublic::callMethod(ChildClassName::class, 'protectedStaticMethod', ['BB']) . "\n";
// Этот вызов закончится ошибкой, константа privateStaticMethod() private и существует только в классе родителе
echo NotPublic::callMethod(ChildClassName::class, 'privateStaticMethod', ['BB']) . "\n";

// * * *

// можно использовать объект, а не имя класса

$object = new TestClassName();

// Выведет 'object'
echo NotPublic::callMethod($object, 'protectedObjectMethod') . "\n";

// * * *

// "Работа через объект-читатель"

$object = new TestClassName();

// Получение "читателя"
$reader = NotPublic::instance($object);

// Выведет 'protected+CC'
echo $reader->callStatic('protectedStaticMethod', ['CC']) . "\n";
// Выведет 'object'
echo $reader->call('protectedObjectMethod') . "\n";
```

---

[<< Оглавление](../README.md) | [Инструменты](README.md)
