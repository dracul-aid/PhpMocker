<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Filters\Storages;

/**
 * Хранилище списка пространств имен для вариантов фильтров "определения, какие классы нужно преобразовывать в мок-классы"
 *
 * Оглавление:
 * @see self::add() - Добавить в фильтр новое пространство имен
 * @see self::addList() - Добавить в фильтр новые пространства имен из массива
 * @see self::remove() - Удалит из фильтра пространство имен
 * @see self::removeList() - Удалит из фильтра пространства имен переданные в массиве
 * @see self::in() - Проверит, есть ли пространство имен в хранилище
 * @see self::getStorageData() - Вернет массив со всеми пространствами имен хранимыми в хранилище
 */
class AutoloaderFilerNamespaceStorage extends AbstractAutoloaderFilerStorage
{
    /**
     * Дерево частей пространства имен
     *
     * @var array
     */
    private array $tree = [];

    public function add(string $addValue): AbstractAutoloaderFilerStorage
    {
        $namespaceParts = explode('\\', $addValue);
        $nowBranch = &$this->tree;
        foreach ($namespaceParts as $name)
        {
            if (!isset($nowBranch[$name])) $nowBranch[$name] = [];
            $nowBranch = &$nowBranch[$name];
        }
        $nowBranch = true;

        return $this;
    }

    public function remove(string $removeValue): AbstractAutoloaderFilerStorage
    {
        $namespaceParts = explode('\\', $removeValue);
        $this->removeTreeBranch($this->tree, $namespaceParts);

        return $this;
    }

    public function in(string $value): bool
    {
        $namespaceParts = explode('\\', $value);
        $nowBranch = &$this->tree;

        foreach ($namespaceParts as $name)
        {
            if (empty($nowBranch[$name]))
            {
                return false;
            }
            elseif (is_array($nowBranch[$name]))
            {
                $nowBranch = &$nowBranch[$name];
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    public function getStorageData(): array
    {
        $_return = [];
        $this->treeBranchToArray('', $this->tree, $_return);

        return $_return;
    }

    /**
     * Удаляет из дерева (частей пространства имен) нужную ветку
     *
     * @param   array     &$branch            Ссылка на удаляемую ветку дерева частей пространства имен
     * @param   string[]   $namespaceParts    Массив частей удаляемого из дерева пространства имен
     *
     * @return  void
     */
    private function removeTreeBranch(array &$branch, array $namespaceParts): void
    {
        $nowIndex = array_shift($namespaceParts);

        if (!empty($branch[$nowIndex]) && is_array($branch[$nowIndex]))
        {
            $this->removeTreeBranch($branch[$nowIndex], $namespaceParts);
        }

        unset($branch[$nowIndex]);
    }

    /**
     * Пройдет по переданным ветвям дерева (частей пространства имен) и создаст массив с всеми пространствами имен
     *
     * @param   string   $tmpString     Накопленная часть пространства имен для текущей ветви дерева
     * @param   array    $treeBranch    Массив-ветвь, часть дерева пространства имен
     * @param   array   &$result        Массив для накопления результатов преобразования
     *
     * @return void
     */
    private function treeBranchToArray(string $tmpString, array $treeBranch, array &$result): void
    {
        foreach ($treeBranch as $name => $branch)
        {
            $index = ($tmpString ? "{$tmpString}\\" : '') . $name;

            if (is_array($branch))
            {
                $this->treeBranchToArray($index, $branch, $result);
            }
            else
            {
                $result[$index] = $index;
            }
        }
    }
}
