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
 * Осуществляет чтение блочного комментария, разгружает код для @see PhpReader
 *
 * Для чтения однострочных комментариев @see CommentLineReader
 *
 * Оглавление:
 * @see CommentBlockReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class CommentBlockReader extends AbstractReader
{
    /**
     * Для хранения результатов чтения строки (содержимое строки)
     */
    private string $result;

    public function clear(): void
    {
        $this->result = '';
    }

    public static function isStart(PhpReader $phpReader): bool
    {
        return $phpReader->codeString->charFirst === '/' && $phpReader->codeString->charSecond === '*';
    }

    public function start(): void
    {
        $this->clear();

        // очищаем прочитанный 1 и 2 символы (дальнейшее чтение будет проводиться с конца метки)
        $this->phpReader->codeString->charClear();
    }

    public function run(): ?AbstractReader
    {
        if ($this->phpReader->codeString->charFirst === '*' && $this->phpReader->codeString->charSecond === '/')
        {
            // Нет смысла в сохранении комментариев
            // $this->phpReader->codeTmp->addString("/*{$this->tmpValue}*/", '/');

            $this->phpReader->codeString->charClear();
            return null;
        }
        else
        {
            $this->result .= $this->phpReader->codeString->charFirst;

            return $this;
        }
    }
}
