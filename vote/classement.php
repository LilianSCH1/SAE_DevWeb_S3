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
require_once '../database/promote_to_top.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getConnection();

// Check and run promotion if needed (every Monday at 16:00 UTC)
promoteToTopIfNeeded();

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $currentUser = User::findById((int)$_SESSION['user_id']);
    $isAdmin = ($currentUser && $currentUser->role === 'admin');
}

// Handle status switch if admin
if (isset($_POST['switch_statuses']) && $isAdmin) {
    $categories = ['musique', 'chanteur', 'groupe'];
    foreach ($categories as $type) {
        $table = ($type === 'musique') ? 'musique' : (($type === 'chanteur') ? 'artiste' : 'groupe');
        $statusCol = 'Status' . $table;

        // First, move 'classement' to 'archive_top'
        $pdo->prepare("UPDATE {$table} SET {$statusCol} = 'archive_top' WHERE {$statusCol} = 'classement'")->execute();

        // Then, move 'valide' to 'classement'
        $pdo->prepare("UPDATE {$table} SET {$statusCol} = 'classement' WHERE {$statusCol} = 'valide'")->execute();
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle adding comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (isset($_SESSION['user_id'])) {
        $type = $_POST['type'];
        $id = (int)$_POST['id'];
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $stmt = $pdo->prepare("INSERT INTO commentaire (TypeContenu, UserID, Commentaire) VALUES (?, ?, ?)");
            $stmt->execute([$type, $_SESSION['user_id'], $comment]);
        }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle adding general comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_general_comment'])) {
    if (isset($_SESSION['user_id'])) {
        // Check if user has already commented this year
        $startOfYear = date('Y-m-d', strtotime('1st january this year'));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire WHERE UserID = ? AND TypeContenu = 'general' AND DateCommentaire >= ?");
        $stmt->execute([$_SESSION['user_id'], $startOfYear]);
        if ($stmt->fetchColumn() == 0) {
            $comment = trim($_POST['comment']);
            if (!empty($comment)) {
                $stmt = $pdo->prepare("INSERT INTO commentaire (TypeContenu, UserID, Commentaire) VALUES ('general', ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $comment]);
            }
        }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
// Handle editing comment
    if (isset($_SESSION['user_id'])) {
        $commentId = (int)$_POST['comment_id'];
        $newComment = trim($_POST['comment']);
        if (!empty($newComment)) {
            // Check if user owns the comment
            $stmt = $pdo->prepare("SELECT UserID FROM commentaire WHERE CommentaireID = ?");
            $stmt->execute([$commentId]);
            $commentOwner = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($commentOwner && $commentOwner['UserID'] == $_SESSION['user_id']) {
                $stmt = $pdo->prepare("UPDATE commentaire SET Commentaire = ? WHERE CommentaireID = ?");
                $stmt->execute([$newComment, $commentId]);
            }
        }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle reporting comment as offensive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_comment'])) {
    if (isset($_SESSION['user_id'])) {
        $commentId = (int)$_POST['comment_id'];
        $reportReason = trim($_POST['report_reason'] ?? '');
        $stmt = $pdo->prepare("UPDATE commentaire SET is_offensive = 1, report_reason = ? WHERE CommentaireID = ?");
        $stmt->execute([$reportReason, $commentId]);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle deleting comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    if (isset($_SESSION['user_id'])) {
        $commentId = (int)$_POST['comment_id'];
        // Check if user owns the comment or is admin and comment is offensive
        $stmt = $pdo->prepare("SELECT UserID, is_offensive FROM commentaire WHERE CommentaireID = ?");
        $stmt->execute([$commentId]);
        $commentData = $stmt->fetch(PDO::FETCH_ASSOC);
        $canDelete = false;
        if ($commentData) {
            if ($commentData['UserID'] == $_SESSION['user_id']) {
                $canDelete = true; // Owner
            } elseif ($isAdmin && $commentData['is_offensive']) {
                $canDelete = true; // Admin and offensive
            }
        }
        if ($canDelete) {
            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE CommentaireID = ?");
            $stmt->execute([$commentId]);
        }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle restoring comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_comment'])) {
    if ($isAdmin) {
        $commentId = (int)$_POST['comment_id'];
        $stmt = $pdo->prepare("UPDATE commentaire SET is_offensive = 0, report_reason = NULL WHERE CommentaireID = ?");
        $stmt->execute([$commentId]);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
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

// Fetch comments for top items
$comments = [];
foreach ($categories as $type) {
    if (!empty($topItems[$type])) {
        foreach ($topItems[$type] as $item) {
            $stmt = $pdo->prepare("
                SELECT c.Commentaire, c.DateCommentaire, u.UserPseudo
                FROM commentaire c
                JOIN utilisateur u ON c.UserID = u.UserID
                WHERE c.TypeContenu = ?
                ORDER BY c.DateCommentaire DESC
            ");
            $stmt->execute([$type]);
            $comments[$type][$item['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// Fetch general comments
$generalComments = [];
$stmt = $pdo->prepare("
    SELECT c.CommentaireID, c.Commentaire, c.DateCommentaire, c.UserID, c.is_offensive, c.report_reason, u.UserPseudo
    FROM commentaire c
    JOIN utilisateur u ON c.UserID = u.UserID
    WHERE c.TypeContenu = 'general'
    ORDER BY c.DateCommentaire DESC
");
$stmt->execute();
$generalComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user has already commented this year (only for non-admins)
$userHasCommentedThisYear = false;
if (isset($_SESSION['user_id']) && !$isAdmin) {
    $startOfYear = date('Y-m-d', strtotime('1st january this year'));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM commentaire WHERE UserID = ? AND TypeContenu = 'general' AND DateCommentaire >= ?");
    $stmt->execute([$_SESSION['user_id'], $startOfYear]);
    $userHasCommentedThisYear = $stmt->fetchColumn() > 0;
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
                <form method="post" style="display: inline;">
                    <button type="submit" name="switch_statuses" class="btn btn-danger me-3" onclick="return confirm('Êtes-vous sûr de vouloir changer le statut de tous les éléments ?')">Changer Statuts (Valide → Classement, Classement → Archive)</button>
                </form>
                <button class="btn btn-primary me-3" onclick="toggleValideCards()">Afficher les cartes valides</button>
                <button class="btn btn-secondary" onclick="toggleArchivedCards()">Afficher les archives</button>
            </div>
            <?php endif; ?>

            <!-- Annual Winners Podium -->
            <div class="annual-winners mb-5">
                <h3 class="text-center mb-4">🏆 Top de l'Année</h3>
                <div class="row">
                    <!-- Musiques -->
                    <div class="col-md-4">
                        <div class="winner-category">
                            <h4 class="text-center">Musiques</h4>
                            <div class="podium" id="musique-podium">
                                <?php if (!empty($topItems['musique'])): ?>
                                    <?php $rank = 1; $previousVotes = null; $count = 0; foreach ($topItems['musique'] as $item): if ($previousVotes !== null && $item['votes'] < $previousVotes) { $rank++; } $class = ($count >= 3) ? 'd-none' : ''; ?>
                                        <div class="podium-item rank-<?php echo $rank; ?> <?php echo $class; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                            <?php $previousVotes = $item['votes']; $count++; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($topItems['musique']) > 3): ?>
                                        <button class="btn btn-secondary mt-3" onclick="toggleShow('musique')">Voir plus</button>
                                    <?php endif; ?>
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
                            <div class="podium" id="chanteur-podium">
                                <?php if (!empty($topItems['chanteur'])): ?>
                                    <?php $rank = 1; $previousVotes = null; $count = 0; foreach ($topItems['chanteur'] as $item): if ($previousVotes !== null && $item['votes'] < $previousVotes) { $rank++; } $class = ($count >= 3) ? 'd-none' : ''; ?>
                                        <div class="podium-item rank-<?php echo $rank; ?> <?php echo $class; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                            <?php $previousVotes = $item['votes']; $count++; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($topItems['chanteur']) > 3): ?>
                                        <button class="btn btn-secondary mt-3" onclick="toggleShow('chanteur')">Voir plus</button>
                                    <?php endif; ?>
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
                            <div class="podium" id="groupe-podium">
                                <?php if (!empty($topItems['groupe'])): ?>
                                    <?php $rank = 1; $previousVotes = null; $count = 0; foreach ($topItems['groupe'] as $item): if ($previousVotes !== null && $item['votes'] < $previousVotes) { $rank++; } $class = ($count >= 3) ? 'd-none' : ''; ?>
                                        <div class="podium-item rank-<?php echo $rank; ?> <?php echo $class; ?>">
                                            <div class="rank-badge">#<?php echo $rank; ?></div>
                                            <img src="../create/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="winner-image">
                                            <div class="winner-info">
                                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <span class="votes"><?php echo $item['votes']; ?> votes</span>
                                            </div>
                                            <?php $previousVotes = $item['votes']; $count++; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($topItems['groupe']) > 3): ?>
                                        <button class="btn btn-secondary mt-3" onclick="toggleShow('groupe')">Voir plus</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                      <p class="text-center text-muted">Aucun top groupe</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commentary Section -->
            <div class="commentary-section mb-5">
                <h3 class="text-center mb-4">💬 Commentaires sur les Tops</h3>
                <div class="comments-list mb-4">
                    <?php if (!empty($generalComments)): ?>
                        <?php foreach ($generalComments as $comment): ?>
                            <?php if (!$comment['is_offensive'] || $comment['UserID'] == ($_SESSION['user_id'] ?? null) || $isAdmin): ?>
                                <div class="comment <?php echo ($isAdmin && $comment['is_offensive']) ? 'comment-offensive' : ''; ?>">
                                    <strong><?php echo htmlspecialchars($comment['UserPseudo']); ?>:</strong>
                                    <?php if ($comment['is_offensive'] && $comment['UserID'] == ($_SESSION['user_id'] ?? null)): ?>
                                        <p><em>Ce commentaire est en cours de révision par l'équipe de modération.</em></p>
                                    <?php else: ?>
                                        <p><?php echo htmlspecialchars($comment['Commentaire']); ?></p>
                                    <?php endif; ?>
                                    <small><?php echo date('d/m/Y H:i', strtotime($comment['DateCommentaire'])); ?><?php echo ($isAdmin && $comment['is_offensive']) ? ' <span class="badge bg-danger">Signalé</span>' : ''; ?></small>
                                    <?php if ($isAdmin && $comment['is_offensive'] && !empty($comment['report_reason'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted"><strong>Raison du signalement:</strong> <?php echo htmlspecialchars($comment['report_reason']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    <div class="comment-actions">
                                        <?php if (isset($_SESSION['user_id']) && !$comment['is_offensive'] && $comment['UserID'] != $_SESSION['user_id']): ?>
                                            <button class="btn btn-sm btn-outline-warning" onclick="reportComment(<?php echo $comment['CommentaireID']; ?>)">Signaler</button>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['user_id']) && $comment['UserID'] == $_SESSION['user_id'] && !$comment['is_offensive']): ?>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editComment(<?php echo $comment['CommentaireID']; ?>, '<?php echo addslashes($comment['Commentaire']); ?>')">Modifier</button>
                                        <?php endif; ?>
                                        <?php if ($isAdmin && $comment['is_offensive']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['CommentaireID']; ?>">
                                                <button type="submit" name="restore_comment" class="btn btn-sm btn-outline-success" onclick="return confirm('Êtes-vous sûr de vouloir remettre ce commentaire ?')">Annuler le signalement</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['user_id']) && (($comment['UserID'] == $_SESSION['user_id'] && !$comment['is_offensive']) || ($isAdmin && $comment['is_offensive']))): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['CommentaireID']; ?>">
                                                <button type="submit" name="delete_comment" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($userHasCommentedThisYear): ?>
                        <p class="text-muted text-center">Vous ne pouvez envoyer qu'un commentaire par classement.</p>
                    <?php else: ?>
                        <form method="post" class="comment-form">
                            <div class="input-group">
                                <input type="text" name="comment" class="form-control" placeholder="Partagez votre avis sur les tops de l'année..." required>
                                <button type="submit" name="add_general_comment" class="btn btn-primary">Commenter</button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Connectez-vous pour laisser un commentaire.</p>
                <?php endif; ?>
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

    <!-- Report Comment Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Signaler un commentaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="comment_id" id="reportCommentId">
                        <div class="mb-3">
                            <label for="reportReason" class="form-label">Raison du signalement</label>
                            <textarea class="form-control" id="reportReason" name="report_reason" rows="3" placeholder="Veuillez expliquer pourquoi vous signalez ce commentaire..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="report_comment" class="btn btn-warning">Signaler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script.js"></script>
    <script src="../script/modals.js"></script>
    <script src="script/toggle.js"></script>

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

        function reportComment(commentId) {
            document.getElementById('reportCommentId').value = commentId;
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();
        }

        function editComment(commentId, currentComment) {
            const newComment = prompt('Modifier le commentaire:', currentComment);
            if (newComment !== null && newComment.trim() !== '') {
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';

                const commentIdInput = document.createElement('input');
                commentIdInput.type = 'hidden';
                commentIdInput.name = 'comment_id';
                commentIdInput.value = commentId;

                const commentInput = document.createElement('input');
                commentInput.type = 'hidden';
                commentInput.name = 'comment';
                commentInput.value = newComment.trim();

                const editInput = document.createElement('input');
                editInput.type = 'hidden';
                editInput.name = 'edit_comment';
                editInput.value = '1';

                form.appendChild(commentIdInput);
                form.appendChild(commentInput);
                form.appendChild(editInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

</body>
</html>
