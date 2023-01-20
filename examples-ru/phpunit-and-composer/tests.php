<?php declare(strict_types=1);

/**
 * Файл для запуска юнит-тестов
 *
 * @run php ./tests.php
 *
 * @var \DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit
 */

// Путь к директории Vendor
$vendorPath = __DIR__ . '/vendor';

// Подключение автозагрузчика композера
require("{$vendorPath}/autoload.php");
// Получение настройщика автозагрузчика PhpMocker
$autoloaderInit = require(dirname(__DIR__, 2) . '/src/autoloader.php');

// Указываем автозагрузчику PhpMocker-а расположение папки Vendor
$autoloaderInit->setComposerVendorPath($vendorPath);

// Если нужно включить кеш для сохранения созданных автозагрузчиком мок-классов
// то с помощью этого метода можно указать каталог для хранения кеша
// $autoloaderInit->setMockClassCachePath(__DIR__ . '/11111/2222/333');

// Активируем PhpMocker автозагрузчик и отключаем автозагрузчик композера
$autoloaderInit->create();

// * * *

// запуск отработки юнит-тестов
require("{$vendorPath}/phpunit/phpunit/phpunit");
