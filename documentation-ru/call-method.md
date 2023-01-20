# PhpMocker - Обработка вызова мок-метода
[<< Оглавление](README.md)

Ресурсы
* [Менеджер метода](manager-method/README.md)
* [Кейс вызова метода](mock-cases/README.md)
* `DraculAid\PhpMocker\Managers\Tools\CallResult`: Ответ выполнения кейса-вызова

**При вызове мок-метода срабатывает следующая логика**
1) Если для объекта метода есть "менеджер мок-объекта" - обрабатывается вызов для него
2) Если для объекта метода есть "менеджер мок-класса" - обрабатывается вызов для него
3) Отрабатывает код самого метода

**Под отрабатыванием вызова подразумевается**
1) Если "менеджер мок-метода" содержит `MethodManager::$userFunction` - она будет выполнена, если вернула `CallResult` - это **КОНЕЦ ВЫПОЛНЕНИЯ**
2) Если в "менеджер мок-метода" есть "кейс вызова" с нужными аргументами - это **КОНЕЦ ВЫПОЛНЕНИЯ**
3) Если в "менеджер мок-метода" есть "кейс вызова по умолчанию" - это **КОНЕЦ ВЫПОЛНЕНИЯ**

**Внутри кейса вызова выполнение происходит в следующем порядке**
1) Выполняется `MethodCase::$userFunction` (если есть), если вернула `CallResult` - это **КОНЕЦ ВЫПОЛНЕНИЯ**
2) Если было установлено исключение, оно будет выброшено (это **КОНЕЦ ВЫПОЛНЕНИЯ**)
3) Происходит замена аргументов (если замена была установлена)
4) Если кейс вызова подразумевает остановку работы метода и возвращение им значения - это **КОНЕЦ ВЫПОЛНЕНИЯ**
5) Выполняется изначальный код метода

При обращении к "менеджеру мок-метода" через "менеджер мок-класса" кейс вызова назначается вне зависимости от того, в каком
объекте произойдет вызов. При обращении к "менеджеру мок-метода" через "менеджер мок-объекта" обрабатываться будет только
вызов метода именно этого объекта (см пример ниже)

## Пример с последовательностью выполнения кейсов вызова

```php
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Tools\CallableObject;
use DraculAid\PhpMocker\Managers\Tools\CallResult;

// Создаем мок-класс из PHP кода и получаем менеджер для него
$classManager = MockCreator::hardFromPhpCode('class MyTestClass {
    public static function f1($arg1, $arg2): string {return "F1:" $arg1 . "&" . $arg2;}
}');

// * * *

// Выведет F1:11&22
echo MyTestClass::f1('11', '22') . "\n";

// Установим, в начале выполнения функции MyTestClass::f1() должна выполниться "пользовательская функция",
// но она не приостановит выполнение кода мок-метода. В данном случае ответ "пользовательской функции" будет проигнорирован
$classManager->getMethodManager('f1')->userFunction = new CallableObject(static function() {echo 'ZZZ'; return 'ABC'});
// Выведет ZZZF1:11&22
// ZZZ - берется, так как в "пользовательской функции" есть echo 'ZZZ';
echo MyTestClass::f1('11', '22') . "\n";

// * * *

// установим, что для определенных аргументов, метод должен вернуть
$classManager->getMethodManager('f1')->case('11', '22')->setWillReturn('Function-1-mock-result-11-22');
// Выведет ZZZFunction-1-mock-result-11-22
// ZZZ - берется, так как в "пользовательской функции" есть echo 'ZZZ';
echo MyTestClass::f1('11', '22') . "\n";
// Выведет ZZZF1:33&44
// ZZZ - берется, так как в "пользовательской функции" есть echo 'ZZZ';
echo MyTestClass::f1('33', '44') . "\n";

// * * *

// убираем пользовательскую функцию
$classManager->getMethodManager('f1')->userFunction = null;
// Выведет Function-1-mock-result-11-22
echo MyTestClass::f1('11', '22') . "\n";
// Выведет F1:33&44
echo MyTestClass::f1('33', '44') . "\n";

// * * *

// что метод должен возвращать, если не отработал никакой "кейс вызова"
$classManager->getMethodManager('f1')->defaultCase()->setWillReturn('DEFAULT-RESULT');
// Выведет Function-1-mock-result-11-22
echo MyTestClass::f1('11', '22') . "\n";
// Выведет DEFAULT-RESULT
echo MyTestClass::f1('33', '44') . "\n";

// * * *

// Пользовательская функция вернет результат работы метода
$classManager->getMethodManager('f1')->userFunction = new CallableObject(static function() {return new CallResult(true, 'USER-F-RESULT')});
// Выведет USER-F-RESULT
echo MyTestClass::f1('11', '22') . "\n";
// Выведет USER-F-RESULT
echo MyTestClass::f1('33', '44') . "\n";
```

## Наглядный пример менеджера-метода от Мок-Класса и Мок-объекта

"Менеджер мок-метода" полученный от мок-объекта, распространяется только на вызовы в объекте. "Менеджер мок-метода"
полученный от мок-класса, на все объекты и дочерние классы.

```php
use DraculAid\PhpMocker\MockCreator;

// создание мок-класса из PHP кода и получение "менеджера мок-класса"
$classManager = MockCreator::hardFromPhpCode('class TestClassName {
    public function f1() {return "111";}
    public function f2() {return "222";}
}');

// создание мок-объектов и получение "менеджеров мок-объектов" для них
$objectManager1 = $classManager->createObjectAndManager([], [], $object1);
$objectManager2 = $classManager->createObjectAndManager([], [], $object2);

// * * *

// Выведет '111'
echo $object1->f1() . "\n";
// Выведет '111'
echo $object2->f1() . "\n";

// Выведет '222'
echo $object1->f2() . "\n";
// Выведет '222'
echo $object2->f2() . "\n";

// * * *

// все вызовы метода TestClassName::f1() должны будут возвращать 'AAA'
$classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');

// Выведет 'AAA'
echo $object1->f1() . "\n";
// Выведет 'AAA'
echo $object2->f1() . "\n";

// Выведет '222'
echo $object1->f2() . "\n";
// Выведет '222'
echo $object2->f2() . "\n";

// * * *

// вызов TestClassName::f2() для объекта $object1 должны будут возвращать 'BBB'
$objectManager1->getMethodManager('f2')->defaultCase()->setWillReturn('BBB');

// Выведет 'AAA'
echo $object1->f1() . "\n";
// Выведет 'AAA'
echo $object2->f1() . "\n";

// Выведет 'BBB'
echo $object1->f2() . "\n";
// Выведет '222'
echo $object2->f2() . "\n";

// * * *

// вызов TestClassName::f1() для объекта $object1 должны будут возвращать 'A1A1A1'
$objectManager1->getMethodManager('f1')->defaultCase()->setWillReturn('A1A1A1');

// Выведет 'A1A1A1'.
// Обработка кейса в первую очередь происходит для мок-объекта, и только далее, для мок-класса
echo $object1->f1() . "\n";
// Выведет 'AAA'
echo $object2->f1() . "\n";

// Выведет 'BBB'
echo $object1->f2() . "\n";
// Выведет '222'
echo $object2->f2() . "\n";
```

---

[<< Оглавление](README.md)
