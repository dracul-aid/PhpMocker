<?php

/**
 * Запуск юнит-тестов
 *
 * @run php run.php tests - Запуск всех тестов (запускает тесты из директории "tests")
 * @run php run.php tests.php - Запуск теста из конкретного файла (например, "tests/CreateOptions/ClassNameTest.php")
 */

require_once('vendor/autoload.php');

$phpUnitPath = realpath('vendor/phpunit/phpunit/phpunit');
if ($phpUnitPath) require_once($phpUnitPath);
else die('Not found phpUnit library: ' . getcwd() . '\vendor\phpunit\phpunit\phpunit');
