<?php
session_start();

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/connexion.php');
    exit;
}

$currentUser = User::findById((int)$_SESSION['user_id']);
if (!$currentUser || $currentUser->role !== 'admin') {
    header('Location: ../index/index.php');
    exit;
}

require '../database/dbconnect.php';
$pdo = dbconnect();

// Traitement des actions de validation/refus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if (($action === 'valider' || $action === 'refuser') && $id > 0) {
        if ($type === 'artiste') {
            // adapte ici selon ton type de colonne (INT ou ENUM)
            $newStatus = $action === 'valider' ? 'valide' : 'refusee';
            $stmt = $pdo->prepare("UPDATE artiste SET StatusArtiste = ? WHERE ArtisteID = ?");
            $stmt->execute([$newStatus, $id]);
        } elseif ($type === 'musique') {
            $newStatus = $action === 'valider' ? 'valide' : 'refusee';
            $stmt = $pdo->prepare("UPDATE musique SET StatusMusique = ? WHERE MusiqueID = ?");
            $stmt->execute([$newStatus, $id]);
        } elseif ($type === 'groupe') {
            $newStatus = $action === 'valider' ? 'valide' : 'refusee';
            $stmt = $pdo->prepare("UPDATE groupe SET StatusGroupe = ? WHERE GroupeID = ?");
            $stmt->execute([$newStatus, $id]);
        }

        // Pour éviter le repost en cas de F5
        header('Location: dashboard_admin.php');
        exit;
    }
}

