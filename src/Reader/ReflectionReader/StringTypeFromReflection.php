<?php

declare(strict_types=1);

namespace DraculAid\PhpMocker\Reader\ReflectionReader;

use DraculAid\PhpMocker\Exceptions\Reader\ReflectionReaderUndefinedTypeException;

/**
 * Класс-функция, с функционалом для получения строкового представления типов из рефлексии
 * @see StringTypeFromReflection::exe()
 */
class StringTypeFromReflection
{
    /**
     * Вернет строковое представление типов данных из рефлексии
     *
     * @param   \ReflectionType   $reflectionType   Объект рефлексии "типа данных"
     *
     * @return  string
     *
     * @throws  ReflectionReaderUndefinedTypeException  Если типы данных были представлены в виде неизвестного типа
     */
    public static function exe(\ReflectionType $reflectionType): string
    {
        return match (true) {
            is_a($reflectionType, \ReflectionNamedType::class) => self::basic($reflectionType),
            is_a($reflectionType, \ReflectionUnionType::class) => self::union($reflectionType),
            is_a($reflectionType, \ReflectionIntersectionType::class) => self::intersection($reflectionType),
            // все прочие варианты приведут к выброшенному исключению
            default => throw new ReflectionReaderUndefinedTypeException(),
        };
    }

    /**
     * Вернет строковое представление для "пересечения типов" (AND)
     *
     * @param   \ReflectionIntersectionType   $reflectionType   Рефлексия пересечения типов
     *
     * @return  string
     */
    private static function intersection(\ReflectionIntersectionType $reflectionType): string
    {
        $_return = [];

        foreach ($reflectionType->getTypes() as $type)
        {
            $_return[] = self::getName($type);
        }

        return implode('&', $_return);
    }

    /**
     * Вернет строковое представление для "перечисления типов" (OR)
     *
     * @param   \ReflectionUnionType   $reflectionType   Рефлексия перечисления типов
     *
     * @return  string
     */
    private static function union(\ReflectionUnionType $reflectionType): string
    {
        $_return = [];

        foreach ($reflectionType->getTypes() as $type)
        {
            // если присутствует mixed - то только одного его и нужно вернуть
            if ($type->getName() === 'mixed')
            {
                return 'mixed';
            }

            $_return[] = self::getName($type);
        }

        return implode('|', $_return);
    }

    /**
     * Вернет строковое представление для простого типа
     *
     * @param   \ReflectionUnionType   $reflectionType   Рефлексия перечисления типов
     *
     * @return  string
     */
    private static function basic(\ReflectionNamedType $reflectionType): string
    {
        $_return = self::getName($reflectionType);

        if ($_return === 'mixed') return 'mixed';
        elseif ($reflectionType->allowsNull()) $_return .= "|null";

        return $_return;
    }

    /**
     * Вернет строковое представление названия типа данных, если тип не встроенный, он будет начинаться с "\"
     *
     * @param   \ReflectionNamedType   $reflectionType   Рефлексия типа данных
     *
     * @return  string   Вернет строковое представление названия типа данных
     */
    private static function getName(\ReflectionNamedType $reflectionType): string
    {
        $name = $reflectionType->getName();

        if ($reflectionType->isBuiltin() || $name === 'self' || $name === 'static' || $name === 'callable') return $name;
        else return "\\{$name}";
    }
}
