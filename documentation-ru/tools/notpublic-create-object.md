# Инструменты PhpMocker - Создание объектов
[<< Оглавление](../README.md) | [Инструменты](README.md)

`DraculAid\PhpMocker\NotPublic::createObject()` Позволяет создавать объекты, без вызова или с вызовом конструктора (даже если 
конструктор был `private` или `protected`). И дает возможность установить ряд свойств (в том числе `private` и `protected`)

`DraculAid\PhpMocker\NotPublic::createObject()` принимает 3 аргумента (_2 и 3 аргументы - необязательные_):
1) Имя класса
2) FALSE (если создание без вызова конструктора) или массив с аргументами конструктора
3) Массив, с свойствами для установки (пустой массив, если ничего устанавливать не надо)

При создании объекта из мок-класса, даже если не вызывался конструктор, автоматически будет "создан и менеджер мок-объекта".
Следовательно, в списке объектов мок-класса, созданный объект появится тоже.  

## Пример создания объектов
```php
use DraculAid\PhpMocker\NotPublic;

// создание тестового класса
class TestClassName {
    public string $publicVar = 'public_not_set';
    protected string $protectedVar = 'protected_not_set';
    private string $privateVar = 'private_not_set';
    
    public bool $callConstructor = false;
    public string $constructorSetVar = 'construct_not_set';

    // конструкция new TestClassName() приведет к возникновению критической ошибки
    protected function __construct(string $setVar)
    {
        $this->callConstructor;
        $this->constructorSetVar = $setVar;
    }
    
    public function getProtectedVar(): string
    {
        return $this->protectedVar; 
    }
    
    public function getPrivateVar(): string
    {
        return $this->privateVar;
    }
}

// * * *

// Создает объект TestClassName без вызова конструктора
$object = NotPublic::createObject(TestClassName::class);
$object = NotPublic::createObject(TestClassName::class, false);

$object->callConstructor; // хранит FALSE
$object->publicVar; // хранит 'public_not_set'
$object->publicVar; // хранит 'construct_not_set'
$object->getProtectedVar(); // Вернет 'protected_not_set'
$object->getPrivateVar(); // Вернет 'private_not_set'

// * * *

// Создает объект TestClassName без вызова конструктора с установкой свойств
// список свойств - массив, с ключами "именами свойств" и значениями "значениями свойств"
$object = NotPublic::createObject(TestClassName::class, false, ['publicVar' => 'AAA', 'protectedVar' => 'BBB', 'privateVar' => 'CCC']);

$object->callConstructor; // хранит FALSE
$object->publicVar; // хранит 'public_not_set'
$object->publicVar; // хранит 'AAA'
$object->getProtectedVar(); // Вернет 'BBB'
$object->getPrivateVar(); // Вернет 'CCC'

// * * *

// Создает объект TestClassName с вызовом конструктора (в данном примере конструктор protected)
$object = NotPublic::createObject(TestClassName::class, ['ABC']);

$object->callConstructor; // хранит TRUE
$object->publicVar; // хранит 'ABC'
$object->publicVar; // хранит 'construct_not_set'
$object->getProtectedVar(); // Вернет 'protected_not_set'
$object->getPrivateVar(); // Вернет 'private_not_set'

// * * *

// Создает объект TestClassName с вызовом конструктора (в данном примере конструктор protected) с установкой свойств
// список свойств - массив, с ключами "именами свойств" и значениями "значениями свойств"
$object = NotPublic::createObject(TestClassName::class, ['ABC'], ['publicVar' => 'AAA', 'protectedVar' => 'BBB', 'privateVar' => 'CCC']);

$object->callConstructor; // хранит TRUE
$object->publicVar; // хранит 'ABC'
$object->publicVar; // хранит 'AAA'
$object->getProtectedVar(); // Вернет 'BBB'
$object->getPrivateVar(); // Вернет 'CCC'
```

## Нюансы вызова private конструктора и попыток записи в private свойства

```php
use DraculAid\PhpMocker\NotPublic;

// создание тестового класса
class TestClassName {
    public bool $callConstruct = false;
    private string $privateVar = 'not_set';
    private function __construct()
    {
        $this->callConstruct = true;
    }
    public function getPrivateVar(): string
    {
        return $this->privateVar;
    }
}

// Создание потомка тестового класса
class ChildClassName extends TestClassName {}

// * * *

// Создание объекта TestClassName пройдет успешно
$object = NotPublic::createObject(TestClassName::class, [], ['privateVar' => 'AAA']);

// Создание объекта ChildClassName закончится ошибкой.
// Ошибка будет вызвана тем, что TestClassName имел приватный конструктор, а его потомок, ChildClassName, не переопределял
// конструктор, а следовательно как бы вообще его не имеет, поэтому вызвать его невозможно
$object = NotPublic::createObject(ChildClassName::class, []);

// * * *

// Создать объект ChildClassName без вызова конструктора можно
$object = NotPublic::createObject(ChildClassName::class, false, ['privateVar' => 'AAA']);

// Вернет следующее содержимое объекта:
// object(ChildClassName)#5 (3) {
//  ["callConstruct"] => bool(false)
//  ["privateVar":"TestClassName":private] => string(7) "not_set"
//  ["privateVar"] => string(2) "11"
// }
var_dump($object);

// Иными словами, PHP создаст public $privateVar для объекта
$object->privateVar; // Вернет 'AAA'
$object->getPrivateVar(); // Вернет 'not_set'
```

---

[<< Оглавление](../README.md) | [Инструменты](README.md)
