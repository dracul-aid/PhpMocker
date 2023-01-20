<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\Services\GeneratorPhpCode;

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Tools\ClassTools;

/**
 * Класс-функция для создания PHP кода, отработки вызова мок-метода
 * @see GeneratorCallMockManagerPhpCode::exe()
 */
class GeneratorCallMockManagerPhpCode
{
    /**
     * Схема метода, для которого создается код
     */
    private MethodScheme $methodScheme;

    /**
     * Уникальный идентификатор мок-класса
     * @see ClassManager::$index
     */
    private string $index;

    /**
     * Имя переменной для хранения ответа "менеджера мок объекта", хранит @see \DraculAid\PhpMocker\Managers\Tools\CallResult
     */
    private string $nameMockResult;

    /**
     * Имя переменной для накопления массива ссылок на аргументы функции
     */
    private string $nameFunctionArgumentsVar;

    /**
     * Создаст PHP кода, отработки вызова мок-метода
     *
     * @param   MethodScheme   $methodScheme   Схема метода, для которого создается код
     * @param   string         $index          Уникальный идентификатор мок-класса
     *
     * @return  string
     *
     * @throws \ReflectionException
     *
     * @todo   Необходимо доработать, функции могут "возвращать значения" с помощью ссылочных аргументов
     */
    public static function exe(MethodScheme $methodScheme, string $index): string
    {
        $creator = new self();
        $creator->methodScheme = $methodScheme;
        $creator->index = $index;

        return $creator->run();
    }

    private function __construct() {}

    /**
     * Выполняет генерацию PHP кода, отработки вызова мок-метода
     *
     * @return string
     */
    private function run(): string
    {
        $_return = '';

        $this->nameMockResult = "\$__mocker__{$this->index}__mock_result";
        $this->nameFunctionArgumentsVar = "\$__mocker__{$this->index}__function_arguments";

        // * * *

        $_return .= self::generateFunctionArgumentsToArray();
        $_return .= self::generateSendCallDataToMockManager();
        $_return .= self::generateRewriterArguments();
        $_return .= self::generateMockReturn();

        // * * *

        return $_return;
    }

    /**
     * Генерирует блок с установкой значений аргументам функций
     *
     * @return string
     */
    private function generateRewriterArguments(): string
    {
        /** В foreach перебирается @see CallResult::$rewriteArguments */
        $_return = AbstractMocker::NEW_LINE_FOR_METHOD_CODE . "if ({$this->nameMockResult} !== null) foreach ({$this->nameMockResult}->rewriteArguments as \$name => \$value ) {";
            $_return .= '$$name = $value;';
        $_return .= '}';

        return $_return;
    }

    /**
     * Генерирует блок, с проверкой ответа обработчика вызова мок-метода, если надо вернет результат работы функции
     *
     * @return string
     */
    private function generateMockReturn(): string
    {
        return AbstractMocker::NEW_LINE_FOR_METHOD_CODE
            /** @see \DraculAid\PhpMocker\Managers\Tools\CallResult::$isCanReturn */
            . "if ( {$this->nameMockResult} !== null && {$this->nameMockResult}->isCanReturn ) return"
            /** @see \DraculAid\PhpMocker\Managers\Tools\CallResult::$canReturnData */
            . ($this->methodScheme->canReturnValues() ? " {$this->nameMockResult}->canReturnData;" : ";");
    }

    /**
     * Сгенерирует PHP код для записи аргументов функции в массив, для передачи в "объект описания вызова" @see HasCalled
     *
     * @return string
     */
    private function generateFunctionArgumentsToArray(): string
    {
        // объявление переменной для хранения массива аргументов
        $_return = AbstractMocker::NEW_LINE_FOR_METHOD_CODE . "{$this->nameFunctionArgumentsVar} = [];";

        // * * *

        /**
         * Получение имен аргументов функции, и наполнения массива ссылок на аргументы функции
         * @see ClassTools::getMethodArgumentNames()
         */
        $_return .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE
            . 'foreach ('
                . '\\' . ClassTools::class . '::getMethodArgumentNames(static::class, __FUNCTION__) as $argumentName'
            . ') {'
                . $this->nameFunctionArgumentsVar
                . '[$argumentName] = &$$argumentName;'
            . '}';

        // * * *

        return $_return;
    }

    /**
     * Сгенерирует PHP код для c отправкой данных о вызове мок-метода в обработчик вызовов моков @see HasCalled::exeForMethod()
     *
     * @return string
     */
    private function generateSendCallDataToMockManager(): string
    {
        /** @see HasCalled::$ownerClass */
        $argumentsForSend = "'" . $this->methodScheme->getClassScheme()->getFullName() . "', ";

        /** @see  HasCalled::$callClass */
        $argumentsForSend .= "static::class, ";

        /** @see  HasCalled::$callObject */
        $argumentsForSend .= ($this->methodScheme->isStatic ? 'null' : '$this');

        // имя вызванной функции
        $argumentsForSend .= ', __FUNCTION__';

        // массив ссылок на аргументы функции
        $argumentsForSend .= ", {$this->nameFunctionArgumentsVar}";

        // * * *

        /** Вызов менеджера метода, @see HasCalled::exeForMethod() */
        return AbstractMocker::NEW_LINE_FOR_METHOD_CODE . "{$this->nameMockResult} = \\" . HasCalled::class . "::exeForMethod({$argumentsForSend});";
    }
}
