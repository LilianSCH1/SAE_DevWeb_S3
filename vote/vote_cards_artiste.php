<?php if (empty($artistes)): ?>
    <p class="text-muted text-nowrap">Aucun artiste pour le moment.</p>
<?php else: ?>
    <?php foreach ($artistes as $artiste): ?>
        <?php
        $imgPath   = '../create/' . ltrim($artiste['ImageProfil'], '/');
        $audioPath = '../create/' . ltrim($artiste['CheminFichierMP3'], '/');
        ?>
        <article class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($artiste['NomArtiste']); ?>
                </h3>
                <span class="content-card-type">ARTISTE</span>
                <div class="content-card-date">
                    <?php if (!empty($artiste['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($artiste['DateAffichee']); ?>
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
                    <?php echo nl2br(htmlspecialchars($artiste['BiographieCourte'])); ?>
                </p>
            </div>

            <div class="content-card-footer">
                <button class="btn-outline-orange btn-play-audio"
                        data-audio="<?php echo htmlspecialchars($audioPath); ?>">
                    ▶ Écouter
                </button>
                <button class="btn-orange">
                    ❤ Voter pour cet artiste
                </button>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
