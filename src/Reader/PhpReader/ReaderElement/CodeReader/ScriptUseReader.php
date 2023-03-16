<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;

use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\AbstractReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;
use DraculAid\PhpMocker\Schemes\UseScheme;
use DraculAid\PhpMocker\Schemes\UseSchemeType;
use DraculAid\PhpMocker\Tools\ClassTools;
use DraculAid\PhpMocker\Tools\Php8Functions;

/**
 * Осуществляет чтение конструкций USE (для подключения классов, функций и констант), разгружает код для:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see ScriptUseReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class ScriptUseReader extends AbstractReader
{
    /**
     * Для накопления PHP кода с определением USE
     */
    private string $tmpUseCode = '';

    public function clear(): void
    {
        $this->tmpUseCode = ' ';
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        // "use" in code
        return $phpReader->codeString->isWordStart('u', 's', 'e');
    }

    public function start(): void
    {
        $this->clear();
        $this->phpReader->codeString->charClear();
        $this->phpReader->codeString->phpCode = substr($this->phpReader->codeString->phpCode, 2);
    }

    public function run(): ?AbstractReader
    {
        if ($this->phpReader->codeString->charFirst === ';')
        {
            $this->stopReadUse();
            return null;
        }
        else
        {
            $this->tmpUseCode .= $this->phpReader->codeString->charFirst;
            return $this;
        }
    }

    /**
     * Отрабатывает конец чтения use для скрипта
     *
     * @return void
     */
    private function stopReadUse(): void
    {
        $this->tmpUseCode = " {$this->tmpUseCode} ";

        // это определение нескольких элементов в рамках одного пространства имен
        // конструкция use catalog/{class1, class2, const CONST}
        if (($tmp = strpos($this->tmpUseCode, '{')) !== false)
        {
            $namespacePrefix = substr(trim(substr($this->tmpUseCode, 0, $tmp)), 0, -1);
            $nameStringList = substr(rtrim($this->tmpUseCode), $tmp + 1, -1);

            foreach (explode(',', $nameStringList) as $typeAndNameAndAlias)
            {
                $this->parseUseStringValue($typeAndNameAndAlias, $namespacePrefix);
            }
        }
        // это определения нескольких элементов в рамках разных пространств имен
        // конструкция: use catalog_1/class_1, catalog_2/class_2, const CONST;
        elseif (Php8Functions::str_contains($this->tmpUseCode, ','))
        {
            foreach (explode(',', $this->tmpUseCode) as $useValue)
            {
                $this->parseUseStringValue($useValue, '');
            }
        }
        // это определения одного элемента
        // конструкция: use catalog_1/class_1;
        else
        {
            $this->parseUseStringValue($this->tmpUseCode, '');
        }
    }

    /**
     * Парсит строку use для скрипта (ищет вызываемый элемент и его тип)
     *
     * @param   string   $useValue          Имя элемента, его пространство имен и псевдоним
     * @param   string   $namespacePrefix   Пространство имен, относительно которых указанны имена в $useValue
     *
     * @return  void
     */
    private function parseUseStringValue(string $useValue, string $namespacePrefix): void
    {
        $useValue = " {$useValue} ";
        $useType = $this->clearTypeFromUseStringValue($useValue);

        if (strpos($useValue, '\\') > 0)
        {
            $namespace = $namespacePrefix !== ''
                ? implode('\\', [$namespacePrefix, trim(ClassTools::getNamespace($useValue))])
                : trim(ClassTools::getNamespace($useValue));

            $this->saveUseElementIntoClassScheme(
                $namespace,
                trim(ClassTools::getNameWithoutNamespace($useValue)),
                $useType
            );
        }
        else
        {
            $this->saveUseElementIntoClassScheme($namespacePrefix, trim($useValue), $useType);
        }
    }

    /**
     * Вернет строку с содержимым конструкции USE и найдет тип use (для класса, константы или функции)
     *
     * @param   string   $useString   Строка с содержимым use
     *
     * @return  UseSchemeType   Вернет "тип конструкции use"
     */
    private function clearTypeFromUseStringValue(string &$useString): UseSchemeType
    {
        // если это use для констант
        if (($tmp = strpos($useString, ' const ')) !== false)
        {
            $useString = substr($useString, $tmp + 7);
            return UseSchemeType::CONSTANTS();
        }
        // если это use для функции
        elseif (($tmp = strpos($useString, ' function ')) !== false)
        {
            $useString = substr($useString, $tmp + 10);
            return UseSchemeType::FUNCTIONS();
        }
        // это для классов
        else
        {
            return UseSchemeType::CLASSES();
        }
    }

    /**
     * Добавляет в результат найденный use (класс, константу или функцию)
     *
     * @param   string          $namespace   Пространство имен
     * @param   string          $name        Используемый элемент:
     *                                       * Имя класса, константы или функции
     *                                       * имя и псевдоним (через AS)
     * @param   UseSchemeType   $useType     Тип use скрипта (вызов класса, функции или константы)
     *
     * @return  void
     */
    private function saveUseElementIntoClassScheme(string $namespace, string $name, UseSchemeType $useType): void
    {
        // AS может быть написан в верхнем регистре
        $name = str_ireplace(' as ', ' as ', $name);
        $nameAndAlias = explode(' as ', $name);

        $useObject = new UseScheme($useType);
        $useObject->namespace = trim($namespace);
        $useObject->name = trim($nameAndAlias[0]);
        $useObject->alias = empty($nameAndAlias[1]) ? '' : trim($nameAndAlias[1]);

        $this->phpReader->tmpResult->uses[] = $useObject;
    }
}
