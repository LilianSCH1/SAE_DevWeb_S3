<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../database/dbconnect.php';
require_once __DIR__ . '/../class/User.php';

$voteToken = $_COOKIE['vote_token'] ?? null;
$pdo = dbconnect();

// --- AJOUT : traitement archivage musique ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_musique'])) {
    // Vérifier que l'utilisateur est admin
    $currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
    if (!$currentUser || $currentUser->role !== 'admin') {
        http_response_code(403);
        exit('Accès refusé.');
    }

    $musiqueId = isset($_POST['musique_id']) ? (int)$_POST['musique_id'] : 0;

    if ($musiqueId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE musique SET StatusMusique = 'archive_suppr' WHERE MusiqueID = :id LIMIT 1");
            $stmt->execute([':id' => $musiqueId]);
        } catch (Exception $e) {
            // Optionnel : journaliser l'erreur
        }
    }

    // Redirection pour éviter resoumission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// --- AJOUT : traitement archivage artiste ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_artiste'])) {
    // Vérifier que l'utilisateur est admin
    $currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
    if (!$currentUser || $currentUser->role !== 'admin') {
        http_response_code(403);
        exit('Accès refusé.');
    }

    $artisteId = isset($_POST['artiste_id']) ? (int)$_POST['artiste_id'] : 0;

    if ($artisteId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE artiste SET StatusArtiste = 'archive_suppr' WHERE ArtisteID = :id LIMIT 1");
            $stmt->execute([':id' => $artisteId]);
        } catch (Exception $e) {
            // Optionnel : log
        }
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// --- AJOUT : traitement archivage groupe ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_groupe'])) {
    // Vérifier que l'utilisateur est admin
    $currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
    if (!$currentUser || $currentUser->role !== 'admin') {
        http_response_code(403);
        exit('Accès refusé.');
    }

    $groupeId = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

    if ($groupeId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE groupe SET StatusGroupe = 'archive_suppr' WHERE GroupeID = :id LIMIT 1");
            $stmt->execute([':id' => $groupeId]);
        } catch (Exception $e) {
            // Optionnel : log
        }
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}


// Vérifier le rôle de l'utilisateur
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
$userCanCreate = $currentUser && in_array($currentUser->role, ['certifie', 'admin']);

// Recherche
$searchQuery = trim($_GET['q'] ?? '');
$searchCondition = '';
$params = [];

