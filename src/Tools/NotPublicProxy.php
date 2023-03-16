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

use DraculAid\PhpMocker\NotPublic;

/**
 * Создает объект, проксирующий вызов "другого объекта" для получения доступа к непубличным свойствам и методам
 *
 * В отличие от @see NotPublic::instance() не дает доступа к статическим элементам и константам, но позволяет
 * типизировать переменные (в докблоках) тем же типом, что и оригинальный объект, тем самым облегчая разработку
 */
class NotPublicProxy
{
    private NotPublic $___not_public_object___;

    public function __construct(object $toObject)
    {
        $this->___not_public_object___ = NotPublic::instance($toObject);
    }

    public function __invoke(): NotPublic
    {
        return $this->___not_public_object___;
    }

    public function __call(string $name, array $arguments)
    {
        return $this()->call($name, $arguments);
    }

    public function __get(string $name)
    {
        return $this()->get($name);
    }

    public function __set(string $name, $data): void
    {
        $this()->set($name, $data);
    }
}
