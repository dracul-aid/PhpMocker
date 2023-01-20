<?php

/**
 * Пример использования автозагрузчика, с возможностью загружать все загружаемые классы, как мок-классы
 *
 * @run php autoload.php
 *
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit
 */

/**
 * Загрузка основных классов, необходимых для создания автозагрузичка
 * Получение объекта инициализации автозагрузчика
 */

// Этот путь к файлу актуален, при вызове этого примера напрямую
$autoloaderInit = require(dirname(__DIR__) . '/src/autoloader.php');

// В рабочем проекте (использующим композер), путь будет таким: vendor/DraculAid/PhpMocker/src/autoloader.php
// В данном адресе "vendor" - это каталог vendor созданный композером (каталог с используемыми вами пакетами)
// $autoloaderInit = require('vendor/DraculAid/PhpMocker/src/autoloader.php');

/**
 * === Настройка работы автозагрузчика ===
 */

// По умолчанию, при создании автозагрузчика, будет отключен автозагрузчик композера
// но его можно оставить, для этого следует использовать:
// $autoloaderInit->setDriverAutoloaderUnregister(false);

// Настройщик попробует сам найти путь до папки vendor, но если у него это не удаться, сообщить путь до нее можно так:
// $autoloaderInit->setComposerVendorPath('путь до папки vendor');

/**
 * === Если вы не используете автозагрузчик композера ===
 *
 * Если вы используете не автозагрузчик композера, а какое-то другое решение, то необходимо передать функцию поиска пути
 * до загружаемого класса (трейта, интерфейса, перечисления). Это может быть любой callable вариант.
 * Например, с анонимной функцией:
 * $autoloaderInit->setFunctionGetPath(new \DraculAid\PhpMocker\Tools\CallableObject\CallableObject(static function () {return 'path'}));
 * Или указав метод вашего объекта-автозагрузчика
 * $autoloaderInit->setFunctionGetPath(new \DraculAid\PhpMocker\Tools\CallableObject\CallableObject([$yourAutoloaderObject, 'searchClassFunction']));
 *
 * Подробнее @see DraculAid\PhpMocker\ClassAutoloader\Autoloader::$autoloaderDriver
 */

/**
 * === Получение (и инициализация) автозагрузчика ===
 */

// после установки всех значений можно получить автозагрузчик и зарегестрировать его
// После вызова $autoloaderInit->create() загрузка классов будет вестись через него,
// и все загружаемые классы могут быть загружены как мок-классы
// $autoloaderObject - может понадобиться для каких либо "тонких настроек"
// (например, если нужно превращать не все загружаемые классы в мок-классы)
$autoloaderObject = $autoloaderInit->create();

// Можно отключить переработку загружаемых классов в мок-классы
$autoloaderObject->autoMockerEnabled = false;

// Для включения преобразования загрузки классов в мок-классы
$autoloaderObject->autoMockerEnabled = true;
