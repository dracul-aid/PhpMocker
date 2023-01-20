# PhpMocker - Создание мок-классов с помощью наследования
[<< Оглавление](../README.md) | [Мок-классы](README.md)

Механизм создания мок-классов с помощью наследования схож с механизмом используемым в библиотеке юнит-тестирования PphUnit.
Создается класс-наследник, в котором переопределяются все возможные методы (включая методы родителей).

Этот способ имеет следующие ограничения:
* Невозможно создать мок-классы для `final class` и `enum`
* Невозможность создания мок-методов для `final` и `private function`

**ВНИМАНИЕ: по умолчанию, все созданные мок-классы получат случайное имя.** Установить конкретно имя класса, можно с помощью
[параметров создания мок-классов](create-options/README.md)

_В PphUnit также отключена возможность создавать мок-методы для `static function` - это вызвано не невозможностью такого
создания, а логическими проблемами с которыми сталкиваются некоторые разработчики и по причине того, что для большинства
тестов такие мок-методы просто бесполезны. Создав мок-метод `MockClass::staticMethod()`, вы ни как не повлияете на то,
что в коде вызывается родительский метод `ParentClass::staticMethod()`, который по прежнему не является моком_

### Создание мок-классов для классов и абстрактных классов

```php
/**
 * Создаст мок-класс с помощью наследования класс $className и вернет "менеджер мок-класса"
 *
 * $className - Полное имя класса (включая пространство имен). Например \DraculAid\PhpMocker\MockCreator::class
 */
$classManagers = \DraculAid\PhpMocker\MockCreator::softClass($className);

// Создание мок-класса с определенными параметрами - $createOptions (например установкой имени создаваемого класса)
$classManagers = \DraculAid\PhpMocker\MockCreator::softClass($className, $createOptions);
```

С помощью `MockCreator::softClass()` можно создать мок-класс для обычных и абстрактных классов

Абстрактные методы, будут "пустыми" методами. Для корректной работы им обязательно будет необходимо назначить "_результат
работы_". Вызов без этого назначения приведет к ошибке.

### Создание мок-класса для трейтов

```php
/**
 * Создаст мок-класс, для автоматически созданного класса имплементирующего трейт (трейты)
 * Вернет "менеджер мок-класса"
 *
 * $traitOrTraits - Строка с полным именем трейта или массив со списком трейтов
 */
$classManagers = \DraculAid\PhpMocker\MockCreator::softTrait($traitOrTraits);

// Создание мок-класса с определенными параметрами - $createOptions (например установкой имени создаваемого класса)
$classManagers = \DraculAid\PhpMocker\MockCreator::softTrait($traitOrTraits, $createOptions);
```

`MockCreator::softTrait` сначала создает класс, имплементирующий указанные трейты. А потом создает мок-класс наследующий
этому автоматически созданному классу.

## Примеры использования

### Создание мок-класса для абстрактного класса

```php
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\CreateOptions\ClassName;

// класс, для которого будет создаваться мок-класс
abstract class MyClass()
{
    abstract public function methodAbstract(): string;
    public function methodPublic(): string {}
    final public function methodFinal(): string {}
    private function methodPrivate(): string {}
    
    public static function methodStatic(): string
    {
        return 'AAA';
    }
}

// * * *

// Создаст Мок-класс для MyClass и вернет менеджер мок-класса
// Созданные класс будет иметь случайное имя
// (т.е. что-то вроде: class ___class_name_1231234334___ extends MyClass {})
$classManagers = MockCreator::softClass(MyClass::class);

// * * *

// Создаст Мок-класс для MyClass и вернет менеджер мок-класса
// Созданный мок-класс получит имя MyMockClass
// (т.е. что-то вроде: class MyMockClass extends MyClass {})
$classManagers = MockCreator::softClass(MyClass::class, new ClassName('MyMockClass'));

/** @var MyClass $testObject - Создаст объект мок-класса */
$testObject = $classManagers->createObjectAndManager();

// * * *

// этот вызов закончится ошибкой, так как methodAbstract() не получил никакого "ответа"
$testObject->methodAbstract();

// Установка ответа для метода methodAbstract()
$classManagers->getMethodManager('methodAbstract')->defaultCase()->setWillReturn('AAA');
// этот вызов вернет 'AAA'
echo $testObject->methodAbstract() . "\n";

// * * *

// Эти вызовы завершатся ошибкой, так как созданный с помощью наследования мок-класс
// не может создать мок-методы если они были описаны как final или private 
$classManagers->getMethodManager('methodFinal')->defaultCase();
$classManagers->getMethodManager('methodPrivate')->defaultCase();

// * * *

// вернет TRUE
// MyMockClass::methodStatic() и MyClass::methodStatic() вернут 'AAA'
echo MyMockClass::methodStatic() === MyClass::methodStatic() ? 'FALSE' : 'TRUE';

// MyMockClass::methodStatic() всегда должен возвращать 'BBB'
$classManagers->getMethodManager('methodStatic')->defaultCase('BBB');

// вернет FALSE
// MyMockClass::methodStatic() вернет 'BBB'
// MyClass::methodStatic() вернет 'AAA'
echo MyMockClass::methodStatic() === MyClass::methodStatic() ? 'FALSE' : 'TRUE';
```

### Создание мок-класса для тестирования трейтов

```php
use DraculAid\PhpMocker\MockCreator;

// Создаст мок-класс для тестирования трейта "MyTrait"
$classManagers = MockCreator::softTrait('MyTrait');

// Создание мок-класса для тестирования трейтов 'MyTrait1' и 'MyTrait2'
$classManagers = MockCreator::softTrait(['MyTrait1', 'MyTrait2']);
```

---

[<< Оглавление](../README.md) | [Мок-классы](README.md)
