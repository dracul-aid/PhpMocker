<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement;

use DraculAid\PhpMocker\Reader\PhpReader;

/**
 * Абстрактный класс анализаторов кода, дочерние классы, главным образом, разгружают код для {@see PhpReader}
 * и используются в:
 * {@see PhpReader::run()}
 * {@see AttributesReader::run()}
 *
 * Оглавление:
 * @see AbstractReader::$phpReader - Объект "читатель кода", для которого идет накопление временных результатов
 * @see AbstractReader::searchReader() - Вернет объект, для дальнейшего чтения кода по анализу текущего читаемого кода
 * @see AbstractReader::getReaderObjectByClass() - Вернет объект для анализа конкретного кода, по имени класса для этого объекта
 * --- Для дочерних классов:
 * @see AbstractReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
abstract class AbstractReader
{
    /**
     * Объект "читатель кода", для которого идет накопление временных результатов
     */
    readonly public PhpReader $phpReader;

    /**
     * @param   PhpReader   $phpReader   Объект "читатель кода", для которого идет накопление временных результатов
     */
    protected function __construct(PhpReader $phpReader)
    {
        $this->phpReader = $phpReader;
    }

    /**
     * Очищает ранее накопленные временные данные
     *
     * @return void
     */
    public function clear(): void {}

    /**
     * Вернет объект, для дальнейшего чтения кода по анализу текущего читаемого кода
     *
     * @param   PhpReader    $phpReader        Объект "читатель кода", для которого идет накопление временных результатов
     * @param   bool         $withAttributes   Должен ли быть включен класс для чтения атрибутов
     * @param   null|string  $defaultClass     Что вернет, если не был найден класс для чтения
     *
     * @return  null|AbstractReader   Вернет объект "читатель кода" (или NULL, если читатель кода не был найден)
     */
    public static function searchReader(PhpReader $phpReader, bool $withAttributes): null|self
    {
        $readerClass = match (true) {
            StringReader::isStart($phpReader) => StringReader::class,
            CommentBlockReader::isStart($phpReader) => CommentBlockReader::class,
            CommentLineReader::isStart($phpReader) => CommentLineReader::class,
            $withAttributes && AttributesReader::isStart($phpReader) => AttributesReader::class,
            HeredocAndNowdocReader::isStart($phpReader) => HeredocAndNowdocReader::class,
            default => null,
        };

        if ($readerClass === null) return null;
        else return self::getReaderObjectByClass($readerClass, $phpReader);
    }

    /**
     * Вернет объект для анализа конкретного кода, по имени класса для этого объекта
     *
     * @param   string      $readerClass   Имя класса "читателя кода"
     * @param   PhpReader   $phpReader     Объект "читатель кода", для которого идет накопление временных результатов
     *
     * @return  AbstractReader
     */
    public static function getReaderObjectByClass(string $readerClass, PhpReader $phpReader): self
    {
        /**
         * Массив с ранее созданными "читателями кода"; Ключи массива переменную $index ниже
         * @var AbstractReader[] $_reader_storage
         */
        static $_reader_storage = [];
        $index = spl_object_id($phpReader) . "-{$readerClass}";

        // * * *

        if(empty($_reader_storage[$index])) $_reader_storage[$index] = new $readerClass($phpReader);
        $_reader_storage[$index]->start();

        return $_reader_storage[$index];
    }

    /**
     * Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
     * (строкой, ключевым словом классов, комментарием...)
     *
     * @param   PhpReader   $phpReader    Объект "читатель кода"
     *
     * @return  bool
     */
    abstract public static function isStart(PhpReader $phpReader): bool;

    /**
     * Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
     *
     * @return void
     */
    abstract public function start(): void;

    /**
     * Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
     *
     * @return   null|AbstractReader   Вернет объект для дальнейшего чтения или NULL, если объект для дальнейшего чтения не определен
     */
    abstract public function run(): null|self;
}
