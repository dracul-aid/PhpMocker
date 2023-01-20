<?php

/**
 * Примеры работы с Мок-Классами, созданными с помощью изменения кода, в момент загрузки классов
 *
 * Посмотреть примеры работы с Мок-Классами, созданными с помощью изменения кода, без автозагрузки, можно тут: hard-mock.php
 *
 * @run php hard-mock-with-autoload.php
 *
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit
 */

/**
 * === Активация автозагрузчика классов ===
 * Подробнее, можно посмотреть тут: autoload.php
 */

// Получаем настройщик автозагрузки
$autoloaderInit = require(dirname(__DIR__) . '/src/autoloader.php');
// Активируем автозагрузчик классов
// С этого момента все загружаемые классы, кроме классов в пространстве имен DraculAid\PhpMocker\***
// будут загружены как Мок-Классы
$autoloaderObject = $autoloaderInit->create();

// * * *

// Запрашиваем значение константы, что класс загружен
echo 'PUBLIC_CONST => ' . \DraculAidPhpMockerExamples\BasicClass::PUBLIC_CONST . "\n";

// проверяем, был ли загружен класс, как Мок-Класс
echo 'Class '
    . \DraculAidPhpMockerExamples\BasicClass::class
    . (\DraculAid\PhpMocker\Managers\ClassManager::getManager(\DraculAidPhpMockerExamples\BasicClass::class) ? ' is Mock Class' : ' is NOT Mock Class')
    . "\n\n";

// * * *

// Попытка загрузить класс из пространства имен \DraculAid\PhpMocker\*** просто загрузит класс
// (т.е. класс не "превратится" в мок-класс)
echo 'PHP Mocker version => ' . \DraculAid\PhpMocker\MockCreator::VERSION . "\n";
echo 'Class '
    . \DraculAid\PhpMocker\MockCreator::class
    . (\DraculAid\PhpMocker\Managers\ClassManager::getManager(\DraculAid\PhpMocker\MockCreator::class) ? ' is Mock Class' : ' is NOT Mock Class')
    . "\n\n";

// * * *

// Также можно принудительно отключить, загрузку классов с превращением их в мок-классы
$autoloaderObject->autoMockerEnabled = false;

// обращение к классу (он автозагрузится)
echo 'ABSTRACT_CLASS_CONST => ' . \DraculAidPhpMockerExamples\AbstractCLass::ABSTRACT_CLASS_CONST . "\n";

// проверка класса, он не станет Мок-Классом
echo 'Class '
    . \DraculAidPhpMockerExamples\AbstractCLass::class
    . (\DraculAid\PhpMocker\Managers\ClassManager::getManager(\DraculAidPhpMockerExamples\AbstractCLass::class) ? ' is Mock Class' : ' is NOT Mock Class')
    . "\n\n";

// Снова включаем превращение классов при загрузке в мок-классы
$autoloaderObject->autoMockerEnabled = true;

// * * *

// Интерфейсы всегда загружаются без превращения в мок-классы, так как в интерфейсах не может быть код
// обращение к классу (он автозагрузится)
echo 'INTERFACE_CONST => ' . \DraculAidPhpMockerExamples\ClassInterface::INTERFACE_CONST . "\n";

// проверка класса, он не станет Мок-Классом
echo 'Class '
    . \DraculAidPhpMockerExamples\ClassInterface::class
    . (\DraculAid\PhpMocker\Managers\ClassManager::getManager(\DraculAidPhpMockerExamples\ClassInterface::class) ? ' is Mock Class' : ' is NOT Mock Class')
    . "\n";

/**
 * Для взаимодействия с классом:
 *     Создание объектов из мок-классов: new-object.php
 *     Чтение не публичных свойств и констант, вызов не публичных методов: not-public.php
 *     Работа с мок-методами: methods.php
 */