<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/User.php';
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;

// Token de vote propre à l'utilisateur
$voteToken = $currentUser ? $currentUser->token : null;
?>

<?php if (empty($artistes)): ?>
    <p class="text-muted text-nowrap">Aucun artiste pour le moment.</p>
<?php else: ?>
    <?php foreach ($artistes as $artiste): ?>
        <?php
        $imgPath   = '../create/' . ltrim($artiste['ImageProfil'] ?? '', '/');
        $audioPath = '../create/' . ltrim($artiste['CheminFichierMP3'] ?? '', '/');

        // Détecter si cet utilisateur a déjà voté pour cet artiste
        $hasVoted = false;
        if (!empty($voteToken)) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM vote
                WHERE Token = :token AND TypeContenu = 'chanteur' AND ContenuID = :id
            ");
            $stmt->execute([
                ':token' => $voteToken,
                ':id'    => (int)($artiste['ArtisteID'] ?? 0)
            ]);
            $hasVoted = $stmt->fetchColumn() > 0;
        }
        ?>
        <article class="content-card">
            <div class="content-card-header">
                <?php if ($currentUser && $currentUser->role === 'admin'): ?>
                    <form method="post" class="delete-card-btn" aria-label="Supprimer artiste">
                        <input type="hidden" name="delete_artiste" value="1">
                        <input type="hidden" name="artiste_id" value="<?php echo (int)($artiste['ArtisteID'] ?? 0); ?>">
                        <input type="hidden" name="image_profil" value="<?php echo htmlspecialchars($artiste['ImageProfil'] ?? ''); ?>">
                        <input type="hidden" name="chemin_fichier" value="<?php echo htmlspecialchars($artiste['CheminFichierMP3'] ?? ''); ?>">
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:20px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                <?php endif; ?>
                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($artiste['NomArtiste'] ?? ''); ?>
                </h3>
                <span class="content-card-type">ARTISTE</span>
                <div class="content-card-date">
                    <?php if (!empty($artiste['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($artiste['DateAffichee'] ?? ''); ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="toggle-desc-btn">+</button>
            </div>

            <div class="content-card-body">
                <div class="content-card-image"
                    style="background-image:url('<?php echo htmlspecialchars($imgPath); ?>');">
                </div>

                <div class="content-card-separator"></div>

                <p class="content-card-description">
                    <?php echo nl2br(htmlspecialchars($artiste['BiographieCourte'] ?? '')); ?>
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

                <button
                    type="button"
                    class="btn btn-orange btn-vote"
                    data-type-contenu="chanteur"
                    data-contenu-id="<?php echo (int)($artiste['ArtisteID'] ?? 0); ?>"
                    data-voted="<?php echo $hasVoted ? '1' : '0'; ?>">
                    <?php echo $hasVoted ? 'Supprimer mon vote' : '❤ Voter pour cet artiste'; ?>
                </button>

                <span class="vote-count">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE TypeContenu = 'chanteur' AND ContenuID = :id");
                    $stmt->execute([':id' => (int)($artiste['ArtisteID'] ?? 0)]);
                    echo (int)$stmt->fetchColumn();
                    ?>
                </span>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>