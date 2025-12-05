<?php foreach ($musiques as $musique): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <div class="card-container">
            <div class="inner-container">
                <div class="border-outer">
                    <div class="main-card">
                        <div class="glow-layer-1"></div>
                        <div class="glow-layer-2"></div>
                        <div class="overlay-1"></div>
                        <div class="overlay-2"></div>
                        <div class="background-glow"></div>
                        <div class="content-container">
                            <div class="scrollbar-glass">Musique</div>
                            <h3 class="title"><?php echo htmlspecialchars($musique['Titre']); ?></h3>
                            <div class="card-image"><img src="<?php echo htmlspecialchars($musique['ImageCouverture']); ?>" alt="<?php echo htmlspecialchars($musique['Titre']); ?>"></div>
                            <p class="description"><?php echo htmlspecialchars($musique['Artiste']); ?></p>
                            <button class="vote-btn w-100"><i class="bi bi-heart-fill me-2"></i>Voter</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>