<?php

/**
 * Вызов этого файла вернет "настройщик автозагрузчика класса" @see \DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit
 *
 * Пример вызова:
 * $autoloaderInitObject = require_once('vendor/DraculAid/PhpMocker/src/autoloader.php');
 *
 * Более детальный пример в файле vendor/DraculAid/PhpMocker/examples-ru/autoload.php
 */

declare(strict_types=1);

// Загрузка необходимых для работы "автозагрузчика классов" классов
// Загружаются напрямую, что бы избежать вызова каких либо автозагрузчиков в проекте
if (!class_exists(\DraculAid\PhpMocker\ClassAutoloader\Autoloader::class, false)) require_once(__DIR__ . '/ClassAutoloader/Autoloader.php');
if (!class_exists(\DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit::class, false)) require_once(__DIR__ . '/ClassAutoloader/AutoloaderInit.php');
if (!interface_exists(\DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface::class, false)) require_once(__DIR__ . '/ClassAutoloader/Drivers/AutoloaderDriverInterface.php');
if (!class_exists(\DraculAid\PhpMocker\ClassAutoloader\Drivers\ComposerAutoloaderDriver::class, false)) require_once(__DIR__ . '/ClassAutoloader/Drivers/ComposerAutoloaderDriver.php');

return new \DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit();
