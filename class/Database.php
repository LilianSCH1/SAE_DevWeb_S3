<?php
class Database
{
    private const SERVER = 'localhost';
    private const BASE   = 'mypulse';
    private const USER   = 'root';
    private const PASS   = '';

    public static function getConnection(): PDO
    {
        $dsn = 'mysql:dbname=' . self::BASE . ';host=' . self::SERVER . ';charset=utf8';
        $pdo = new PDO($dsn, self::USER, self::PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
