<?php

/**
 * Пример изменения работы конструктора
 *
 * @run php new-constructor.php
 */


use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use \DraculAid\PhpMocker\Managers\Tools\CallResult;

// Подключим автозагрузчик композера
(require(dirname(__DIR__) . '/src/autoloader.php'))->setDriverAutoloaderUnregister(false)->create(false);

// * * *

// класс, работу которого будем менять
$classCode = <<<CLASSCODE
    class NewFunctionCodeTestClass {
        public string \$constructString = 'not_value';
        public function __construct(string \$arg = '')
        {
            \$this->constructString = \$this->constructString . ' | Basic Construct';
        }  
    }
CLASSCODE;

// Создаем менеджер мок-класса (и мок-класс, с помощью изменения PHP кода)
$mockClassManager = \DraculAid\PhpMocker\MockCreator::hardFromPhpCode($classCode);
$constructorMockManager = $mockClassManager->getMethodManager('__construct');

// * * *

// проверяем базовое поведение
echo "======== CASE 1 ========\n";
echo 'constructString === ' . (new NewFunctionCodeTestClass)->constructString . "\n\n";

// * * *

// используем функционал, для отмены выполнения конструктора и перезаписи свойств созданного объекта
$constructorMockManager->defaultCase()->setClearConstructor(['constructString' => 'testCase2']);
echo "======== CASE 2 ========\n";
echo 'constructString === ' . (new NewFunctionCodeTestClass)->constructString . "\n\n";

// Возвращаем стандартное поведение
$constructorMockManager->defaultCase()->setUserFunction(null);
echo "======== CASE 3 ========\n";
echo 'constructString === ' . (new NewFunctionCodeTestClass)->constructString . "\n\n";


// * * *

// в более сложных кейсах мы можем назначить любую пользовательскую функцию без отключения конструктора
$constructorMockManager->defaultCase()->setUserFunction(static function (HasCalled $hasCalled, MethodManager $methodManager) {
    $hasCalled->callObject->constructString = "Case 4 value (call with \$arg = {$hasCalled->arguments['arg']})";
});

echo "======== CASE 4 ========\n";
echo 'constructString === ' . (new NewFunctionCodeTestClass('123'))->constructString . "\n\n";


// * * *

// в более сложных кейсах мы можем назначить любую пользовательскую функцию с отключением конструктора
$constructorMockManager->defaultCase()->setUserFunction(static function (HasCalled $hasCalled, MethodManager $methodManager) {
    $hasCalled->callObject->constructString = "Case 4 value (call with \$arg = {$hasCalled->arguments['arg']})";

    // В случае надобности - можно отменить дальнейшее выполнение конструктора
    return CallResult::createForStopMethod();
});

echo "======== CASE 5 ========\n";
echo 'constructString === ' . (new NewFunctionCodeTestClass('321'))->constructString . "\n\n";
