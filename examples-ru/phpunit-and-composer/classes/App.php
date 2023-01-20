<?php

namespace DraculAid\PhpMockerExamples\Classes;

/**
 * Исполнитель тестового приложения
 */
class App
{
    /**
     * Исполняет тестовое приложение
     */
    public static function exe(): void
    {
        if (empty($_SERVER['argv'][1]))
        {
            echo "\n========= User List =========\n\n";
            echo self::loadAllUsersAndGetUserListString();

            echo "Run command for view user details: php index.php userId\n\n";
        }
        else
        {
            try {
                $userData = ModelUsers::getFromId($_SERVER['argv'][1]);
                echo self::userObjectToString($userData);
            }
            catch (\RuntimeException) {
                echo "User with ID {$_SERVER['argv'][1]} not found";
            }
        }
    }

    private static function loadAllUsersAndGetUserListString(): string
    {
        $_return = "#      NAME        ID\n------------------------\n";

        $users = array_values(ModelUsers::getList(0, 10));

        foreach ($users as $number => $userObj)
        {
            $_return .= '#' . ($number + 1) . sprintf('%+10s', $userObj->name) . "    {$userObj->id}\n";
        }

        $_return .= "------------------------\n";

        return $_return;
    }

    private static function userObjectToString(ModelUsers $modelUser): string
    {
        $_return = "=== User Info ===\n";
        $_return .= "ID:     {$modelUser->id}\n";
        $_return .= "Name:   {$modelUser->name}\n";
        $_return .= "Gender: {$modelUser->gender}\n";

        return "{$_return}\n";
    }
}
