# Инструменты PhpMocker - Взаимодействие с непубличными свойствами
[<< Оглавление](../README.md) | [Инструменты](README.md)

`DraculAid\PhpMocker\NotPublic` предоставляет функционал для чтения и записи `protected` и `private` свойств. При взаимодействии
с `private` свойствами можно взаимодействовать только со свойствами определенными в классе, при взаимодействии с методами
определенными в родителях будет выброшена критическая ошибка

`DraculAid\PhpMocker\NotPublic::readProperty()` и `DraculAid\PhpMocker\NotPublic::writeProperty()` Позволяют читать и записывать
в свойства. Если в функцию переданы объект - это будет взаимодействие с свойством объекта, если передана строка с именем
класса - это будет взаимодействие с статическим свойством.

`DraculAid\PhpMocker\NotPublic::get()`, `DraculAid\PhpMocker\NotPublic::getStatic()`, `DraculAid\PhpMocker\NotPublic::set()`, `DraculAid\PhpMocker\NotPublic::setStatic()`
Позволяют читать и писать в свойства, через объект "читатель непубличных элементов класса"

```php
use DraculAid\PhpMocker\NotPublic;

// создание тестового класса
class TestClassName {
    protected static $protectedStaticVar = 'protected-static-var';
    private static $privateStaticVar = 'private-static-var';
    
    protected $protectedObjectVar = 'protected-object-var';
}

// Создание потомка тестового класса
class ChildClassName extends TestClassName {}

// * * *

// "процедурный стиль" чтения и записи статических свойств

// Выведет 'protected-static-var'
echo NotPublic::readProperty(TestClassName::class, 'protectedStaticVar') . "\n";
// Выведет 'private-static-var'
echo NotPublic::readProperty(TestClassName::class, 'privateStaticVar') . "\n";

// Выведет 'protected-static-var'
echo NotPublic::readProperty(ChildClassName::class, 'protectedStaticVar') . "\n";
// Этот вызов закончится ошибкой, константа PRIVATE_CONST private и существует только в классе родителе
echo NotPublic::readProperty(ChildClassName::class, 'privateStaticVar') . "\n";

// Запись в TestClassName::protectedStaticVar = 'new value'
echo NotPublic::readProperty(TestClassName::class, 'protectedStaticVar', 'new value');

// * * *

// "процедурный стиль" чтения и записи свойств объекта

$object = new TestClassName();

// Выведет 'protected-object-var'
echo NotPublic::readProperty($object, 'protectedObjectVar') . "\n";

// Запись в $object->protectedObjectVar = 'new value'
echo NotPublic::readProperty(TestClassName::class, 'protectedObjectVar', 'new value');

// * * *

// "Работа через объект-читатель"

$object = new TestClassName();

// Получение "читателя"
$reader = NotPublic::instance($object);

// Выведет 'protected-static-var'
echo $reader->getStatic('protectedStaticVar') . "\n";
// Выведет 'protected-object-var'
echo $reader->get('protectedObjectVar') . "\n";

// Запись в TestClassName::protectedStaticVar = 'AAA'
$reader->getStatic('protectedStaticVar', 'AAA');
// Запись в $object->protectedObjectVar = 'BBB'
$reader->get('protectedObjectVar', 'BBB');
```

## Запись списка свойств

---

[<< Оглавление](../README.md) | [Инструменты](README.md)
