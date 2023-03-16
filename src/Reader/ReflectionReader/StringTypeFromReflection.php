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
        if (PHP_MAJOR_VERSION > 7)
        {
            if (is_a($reflectionType, \ReflectionNamedType::class)) return self::basic($reflectionType);
            elseif (is_a($reflectionType, \ReflectionUnionType::class)) return self::union($reflectionType);
            elseif (is_a($reflectionType, \ReflectionIntersectionType::class)) return self::intersection($reflectionType);
        }
        else
        {
            if (is_a($reflectionType, \ReflectionNamedType::class)) return self::basic($reflectionType);
        }

        throw new ReflectionReaderUndefinedTypeException();
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
        if (PHP_MAJOR_VERSION < 8) return '';

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
        elseif ($reflectionType->allowsNull()) $_return = "?{$_return}";

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

        if (PHP_MAJOR_VERSION > 7)
        {
            if ($reflectionType->isBuiltin() || $name === 'self' || $name === 'static' || $name === 'callable') return $name;
            else return "\\{$name}";
        }
        else
        {
            if ($reflectionType->isBuiltin() || $name === 'self' || $name === 'callable') return $name;
            else return "\\{$name}";
        }
    }
}
