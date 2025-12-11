<?php
session_start();

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = Database::getConnection();

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Vérifier la connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour voter.']);
    exit;
}

// Récupérer l'utilisateur
$currentUser = User::findById((int)$_SESSION['user_id']);
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit;
}

// Vérifier le rôle : uniquement certifiés et basique
$allowedRoles = ['certifie', 'basique'];
if (!in_array($currentUser->role, $allowedRoles, true)) {
    echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits de voter.']);
    exit;
}

// Générer ou récupérer le token unique navigateur
$token = $_COOKIE['vote_token'] ?? null;
if (!$token) {
    $token = bin2hex(random_bytes(32));
    setcookie('vote_token', $token, time() + (365 * 24 * 60 * 60), '/', '', false, true);
}

// Récupérer les données du POST
$typeContenu = $_POST['type_contenu'] ?? '';
$contenuID   = isset($_POST['contenu_id']) ? (int)$_POST['contenu_id'] : 0;
$mode        = $_POST['mode'] ?? 'vote'; // 'vote' ou 'delete'

// Valider
$allowedTypes = ['musique', 'chanteur', 'groupe'];
if (!in_array($typeContenu, $allowedTypes, true) || $contenuID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

/**
 * SUPPRESSION DU VOTE
 */
if ($mode === 'delete') {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM vote
            WHERE Token = :token AND TypeContenu = :type AND ContenuID = :id
        ");
        $stmt->execute([
            ':token' => $token,
            ':type'  => $typeContenu,
            ':id'    => $contenuID
        ]);
    } catch (PDOException $e) {
        error_log('Vote delete error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
        exit;
    }

    // Recalculer le total de cette carte
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM vote
        WHERE TypeContenu = :type AND ContenuID = :id
    ");
    $stmt->execute([
        ':type' => $typeContenu,
        ':id'   => $contenuID
    ]);
    $total = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'total'   => $total,
        'mode'    => 'deleted'
    ]);
    exit;
}

/**
 * AJOUT / CHANGEMENT DE VOTE
 */

// Protection double-clic : déjà voté pour CE contenu ?
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM vote
    WHERE Token = :token AND TypeContenu = :type AND ContenuID = :id
");
$stmt->execute([
    ':token' => $token,
    ':type'  => $typeContenu,
    ':id'    => $contenuID
]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Vous avez déjà voté pour cet élément.']);
    exit;
}

// Y a-t-il déjà un vote dans cette catégorie ?
$stmt = $pdo->prepare("
    SELECT VoteID, ContenuID FROM vote
    WHERE Token = :token AND TypeContenu = :type
");
$stmt->execute([
    ':token' => $token,
    ':type'  => $typeContenu
]);
$existingVote = $stmt->fetch(PDO::FETCH_ASSOC);

try {
    if ($existingVote) {
        // Changer de choix dans la même catégorie
        $stmt = $pdo->prepare("
            UPDATE vote
            SET ContenuID = :id, DateVote = NOW()
            WHERE VoteID = :voteId
        ");
        $stmt->execute([
            ':id'     => $contenuID,
            ':voteId' => (int)$existingVote['VoteID']
        ]);
    } else {
        // Aucun vote dans cette catégorie : INSERT
        $stmt = $pdo->prepare("
            INSERT INTO vote (Token, TypeContenu, ContenuID, DateVote, ValeurVote)
            VALUES (:token, :type, :id, NOW(), 1)
        ");
        $stmt->execute([
            ':token' => $token,
            ':type'  => $typeContenu,
            ':id'    => $contenuID
        ]);
    }
} catch (PDOException $e) {
    error_log('Vote save error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    exit;
}

// Recalculer le total de cette carte
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM vote
    WHERE TypeContenu = :type AND ContenuID = :id
");
$stmt->execute([
    ':type' => $typeContenu,
    ':id'   => $contenuID
]);
$total = (int)$stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'total'   => $total,
    'mode'    => 'added'
]);
exit;
