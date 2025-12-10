<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../class/User.php';
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
?>

<?php if (empty($musiques)): ?>
    <p class="text-muted text-nowrap">Aucune musique pour le moment.</p>
<?php else: ?>
    <?php foreach ($musiques as $musique): ?>
        <?php
        // chemins à partir de la valeur BDD
        // ImageCouverture = "uploads/musiques/couvertures/XXX.jpg"
        $imgPath   = '../create/' . ltrim($musique['ImageCouverture'], '/');
        $audioPath = '../create/' . ltrim($musique['CheminFichierMP3'], '/');

        ?>
        <article class="content-card">
            <div class="content-card-header">
                <?php if ($currentUser && $currentUser->role === 'admin'): ?>
                    <form method="post" class="delete-card-btn" aria-label="Archiver musique">
                        <input type="hidden" name="action" value="archiver">
                        <input type="hidden" name="type" value="musique">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($musique['MusiqueID'] ?? ''); ?>">
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:20px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>

                <?php endif; ?>
                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($musique['Titre']); ?>
                </h3>
                <span class="content-card-type">MUSIQUE</span>
                <div class="content-card-date">
                    <?php if (!empty($musique['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($musique['DateAffichee']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card-body">
                <div class="content-card-image"
                    style="background-image:url('<?php echo htmlspecialchars($imgPath); ?>');">
                </div>

                <div class="content-card-separator"></div>

                <p class="content-card-subtitle">
                    <?php echo htmlspecialchars($musique['Artiste']); ?>
                </p>

                <p class="content-card-description">
                    <?php /* description longue si tu ajoutes un champ dédié */ ?>
                </p>
            </div>

            <div class="content-card-footer">
                <div class="audio-player-container">
                    <button class="btn-outline-orange btn-play-audio"
                        data-audio="<?php echo htmlspecialchars($audioPath); ?>">
                        ▶ Écouter
                    </button>
                    <div class="progress-bar-container" style="display: none;">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill"></div>
                        </div>
                        <button class="btn-play-pause">⏸</button>
                    </div>
                </div>
                <button class="btn-orange">
                    ❤ Voter pour cette musique
                </button>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>