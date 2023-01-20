<?php

/**
 * Пример использования мок-классов созданных с помощью изменения PHP кода
 *
 * Посмотреть примеры мок-классов, созданных при загрузке класса, можно тут: hard-mock-with-autoload.php
 *
 * @run php hard-mock.php
 */

// Подключим автозагрузчик композера
(require(dirname(__DIR__) . '/src/autoloader.php'))->setDriverAutoloaderUnregister(false)->create(false);

// * * *

// Имя класса
$className = "DraculAidPhpMockerTestClassName" . uniqid();

// PHP код с описанием класса
$phpCode = "class {$className} {}";

// Получение менеджера мок-класса
// Выполнение этого кода закончится ошибкой, если класс уже был ранее объявлен
$mockClassManagerForPhpCode = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCode);

// * * *

// Получение мок-класса из файла
// Выполнение этого кода закончится ошибкой, если класс уже был ранее объявлен
$mockClassManagerForScript = \DraculAid\PhpMocker\MockCreator::hardFromScript(__DIR__ . '/classes/BasicClass.php');

// * * *

// Оба способа позволяют получить Мок-Класс под специально указанным именем
// Создать класс с полным именем "new_name"
$mockClassManagerWithNewName1 = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCode, new \DraculAid\PhpMocker\CreateOptions\ClassName('new_name'));
// Можно создать мок-класс в нужном пространстве имен
$mockClassManagerWithNewName2 = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCode, new \DraculAid\PhpMocker\CreateOptions\ClassName('my_namespace\\new_name'));

// * * *

// Если в PHP коде (в том числе и в файле-скрипте) будет несколько классов, то функция вернет массив "менеджеров классов"
$className1 = "DraculAidPhpMockerTestClassName1" . uniqid();
$className2 = "DraculAidPhpMockerTestClassName2" . uniqid();
$phpCodeTwoClass = "abstract class {$className1} {} class {$className2} extends {$className1} {}";
$arrayWithMockClassManagersForPhpCode = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCodeTwoClass);

// доступ к менеджеру осуществляется по имени класса
// первый менеджер
$arrayWithMockClassManagersForPhpCode[$className1];
// второй менеджер
$arrayWithMockClassManagersForPhpCode[$className2];

// * * *

// также можно установить новые имена и нескольким классам
$arrayWithMockClassManagersForPhpCode = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode(
    $phpCodeTwoClass,
    new \DraculAid\PhpMocker\CreateOptions\ClassNameList([
        $className1 => 'new_namespace\\new_class_name',
        $className2 => 'new_name_without_namespace',
    ])
);

// доступ к менеджеру осуществляется по новым именам класса
// первый менеджер
$arrayWithMockClassManagersForPhpCode['new_namespace\\new_class_name'];
// второй менеджер
$arrayWithMockClassManagersForPhpCode['new_name_without_namespace'];


/**
 * Для взаимодействия с классом:
 *     Создание объектов из мок-классов: new-object.php
 *     Чтение не публичных свойств и констант, вызов не публичных методов: not-public.php
 *     Работа с мок-методами: methods.php
 */