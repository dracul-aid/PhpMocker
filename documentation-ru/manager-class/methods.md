# PhpMocker - Менеджер Мок-Классов - Менеджеры методов
[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)

Для взаимодействия с методами (в том числе и `protected` и `private`), в том числе, и для установки мок-значений
для результатов работы метода, нужно получить [менеджер метода](../manager-method/README.md)

`ClassManager::getMethodManager()` Вернет менеджер метода, если его нет - попытается его создать (при провале создания
будет выброшено исключение)

`ClassManager::$methodManagers` Содержит список всех созданных менеджеров методов для класса, пока не было обращения к
`ClassManager::getMethodManager()` для метода, в этом списке его не будет

`ClassManager::$mockMethodNames` Содержит список всех методов мок-класса, которые могут выступать как мок-методы

```php
use DraculAid\PhpMocker\MockCreator;

// создание класса TestClassName для которого будет далее создан мок-класс
eval("class TestClassName {
    public static function canBeMockMethod(\$a) {return 'A=>' . \$a;}
    final public static function canNotBeMockMethod(\$a) {return 'B=>' . \$a;}
}");

// Создаем мок-класс для TestClassName и получаем "менеджер мок-класса"
$classManager = MockCreator::softClass('TestClassName');

// * * *

// Вернет ['canBeMockMethod' => 'canBeMockMethod']
var_dump($classManager->mockMethodNames);

// Вернет пустой массив
var_dump($classManager->methodManagers);

// * * *

// Получение менеджера метода для метода canBeMockMethod()
$canBeMockMethodManager = $classManager->getMethodManager('canBeMockMethod');

// Вернет ['canBeMockMethod' => object]
var_dump($classManager->methodManagers);

// Идентичные обращения к менеджеру метода
// $canBeMockMethodManager
// $classManager->getMethodManager('canBeMockMethod')
// $classManager->methodManagers['canBeMockMethod']

// установка "ответа по умолчанию" для метода
// теперь, при вызове метода он всегда будет возвращать 'AAA'
$canBeMockMethodManager->defaultCase()->setWillReturn('AAA');

// * * *

// Получение менеджера метода для метода canNotBeMockMethod()
$canNotBeMockMethodManager = $classManager->getMethodManager('canNotBeMockMethod');

// Вернет ['canBeMockMethod' => object, 'canNotBeMockMethod' => object]
var_dump($classManager->methodManagers);

// попытка назначить значение для возврата для метода canNotBeMockMethod закончится провалом,
// так как этот метод не может быть мок-методом (мок-классы созданные с помощью наследования не могут создавать
// мок-методы для final методов)
$canBeMockMethodManager->defaultCase()->setWillReturn('BBB');
```

--- 

[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)