if (!empty($searchQuery)) {
    $searchCondition = " AND (Titre LIKE :search OR Artiste LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

// Affichage des données de cartes de musiques
$musiques = $pdo->prepare("
    SELECT MusiqueID,
           Titre,
           Artiste,
           ImageCouverture,
           CheminFichierMP3,
           AnneePublication as DateAffichee,
           NombreVotes
    FROM musique
    WHERE StatusMusique = 'valide'" . $searchCondition . "
    ORDER BY DateAffichee DESC
");
$musiques->execute($params);
$musiques = $musiques->fetchAll(PDO::FETCH_ASSOC);

// Recherche pour artistes
$searchConditionArtiste = '';
$paramsArtiste = [];

if (!empty($searchQuery)) {
    $searchConditionArtiste = " AND NomArtiste LIKE :search";
    $paramsArtiste[':search'] = '%' . $searchQuery . '%';
}

// Affichage des données de cartes d'artistes
$artistes = $pdo->prepare("
    SELECT ArtisteID,
           NomArtiste,
           BiographieCourte,
           ImageProfil,
           CheminFichierMP3,
           AnneeNaissance as DateAffichee,
           NombreVotes
    FROM artiste
    WHERE StatusArtiste = 'valide'" . $searchConditionArtiste . "
    ORDER BY DateAffichee DESC
");
$artistes->execute($paramsArtiste);
$artistes = $artistes->fetchAll(PDO::FETCH_ASSOC);

// Recherche pour groupes
$searchConditionGroupe = '';
$paramsGroupe = [];

if (!empty($searchQuery)) {
    $searchConditionGroupe = " AND NomGroupe LIKE :search";
    $paramsGroupe[':search'] = '%' . $searchQuery . '%';
}

// Affichage des données de cartes de groupes
$groupes = $pdo->prepare("
    SELECT GroupeID,
           NomGroupe,
           BiographieCourte,
           ImageGroupe,
           CheminFichierMP3,
           AnneeFormation as DateAffichee,
           NombreVotes
    FROM groupe
    WHERE StatusGroupe = 'valide'" . $searchConditionGroupe . "
    ORDER BY DateAffichee DESC
");
$groupes->execute($paramsGroupe);
$groupes = $groupes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Voter - MyPulse</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/cards.css">
</head>

<body>
    <?php require '../index/header.php'; ?>

    <section class="py-5" style="margin-top:80px; min-height:80vh;">
        <div class="container">

            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Vote</span>
                <h2 class="section-title">Choisissez vos artistes, musiques et groupes préférés</h2>
            </div>

            <!-- Barre de recherche -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <form method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control search-input" placeholder="Rechercher des musiques, artistes ou groupes..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            <button class="btn btn-primary search-btn" type="submit">
                                <i class="bi bi-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <ul class="nav nav-pills justify-content-center mb-4" id="voteTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-musique" data-bs-toggle="pill"
                        data-bs-target="#pane-musique" type="button" role="tab">Musiques</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-artiste" data-bs-toggle="pill"
                        data-bs-target="#pane-artiste" type="button" role="tab">Artistes</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-groupe" data-bs-toggle="pill"
                        data-bs-target="#pane-groupe" type="button" role="tab">Groupes</button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- MUSIQUES -->
                <div class="tab-pane fade show active" id="pane-musique" role="tabpanel" aria-labelledby="tab-musique">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_musique.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer une musique
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require '../vote/vote_cards_musique.php'; ?>
                    </div>
                </div>

                <!-- ARTISTES -->
                <div class="tab-pane fade" id="pane-artiste" role="tabpanel" aria-labelledby="tab-artiste">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_artiste.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer un artiste
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require '../vote/vote_cards_artiste.php'; ?>
                    </div>
                </div>

                <!-- GROUPES -->
                <div class="tab-pane fade" id="pane-groupe" role="tabpanel" aria-labelledby="tab-groupe">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_groupe.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer un groupe
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require '../vote/vote_cards_groupe.php'; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>

    <!-- Cookie Pop-up -->
    <div id="cookie-popup" class="cookie-popup">
        <div class="cookie-popup-content">
            <div class="cookie-popup-header">
                <h5>🍪 Gestion des Cookies</h5>
                <button type="button" class="btn-close" aria-label="Fermer" onclick="closeCookiePopup()"></button>
            </div>
            <div class="cookie-popup-body">
                <h6>TYPES DE COOKIES UTILISÉS</h6>
                <p>Nous utilisons différents types de cookies pour améliorer votre expérience sur MyPulse :</p>
                <ul>
                    <li><strong>Cookies essentiels :</strong> Indispensables au fonctionnement, ils gèrent l'authentification, les votes uniques par catégorie et les sessions utilisateur. Aucun consentement n'est requis.</li>
                    <li><strong>Cookies analytiques :</strong> Anonymes, ils mesurent l'audience (pages vues, classements consultés) pour optimiser la plateforme. Consentement préalable via notre bandeau.</li>
                    <li><strong>Cookies fonctionnels :</strong> Personnalisent l'interface (thèmes sombre/clair, notifications) et intègrent les partages sociaux pour les résultats de concours. Aucun cookie publicitaire tiers n'est utilisé ; durée maximale de 6 mois, renouvelable avec consentement.</li>
                </ul>

                <h6>GESTION ET CONSENTEMENT</h6>
                <p>Lors de votre première visite, un bandeau collecte votre consentement exprès pour les cookies non essentiels. Modifiez vos préférences via l'icône en bas d'écran ou les paramètres de votre navigateur. Refuser les cookies analytiques n'empêche pas l'accès aux votes ou classements.</p>

                <h6>VOS DROITS</h6>
                <p>Conformément au RGPD, contactez mypulse.company@gmail.com pour accéder, rectifier ou supprimer les données cookies.</p>
            </div>
            <div class="cookie-popup-footer">
                <button type="button" class="btn btn-outline-primary me-2" onclick="manageCookiePreferences()">Gérer les préférences</button>
                <button type="button" class="btn btn-outline-secondary me-2" onclick="rejectNonEssentialCookies()">Refuser non-essentiels</button>
                <button type="button" class="btn btn-primary" onclick="acceptAllCookies()">Accepter tout</button>
            </div>
        </div>
    </div>

    <audio id="vote-audio-player"></audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/modals.js"></script>
    <script src="../script/script.js"></script>
</body>

</html>