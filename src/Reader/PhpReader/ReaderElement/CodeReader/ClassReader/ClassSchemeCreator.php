<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Exceptions\Reader\PhpReaderUndefinedTypeClassException;
use DraculAid\PhpMocker\Reader\PhpReader\CodeTmp;
use DraculAid\PhpMocker\Reader\PhpReader\TmpResult;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Tools\ClassTools;
use DraculAid\PhpMocker\Tools\Php8Functions;

/**
 * Класс, для создания схемы класса, используется для разгрузки кода в:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Класс-функция @see ClassSchemeCreator::exe()
 */
class ClassSchemeCreator
{
    /**
     * Объект с накапливаемой строкой кода
     */
    private CodeTmp $codeTmp;
    /**
     * Объект для хранения временных результатов
     */
    private TmpResult $tmpResult;

    /**
     * Создаст схему класса и определит имя и родителей класса
     *
     * @throws  PhpReaderUndefinedTypeClassException  Если не удалось определить тип класса
     */
    public static function exe(CodeTmp $codeTmp, TmpResult $tmpResult): void
    {
        $creator = new self();
        $creator->codeTmp = $codeTmp;
        $creator->tmpResult = $tmpResult;

        $creator->run();
    }

    private function __construct() {}

    /**
     * Определение типа класса (интерфейс, абстрактный класс...) и получение имени класса
     * в результате свой работы создает схему читаемого класса
     *
     * (!) Не проводит посимвольное чтение кода, а анализирует временно накопленный код
     *
     * @return void
     *
     * @throws PhpReaderUndefinedTypeClassException  Если не удалось определить тип класса
     */
    private function run(): void
    {
        // к текущему накопленному результату добавим пробел, что бы вести поиск типов классов без регулярных выражений
        // так как иначе, перед ключевыми словами стоял бы или пробел или "начало строки"
        // добавим пробел в конце, так как имя класса могло оканчиваться на "конец строки"
        $this->codeTmp->set(" {$this->codeTmp->result} ", $this->codeTmp->lastChar);

        // это класс или абстрактный класс
        if (($tmp = strpos($this->codeTmp->result, ' class ')) !== false)
        {
            $className = $this->searchClassName($tmp + 7);

                if (Php8Functions::str_contains($this->codeTmp->result, ' abstract ')) $this->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::ABSTRACT_CLASSES(), $className);
                else $this->tmpResult->schemeClass = new ClassScheme(ClassSchemeType::CLASSES(), $className);
            }
        // это интерфейс
        elseif (($tmp = strpos($this->codeTmp->result, ' interface ')) !== false)
        {
            $this->tmpResult->schemeClass = new ClassScheme(
                ClassSchemeType::INTERFACES(),
                $this->searchClassName($tmp + 11)
            );
        }
        // это трейт
        elseif (($tmp = strpos($this->codeTmp->result, ' trait ')) !== false)
        {
            $this->tmpResult->schemeClass = new ClassScheme(
                ClassSchemeType::TRAITS(),
                $this->searchClassName($tmp + 7)
            );
        }
        // это перечисление
        elseif (($tmp = strpos($this->codeTmp->result, ' enum ')) !== false)
        {
            $this->tmpResult->schemeClass = new ClassScheme(
                ClassSchemeType::ENUMS(),
                $this->searchClassName($tmp + 6)
            );
        }
        // неизвестный тип класса
        else
        {
            throw new PhpReaderUndefinedTypeClassException();
        }

        // * * *

        $this->tmpResult->schemeClass->namespace = $this->tmpResult->namespace;
        $this->tmpResult->schemeClass->uses = $this->tmpResult->uses;
        if (Php8Functions::str_contains($this->codeTmp->result, ' final ')) $this->tmpResult->schemeClass->isFinal = true;
        if (Php8Functions::str_contains($this->codeTmp->result, ' readonly ')) $this->tmpResult->schemeClass->isReadonly = true;

        $this->tmpResult->schemeClass->isInternal = ClassTools::isInternal($this->tmpResult->schemeClass->getFullName());

        $this->searchParents();
        $this->searchInterfaces();

        if ($this->tmpResult->schemeClass->type === ClassSchemeType::ENUMS()) $this->enumSearchType();

        $this->saveAttributes();
    }

    /**
     * Ищет тип перечисления
     * enum Name: type {}
     *
     * @return void
     */
    private function enumSearchType(): void
    {
        $position = strpos($this->codeTmp->result, ':');
        if ($position < 1)
        {
            return;
        }

        // * * *

        $this->tmpResult->schemeClass->enumType = trim(substr($this->codeTmp->result, $position+1));
    }

    /**
     * Добавит в созданную схему класса найденные ранее атрибуты (и очистит список "ранее найденных атрибутов")
     *
     * @return void
     */
    private function saveAttributes(): void
    {
        $this->tmpResult->schemeClass->attributes = $this->tmpResult->attributes;
        $this->tmpResult->attributes = [];

        // пройдем по всем атрибутам и припишем их к текущей схеме класса
        foreach ($this->tmpResult->schemeClass->attributes as $attribute)
        {
            $attribute->setOwnerScheme($this->tmpResult->schemeClass);
        }
    }

    /**
     * Для метода создания схемы класса найдет имя класса
     *
     * @param   int   $start    Позиция с которой начинается имя
     *
     * @return  string   Вернет имя класса
     */
    private function searchClassName(int $start): string
    {
        $endName = strpos($this->codeTmp->result, ' ', $start);
        $endName2 = strpos($this->codeTmp->result, ':', $start);
        if ($endName2 > 0 ) $endName = min($endName, $endName2);

        return substr($this->codeTmp->result, $start, $endName - $start);
    }

    /**
     * Осуществляет поиск и установку в текущую схему классов "родителей" для класса
     *
     * @return void
     */
    private function searchParents(): void
    {
        if (($start = strpos($this->codeTmp->result, ' extends ')) !== false)
        {
            $start += 9;

            // имена родителей указанны до интерфейсов или до конца ключевых слов класса
            $end = strpos($this->codeTmp->result, ' implements ');
            if ($end === false) $end = strlen($this->codeTmp->result) - $start - 1;
            else $end -= $start;

            // получаем строку со списком родителей и получаем из нее массив родителей
            // пройдем по всем родителям и уничтожаем пробельные символы
            $parentList = [];
            foreach (explode(',', substr($this->codeTmp->result, $start, $end)) as $parent)
            {
                $parentList[] = trim($parent);
            }

            if ($this->tmpResult->schemeClass->type === ClassSchemeType::INTERFACES()) $this->tmpResult->schemeClass->interfaces = $parentList;
            elseif ($this->tmpResult->schemeClass->type->canUseExtends()) $this->tmpResult->schemeClass->parent = implode(', ', $parentList);
        }
    }

    /**
     * Осуществляет поиск и установку в текущую схему классов реализуемых им интерфейсов
     *
     * Список интерфейсов для интерфейсов уже был найден @see self::searchParents()
     *
     * @return void
     */
    private function searchInterfaces(): void
    {
        // если это интерфейс или тип класса не поддерживающий интерфейсы - выйдем
        if ($this->tmpResult->schemeClass->type === ClassSchemeType::INTERFACES() || !$this->tmpResult->schemeClass->type->canUseInterfaces())
        {
            return;
        }

        // * * *

        // если есть интерфейсы
        if (($start = strpos($this->codeTmp->result, ' implements ')) !== false)
        {
            foreach (explode(',', substr($this->codeTmp->result, $start + 12, -1)) as $interface)
            {
                $this->tmpResult->schemeClass->interfaces[] = trim($interface);
            }
        }
    }
}
