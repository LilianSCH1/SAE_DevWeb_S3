<!-- Section d'accueil avec présentation -->
<header class="header" id="home" style="margin-top: 60px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold">Votez pour vos musiques préférées</h1>
                <p class="lead my-4">Découvrez et votez pour vos musiques, chanteurs, groupes et musiciens préférés. La communauté décide !</p>
                <div>
                    <a href="../login/redir_vote.php" class="btn btn-primary btn-lg me-2">Voter maintenant</a>
                    <a href="../vote/classement.php" class="btn btn-outline-primary btn-lg">Voir le classement</a>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Logo principal de l'application -->
                <img src="../icons/logos/MyPulse_Black-removebg-preview.png" alt="MyPulse Logo" class="img-fluid rounded-4">
            </div>
        </div>
    </div>
</header>

<!-- Section des statistiques -->
<section class="stats py-4">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="stat-number"><span class="counter" data-target="0">0</span></h3>
                    <p class="stat-text">Contenus</p>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="stat-number"><span class="counter" data-target="0">0</span></h3>
                    <p class="stat-text">Catégories</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <h3 class="stat-number"><span class="counter" data-target="0">0</span></h3>
                    <p class="stat-text">Votes totaux</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section des couvertures de musiques défilantes -->
<section class="music-covers py-4">
    <div class="container-fluid">
        <div class="scroll-container">
            <div class="scroll-content">
                <?php
                require_once '../class/Database.php';
                try {
                    $pdo = Database::getConnection();
                    $stmt = $pdo->prepare("SELECT ImageCouverture, Titre FROM musique WHERE StatusMusique = 'valide' ORDER BY DateProposition DESC");
                    $stmt->execute();
                    $allMusiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Remove duplicates based on cover image base name (before timestamp)
                    $uniqueCovers = [];
                    foreach ($allMusiques as $musique) {
                        $coverPath = $musique['ImageCouverture'];
                        // Extract base name (remove timestamp and extension)
                        $baseName = preg_replace('/_\d+(_couverture)?\..+$/', '', $coverPath);
                        if (!isset($uniqueCovers[$baseName])) {
                            $uniqueCovers[$baseName] = $musique;
                        }
                    }

                    foreach ($uniqueCovers as $musique) {
                        echo '<div class="cover-item">';
                        echo '<img src="../create/' . htmlspecialchars($musique['ImageCouverture']) . '" alt="' . htmlspecialchars($musique['Titre']) . '" class="cover-img">';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    // En cas d'erreur, afficher rien ou un message
                    echo '<!-- Erreur de chargement des couvertures -->';
                }
                ?>
            </div>
        </div>
    </div>
</section>
