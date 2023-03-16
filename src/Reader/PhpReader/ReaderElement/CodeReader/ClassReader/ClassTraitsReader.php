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
 * Класс для осуществления чтения трейтов в PHP коде
 */
class ClassTraitsReader extends AbstractClassElementsReader
{
    /**
     * Находится ли чтение внутри блока кода (внутри фигурных скобок)
     */
    private bool $readCodeBlock = false;

    protected function createSchemes(): void
    {
        $this->readCodeBlock = false;
    }

    public function run(): ?AbstractClassElementsReader
    {
        $this->phpReader->codeTmp->addChar();

        // если идет чтение блока кода
        if ($this->readCodeBlock)
        {
            // если встретили закрывающуюся скобку - это конец трейта
            if ($this->phpReader->codeString->charFirst === '}')
            {
                $this->runReadFinish();
            }
        }
        // если встретили открывающуюся фигурную скобку - начато чтение блока кода
        elseif ($this->phpReader->codeString->charFirst === '{')
        {
            $this->readCodeBlock = true;
        }
        // если встретили точку-запятую и она была не в фигурных скобках - это конец определения трейта
        elseif ($this->phpReader->codeString->charFirst === ';')
        {
            $this->runReadFinish();
            return null;
        }

        return $this;
    }

    /**
     * Отрабатывает конец определения метода
     *
     * @return void
     */
    protected function runReadFinish(): void
    {
        // запоминаем код трейтов в схеме + уничтожаем лишние пробельные символы
        $this->phpReader->tmpResult->schemeClass->traitsPhpCode .= 'use ' . trim($this->phpReader->codeTmp->result);

        // сбрасываем указание, что читали константу
        $this->ReadFinish();
    }
}
