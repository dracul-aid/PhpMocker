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

use DraculAid\PhpMocker\Schemes\PropertyScheme;

/**
 * Читает PHP код для создания схем свойств, разгружает код для:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * @see PropertyScheme - Класс с описанием схем свойств
 *
 * Оглавление:
 * @see ClassConstantsReader::start() - Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class ClassPropertiesReader extends AbstractClassElementsValuesReader
{
    protected function createSchemes(): void
    {
        $this->tmpClassElement->scheme = new PropertyScheme($this->phpReader->tmpResult->schemeClass, '');
        $this->tmpClassElement->scheme->view = $this->tmpClassElement->getView();
        $this->tmpClassElement->scheme->isStatic = $this->tmpClassElement->isStatic;
        $this->tmpClassElement->scheme->isReadonly = $this->tmpClassElement->isReadonly;
    }

    protected function ReadElementWithValueFinish(): void
    {
        if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_NAME) $this->runNameStopRead();

        $this->tmpClassElement->scheme->innerPhpCode = trim($this->phpReader->codeTmp->result);
        if ($this->tmpClassElement->scheme->innerPhpCode !== '') $this->tmpClassElement->scheme->isValue = true;

        $varType = trim($this->tmpClassElement->endWord);
        if ($varType !== '')
        {
            // сокращенную запись "тип данных и NULL" преобразуем в полную
            if ($varType[0] === '?') $varType = 'null|' . substr($varType, 1);
            $this->tmpClassElement->scheme->type = $varType;
        }

        $this->phpReader->tmpResult->schemeClass->properties[$this->tmpClassElement->scheme->name] = $this->tmpClassElement->scheme;

        $this->ReadFinish();
    }
}
