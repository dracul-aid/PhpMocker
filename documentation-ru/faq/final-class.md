# PhpMocker - Создание mock классов/методов для final class (финальных классов)
[<< Оглавление](../README.md) | [Вопросы/Ответы](README.md)

---

_Вопрос: можно ли используя PhpMocker создавать мок-классы и мок-методы, для финальных классов (конструкции final class {})_

**Коротко - Да, можно**

Используя `MockCreator::hardFromPhpCode()` или `MockCreator::hardFromScript()`, или [Автозагрузку с преобразованием в мок-классы](../autoloader/README.md)
Подробнее о том, [как создавать мок-классы](../mock-classes/README.md)

---

_Вопрос: **PHPUnit** не позволяет создать mock для final class {}, почему?_

PHPUnit, и подобные фреймворки для юнит-тестирования, создают мок-классы с помощью наследования,
а наследовать от финальных классов - невозможно. Поэтому PHPUnit и не поддерживает моки для конструкции `final class {}` 
или `final function`

PhpMocker может создавать мок-классы не только с помощью наследования, но и с помощью изменения PHP кода, описывающего класс.
Благодаря этому и появляется возможность создать моки.

---

## Пример создания моков для final class

```php
use DraculAid\PhpMocker\MockCreator;

// код с описанием класса "MyFinalClass"
$phpCode = "final class MyFinalClass {
    public function f1(): string
    {
       return 'AAA';
    }
}";

// Создаст мок-класс под именем "MyFinalClass" и вернет менеджер для управления им
$classManager = MockCreator::hardFromPhpCode($phpCode);

// Создание тестового объекта
$testObject = new MyFinalClass();

// Выведет 'AAA' 
echo $testObject->fi1();

// метод f1() класса MyFinalClass должен возвращать 'BBB'
$classManager->getMethodManager('f1')->defaultCase()->setWillReturn('BBB');

// Выведет 'BBB' 
echo $testObject->fi1();
```

## Пример создания мок-классов для final class в PHPUnit

```php
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\MockCreator;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testRun(): void
    {
        $testObject = $this->getTestObject();
        
        self::assertEquals('AAA', $testObject->f1());
    
        // метод f1() класса MyFinalClass должен возвращать 'BBB'
        ClassManager::getManager(MyFinalClass::class)->getMethodManager('f1')->defaultCase()->setWillReturn('BBB');
        
        self::assertEquals('BBB', $testObject->f1());
    }
    
    private function getTestObject(): object
    {
        // код с описанием класса "MyFinalClass"
        $phpCode = "final class MyFinalClass {
            public function f1(): string
            {
               return 'AAA';
            }
        }";
        
        // Создаст мок-класс под именем "MyFinalClass" и вернет менеджер для управления им
        $classManager = MockCreator::hardFromPhpCode($phpCode);
        
        // Создание тестового объекта
        return new MyFinalClass();
    }
}
```
---

**Ключевые слова**
* Mock final class
* Mock PHPUnit
* Mock PHPUnit Final Class
* Мок для финальных классов

---

[<< Оглавление](../README.md) | [Вопросы/Ответы](README.md)
