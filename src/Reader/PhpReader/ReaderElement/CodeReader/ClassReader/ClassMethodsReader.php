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

use DraculAid\PhpMocker\Schemes\MethodScheme;

/**
 * Читает PHP код для создания схем методов классов, разгружает код для:
 * @see CodeReader - непосредственно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * @see MethodScheme - Класс с описанием схем методов классов
 *
 * Оглавление:
 * @see ClassConstantsReader::start() - Вернет "объект читатель кода" для дальнейшего чтения и создаст схему создаваемого элемента
 * @see self::run() - Проводит обработку прочитанного символа, и определяет не конец ли это работы "читателя кода"
 */
class ClassMethodsReader extends AbstractClassElementsReader
{
    /**
     * Уровень вложенности круглых скобок
     */
    private int $deepBrackets = 0;

    /**
     * Глубина блоков кода (фигурных скобок) с которых начато чтение тела функции
     */
    private int $codeBlockStartFunctionDeep = 0;

    protected function createSchemes(): void
    {
        $this->deepBrackets = 0;

        $this->tmpClassElement->scheme = new MethodScheme($this->phpReader->tmpResult->schemeClass, '');
        $this->tmpClassElement->scheme->view = $this->tmpClassElement->getView();
        $this->tmpClassElement->scheme->isStatic = $this->tmpClassElement->isStatic;
        $this->tmpClassElement->scheme->isFinal = $this->tmpClassElement->isFinal;
        $this->tmpClassElement->scheme->isAbstract = $this->tmpClassElement->isAbstract;
    }

    public function run(): null|self
    {
        if ($this->phpReader->codeString->charFirst === '(') $this->deepBrackets++;
        elseif ($this->phpReader->codeString->charFirst === ')') $this->deepBrackets--;

        // * * *

        // идет чтение тела метода (кода функции)
        if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_VALUE)
        {
            // встретилась завершающая набор кода фигурная скобка, это конец определения функции
            if ($this->phpReader->codeString->charFirst === '}' && $this->phpReader->tmpResult->codeBlockDeep === $this->codeBlockStartFunctionDeep)
            {
                // конец чтения кода функции
                $this->runFunctionBodyStopRead();
                // конец чтения функции
                $this->runReadingMethodFinish();

                return null;
            }
            // если это не конец кода функции - запоминаем символ
            else
            {
                $this->phpReader->codeTmp->addChar();
            }
        }
        // идет чтение аргументов
        elseif ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_METHOD_ARGUMENTS)
        {
            if ($this->phpReader->codeString->charFirst === ')' && $this->deepBrackets === 0)
            {
                // отрабатываем конец набора аргументов
                $this->runArgumentsStopRead();
                $this->phpReader->codeTmp->resultClear();
            }
            // если это не конец кода аргументов - запомним символ
            else
            {
                $this->phpReader->codeTmp->addChar();
            }
        }
        // если это не набор кода функции или аргументов функции
        else
        {
            // встретилась первая круглая скобка - начало набора кода аргументов функции
            if ($this->phpReader->codeString->charFirst === '(' && $this->deepBrackets === 1)
            {
                if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_NAME)
                {
                    // включен режим чтения аргументов
                    $this->runArgumentsStartRead();
                    $this->phpReader->codeTmp->resultClear();
                }
            }
            // встретилось двоеточие - включим режим чтения возвращаемого значения
            elseif ($this->phpReader->codeString->charFirst === ':')
            {
                $this->runReturnStartRead();
                $this->phpReader->codeTmp->resultClear();
            }
            // встретилась фигурная скобка - начинаем читать код
            elseif ($this->phpReader->codeString->charFirst === '{')
            {
                $this->codeBlockStartFunctionDeep = $this->phpReader->tmpResult->codeBlockDeep - 1;
                $this->runFunctionBodyStartRead();
                $this->phpReader->codeTmp->resultClear();
            }
            // встретилась точка-запятая, это конец определения функции
            elseif ($this->phpReader->codeString->charFirst === ';')
            {
                // отработаем конец определения абстрактного метода
                $this->runFunctionAbstractEndRead();

                return null;
            }
            // все прочие варианты - запомним символ
            else
            {
                $this->phpReader->codeTmp->addChar();
            }
        }

        return $this;
    }

    /**
     * Отрабатывает начало чтения аргументов
     *
     * @return void
     */
    protected function runArgumentsStartRead(): void
    {
        $this->runNameStopRead();

        $this->tmpClassElement->writeFor = TmpClassElement::WRITE_FOR_METHOD_ARGUMENTS;
        $this->phpReader->readWithStrings = true;
    }

    /**
     * Отрабатывает конец чтения аргументов
     *
     * @return void
     */
    protected function runArgumentsStopRead(): void
    {
        $this->tmpClassElement->scheme->argumentsPhpCode = trim($this->phpReader->codeTmp->result);
        $this->tmpClassElement->writeFor = 0;
        $this->phpReader->readWithStrings = false;
    }

    /**
     * Отрабатывает начало чтения типа возвращаемого функцией значения
     *
     * @return void
     */
    protected function runReturnStartRead(): void
    {
        $this->tmpClassElement->writeFor = TmpClassElement::WRITE_FOR_METHOD_RETURN;
    }

    /**
     * Отрабатывает начало чтения типа возвращаемого функцией значения
     *
     * @return void
     */
    protected function runReturnStopRead(): void
    {
        $this->tmpClassElement->scheme->returnType = trim($this->phpReader->codeTmp->result);
        $this->tmpClassElement->writeFor = 0;
    }

    /**
     * Отрабатывает начало чтения кода функции
     *
     * @return void
     */
    protected function runFunctionBodyStartRead(): void
    {
        if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_METHOD_RETURN) $this->runReturnStopRead();
        
        $this->tmpClassElement->writeFor = TmpClassElement::WRITE_FOR_VALUE;
        $this->phpReader->readWithStrings = true;
    }

    /**
     * Отрабатывает конец чтения кода функции
     *
     * @return void
     */
    protected function runFunctionBodyStopRead(): void
    {
        $this->tmpClassElement->scheme->innerPhpCode = trim($this->phpReader->codeTmp->result);
        $this->phpReader->readWithStrings = false;
    }

    /**
     * Отрабатывает конец определения абстрактного метода (метода в интерфейсе)
     *
     * @return void
     */
    protected function runFunctionAbstractEndRead(): void
    {
        if ($this->tmpClassElement->writeFor === TmpClassElement::WRITE_FOR_METHOD_RETURN) $this->runReturnStopRead();
        $this->runReadingMethodFinish();
    }

    /**
     * Отрабатывает конец определения метода
     *
     * @return void
     */
    protected function runReadingMethodFinish(): void
    {
        // если имя метода начиналось с "указателя ссылки"
        if ($this->tmpClassElement->scheme->name[0] === '&')
        {
            $this->tmpClassElement->scheme->name = substr($this->tmpClassElement->scheme->name, 1);
            $this->tmpClassElement->scheme->isReturnLink = true;
        }

        $this->phpReader->tmpResult->schemeClass->methods[$this->tmpClassElement->scheme->name] = $this->tmpClassElement->scheme;

        $this->ReadFinish();
    }
}
