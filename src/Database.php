<?php
namespace App;


class Database
{
    private static $instance = null;
    private $pdo;


    private function __construct()
    {
        $dbHost = 'localhost';
        $dbName = 'pos_midterm_project';
        $dbUser = 'root';
        $dbPass = '';
        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $dbUser, $dbPass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]);
    }


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}