<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader;

use DraculAid\PhpMocker\Reader\PhpReader\CodeString;
use DraculAid\PhpMocker\Reader\PhpReader\CodeTmp;
use DraculAid\PhpMocker\Reader\PhpReader\TmpResult;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\AbstractReader;
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Класс для получения схем класса на основе PHP кода
 * (классов, трейтов, интерфейсов и перечислений)
 *
 * Оглавление:
 * @see PhpReader::CodeToSchemes() - Прочитает PHP код и вернет схемы для всех найденных в нем классов
 * --- Свойства читателя PHP кода
 * @see self::$codeString [const] - Объект с еще не обработанным кодом
 * @see self::$codeTmp [const] - Объект с накапливаемой строкой кода
 * @see self::$tmpResult [const] - Объект для хранения временных результатов
 * @see self::$readWithStrings - Нужно ли "временный результат" помещать строки
 * @see self::$result - Для накопления ответа (список найденных схем классов)
 *
 * Ссылки на дочерние сущности:
 * --- Чтение "не кода"
 * @see PhpReader\ReaderElement\AttributesReader - Чтение атрибутов
 * @see PhpReader\ReaderElement\StringReader - Строк
 * @see PhpReader\ReaderElement\HeredocAndNowdocReader - Heredoc и Nowdoc строк
 * @see PhpReader\ReaderElement\CommentBlockReader - Блочных комментериев
 * @see PhpReader\ReaderElement\CommentLineReader - Строчных комментариев
 * --- Чтение кода скрипта (т.е. вне класса) @see PhpReader\ReaderElement\CodeReader
 * @see PhpReader\ReaderElement\CodeReader\ScriptNamespaceReader - Оператора пространства имен
 * @see PhpReader\ReaderElement\CodeReader\ScriptUseReader - Оператора USE (включение классов, функций и констант)
 * --- Чтение кода класса @see PhpReader\ReaderElement\CodeReader\ClassReader
 * @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassSchemeCreator - Создание схемы класса и чтение ключевых слов класса
 * @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassElementSchemeCreator - Создание схемы элемента класса (определяет, какой "читатель будет работать далее"):
 *    @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassTraitsReader - Чтение трейтов
 *    @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassConstantsReader - Чтение констант
 *    @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassMethodsReader - Чтение методов
 *    @see PhpReader\ReaderElement\CodeReader\ClassReader\ClassPropertiesReader - Чтение свойств
 */
class PhpReader
{
    /**
     * Объект с еще не обработанным кодом
     */
    readonly public CodeString $codeString;
    /**
     * Объект с накапливаемой строкой кода
     */
    readonly public CodeTmp $codeTmp;

    /**
     * Объект, который в данный момент ведет чтение кода
     * (NULL - объект чтения кода не установлен)
     */
    private null|AbstractReader $codeReader = null;

    /**
     * Объект для хранения временных результатов
     */
    readonly public TmpResult $tmpResult;

    /**
     * Нужно ли "временный результат" помещать строки, {@see self::$codeTmp}
     */
    public bool $readWithStrings = false;

    /**
     * Результат работы (список найденных схем классов)
     *
     * @var ClassScheme[] $result
     */
    public array $result = [];

    /**
     * Объект для чтения кода, если не удалось определить объект в {@see self::$codeReader}
     */
    readonly protected CodeReader $defaultCodeReader;

    /**
     * Обработает PHP код и вернет схемы для всех найденных в нем классов
     *
     * @param   string   $phpCode   PHP код для анализа
     *
     * @return  ClassScheme[]   Массив с найденными схемами классов
     */
    public static function CodeToSchemes(string $phpCode): array
    {
        $reader = new self($phpCode);
        $reader->run();

        return $reader->result;
    }

    /**
     * @param  string  $phpCode   Строка с PHP кодом для анализа
     */
    protected function __construct(string $phpCode)
    {
        $this->codeString = new PhpReader\CodeString($phpCode);
        $this->codeTmp = new PhpReader\CodeTmp($this);
        $this->tmpResult = new TmpResult();
        $this->defaultCodeReader = new CodeReader($this);
    }

    /**
     * Анализ PHP кода и создание схем классов для найденных в коде классов
     *
     * @return void
     */
    protected function run(): void
    {
        while ($this->codeString->read())
        {
            if ($this->codeReader !== null)
            {
                $this->codeReader = $this->codeReader->run();
            }
            else
            {
                $this->codeReader = AbstractReader::searchReader($this, true);
                if ($this->codeReader === null)
                {
                    $this->defaultCodeReader->run();
                }
            }
        }
    }
}
