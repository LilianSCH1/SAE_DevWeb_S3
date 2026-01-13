<?php
require_once '../class/Database.php';
$pdo = Database::getConnection();

$next = $pdo->query("SELECT IFNULL(MAX(UserID),0)+1 FROM utilisateur")->fetchColumn();
$pdo->exec("ALTER TABLE utilisateur AUTO_INCREMENT = " . (int)$next);

echo "AUTO_INCREMENT remis à $next";
