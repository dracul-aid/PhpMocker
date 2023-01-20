# Инструменты PhpMocker - Получение значения непубличных констант
[<< Оглавление](../README.md) | [Инструменты](README.md)

`DraculAid\PhpMocker\NotPublic::readConstant()` и `DraculAid\PhpMocker\NotPublic::constant()` позволяют читать `protected` и 
`private` константы. При чтении `private` констант можно прочитать только константы определенные в классе, при чтении констант
определенных в родителях будет выброшена критическая ошибка

`DraculAid\PhpMocker\NotPublic::readConstant()` Принимает имя класса (или объект) и имя константы

`DraculAid\PhpMocker\NotPublic::constant()` Использует объект "читатель непубличных элементов класса"

```php
use DraculAid\PhpMocker\NotPublic;

// создание тестового класса
class TestClassName {
    protected const PROTECTED_CONST = 'protected_const_value';
    private const PRIVATE_CONST = 'private_const_value';
}

// Создание потомка тестового класса
class ChildClassName extends TestClassName {}

// * * *

// "процедурный стиль"

// Выведет 'protected_const_value'
echo NotPublic::readConstant(TestClassName::class, 'PROTECTED_CONST') . "\n";
// Выведет 'private_const_value'
echo NotPublic::readConstant(TestClassName::class, 'PRIVATE_CONST') . "\n";

// Выведет 'protected_const_value'
echo NotPublic::readConstant(ChildClassName::class, 'PROTECTED_CONST') . "\n";
// Этот вызов закончится ошибкой, константа PRIVATE_CONST private и существует только в классе родителе
echo NotPublic::readConstant(ChildClassName::class, 'PRIVATE_CONST') . "\n";

// * * *

// можно использовать объект, а не имя класса

$object = new TestClassName();

// Выведет 'protected_const_value'
echo NotPublic::readConstant($object, 'PROTECTED_CONST') . "\n";
// Выведет 'private_const_value'
echo NotPublic::readConstant($object, 'PRIVATE_CONST') . "\n";

// * * *

// "Работа через объект-читатель"

// Получение "читателя"
$reader = NotPublic::instance(TestClassName::class);

// Выведет 'protected_const_value'
echo $reader->constant('PROTECTED_CONST') . "\n";
// Выведет 'private_const_value'
echo $reader->constant('PRIVATE_CONST') . "\n";
```

---

[<< Оглавление](../README.md) | [Инструменты](README.md)
