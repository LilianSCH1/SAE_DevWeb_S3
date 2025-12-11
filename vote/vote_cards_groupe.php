<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/User.php';
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;

// Token de vote propre à l'utilisateur
$voteToken = $currentUser ? $currentUser->token : null;
?>

<?php if (empty($groupes)): ?>
    <p class="text-muted text-nowrap">Aucun groupe pour le moment.</p>
<?php else: ?>
    <?php foreach ($groupes as $groupe): ?>
        <?php
        $imgPath   = '../create/' . ltrim($groupe['ImageGroupe'] ?? '', '/');
        $audioPath = '../create/' . ltrim($groupe['CheminFichierMP3'] ?? '', '/');

        // Détecter si cet utilisateur a déjà voté pour ce groupe
        $hasVoted = false;
        if (!empty($voteToken)) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM vote
                WHERE Token = :token AND TypeContenu = 'groupe' AND ContenuID = :id
            ");
            $stmt->execute([
                ':token' => $voteToken,
                ':id'    => (int)($groupe['GroupeID'] ?? 0)
            ]);
            $hasVoted = $stmt->fetchColumn() > 0;
        }
        ?>
        <article class="content-card">
            <div class="content-card-header">
                <?php if ($currentUser && $currentUser->role === 'admin'): ?>
                    <form method="post" class="delete-card-btn" aria-label="Supprimer groupe">
                        <input type="hidden" name="delete_groupe" value="1">
                        <input type="hidden" name="groupe_id" value="<?php echo (int)($groupe['GroupeID'] ?? 0); ?>">
                        <input type="hidden" name="image_groupe" value="<?php echo htmlspecialchars($groupe['ImageGroupe'] ?? ''); ?>">
                        <input type="hidden" name="chemin_fichier" value="<?php echo htmlspecialchars($groupe['CheminFichierMP3'] ?? ''); ?>">
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:20px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                <?php endif; ?>
                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($groupe['NomGroupe'] ?? ''); ?>
                </h3>
                <span class="content-card-type">GROUPE</span>
                <div class="content-card-date">
                    <?php if (!empty($groupe['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($groupe['DateAffichee'] ?? ''); ?>
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
                    <?php echo nl2br(htmlspecialchars($groupe['BiographieCourte'] ?? '')); ?>
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
                    data-type-contenu="groupe"
                    data-contenu-id="<?php echo (int)($groupe['GroupeID'] ?? 0); ?>"
                    data-voted="<?php echo $hasVoted ? '1' : '0'; ?>">
                    <?php echo $hasVoted ? 'Supprimer mon vote' : '❤ Voter pour ce groupe'; ?>
                </button>

                <span class="vote-count">
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vote WHERE TypeContenu = 'groupe' AND ContenuID = :id");
                    $stmt->execute([':id' => (int)($groupe['GroupeID'] ?? 0)]);
                    echo (int)$stmt->fetchColumn();
                    ?>
                </span>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>