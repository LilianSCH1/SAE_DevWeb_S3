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
                <button type="button" class="delete-card-btn">&#128465;</button>
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
                <button class="btn-outline-orange btn-play-audio"
                    data-audio="<?php echo htmlspecialchars($audioPath); ?>">
                    ▶ Écouter
                </button>
                <button class="btn-orange">
                    ❤ Voter pour cette musique
                </button>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>