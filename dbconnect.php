<?php
define('SERVER', "localhost");
define('BASE', "mypulse");

function dbconnect($user = "root", $passwd = "")
{
  $dsn = "mysql:dbname=" . BASE . ";host=" . SERVER;
  try {
    $connexion = new PDO($dsn, $user, $passwd);
    $connexion->exec("set names utf8"); // Support UTF-8
    return $connexion;
  } catch (PDOException $e) {
    printf("Échec de la connexion : %s\n", $e->getMessage());
    exit();
  }
}

// Exemple d’utilisation
$connexion = dbconnect();
$connexionLilian = dbconnect("Lilian", "cmonmdp");
