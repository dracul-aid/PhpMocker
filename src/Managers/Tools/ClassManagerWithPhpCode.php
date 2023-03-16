<?php

namespace DraculAid\PhpMocker\Managers\Tools;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Расширение класса "менеджера мок-классов" для случаев, когда "создать мок-классов" не создал мок-класс, а "вернул"
 * PHP код с кодом класса
 *
 * Используется в автозагрузчике классов @see Autoloader
 * Автозагрузчик классов получает PHP код мок-класса и кеширует его, поэтому создание мок-класса происходит "отложено"
 *
 * Оглавление:
 * @see self::$createPhpCode - Хранит PHP код, который необходимо выполнить для создания мок-класса
 * @see self::evalPhpMockClassCode() - Выполняет создание мок-класса и регистрирует менеджер в списке менеджеров
 * > Наследует элементы @see ClassManager <
 */
class ClassManagerWithPhpCode extends ClassManager
{
    /**
     * Хранит PHP код, который необходимо выполнить для создания мок-класса
     * (если хранит '' - значит мок-класс уже создан)
     */
    public string $createPhpCode = '';

    /**
     * Выполняет создание мок-класса и регистрирует менеджер в списке менеджеров
     *
     * @return void
     */
    public function evalPhpMockClassCode(): void
    {
        if ($this->createPhpCode === '') return;

        eval($this->createPhpCode);
        $this->createPhpCode = '';

        if ($this->classType !== ClassSchemeType::INTERFACES()) $this->registerInManagerListExecuting();
    }

    /**
     * Этот метод должен "Регистрировать созданный "менеджер мок-классов" в списке всех созданных менеджеров"
     * Но эта регистрация имеет смысл, только после создания мок-класса
     *
     * Выполнением создания мок-класса занимается @see
     *
     * @return void
     */
    protected function registerInManagerList(): void {}
}
