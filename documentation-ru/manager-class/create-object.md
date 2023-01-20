# PhpMocker - Менеджер Мок-Классов - Создание объектов
[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)

`DraculAid\PhpMocker\Managers\ClassManager::createObject()` Создаст мок-объект, вызвав конструктор (даже если он не публичный) 

`DraculAid\PhpMocker\Managers\ClassManager::createObjectWithoutConstructor()` Создаст мок-объект, без вызова конструктора.
Также запишет в объект свойства (в том числе и не публичные)

`DraculAid\PhpMocker\Managers\ClassManager::createObjectAndManager()` Создаст мок-объект и вернет [менеджер мок-объекта](../manager-object/README.md),
Функция поддерживает создание со следующими возможностями: 
* Создать объект без вызова конструктора
* Создать объект с вызовом непубличного конструктора
* Установит свойства после создания объекта (в том числе и не публичные свойства)

```php
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;

// Создание мок-класса
$mockClassManager = MockCreator::hardFromPhpCode('class TestMockClass {
    public $a = '';
    public $b = '';
    public $c = '';
    protected $d = '';
    private $e = '';
    protected function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;    
    }
    public static function create($a, $b)
    {
        return new self($a, $b);
    }
}');

/**
 * Создание объекта с помощью оператора new
 * Функция TestMockClass::create содержит оператор new (см определение класса выше)
 */
$mockObject = TestMockClass::create('AAA', 'BBB');
$objectManager = ObjectManager::getManager($mockObject);

$mockObject->a; // вернет AAA
$mockObject->b; // вернет BBB

/**
 * Создание объекта через специальный метод "менеджера мок-класса"
 * Создание объекта будет производиться через вызов конструктора, даже если конструктор protected или private
 */
$mockObject = $mockClassManager->createObject('AAA', 'BBB');
$objectManager = ObjectManager::getManager($mockObject);

$mockObject->a; // вернет AAA
$mockObject->b; // вернет BBB

/**
 * Создание объекта через специальный метод "менеджера мок-класса", без вызова конструктора
 * 
 * @see ClassManager::createObjectWithoutConstructor ($setProperties[, $returnObjectManager])
 * $setProperties: Список свойств объекта для установки (формат: имя свойства => значение)
 * $returnObjectManager: Сюда будет помещен "менеджер мок-объекта"
 * RETURN: созданный мок-объект
 */
$mockObject = $mockClassManager->createObjectWithoutConstructor(['c' => 'CCC', 'd' => 'DDD', 'e' => 'EEE'], $objectManager);

$mockObject->a; // вернет ''
$mockObject->b; // вернет ''
$mockObject->c; // вернет CCC
$objectManager->getProperty('d'); // Вернет 'DDD'
$objectManager->getProperty('e'); // Вернет 'EEE'

/**
 * Создание мок-объекта (с вызовом или без конструктора, с установкой или без свойств) и получение "менеджера мок-объекта"
 * 
 * --- Создаст объект и вернет менеджер, без вызова конструктора
 * @see ClassManager::createObjectAndManager()
 * 
 * --- Создаст объект и вернет менеджер, без вызова конструктора. Установит свойства (в том числе protected или private)
 * @see ClassManager::createObjectAndManager (false, $setProperties)
 * 
 * --- Создаст объект и вернет менеджер, с вызовом конструктора, в том числе и (protected или private)
 * @see ClassManager::createObjectAndManager ($constructorArguments)
 * 
 * --- Создаст объект и вернет менеджер, с вызовом конструктора, в том числе и (protected или private).
 * --- Установит свойства (в том числе protected или private)
 * @see ClassManager::createObjectAndManager ($constructorArguments, $setProperties)
 * 
 * $setProperties: Список свойств объекта для установки (формат: имя свойства => значение)
 * $constructorArguments: Список аргументов конструктора (формат: последовательность аргументов)
 * RETURN: "менеджер мок-объекта"
 * 
 * Третьим параметром может получить созданный мок-объект
 * @see ClassManager::createObjectAndManager ($constructorArguments, $setProperties, $mockObject)
 */

$objectManager = $mockClassManager->createObjectAndManager(['AAA', 'BBB'], ['c' => 'CCC', 'd' => 'DDD', 'e' => 'EEE'], $mockObject);

$mockObject->a; // вернет 'AAA'
$mockObject->b; // вернет 'BBB'
$mockObject->c; // вернет CCC
$objectManager->getProperty('d'); // Вернет 'DDD'
$objectManager->getProperty('e'); // Вернет 'EEE'
```

--- 

[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)
