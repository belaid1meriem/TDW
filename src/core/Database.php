<?php

namespace Core;

class Database {
    private static ?\PDO $connection = null;

    private function __construct(){}

    public static function getConnection(): \PDO
    {
        if (self::$connection === null) {
            $config = Config::database();

            try {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
                self::$connection = new \PDO($dsn, $config['username'], $config['password']);
                self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                die("DATABASE CONNECTION FAILED: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
