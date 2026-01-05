<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<?php
require_once '../class/Database.php';
require_once '../class/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getConnection();

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $currentUser = User::findById((int)$_SESSION['user_id']);
    $isAdmin = ($currentUser && $currentUser->role === 'admin');
}

// Fetch top items for each category (status = 'classement')
$topItems = [];
// Fetch valide items for each category
$valideItems = [];
// Fetch archived top items for each category
$archivedItems = [];
$categories = ['musique', 'chanteur', 'groupe'];
foreach ($categories as $type) {
    $table = ($type === 'musique') ? 'musique' : (($type === 'chanteur') ? 'artiste' : 'groupe');
    $idCol = ($type === 'musique') ? 'MusiqueID' : (($type === 'chanteur') ? 'ArtisteID' : 'GroupeID');
    $titleCol = ($type === 'musique') ? 'Titre' : (($type === 'chanteur') ? 'NomArtiste' : 'NomGroupe');
    $imageCol = ($type === 'musique') ? 'ImageCouverture' : (($type === 'chanteur') ? 'ImageProfil' : 'ImageGroupe');
    $statusCol = 'Status' . $table;

    // Fetch classement items
    $stmt = $pdo->prepare("
        SELECT {$idCol} as id, {$titleCol} as title, {$imageCol} as image, NombreVotes as votes
        FROM {$table}
        WHERE {$statusCol} = 'classement'
        ORDER BY NombreVotes DESC, {$idCol} ASC
    ");
    $stmt->execute();
    $topItems[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch valide items
    $stmt = $pdo->prepare("
        SELECT {$idCol} as id, {$titleCol} as title, {$imageCol} as image, NombreVotes as votes
        FROM {$table}
        WHERE {$statusCol} = 'valide'
        ORDER BY NombreVotes DESC, {$idCol} ASC
    ");
    $stmt->execute();
    $valideItems[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch archived items
    $stmt = $pdo->prepare("
        SELECT {$idCol} as id, {$titleCol} as title, {$imageCol} as image, NombreVotes as votes
        FROM {$table}
        WHERE {$statusCol} = 'archive_top'
        ORDER BY NombreVotes DESC, {$idCol} ASC
    ");
    $stmt->execute();
    $archivedItems[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php require '../index/header.php'; ?>

    <!-- Section des classements -->
    <section class="py-5" style="margin-top: 80px;">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Classements</span>
                <h2 class="section-title">Top musiques & artistes</h2>
                <p class="section-description">Les contenus les plus votés par la communauté</p>
            </div>

            <!-- Admin Controls -->
            <?php if ($isAdmin): ?>
            <div class="admin-controls text-center mb-4">
                <button class="btn btn-primary me-3" onclick="toggleValideCards()">Afficher les cartes valides</button>
                <button class="btn btn-secondary" onclick="toggleArchivedCards()">Afficher les archives</button>
            </div>
            <?php endif; ?>

            <!-- Weekly Winners Podium -->
            <div class="weekly-winners mb-5">
                <h3 class="text-center mb-4">🏆 Top de la Semaine</h3>
                <div class="row">
                    <!-- Musiques -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Musiques</h4>
                            <div class="podium">
                                <?php if (!empty($topItems['musique'])): ?>
                                    <?php $rank = 1; foreach ($topItems['musique'] as $item): ?>
                                        <div class="podium-item rank-<?php echo $rank; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php $rank++; endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucun top musique</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Artistes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Artistes</h4>
                            <div class="podium">
                                <?php if (!empty($topItems['chanteur'])): ?>
                                    <?php $rank = 1; foreach ($topItems['chanteur'] as $item): ?>
                                        <div class="podium-item rank-<?php echo $rank; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php $rank++; endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucun top artiste</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Groupes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Groupes</h4>
                            <div class="podium">
                                <?php if (!empty($topItems['groupe'])): ?>
                                    <?php $rank = 1; foreach ($topItems['groupe'] as $item): ?>
                                        <div class="podium-item rank-<?php echo $rank; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php $rank++; endforeach; ?>
                                <?php else: ?>
                                      <p class="text-center text-muted">Aucun top groupe</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valide Cards Section (Hidden by default) -->
            <div id="valide-cards-section" class="valide-cards-section mb-5" style="display: none;">
                <h3 class="text-center mb-4">📋 Cartes Valides</h3>
                <div class="row">
                    <!-- Valide Musiques -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Musiques Valides</h4>
                            <div class="podium">
                                <?php if (!empty($valideItems['musique'])): ?>
                                    <?php foreach ($valideItems['musique'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucune musique valide</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Valide Artistes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Artistes Valides</h4>
                            <div class="podium">
                                <?php if (!empty($valideItems['chanteur'])): ?>
                                    <?php foreach ($valideItems['chanteur'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucun artiste valide</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Valide Groupes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Groupes Valides</h4>
                            <div class="podium">
                                <?php if (!empty($valideItems['groupe'])): ?>
                                    <?php foreach ($valideItems['groupe'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                      <p class="text-center text-muted">Aucun groupe valide</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archived Cards Section (Hidden by default) -->
            <div id="archived-cards-section" class="archived-cards-section mb-5" style="display: none;">
                <h3 class="text-center mb-4">📚 Archives des Tops</h3>
                <div class="row">
                    <!-- Archived Musiques -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Musiques Archivées</h4>
                            <div class="podium">
                                <?php if (!empty($archivedItems['musique'])): ?>
                                    <?php foreach ($archivedItems['musique'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucune musique archivée</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Archived Artistes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Artistes Archivés</h4>
                            <div class="podium">
                                <?php if (!empty($archivedItems['chanteur'])): ?>
                                    <?php foreach ($archivedItems['chanteur'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">Aucun artiste archivé</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Archived Groupes -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Groupes Archivés</h4>
                            <div class="podium">
                                <?php if (!empty($archivedItems['groupe'])): ?>
                                    <?php foreach ($archivedItems['groupe'] as $item): ?>
                                        <div class="podium-item">
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                      <p class="text-center text-muted">Aucun groupe archivé</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php require '../index/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script.js"></script>
    <script src="../script/modals.js"></script>

    <script>
        function toggleValideCards() {
            const section = document.getElementById('valide-cards-section');
            const button = event.target;

            if (section.style.display === 'none') {
                section.style.display = 'block';
                button.textContent = 'Masquer les cartes valides';
                button.classList.remove('btn-primary');
                button.classList.add('btn-warning');
            } else {
                section.style.display = 'none';
                button.textContent = 'Afficher les cartes valides';
                button.classList.remove('btn-warning');
                button.classList.add('btn-primary');
            }
        }

        function toggleArchivedCards() {
            const section = document.getElementById('archived-cards-section');
            const button = event.target;

            if (section.style.display === 'none') {
                section.style.display = 'block';
                button.textContent = 'Masquer les archives';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-info');
            } else {
                section.style.display = 'none';
                button.textContent = 'Afficher les archives';
                button.classList.remove('btn-info');
                button.classList.add('btn-secondary');
            }
        }
    </script>

</body>
</html>
