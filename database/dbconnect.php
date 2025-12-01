<?php
define('SERVER', "localhost");
define('BASE', "mypulse");

function dbconnect($user = "root", $passwd = "")
{
  $dsn = "mysql:dbname=" . BASE . ";host=" . SERVER;
  try {
    $connexion = new PDO($dsn, $user, $passwd);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->exec("SET NAMES utf8");

    return $connexion;
  } catch (PDOException $e) {
    printf("Échec de la connexion : %s\n", $e->getMessage());
    exit();
  }
}
