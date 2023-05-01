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

use DraculAid\PhpMocker\Schemes\ConstantScheme;

/**
 * Читает PHP код для создания схем констант, разгружает код для:
 * * {@see CodeReader} - непосредственно в анализаторе кода
 * * {@see PhpReader} - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * * {@see ConstantScheme} - Класс с описанием схем констант
 * * {@see ClassConstantsReader} - Читатель "констант класса"
 *
 * Оглавление:
 * @see self::start() - Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class EnumCasesReader extends AbstractClassElementsValuesReader
{
    protected function createSchemes(): void
    {
        $this->tmpClassElement->scheme = new ConstantScheme($this->phpReader->tmpResult->schemeClass, '');
        $this->tmpClassElement->scheme->isEnumCase = true;
    }

    protected function ReadElementWithValueFinish(): void
    {
        if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_NAME) $this->runNameStopRead();

        $this->tmpClassElement->scheme->innerPhpCode = trim($this->phpReader->codeTmp->result);
        $this->phpReader->tmpResult->schemeClass->constants[$this->tmpClassElement->scheme->name] = $this->tmpClassElement->scheme;

        // сбрасываем указание, что читали константу
        $this->ReadFinish();
    }
}
