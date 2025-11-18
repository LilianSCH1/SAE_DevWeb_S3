<?php 
define('USER',"Lilian"); 
define('PASSWD',"cmonmdp"); 
define('SERVER',"localhost"); 
define('BASE',"mypulse"); 


function dbconnect(){ 
  $dsn="mysql:dbname=".BASE.";host=".SERVER; 
  try{ 
    $connexion=new PDO($dsn,USER,PASSWD); 
    $connexion->exec("set names utf8"); //Support utf8
  } 
  catch(PDOException $e){ 
    printf("Échec de la connexion: %s\n", $e->getMessage()); 
    exit(); 
  } 
  return $connexion; 
} 
?> 