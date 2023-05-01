<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\Tools;

/**
 * Ответ вызова мок-метода (Результат работы мок-метода)
 *
 * Оглавление:
 * @see self::$isCanReturn - Указание, что функция не должна выполняться, а должна вернуть заготовленный ответ
 * @see self::$canReturnData - Какие данные должен вернуть функция
 * @see self::$rewriteArguments - Массив, с значениями для перезаписи аргументов метода
 */
class CallResult
{
    /**
     * Указание, что функция не должна выполняться, а должна вернуть заготовленный ответ
     *
     * Возвращаемый результат @see MethodCase::$canReturnData
     */
    public bool $isCanReturn = false;

    /**
     * Какие данные должен вернуть функция
     *
     * Данные будут возвращены, если только @see MethodCase::$isCanReturn === true
     */
    public mixed $canReturnData = null;

    /**
     * Массив, с значениями для перезаписи аргументов метода
     * * Ключ: имя аргумента
     * * Значение: значение, для записи в аргумент
     *
     * @var array<string, mixed> $rewriteArguments
     */
    public array $rewriteArguments = [];

    /**
     * @param   bool    $isCanReturn        TRUE - если нужно вернуть $canReturnData или FALSE - если нужно выполнить код метода
     * @param   mixed   $canReturnData      Данные для возврата (значение для return)
     * @param   array   $rewriteArguments   Массив, с значениями для перезаписи аргументов метода
     */
    public function __construct(bool $isCanReturn, mixed $canReturnData = null, array $rewriteArguments = [])
    {
        $this->isCanReturn = $isCanReturn;
        $this->canReturnData = $canReturnData;
        $this->rewriteArguments = $rewriteArguments;
    }

    /**
     * Упрощенное создание "ответа вызова мок-метода", для случаев, когда мок-метод не должен выполнять своий изначальный код
     *
     * @param   null|mixed   $canReturnData    Возвращаемое функцией значение
     *
     * @return  static
     */
    public static function createForStopMethod(mixed $canReturnData = null): self
    {
        return new self(true, $canReturnData);
    }

    /**
     * Создаст
     *
     * @return static
     */
    public static function none(): self
    {
        return new self(false, null);
    }
}
