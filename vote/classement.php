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

// Fetch top items for each category (status = 'classement')
$topItems = [];
$categories = ['musique', 'chanteur', 'groupe'];
foreach ($categories as $type) {
    $table = ($type === 'musique') ? 'musique' : (($type === 'chanteur') ? 'artiste' : 'groupe');
    $idCol = ($type === 'musique') ? 'MusiqueID' : (($type === 'chanteur') ? 'ArtisteID' : 'GroupeID');
    $titleCol = ($type === 'musique') ? 'Titre' : (($type === 'chanteur') ? 'NomArtiste' : 'NomGroupe');
    $imageCol = ($type === 'musique') ? 'ImageCouverture' : (($type === 'chanteur') ? 'ImageProfil' : 'ImageGroupe');
    $statusCol = 'Status' . $table;

    $stmt = $pdo->prepare("
        SELECT {$idCol} as id, {$titleCol} as title, {$imageCol} as image, NombreVotes as votes
        FROM {$table}
        WHERE {$statusCol} = 'classement'
        ORDER BY NombreVotes DESC, {$idCol} ASC
    ");
    $stmt->execute();
    $topItems[$type] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        </div>
    </section>


    <?php require '../index/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script.js"></script>
    <script src="../script/modals.js"></script>
    
</body>
</html>
