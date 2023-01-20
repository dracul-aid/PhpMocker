<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Tools;

/**
 * Статический класс с набором функций для облегчения тестирования
 *
 * Оглавление:
 * @see TestTools::waitThrow() - Вызывает переданную функцию и проверяет, ее выполнение приведет к ожидаемой исключению или нет
 */
class TestTools
{
    /**
     * Вызывает переданную функцию и проверяет, ее выполнение приведет к ожидаемой ошибке (исключению) или нет
     *
     * @param   callable      $function         Функция для проверки
     * @param   array         $arguments        Аргументы вызова функции
     * @param   string        $throwableName    Имя ожидаемого класса исключения
     * @param   null|string   $message          Если передана, проверит, что исключение имеет именно такое описание
     * @param   null|int      $code             Если передано, проверит, что исключение вернет именно такой код
     *
     * @return  bool   Вернет TRUE если во время выполнения функции было выброшено нужное исключение или FALSE - если функция выполнилась успешно
     *
     * @throws  \Throwable Все исключения, кроме указанного в $throwableName будут проброшены далее
     */
    public static function waitThrow(callable $function, array $arguments, string $throwableName, null|string $message = null, null|int $code = null): bool
    {
        try {
            $function(...$arguments);
            return false;
        }
        catch (\Throwable $error) {
            if (is_a($error, $throwableName))
            {
                if ($code !== null && $error->getCode() !== $code) return false;
                if ($message !== null && $error->getMessage() !== $message) return false;

                return true;
            }
            else
            {
                throw $error;
            }
        }
    }
}
