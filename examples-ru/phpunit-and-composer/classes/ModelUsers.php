<?php

namespace DraculAid\PhpMockerExamples\Classes;

/**
 * Класс тестового проекта, имитирующий "модель данных 'Пользователь'"
 */
class ModelUsers
{
    public function __construct(
        readonly public string $id,
        public string $name,
        public string $gender,
    ) {}

    /**
     * Вернет список пользователей
     *
     * @param   int   $start   Начало выборки (от 0-ля)
     * @param   int   $end     Конец выборки
     *
     * @return  ModelUsers[]
     */
    public static function getList(int $start, int $end): array
    {
        $bdResult = BdUser::getList($start, $end);

        if (count($bdResult) === 0) return [];

        // * * *

        $userObjects = [];

        foreach ($bdResult as $userData) {
            $userObjects[$userData['id']] = new self($userData['id'], $userData['name'], $userData['gender']);
        }

        return $userObjects;
    }

    /**
     * Вернет пользователя, по его ID
     *
     * @param   string   $id   ID пользователя
     *
     * @return  ModelUsers
     *
     * @throws  \RuntimeException  Если не был найден пользователь
     */
    public static function getFromId(string $id): self
    {
        $userData = BdUser::getFromId($id);

        return new self($userData['id'], $userData['name'], $userData['gender']);
    }
}
