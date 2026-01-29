<?php
class Database
{
    private const SERVER = 'db';
    private const BASE   = 'mypulse';
    private const USER   = 'mypulse_user';
    private const PASS   = 'mypulse_pass';

    public static function getConnection(): PDO
    {
        $dsn = 'mysql:dbname=' . self::BASE . ';host=' . self::SERVER . ';charset=utf8';
        $pdo = new PDO($dsn, self::USER, self::PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}