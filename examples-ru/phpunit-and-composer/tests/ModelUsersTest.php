<?php

namespace DraculAid\PhpMockerExamples\Tests;

use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Tools\CallableObject;
use DraculAid\PhpMockerExamples\Classes\BdUser;
use DraculAid\PhpMockerExamples\Classes\ModelUsers;
use DraculAid\PhpMocker\MockManager;
use PHPUnit\Framework\TestCase;

class ModelUsersTest extends TestCase
{
    public function testGetList(): void
    {
        /**
         * Вызывает автозагрузку класса @see BdUser (если класс ранее еще не был загружен, то произойдет его автозагрузка)
         * И возвращает "менеджер мок-класса"
         */
        $mockBdUserManager = MockCreator::hardLoadClass(BdUser::class);

        // * * *

        /**
         * Метод @see BdUser::getList Должен всегда возвращать пустой массив
         */
        $mockBdUserManager->getMethodManager('getList')->defaultCase()->setWillReturn([]);

        // проводим проверку
        self::assertEquals([], ModelUsers::getList(1, 10));
        // проверяем, был ли вызов мок-функции
        self::assertEquals(1, $mockBdUserManager->getMethodManager('getList')->defaultCase()->countCall);

        // * * *

        /**
         * Метод @see BdUser::getList Должен всегда возвращать 2-ух пользователей
         * При установке "результатов работы функции", сбрасываем счетчик вызова функций
         */
        $mockBdUserManager->getMethodManager('getList')->defaultCase()->setWillReturn([
            'ABC0001' => ['id' => 'ABC0001', 'name' => 'Mark', 'gender' => 'M'],
            'ABC0002' => ['id' => 'ABC0002', 'name' => 'Mary', 'gender' => 'W'],
        ], true);

        // проводим проверку
        self::assertEquals([
            'ABC0001' => new ModelUsers('ABC0001', 'Mark', 'M'),
            'ABC0002' => new ModelUsers('ABC0002', 'Mary', 'W'),
        ], ModelUsers::getList(1, 10));
        // проверяем, был ли вызов мок-функции
        self::assertEquals(1, $mockBdUserManager->getMethodManager('getList')->defaultCase()->countCall);

        // * * *

        // возвращение метода в "нормальность"
        $mockBdUserManager->getMethodManager('getList')->clearCases();
    }

    public function testGetFromId(): void
    {
        /**
         * Вызываем автозагрузку класса @see BdUser (если класс ранее еще не был загружен, то произойдет его автозагрузка)
         */
        class_exists(BdUser::class);

        /**
         * Получаем менеджер, для управления мок-классом @see BdUser
         */
        $mockBdUserManager = MockManager::getForClass(BdUser::class);

        // * * *

        /**
         * Метод @see BdUser::getFromId() Всегда вернет пользвателя
         */
        $mockBdUserManager->getMethodManager('getFromId')->defaultCase()->setWillReturn(['id' => 'ABC0001', 'name' => 'Mark', 'gender' => 'M']);

        // проводим проверку
        self::assertEquals(new ModelUsers('ABC0001', 'Mark', 'M'), ModelUsers::getFromId('ABC0001'));
        // проверяем, был ли вызов мок-функции
        self::assertEquals(1, $mockBdUserManager->getMethodManager('getFromId')->defaultCase()->countCall);

        // * * *

        /**
         * Вызов метода @see BdUser::getFromId() Должен привести к выбрасыванию исключения
         */
        $mockBdUserManager->getMethodManager('getFromId')->userFunction = new CallableObject(static function () {
            throw new \RuntimeException('');
        });

        // указываем, что ожидаем исключение
        $this->expectException(\RuntimeException::class);

        // вызываем получение информации о пользователе (будет выброшено исключение)
        ModelUsers::getFromId('ABC0001');

        // * * *

        // возвращение метода в "нормальность"
        $mockBdUserManager->getMethodManager('getList')->clearCases();
    }
}
