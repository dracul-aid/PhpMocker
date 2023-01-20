<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions\Creator;

/**
 * Исключение, используется, если мок-класс с помощью изменения PHP кода не удалось создать, так как PHP код не содержит код
 * для которого можно было бы создать мок-классы
 *
 * Оглавление:
 * @see HardMockClassCreatorPhpCodeWithoutElementsException::$phpCode - PHP код, который вызвал ошибку
 *
 * @todo Это же исключение будет отвечать за случаи отсутствия в PHP коде и функций и include() когда будут создаваться моки для них
 */
class HardMockClassCreatorPhpCodeWithoutElementsException extends AbstractMockClassCreateFailException
{
    /**
     * PHP код, который вызвал ошибку
     */
    public string $phpCode;

    public function __construct(string $phpCode)
    {
        parent::__construct("PHP code without classes");

        $this->phpCode = $phpCode;
    }
}