// Récupération des contenus en attente
$artistes = $pdo->query("
    SELECT ArtisteID, NomArtiste, NomReel, BiographieCourte, ImageProfil, CheminFichierMP3, DateProposition, AnneeNaissance
    FROM artiste
    WHERE StatusArtiste IS NULL OR StatusArtiste = 'enattente'
    ORDER BY DateProposition DESC
")->fetchAll(PDO::FETCH_ASSOC);

$musiques = $pdo->query("
    SELECT MusiqueID, Titre, Artiste, ImageCouverture, CheminFichierMP3, DateProposition, AnneePublication
    FROM musique
    WHERE StatusMusique = 'enattente'
    ORDER BY DateProposition DESC
")->fetchAll(PDO::FETCH_ASSOC);

$groupes = $pdo->query("
    SELECT GroupeID, NomGroupe, AnneeFormation, BiographieCourte, ImageGroupe, CheminFichierMP3, DateProposition
    FROM groupe
    WHERE StatusGroupe = 'enattente'
    ORDER BY DateProposition DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/dashboard-cards.css">
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
            <span class="section-subtitle">Administration</span>
            <h2 class="section-title">Dashboard - Contenus en attente</h2>
        </div>

        <ul class="nav nav-pills justify-content-center mb-4" id="dashboardTabs" role="tablist">
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
                <div class="card-list">
                    <?php if (empty($musiques)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted text-nowrap">Aucune musique en cours de validation</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($musiques as $musique): ?>
                            <div class="content-card">
                                <div class="content-card-header">
                                    <img src="../create/<?php echo htmlspecialchars($musique['ImageCouverture']); ?>" alt="Couverture" class="content-card-image">
                                    <div class="content-card-info">
                                        <h3 class="content-card-title"><?php echo htmlspecialchars($musique['Titre']); ?></h3>
                                        <p class="content-card-artist"><?php echo htmlspecialchars($musique['Artiste']); ?></p>
                                        <p class="content-card-date">Proposé le: <?php echo htmlspecialchars($musique['DateProposition']); ?></p>
                                    </div>
                                </div>
                                <div class="content-card-body">
                                    <audio controls class="content-card-audio">
                                        <source src="../create/<?php echo htmlspecialchars($musique['CheminFichierMP3']); ?>" type="audio/mpeg">
                                    </audio>
                                </div>
                                <div class="content-card-actions">
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="valider">
                                        <input type="hidden" name="type" value="musique">
                                        <input type="hidden" name="id" value="<?php echo $musique['MusiqueID']; ?>">
                                        <button type="submit" class="btn btn-success">Valider</button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="refuser">
                                        <input type="hidden" name="type" value="musique">
                                        <input type="hidden" name="id" value="<?php echo $musique['MusiqueID']; ?>">
                                        <button type="submit" class="btn btn-danger">Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ARTISTES -->
            <div class="tab-pane fade" id="pane-artiste" role="tabpanel" aria-labelledby="tab-artiste">
                <div class="card-list">
                    <?php if (empty($artistes)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted text-nowrap">Aucun artiste en cours de validation</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($artistes as $artiste): ?>
                            <div class="content-card">
                                <div class="content-card-header">
                                    <img src="../create/<?php echo htmlspecialchars($artiste['ImageProfil']); ?>" alt="Profil" class="content-card-image">
                                    <div class="content-card-info">
                                        <h3 class="content-card-title"><?php echo htmlspecialchars($artiste['NomArtiste']); ?></h3>
                                        <p class="content-card-description"><?php echo htmlspecialchars($artiste['BiographieCourte']); ?></p>
                                        <p class="content-card-date">Proposé le: <?php echo htmlspecialchars($artiste['DateProposition']); ?></p>
                                    </div>
                                </div>
                                <div class="content-card-body">
                                    <audio controls class="content-card-audio">
                                        <source src="../create/<?php echo htmlspecialchars($artiste['CheminFichierMP3']); ?>" type="audio/mpeg">
                                    </audio>
                                </div>
                                <div class="content-card-actions">
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="valider">
                                        <input type="hidden" name="type" value="artiste">
                                        <input type="hidden" name="id" value="<?php echo $artiste['ArtisteID']; ?>">
                                        <button type="submit" class="btn btn-success">Valider</button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="refuser">
                                        <input type="hidden" name="type" value="artiste">
                                        <input type="hidden" name="id" value="<?php echo $artiste['ArtisteID']; ?>">
                                        <button type="submit" class="btn btn-danger">Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- GROUPES -->
            <div class="tab-pane fade" id="pane-groupe" role="tabpanel" aria-labelledby="tab-groupe">
                <div class="card-list">
                    <?php if (empty($groupes)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted text-nowrap">Aucun groupe en cours de validation</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groupes as $groupe): ?>
                            <div class="content-card">
                                <div class="content-card-header">
                                    <img src="../create/<?php echo htmlspecialchars($groupe['ImageGroupe']); ?>" alt="Groupe" class="content-card-image">
                                    <div class="content-card-info">
                                        <h3 class="content-card-title"><?php echo htmlspecialchars($groupe['NomGroupe']); ?></h3>
                                        <p class="content-card-description"><?php echo htmlspecialchars($groupe['BiographieCourte']); ?></p>
                                        <p class="content-card-date">Proposé le: <?php echo htmlspecialchars($groupe['DateProposition']); ?></p>
                                    </div>
                                </div>
                                <div class="content-card-body">
                                    <audio controls class="content-card-audio">
                                        <source src="../create/<?php echo htmlspecialchars($groupe['CheminFichierMP3']); ?>" type="audio/mpeg">
                                    </audio>
                                </div>
                                <div class="content-card-actions">
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="valider">
                                        <input type="hidden" name="type" value="groupe">
                                        <input type="hidden" name="id" value="<?php echo $groupe['GroupeID']; ?>">
                                        <button type="submit" class="btn btn-success">Valider</button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="refuser">
                                        <input type="hidden" name="type" value="groupe">
                                        <input type="hidden" name="id" value="<?php echo $groupe['GroupeID']; ?>">
                                        <button type="submit" class="btn btn-danger">Refuser</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require '../index/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../script/modals.js"></script>
<script src="../script/script.js"></script>
</body>
</html>
