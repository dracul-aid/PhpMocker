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
use DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;
use DraculAid\PhpMocker\Schemes\ViewScheme;

/**
 * Класс, для создания схемы элементов классов (методов, свойств, констант), используется для разгрузки кода в:
 * @see ClassReader - непосредственно в читателе кода класса
 * @see CodeReader - опосредованно в анализаторе кода
 * @see PhpReader - опосредованно в анализаторе скрипта (код, с комментариями и строками)
 *
 * Оглавление:
 * @see ClassElementSchemeCreator::start() - Вернет объект для чтения кода и поиска в не ключевых слов для определения элемента класса
 * @see self::run() - Осуществит чтение кода, для поиска в нем ключевых слов определение элемента класса.
 */
class ClassElementSchemeCreator
{
    /**
     * Объект "читатель кода", для которого идет накопление временных результатов
     */
    private PhpReader $phpReader;

    /**
     * Объект с временными данными описывающими создаваемый элемент
     */
    private TmpClassElement $tmpClassElement;

    private function __construct() {}

    /**
     * Вернет объект для чтения кода и поиска в не ключевых слов для определения элемента класса
     *
     * @return self
     */
    public static function start(PhpReader $phpReader, ?TmpClassElement $tmpClassElement): self
    {
        static $_object_;

        if (!isset($_object_)) $_object_ = new self();
        $_object_->phpReader = $phpReader;
        $_object_->tmpClassElement = $tmpClassElement;
        $_object_->tmpClassElement->clear();

        return $_object_;
    }

    /**
     * Осуществит чтение кода, для поиска в нем ключевых слов определение элемента класса.
     * Определив элемент, вернет объект для дальнейшего чтения кода
     *
     * @return null|static|AbstractClassElementsReader
     *
     * Возвращает, как самого себя, так и объект чтения конкретного элемента класса @seeAbstractClassElementsReader
     * (если был определен читаемый в настоящий момент элемент)
     */
    public function run(): ?object
    {
        // текущее чтение похоже на чтение свойства
        if ($this->phpReader->codeString->charFirst === '$')
        {
            return ClassPropertiesReader::start($this->phpReader, $this->tmpClassElement);
        }
        // текущее чтение похоже на чтение метода - Поиск 'function'
        elseif ($this->phpReader->codeString->isWordStart('f', 'u', 'nction'))
        {
            $_return = ClassMethodsReader::start($this->phpReader, $this->tmpClassElement);
            $this->searchElementWordsClearPhpCode(6);
            return $_return;
        }
        // текущее чтение похоже на чтение константы или варианта перечисления - Поиск 'const'
        elseif ($this->phpReader->codeString->charFirst === 'c')
        {
            if ($this->phpReader->codeString->isWordStart(false, 'o', 'nst'))
            {
                $_return = ClassConstantsReader::start($this->phpReader, $this->tmpClassElement);
                $this->searchElementWordsClearPhpCode(3);
                return $_return;
            }
            // текуще чтение похоже на "вариант перечисления" - Поиск 'case'
            elseif ($this->phpReader->codeString->isWordStart(false, 'a', 'se'))
            {
                $_return = EnumCasesReader::start($this->phpReader, $this->tmpClassElement);
                $this->searchElementWordsClearPhpCode(2);
                return $_return;
            }
        }
        // текущее чтение похоже на чтение включения трейта - Поиск 'use'
        elseif ($this->phpReader->codeString->isWordStart('u', 's', 'e'))
        {
            $_return = ClassTraitsReader::start($this->phpReader, $this->tmpClassElement);
            $this->searchElementWordsClearPhpCode(1);
            return $_return;
        }

        // если не удалось определить тип - попробуем провести анализ ключевых слов
        $this->searchElementWords();
        return $this;
    }

    /**
     * Определение свойств элемента (уровень видимости, финальность, абстрактность....)
     *
     * @return void
     */
    private function searchElementWords(): void
    {
        // если уровень видимости не установлен и это похоже на возможную установку области видимости
        // если произойдет определение области видимости - определение других ключевых слов для элемента проводится не будет
        if ($this->tmpClassElement->view === null && $this->phpReader->codeString->charFirst === 'p')
        {
            if ($this->searchElementWordsView()) return;
        }
        // это статический элемент
        elseif (!$this->tmpClassElement->isStatic && $this->phpReader->codeString->isWordStart('s', 't', 'atic'))
        {
            $this->tmpClassElement->isStatic = true;
            // удалим из необработанного кода ненужное
            $this->searchElementWordsClearPhpCode(4);
        }
        // это свойство только для чтения
        elseif (!$this->tmpClassElement->isReadonly && $this->phpReader->codeString->isWordStart('r', 'e', 'adonly'))
        {
            $this->tmpClassElement->isReadonly = true;
            // удалим из необработанного кода ненужное
            $this->searchElementWordsClearPhpCode(6);
        }
        // это абстрактный метод
        elseif (!$this->tmpClassElement->isAbstract && $this->phpReader->codeString->isWordStart('a', 'b', 'stract'))
        {
            $this->tmpClassElement->isAbstract = true;
            // удалим из необработанного кода ненужное
            $this->searchElementWordsClearPhpCode(6);
        }
        // это финальный элемент
        elseif (!$this->tmpClassElement->isFinal && $this->phpReader->codeString->isWordStart('f', 'i', 'nal'))
        {
            $this->tmpClassElement->isFinal = true;
            // удалим из необработанного кода ненужное
            $this->searchElementWordsClearPhpCode(3);
        }
        // ключевое слово var
        elseif ($this->phpReader->codeString->isWordStart('v', 'a', 'r'))
        {
            // удалим из необработанного кода ненужное
            $this->searchElementWordsClearPhpCode(1);
        }
        // в прочих вариантах - запоминаем на разобранные слова
        else
        {
            $this->tmpClassElement->endWord .= $this->phpReader->codeString->charFirst;
        }
    }

    /**
     * Поиск в читаемом PHP коде указание на область видимости (public, protected, private)
     *
     * @return  bool  Вернет TRUE если было найдено ключевое слово определяющее область видимости
     */
    private function searchElementWordsView(): bool
    {
        // это public элемент
        if ($this->phpReader->codeString->isWordStart(false, 'u', 'blic'))
        {
            $this->tmpClassElement->view = ViewScheme::PUBLIC();
            $this->searchElementWordsClearPhpCode(4);

            return true;
        }
        // это protected или private элемент
        elseif ($this->phpReader->codeString->charSecond === 'r')
        {
            // это protected элемент
            if ($this->phpReader->codeString->phpCode[0] === 'o' && $this->phpReader->codeString->isWordStart(false, false, 'otected'))
            {
                $this->tmpClassElement->view = ViewScheme::PROTECTED();
                $this->searchElementWordsClearPhpCode(7);

                return true;
            }
            // это private элемент
            elseif ($this->phpReader->codeString->phpCode[0] === 'i' && $this->phpReader->codeString->isWordStart(false, false, 'ivate'))
            {
                $this->tmpClassElement->view = ViewScheme::PRIVATE();
                $this->searchElementWordsClearPhpCode(5);

                return true;
            }
        }

        return false;
    }

    /**
     * Удаляет из необработанного кода более ненужную подстроку и очищает прочитанные символы
     *
     * @return void
     */
    protected function searchElementWordsClearPhpCode(int $clearUntil): void
    {
        $this->phpReader->codeString->charClear();
        $this->phpReader->codeString->phpCode = substr($this->phpReader->codeString->phpCode, $clearUntil);
    }
}
