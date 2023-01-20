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
 * Класс-функция, создающая класс, вызывающий трейты
 * @see CreateClassImplementsTraits::exe()
 */
class CreateClassImplementsTraits
{
    /**
     * Создает класс реализующий трейт(ы)
     *
     * @param   string|array   $trait       Имя трейта или список трейтов для реализации
     * @param   string         $className   Имя создаваемого класса ('' - если нужно создать автоматическое имя)
     *
     * @return  string    Вернет строку с именем класса, реализовавшем трейты
     */
    public static function exe(string|array $trait, string $className = ''): string
    {
        if ($className === '') $className = '___sf_class_for_trait_' . uniqid() . '___';

        if (is_string($trait))
        {
            $phpCode = "class {$className} {use {$trait};}";
        }
        else
        {
            $phpCode = '';
            foreach ($trait as $traitEl)
            {
                $phpCode .= "use {$traitEl};";
            }

            $phpCode = "class {$className} {{$phpCode}}";
        }


        // * * *

        eval($phpCode);

        return $className;
    }
}
