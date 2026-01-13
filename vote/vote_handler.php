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
if ($currentUser->role === 'admin') {
    echo json_encode(['success' => false, 'message' => 'Les admins ne peuvent pas voter.']);
    exit;
}

// Vérifier le rôle : uniquement certifiés et basique
$allowedRoles = ['certifie', 'basique'];
if (!in_array($currentUser->role, $allowedRoles, true)) {
    echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits de voter.']);
    exit;
}

// Utiliser session_id() pour anonymiser les votes
$token = session_id();
if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Session invalide.']);
    exit;
}

// Récupérer les données du POST
$typeContenu = $_POST['type_contenu'] ?? '';
$contenuID   = isset($_POST['contenu_id']) ? (int)$_POST['contenu_id'] : 0;
$mode        = $_POST['mode'] ?? 'vote'; // 'vote' ou 'delete'

// Debug logging
error_log("Vote request: mode=$mode, type=$typeContenu, id=$contenuID, user_id=" . ($_SESSION['user_id'] ?? 'none') . ", token=$token");

// Valider
$allowedTypes = ['musique', 'chanteur', 'groupe'];
if (!in_array($typeContenu, $allowedTypes, true) || $contenuID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$table = match($typeContenu) {
    'musique' => 'musique',
    'chanteur' => 'artiste',
    'groupe' => 'groupe',
};

$idColumn = match($typeContenu) {
    'musique' => 'MusiqueID',
    'chanteur' => 'ArtisteID',
    'groupe' => 'GroupeID',
};

/**
 * SUPPRESSION DU VOTE
 * (l'utilisateur clique sur "Supprimer mon vote")
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

    // Recalculer le total de votes pour ce contenu
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM vote
        WHERE TypeContenu = :type AND ContenuID = :id
    ");
    $stmt->execute([
        ':type' => $typeContenu,
        ':id'   => $contenuID
    ]);
    $total = (int)$stmt->fetchColumn();

    // Mettre à jour NombreVotes dans la table correspondante
    try {
        $stmt = $pdo->prepare("
            UPDATE {$table}
            SET NombreVotes = :total
            WHERE {$idColumn} = :id
        ");
        $stmt->execute([
            ':total' => $total,
            ':id'    => $contenuID
        ]);
    } catch (PDOException $e) {
        error_log('Update NombreVotes error (delete): ' . $e->getMessage());
        // Ne pas échouer la requête principale pour une erreur de mise à jour
    }

    echo json_encode([
        'success' => true,
        'total'   => $total,
        'mode'    => 'deleted'
    ]);
    exit;
}

/**
 * AJOUT DU VOTE
 * (l'utilisateur clique sur "❤ Voter ...")
 */

// Vérifier s'il existe déjà un vote dans cette catégorie pour ce token
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
        // If changing to a different content, decrement old count
        if ($existingVote['ContenuID'] != $contenuID) {
            $stmt = $pdo->prepare("
                UPDATE {$table}
                SET NombreVotes = NombreVotes - 1
                WHERE {$idColumn} = :old_id
            ");
            $stmt->execute([':old_id' => $existingVote['ContenuID']]);
        }

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
        // Aucun vote dans cette catégorie pour ce token : INSERT
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

// Recalculer le total de votes pour ce contenu
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM vote
    WHERE TypeContenu = :type AND ContenuID = :id
");
$stmt->execute([
    ':type' => $typeContenu,
    ':id'   => $contenuID
]);
$total = (int)$stmt->fetchColumn();

// Mettre à jour NombreVotes dans la table correspondante
try {
    $stmt = $pdo->prepare("
        UPDATE {$table}
        SET NombreVotes = :total
        WHERE {$idColumn} = :id
    ");
    $stmt->execute([
        ':total' => $total,
        ':id'    => $contenuID
    ]);
} catch (PDOException $e) {
    error_log('Update NombreVotes error (add): ' . $e->getMessage());
    // Ne pas échouer la requête principale pour une erreur de mise à jour
}

echo json_encode([
    'success' => true,
    'total'   => $total,
    'mode'    => 'added'
]);
exit;
