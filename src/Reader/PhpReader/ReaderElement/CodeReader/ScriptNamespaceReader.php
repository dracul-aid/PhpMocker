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
use DraculAid\PhpMocker\Tools\Char;

/**
 * Осуществляет чтение конструкций NAMESPACE (указания пространства имен), разгружает код для:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see ScriptNamespaceReader::isStart() - Проверяет, текущий читаемый PHP код, является ли он кодом определенного типа
 * @see self::clear() - Очищает ранее накопленные временные данные
 * @see self::start() - Проводит выполнение стартовых процедур для начала чтения кода объектом "читателем кода"
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class ScriptNamespaceReader extends AbstractReader
{
    public function clear(): void {}

    public static function isStart(PhpReader $phpReader): bool
    {
        // "namespace" in code
        return $phpReader->codeString->isWordStart('n', 'a', 'mespace');
    }

    public function start(): void
    {
        $this->clear();

        $this->phpReader->codeString->charClear();
        $this->phpReader->codeString->phpCode = substr($this->phpReader->codeString->phpCode, 7);

        if ($this->phpReader->tmpResult->namespace !== '') $this->phpReader->tmpResult->uses = [];
        $this->phpReader->tmpResult->attributes = [];

        $this->phpReader->tmpResult->namespace = '';
    }

    public function run(): null|self
    {
        if ($this->phpReader->codeString->charFirst === ';' || $this->phpReader->codeString->charFirst === '{')
        {
            $this->phpReader->tmpResult->codeBlockForClassDeep = $this->phpReader->tmpResult->codeBlockDeep + 1;
            return null;
        }
        // прочие символы, которые могут выступать в качестве имени пространства имен
        elseif (Char::canBeInsideNameOfVar($this->phpReader->codeString->charFirst) || $this->phpReader->codeString->charFirst === '\\')
        {
            $this->phpReader->tmpResult->namespace .= $this->phpReader->codeString->charFirst;
        }

        return $this;
    }
}
