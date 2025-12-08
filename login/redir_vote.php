<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    // Connecté → accès direct à la page de vote
    header('Location: ../vote/voter.php');
    exit;
}

// Pas connecté → redirection vers la connexion avec message
header('Location: connexion.php?message=' . urlencode('Vous devez vous connecter à un compte pour pouvoir voter.'));
exit;
