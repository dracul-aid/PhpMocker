<?php declare(strict_types=1);

/**
 * Файл для запуска проекта
 *
 * @run php ./index.php
 */

// Подключение автозагрузчика композера
require(__DIR__ . '/vendor/autoload.php');

// Старт работы проекта
\DraculAid\PhpMockerExamples\Classes\App::exe();
