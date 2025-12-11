<?php
require_once '../class/Database.php';

try {
    $pdo = Database::getConnection();

    // Générer des tokens pour les utilisateurs existants qui n'en ont pas
    $stmt = $pdo->prepare("
        UPDATE utilisateur
        SET Token = ?
        WHERE Token IS NULL OR Token = ''
    ");

    $token = bin2hex(random_bytes(32));
    $stmt->execute([$token]);

    echo "Tokens générés pour les utilisateurs existants.\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
