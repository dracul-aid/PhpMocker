<?php

namespace DraculAid\PhpMockerExamples\Classes;

echo "\n!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n";

/**
 * Класс тестового проекта, с имитацией базы данных пользователей
 */
class BdUser
{
    /**
     * Список пользователей, как бы хранимый в базе данных
     */
    private static array $list = [];

    /**
     * Используется, для имитации получения пользователя по ID
     *
     * @param   string   $id
     *
     * @return  array   Вернет массив с описанием пользователя
     *
     * @throws  \RuntimeException  Если не был найден пользователь
     */
    public static function getFromId(string $id): array
    {
        self::init();

        if (empty(self::$list[$id])) throw new \RuntimeException("User ID {$id} Not Found");

        return self::$list[$id];
    }

    /**
     * Используется, для имитации получения списка пользователей
     *
     * @param   int   $start   Начало выборки (от 0-ля)
     * @param   int   $end     Конец выборки
     *
     * @return  array
     */
    public static function getList(int $start, int $end): array
    {
        self::init();

        return array_slice(self::$list, $start, $end);
    }

    /**
     * Создание наполнения для имитатора базы данных пользователей
     *
     * @return void
     */
    private static function init(): void
    {
        if (!empty(self::$list)) return;

        // * * *

        $tmp = [];
        $tmp[] = ['id' => 'ABC0001', 'name' => 'Mark', 'gender' => 'M'];
        $tmp[] = ['id' => 'ABC0002', 'name' => 'Anna', 'gender' => 'W'];
        $tmp[] = ['id' => 'ABC0003', 'name' => 'Mary', 'gender' => 'W'];
        $tmp[] = ['id' => 'ABC0004', 'name' => 'Albert', 'gender' => 'M'];
        $tmp[] = ['id' => 'ABC0005', 'name' => 'Karl', 'gender' => 'M'];
        $tmp[] = ['id' => 'ABC0006', 'name' => 'Henri', 'gender' => 'M'];
        $tmp[] = ['id' => 'ABC0007', 'name' => 'Alice', 'gender' => 'W'];
        $tmp[] = ['id' => 'ABC0008', 'name' => 'George', 'gender' => 'M'];
        $tmp[] = ['id' => 'ABC0009', 'name' => 'Sacha', 'gender' => 'W'];

        // * * *

        foreach ($tmp as $item)
        {
            self::$list[$item['id']] = $item;
        }
    }
}
