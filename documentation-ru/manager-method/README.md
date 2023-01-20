# PhpMocker - Менеджер методов (мок-методов)
[<< Оглавление](../README.md)

* [Кейсы вызовов](cases.md)
* [Предваряющая выполнение метода функция](user-function.md)
* [Счетчики вызовов](counter.md)

Менеджер метода создается с привязкой к [Менеджеру мок-класса](../manager-class/README.md) или [Менеджеру мок-объекта](../manager-object/README.md)

`\DraculAid\PhpMocker\Managers\MethodManager` Класс менеджеров методов

## "Владелец" менеджера, имя и вызов метода

`MethodManager::$ownerManager` "Владельцем" менеджера может выступить "[менеджер мок-класса](../manager-class/README.md)"
или "[менеджер мок-объекта](../manager-object/README.md)", Владелец влияет на работу [Кейсов вызова мок-метода](../mock-cases/README.md)
(там же находится и описание этого влияния)

`MethodManager::$name` Хранит имя метода

`MethodManager::call()` Позволяет произвести вызов метода (даже если это `protected` или `private` метод)

```php
use DraculAid\PhpMocker\Managers\MethodManager

/** @var MethodManager $methodManager Полученный каким-то образом менеджер метода */
$methodManager;

// выведет класс менеджера-родителя
echo get_class($methodManager->ownerManager) . "\n";

// выведет имя метода, например 'myPublicFunction'
echo $methodManager->name . "\n";

// проведет вызов метода, т.е. $object->myPublicFunction($arg1, $arg2)
// аргументы передаются последовательно
echo $methodManager->call($arg1, $arg2);
```

## Менеджер метода или менеджер мок-метода (Различия)

**Менеджер мок-метода** управляет как мок-методом мок-объекта или мок-класса, так и обычным методом. Разница заключается
только в том, что для методов, которые не могут быть моками, доступен только вызов `MethodManager::call()`, а "счетчики
вызовов" и функции связанные с "кейсами вызовов" - нет.

Пример, для мок-классов созданных с помощью наследования
```php
use DraculAid\PhpMocker\MockCreator;

// Создание класса
eval('class TestClassName {
    public function functionIsNotFinal() {}
    final public function functionIsFinal() {}
}');

// создаем мок-класс с помощью наследования
$classManager = MockCreator::softClass('TestClassName');

// * * *

// Метод functionIsNotFinal() является мок-методом
$functionIsNotFinalManager = $classManager->getMethodManager('functionIsNotFinal');
// его можно вызвать
$functionIsNotFinalManager->call();
// можно получить "кейс вызова"
$functionIsNotFinalManager->defaultCase();

// * * *

// Метод functionIsFinal() НЕ является мок-методом
// (мок-классы, созданные с помощью наследования не могут иметь final мок-методы)
$functionIsFinalManager = $classManager->getMethodManager('functionIsFinal');
// его можно вызвать
$functionIsFinalManager->call();
// "кейс вызова" получить нельзя - тут будет выброшено исключение
$functionIsFinalManager->defaultCase();
```

### Мок-методы мок-классов и объектов, созданных с помощью наследования

Все методы классов, кроме `final` и `private`

Это актуально, как для "менеджеров мок-классов", так и для "менеджеров мок-объектов"

### Мок-методы мок-классов и объектов, созданных с помощью изменения PHP кода

Мок-класс будет иметь все методы определенные в нем (но не определенных в используемых трейтах), как мок-методы. Методы
определенные в родителях, не будут мок-методами.

**Наглядно можно понять в примере**

```php
use DraculAid\PhpMocker\MockCreator;

// Класс-родитель. Хранится в classes/TestParentClass.php
class TestParentClass {
    protected function parent_f() {}
}

// Трейт. Хранится в classes/TestTraitClass.php
trait TestTraitClass {
    protected function trait_f() {}
}

// Класс, который будет превращен в мок-класс. Хранится в classes/ClassForMockTest.php
class ClassForMockTest extends TestParentClass {
    use TestTraitClass;
    protected function mock_f() {}
}

// * * *

// Создание мок-класса для класса ClassForMockTest
// код класса будет загружен из файла и преобразован в код мок-класса
$classManager = MockCreator::hardFromScript('classes/ClassForMockTest.php');

// Выведет, что ClassForMockTest имеет три метода:
// parent_f() - этот метод не может быть мок-методом, так как описан в классе-родителе
// trait_f() - этот метод не может быть мок-методом, так как описан в используемом трейте
// mock_f() - этот метод является мок-методом
var_dump(get_class_methods('ClassForMockTest'));

// Будет хранить только mock_f(), так как только этот метод может быть мок-методом 
var_dump($classManager->mockMethodNames);

// * * *

// Корректные операции (вызов метода)
$classManager->getMethodManager('parent_f')->call();
$classManager->getMethodManager('trait_f')->call();
$classManager->getMethodManager('mock_f')->call();

// это тоже корректно (получение "кейса вызова по умолчанию")
$classManager->getMethodManager('mock_f')->defaultCase();

// эти вызовы закончатся выброшенным исключением
$classManager->getMethodManager('parent_f')->defaultCase();
$classManager->getMethodManager('trait_f')->defaultCase();
```

---

[<< Оглавление](../README.md)