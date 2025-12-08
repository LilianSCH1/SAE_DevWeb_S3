<?php if (empty($groupes)): ?>
    <p class="text-muted text-nowrap">Aucun groupe pour le moment.</p>
<?php else: ?>
    <?php foreach ($groupes as $groupe): ?>
        <?php
        $imgPath   = '../create/' . ltrim($groupe['ImageGroupe'], '/');
        $audioPath = '../create/' . ltrim($groupe['CheminFichierMP3'], '/');
        ?>
        <article class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title">
                    <?php echo htmlspecialchars($groupe['NomGroupe']); ?>
                </h3>
                <span class="content-card-type">GROUPE</span>
                <div class="content-card-date">
                    <?php if (!empty($groupe['DateAffichee'])): ?>
                        <?php echo htmlspecialchars($groupe['DateAffichee']); ?>
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
                    <?php echo nl2br(htmlspecialchars($groupe['BiographieCourte'])); ?>
                </p>
            </div>

            <div class="content-card-footer">
                <button class="btn-outline-orange btn-play-audio"
                    data-audio="<?php echo htmlspecialchars($audioPath); ?>">
                    ▶ Écouter
                </button>
                <button class="btn-orange">
                    ❤ Voter pour ce groupe
                </button>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>