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

use DraculAid\PhpMocker\Creator\MockerOptions\AbstractMockerOptions;
use DraculAid\PhpMocker\Creator\ToolsElementNames;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Используется для создания мок-классов с определенным именем
 */
class ClassName implements CreateOptionsInterface
{
    /**
     * @var bool|string
     *    TRUE - Имя будет создано автоматически
     *    string - Явно указанное имя
     */
    private $newName;

    /**
     * @param   bool|string   $newName   Имя создаваемого мок класса
     *                                   * TRUE: Имя будет создано автоматически
     *                                   * string: Явно указанное имя
     */
    public function __construct($newName)
    {
        $this->newName = $newName;
    }

    public function __invoke(ClassScheme $classesScheme, AbstractMockerOptions $mockerOptions): void
    {
        if ($this->newName === true) $classesScheme->setFullName(ToolsElementNames::mockClassName(uniqid()));
        elseif (is_string($this->newName)) $classesScheme->setFullName($this->newName);
    }
}
