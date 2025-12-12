<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

$pdo = Database::getConnection();

$q = trim($_GET['q'] ?? '');

$params = [];
$searchCondition = '';
if ($q !== '') {
    $searchCondition = " AND (Titre LIKE :s OR Artiste LIKE :s)";
    $params[':s'] = '%' . $q . '%';
}

$stmt = $pdo->prepare("SELECT MusiqueID, Titre, Artiste, ImageCouverture, CheminFichierMP3, AnneePublication as DateAffichee FROM musique WHERE StatusMusique IN ('valide', 'en_attente')" . $searchCondition . " ORDER BY DateAffichee DESC");
$stmt->execute($params);
$musiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

$paramsArt = [];
$searchConditionArt = '';
if ($q !== '') {
    $searchConditionArt = " AND NomArtiste LIKE :s";
    $paramsArt[':s'] = '%' . $q . '%';
}
$stmt = $pdo->prepare("SELECT ArtisteID, NomArtiste, BiographieCourte, ImageProfil, CheminFichierMP3, AnneeNaissance as DateAffichee FROM artiste WHERE StatusArtiste IN ('valide', 'en_attente')" . $searchConditionArt . " ORDER BY DateAffichee DESC");
$stmt->execute($paramsArt);
$artistes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$paramsGrp = [];
$searchConditionG = '';
if ($q !== '') {
    $searchConditionG = " AND NomGroupe LIKE :s";
    $paramsGrp[':s'] = '%' . $q . '%';
}
$stmt = $pdo->prepare("SELECT GroupeID, NomGroupe, BiographieCourte, ImageGroupe, CheminFichierMP3, AnneeFormation as DateAffichee FROM groupe WHERE StatusGroupe IN ('valide', 'en_attente')" . $searchConditionG . " ORDER BY DateAffichee DESC");
$stmt->execute($paramsGrp);
$groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Capture HTML from partials
ob_start();
require __DIR__ . '/vote_cards_musique.php';
$musiqueHtml = ob_get_clean();

ob_start();
require __DIR__ . '/vote_cards_artiste.php';
$artisteHtml = ob_get_clean();

ob_start();
require __DIR__ . '/vote_cards_groupe.php';
$groupeHtml = ob_get_clean();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'musiques' => $musiqueHtml,
    'artistes' => $artisteHtml,
    'groupes'  => $groupeHtml,
]);

exit;
