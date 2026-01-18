<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

$pdo = Database::getConnection();

// Utilisateur courant
$currentUser = isset($_SESSION['user_id'])
    ? User::findById((int)$_SESSION['user_id'])
    : null;

// Token de vote anonyme basé sur la session
$voteToken = session_id();
?>

<?php if (empty($musiques)): ?>
    <p class="text-muted text-nowrap">Aucune musique pour le moment.</p>
<?php else: ?>
    <?php foreach ($musiques as $musique): ?>
        <?php
        $imgPath   = '../create/' . ltrim($musique['ImageCouverture'] ?? '', '/');
        $audioPath = '../create/' . ltrim($musique['CheminFichierMP3'] ?? '', '/');

        // Détecter si cet utilisateur a déjà voté pour cette musique
        $hasVoted = false;
        if (!empty($voteToken)) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM vote
                WHERE Token = :token 
                  AND TypeContenu = 'musique' 
                  AND ContenuID = :id
            ");
            $stmt->execute([
                ':token' => $voteToken,
                ':id'    => (int)($musique['MusiqueID'] ?? 0)
            ]);
            $hasVoted = $stmt->fetchColumn() > 0;
        }
        ?>
        <article class="content-card">
            <div class="content-card-header">
                <?php if ($currentUser && $currentUser->role === 'admin'): ?>
                    <form method="post" class="delete-card-btn" aria-label="Supprimer musique">
                        <input type="hidden" name="delete_musique" value="1">
                        <input type="hidden" name="musique_id" value="<?php echo (int)($musique['MusiqueID'] ?? 0); ?>">
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:20px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                <?php endif; ?>

                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($musique['Titre'] ?? ''); ?>
                </h3>
                <?php if (!empty($musique['Artiste'])): ?>
                    <p class="content-card-artist" style="margin:4px 0 0;color:#ffffff;font-size:0.95rem;">
                        <?php echo htmlspecialchars($musique['Artiste']); ?>
                    </p>
                <?php endif; ?>
                <span class="content-card-type">MUSIQUE</span>
                <div class="content-card-date">
                    <?php if (!empty($musique['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($musique['DateAffichee'] ?? ''); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card-body">
                <div class="content-card-image"
                    style="background-image:url('<?php echo htmlspecialchars($imgPath); ?>');">
                </div>

                <div class="content-card-separator"></div>

                <p class="content-card-description">
                    <?php echo nl2br(htmlspecialchars($musique['DescriptionCourte'] ?? '')); ?>
                </p>
            </div>

            <div class="content-card-footer">
                <div class="audio-player-container">
                    <button class="btn btn-outline-orange btn-play-audio"
                        data-audio="<?php echo htmlspecialchars($audioPath); ?>">
                        ▶ Écouter
                    </button>
                    <div class="progress-bar-container" style="display: none; gap: 10px; align-items: center;">
                        <div class="progress-bar-bg" style="flex: 1; height: 6px; background-color: #e9ecef; border-radius: 3px; cursor: pointer;">
                            <div class="progress-bar-fill" style="height: 100%; background-color: #0d6efd; border-radius: 3px; width: 0%;"></div>
                        </div>
                        <span class="progress-time" style="font-size: 12px; min-width: 40px; text-align: right;">0:00</span>
                        <button class="btn-play-pause">⏸</button>
                    </div>
                </div>

                <?php if (!$currentUser || $currentUser->role !== 'admin') : ?>
                    <button
                        type="button"
                        class="btn btn-orange btn-vote"
                        data-type-contenu="musique"
                        data-contenu-id="<?php echo (int)($musique['MusiqueID'] ?? 0); ?>"
                        data-voted="<?php echo $hasVoted ? '1' : '0'; ?>">
                        <?php echo $hasVoted ? 'Supprimer mon vote' : '❤ Voter pour cette musique'; ?>
                    </button>
                <?php endif; ?>


            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>