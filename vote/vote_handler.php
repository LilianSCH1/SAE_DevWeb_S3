<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../database/dbconnect.php';
require_once '../class/User.php';

$pdo = dbconnect();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour voter.']);
    exit;
}

$currentUser = User::findById((int)$_SESSION['user_id']);
if (!$currentUser || !$currentUser->token) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé ou token manquant.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Récupérer les données du vote
$typeContenu = $_POST['type_contenu'] ?? '';
$contenuID = (int)($_POST['contenu_id'] ?? 0);
$valeurVote = 1; // Vote positif par défaut

// Valider les données
$validTypes = ['musique', 'chanteur', 'groupe'];
if (!in_array($typeContenu, $validTypes) || $contenuID <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données de vote invalides.']);
    exit;
}

// Vérifier si l'utilisateur a déjà voté pour ce contenu
$stmt = $pdo->prepare("SELECT VoteID FROM vote WHERE Token = ? AND TypeContenu = ? AND ContenuID = ?");
$stmt->execute([$currentUser->token, $typeContenu, $contenuID]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Vous avez déjà voté pour ce contenu.']);
    exit;
}

// Insérer le vote
$stmt = $pdo->prepare("INSERT INTO vote (Token, TypeContenu, ContenuID, ValeurVote) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$currentUser->token, $typeContenu, $contenuID, $valeurVote])) {
    // Mettre à jour le compteur dans la table resultat si nécessaire
    $stmtUpdate = $pdo->prepare("
        INSERT INTO resultat (TypeContenu, ContenuID, TotalVotes)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE TotalVotes = TotalVotes + 1
    ");
    $stmtUpdate->execute([$typeContenu, $contenuID]);

    echo json_encode(['success' => true, 'message' => 'Vote enregistré avec succès.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du vote.']);
}
?>
