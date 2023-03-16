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
 * Абстрактный класс, для создания классов, максимально имитирующих перечисления в PHP8
 *
 * @property int|string $value
 */
class AbstractEnums
{
    /**
     * Значение варианта перечисления
     * @var mixed $variantValue
     */
    protected $variantValue;

    /**
     * Массив созданных вариантов
     *
     * @var AbstractEnums[] $variants Представляет собой массив, в котором:
     *    * Ключ: строка "класс-вариант" (например "ClassSchemeType-class")
     *    * Значение: объект-вариант перечисления
     */
    private static array $variants = [];

    /**
     * @param   string   $variant   Значение описывающее вариант перечисления
     */
    private function __construct(string $variant)
    {
        $this->variantValue = $variant;
    }

    public function __get(string $name)
    {
        if ($name === 'value') return $this->variantValue;

        throw new \LogicException("Call undefined property: \${$name}");
    }

    /**
     * Вернет объект "вариант перечисления" для значения перечисления
     *
     * @param   string   $variant   Значение описывающее вариант перечисления
     *
     * @return  AbstractEnums
     */
    protected static function createStringVariant(string $variant): AbstractEnums
    {
        $index = static::class . "-{$variant}";

        if (empty(self::$variants[$index])) self::$variants[$index] = new static($variant);

        return self::$variants[$index];
    }
}
