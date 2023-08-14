<?php

/**
 * Класс для управления подключением к базе данных.
 */
class DB
{
    private static $host     = 'your_host';
    private static $dbname   = 'your_dbname';
    private static $username = 'your_username';
    private static $password = 'your_password';

    /**
     * Получение подключения к базе данных.
     *
     * @return PDO Возвращает объект PDO для работы с базой данных.
     */
    public static function getConnection()
    {
        $dsn = "mysql:hostname=" . self::$host . ";dbname=" . self::$dbname;

        $pdo = new PDO($dsn, self::$username, self::$password);

        // Установка режима обработки ошибок
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
