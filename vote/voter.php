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
           AnneePublication as DateAffichee
    FROM musique
    WHERE StatusMusique IN ('valide', 'en_attente')" . $searchCondition . "
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
           AnneeNaissance as DateAffichee
    FROM artiste
    WHERE StatusArtiste IN ('valide', 'en_attente')" . $searchConditionArtiste . "
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
           AnneeFormation as DateAffichee
    FROM groupe
    WHERE StatusGroupe IN ('valide', 'en_attente')" . $searchConditionGroupe . "
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

    <link rel="icon" type="image/png" href="../icons/logos/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="../icons/logos/favicon.svg">
    <link rel="shortcut icon" href="../icons/logos/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../icons/logos/apple-touch-icon.png">
    <link rel="manifest" href="../icons/logos/site.webmanifest">
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

    <audio id="vote-audio-player"></audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/modals.js"></script>
    <script src="../script/script.js"></script>
</body>

</html>