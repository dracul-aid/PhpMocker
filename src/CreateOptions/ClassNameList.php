<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\CreateOptions;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Creator\MockerOptions\AbstractMockerOptions;
use DraculAid\PhpMocker\Creator\ToolsElementNames;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Используется для создания мок-классов с определенным именем в случаях, если создание происходит для списка классов
 *
 * Прямое использование в @see HardMocker
 */
class ClassNameList implements CreateOptionsInterface
{
    private array $newNames;

    /**
     * @param  array   $newNames   Массив соответствий имен:
     *                            * Ключ: имена классов, для которых создаются мок-классы
     *                            * Значение: строка с новым именем класса или TRUE - если имя необходимо сгенерировать автоматически
     */
    public function __construct(array $newNames)
    {
        $this->newNames = $newNames;
    }

    public function __invoke(ClassScheme $classesScheme, AbstractMockerOptions $mockerOptions): void
    {
        if (empty($this->newNames[$classesScheme->getFullName()])) return;

        // * * *

        $newNameValue = $this->newNames[$classesScheme->getFullName()];

        if ($newNameValue === true) $classesScheme->setFullName(ToolsElementNames::mockClassName(uniqid()));
        elseif (is_string($newNameValue)) $classesScheme->setFullName($newNameValue);
    }
}
