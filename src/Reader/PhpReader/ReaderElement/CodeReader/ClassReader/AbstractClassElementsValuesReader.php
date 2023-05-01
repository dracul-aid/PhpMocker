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

/**
 * Абстрактный класс для "читателей кода" проводящих чтение элементов классов имеющих значение, разгружает код для:
 * * {@see CodeReader} - непосредственно в анализаторе кода
 * * {@see PhpReader} - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see self::start() - Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
abstract class AbstractClassElementsValuesReader extends AbstractClassElementsReader
{
    final public function run(): null|self
    {
        if ($this->phpReader->codeString->charFirst === ';')
        {
            $this->ReadElementWithValueFinish();
            return null;
        }
        // это начало накопления значения (и конец накопления имени)
        elseif ($this->phpReader->codeString->charFirst === '=' && $this->tmpClassElement->writeFor !== TmpClassElement::WRITE_FOR_VALUE)
        {
            // отрабатываем конец заполнения имени - сохранение имени в схему
            $this->runNameStopRead();

            $this->phpReader->readWithStrings = true;
            $this->tmpClassElement->writeFor = TmpClassElement::WRITE_FOR_VALUE;
        }
        // если идет какое-то накопление - запомним символ
        elseif ($this->tmpClassElement->writeFor !== 0)
        {
            $this->phpReader->codeTmp->addChar();
        }

        return $this;
    }

    /**
     * Отрабатывает конец чтение кода элемента с значением
     *
     * @return void
     */
    abstract protected function ReadElementWithValueFinish(): void;
}
