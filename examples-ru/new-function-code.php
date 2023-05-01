<?php

/**
 * Пример изменения работы метода
 *
 * @run php new-function-code.php
 */

// Подключим автозагрузчик композера
(require(dirname(__DIR__) . '/src/autoloader.php'))->setDriverAutoloaderUnregister(false)->create(false);

// * * *

// класс, работу которого будем менять
$classCode = <<<CLASSCODE
    class NewFunctionCodeTestClass {
        public static string \$f1_var = 'now_write';
        public static function f1(): void
        {
            self::\$f1_var = 100;
        }
        public static string \$f2_var = 'now_write';
        public static function f2(): string
        {
            self::\$f2_var = 200;
            return 'base string';
        }   
    }
CLASSCODE;

// Создаем менеджер мок-класса (и мок-класс, с помощью изменения PHP кода)
$mockClassManager = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($classCode);

// * * *

// Проверяем, что функции выполняют свою работу как надо

echo "========== CASE 1 ==========\n\n";

NewFunctionCodeTestClass::f1();
echo 'NewFunctionCodeTestClass::f2() = ' .NewFunctionCodeTestClass::f2() . "\n";

echo 'NewFunctionCodeTestClass::$f1_var = ' . NewFunctionCodeTestClass::$f1_var . "\n";
echo 'NewFunctionCodeTestClass::$f2_var = ' . NewFunctionCodeTestClass::$f2_var . "\n";

// * * *

// Этот пример подходит для метода, который не возвращал значение

// Устанавливаем пользовательскую функцию, которая срабатывает при вызове метода NewFunctionCodeTestClass::f1()
$mockClassManager->getMethodManager('f1')->setUserFunction(
    static function () {
        // функция выполняет теперь какую-то другую работу
        NewFunctionCodeTestClass::$f1_var = '222';
        // функция выводит
        echo "--- it generated in " . __LINE__ . " line\n";

        // после выполнения функции, изначальный код метода не должен выполняться, сам метод не возвращает ничего
        return \DraculAid\PhpMocker\Managers\Tools\CallResult::createForStopMethod();
    }
);

echo "\n========== CASE 2 ==========\n\n";

// вызываем функцию, что бы проверить, что она изменила свое поведение
NewFunctionCodeTestClass::f1();

echo 'NewFunctionCodeTestClass::$f1_var = ' . NewFunctionCodeTestClass::$f1_var . "\n";

// * * *

// Этот пример для функции, которая возвращает значение

// Устанавливаем пользовательскую функцию, которая срабатывает при вызове метода NewFunctionCodeTestClass::f1()
$mockClassManager->getMethodManager('f2')->setUserFunction(
    static function () {
        // функция выполняет теперь какую-то другую работу
        NewFunctionCodeTestClass::$f1_var = '333';
        // функция выводит
        echo "--- it generated in " . __LINE__ . " line\n";

        // после выполнения функции, изначальный код метода не должен выполняться, сам метод не возвращает ничего
        return \DraculAid\PhpMocker\Managers\Tools\CallResult::createForStopMethod('test');
    }
);

echo "\n========== CASE 3 ==========\n\n";

// вызываем функцию, что бы проверить, что она изменила свое поведение
echo 'NewFunctionCodeTestClass::f2() = ' .NewFunctionCodeTestClass::f2() . "\n";

echo 'NewFunctionCodeTestClass::$f2_var = ' . NewFunctionCodeTestClass::$f2_var . "\n";
