<?php declare(strict_types=1);

/**
 * Запуск юнит-тестов
 *
 * @run php run.php tests - Запуск всех тестов (запускает тесты из директории "tests")
 * @run php run.php tests.php - Запуск теста из конкретного файла (например, "tests/CreateOptions/ClassNameTest.php")
 */

require_once(dirname(__DIR__) . '/vendor/autoload.php');


$phpUnitPath = dirname(__DIR__) . '/vendor/phpunit/phpunit/phpunit';
if ($phpUnitPath)
{
    // получаем PHP код "консольного приложения PhpUnit" и выбрасываем из него declare(strict_types=1);
    $phpUnitCodeExecutor = explode("\n", file_get_contents($phpUnitPath));
    unset($phpUnitCodeExecutor[0], $phpUnitCodeExecutor[1]);

    eval(implode($phpUnitCodeExecutor));
}
else
{
    die("Not found phpUnit library: {$phpUnitPath}");
}
