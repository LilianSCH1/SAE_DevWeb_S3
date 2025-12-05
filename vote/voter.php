<?php
// En haut du fichier, démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../icons/logos/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="../icons/logos/favicon.svg">
    <link rel="shortcut icon" href="../icons/logos/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../icons/logos/apple-touch-icon.png">
    <link rel="manifest" href="../icons/logos/site.webmanifest">
</head>
<body>
<?php require '../index/header.php'; ?>

<!-- Section de vote -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-subtitle">Votez maintenant</span>
            <h2 class="section-title">Votez pour vos contenus préférés</h2>
            <p class="section-description">Choisissez votre catégorie et votez pour vos musiques, artistes ou groupes favoris</p>
        </div>

        <!-- Onglets de catégories -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <ul class="nav nav-pills justify-content-center" id="voteTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="musics-tab" data-bs-toggle="pill" data-bs-target="#musics-vote" type="button" role="tab">Musiques</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="artists-tab" data-bs-toggle="pill" data-bs-target="#artists-vote" type="button" role="tab">Artistes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="groups-tab" data-bs-toggle="pill" data-bs-target="#groups-vote" type="button" role="tab">Groupes</button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="voteTabsContent">
            <!-- Musiques -->
            <div class="tab-pane fade show active" id="musics-vote" role="tabpanel">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-center mb-4">
                        <a href="../create/creer_musique.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter une musique
                        </a>
                    </div>
                <?php endif; ?>
                <div class="row g-4" id="musics-vote-grid">
                    <!-- Les cartes de musique seront générées dynamiquement -->
                </div>
            </div>

            <!-- Artistes -->
            <div class="tab-pane fade" id="artists-vote" role="tabpanel">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-center mb-4">
                        <a href="../create/creer_artiste.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un artiste
                        </a>
                    </div>
                <?php endif; ?>
                <div class="row g-4" id="artists-vote-grid">
                    <!-- Les cartes d'artistes seront générées dynamiquement -->
                </div>
            </div>

            <!-- Groupes -->
            <div class="tab-pane fade" id="groups-vote" role="tabpanel">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-center mb-4">
                        <a href="../create/creer_groupe.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Ajouter un groupe
                        </a>
                    </div>
                <?php endif; ?>
                <div class="row g-4" id="groups-vote-grid">
                    <!-- Les cartes de groupes seront générées dynamiquement -->
                </div>
            </div>
        </div>
    </div>
</section>

<?php require '../index/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../script/script.js"></script>
</body>
</html>
