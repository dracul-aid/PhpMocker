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

use DraculAid\PhpMocker\Reader\PhpReader;

/**
 * Абстрактный класс для "читателей кода" проводящих чтение элементов классов (методов, констант и свойств), разгружает код для:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see self::start() - Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
abstract class AbstractClassElementsReader
{
    /**
     * Объект "читатель кода", для которого идет накопление временных результатов
     */
    protected PhpReader $phpReader;

    /**
     * Объект с временными данными описывающими создаваемый элемент
     */
    protected TmpClassElement $tmpClassElement;

    protected function __construct() {}

    /**
     * Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
     *
     * @param   PhpReader         $phpReader          Объект "читатель кода", для которого идет накопление временных результатов
     * @param   TmpClassElement   $tmpClassElement    Объект для хранения временных данных о создаваемом элементе
     *
     * @return  static  Вернет объект читатель кода
     */
    final public static function start(PhpReader $phpReader, TmpClassElement $tmpClassElement): static
    {
        static $_objects = [];
        if (empty($_objects[static::class])) $_objects[static::class] = new static();
        else $_objects[static::class]->clear();

        $creator = $_objects[static::class];
        $creator->phpReader = $phpReader;
        $creator->tmpClassElement = $tmpClassElement;
        $creator->tmpClassElement->writeFor = TmpClassElement::WRITE_FOR_NAME;
        $creator->createSchemes();

        // сбрасываем временно накопленный результат в пробелы (что бы облегчить сравнения [избежать ряда регулярных выражений])
        $creator->phpReader->codeTmp->resultClearAndSetSpase();

        return $creator;
    }

    /**
     * Очистка временных данных
     *
     * @return void
     */
    protected function clear(): void {}

    /**
     * Конец заполнения имени элемента
     *
     * @return void
     */
    final protected function runNameStopRead(): void
    {
        $this->tmpClassElement->scheme->name .= trim($this->phpReader->codeTmp->result);
        $this->phpReader->codeTmp->resultClearAndSetSpase();
    }

    /**
     * Конец чтения элемента класса (константы, свойства...)
     *
     * @return void
     */
    final protected function ReadFinish(): void
    {
        $this->attributesSaveIntoScheme();

        $this->phpReader->codeTmp->resultClearAndSetSpase();
        $this->phpReader->readWithStrings = false;
    }

    /**
     * Добавит в созданную схему элемента класса найденные ранее атрибуты (и очистит список "ранее найденных атрибутов")
     *
     * @return void
     */
    final protected function attributesSaveIntoScheme(): void
    {
        if (count($this->phpReader->tmpResult->attributes)>0)
        {
            $this->tmpClassElement->scheme->attributes = $this->phpReader->tmpResult->attributes;
            $this->phpReader->tmpResult->attributes = [];
        }
    }

    /**
     * Отрабатывает очередной символ кода для элемента класса,
     * срабатывает после окончания работы @see ClassElementSchemeCreator::run()
     *
     * @return null|self
     */
    abstract public function run(): null|self;

    /**
     * Создает схему элемента класса
     *
     * @return void
     */
    abstract protected function createSchemes(): void;
}
