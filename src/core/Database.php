<?php

namespace Core;

class Database{
    private static ?\PDO $connection = null;

    private function __construct(){}

    public static function getConnection(): \PDO
    {
        if ( self::$connection === null )
        {
            $config = Config::database();
            
            try {
                $dsn = "mysql:host=". $config["host"].";dbname=". $config["dbname"];
                self::$connection = new \PDO($dsn, $config["username"], $config["password"]);
            } catch (\PDOException $e) {
                die("DATABASE CONNECTION FAILED");
            }
        }
        return self::$connection;
    }
    
}