# PhpMocker - Менеджер мок-объектов - Менеджер мок-метода для мок-объекта
[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)

Для взаимодействия с методами (в том числе и `protected` и `private`), в том числе, и для установки мок-значений
для результатов работы метода, нужно получить [менеджер метода](../manager-method/README.md)

`ObjectManager::getMethodManager()` Вернет менеджер метода, если его нет - попытается его создать (при провале создания
будет выброшено исключение)

`ObjectManager::$methodManagers` Содержит список всех созданных менеджеров методов для мок-объекта, пока не было обращения
к `ObjectManager::getMethodManager()` для метода, в этом списке его не будет

`ObjectManager::$mockMethodNames` Содержит список всех методов мок-объекта, которые могут выступать как мок-методы

```php
use DraculAid\PhpMocker\MockCreator;

// создание класса TestClassName для которого будет далее создан мок-класс
eval("class TestClassName {
    public function canBeMockMethod(\$a) {return 'A=>' . \$a;}
    final public function canNotBeMockMethod(\$a) {return 'B=>' . \$a;}
}");

// Создаем мок-класс для TestClassName и получаем "менеджер мок-класса"
// На основе мок-класса создаем мок-объект
$objectManager = MockCreator::softClass('TestClassName')->createObjectAndManager(false, [], $mockObject);

// * * *

// Вернет ['canBeMockMethod' => 'canBeMockMethod']
var_dump($objectManager->mockMethodNames);

// Вернет пустой массив
var_dump($objectManager->methodManagers);

// * * *

// Получение менеджера метода для метода canBeMockMethod()
$canBeMockMethodManager = $objectManager->getMethodManager('canBeMockMethod');

// Вернет ['canBeMockMethod' => object]
var_dump($objectManager->methodManagers);

// Идентичные обращения к менеджеру метода
// $canBeMockMethodManager
// $objectManager->getMethodManager('canBeMockMethod')
// $objectManager->methodManagers['canBeMockMethod']

// установка "ответа по умолчанию" для метода
// теперь, при вызове метода он всегда будет возвращать 'AAA'
$canBeMockMethodManager->defaultCase()->setWillReturn('AAA');

// * * *

// Получение менеджера метода для метода canNotBeMockMethod()
$canNotBeMockMethodManager = $objectManager->getMethodManager('canNotBeMockMethod');

// Вернет ['canBeMockMethod' => object, 'canNotBeMockMethod' => object]
var_dump($objectManager->methodManagers);

// попытка назначить значение для возврата для метода canNotBeMockMethod закончится провалом,
// так как этот метод не может быть мок-методом (мок-классы созданные с помощью наследования не могут создавать
// мок-методы для final методов)
$canBeMockMethodManager->defaultCase()->setWillReturn('BBB');
```

---

[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)
