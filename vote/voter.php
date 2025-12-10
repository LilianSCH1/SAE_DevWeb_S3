<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../database/dbconnect.php';
require_once __DIR__ . '/../class/User.php';
$pdo = dbconnect();

// Vérifier le rôle de l'utilisateur
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
$userCanCreate = $currentUser && in_array($currentUser->role, ['certifie', 'admin']);

// Affichage des données de cartes d'artistes
$artistes = $pdo->query("
    SELECT NomArtiste,
           BiographieCourte,
           ImageProfil,
           CheminFichierMP3,
           AnneeNaissance as DateAffichee
    FROM artiste
    WHERE StatusArtiste IN ('valide')
    ORDER BY DateAffichee DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Affichage des données de cartes de musiques
$musiques = $pdo->query("
    SELECT Titre,
           Artiste,
           ImageCouverture,
           CheminFichierMP3,
           AnneePublication as DateAffichee
    FROM musique
    WHERE StatusMusique IN ('valide')
    ORDER BY DateAffichee DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Affichage des données de cartes de groupes
$groupes = $pdo->query("
    SELECT NomGroupe,
           BiographieCourte,
           ImageGroupe,
           CheminFichierMP3,
           AnneeFormation as DateAffichee
    FROM groupe
    WHERE StatusGroupe IN ('valide')
    ORDER BY DateAffichee DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Voter - MyPulse</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/cards.css">

    <link rel="icon" type="image/png" href="../icons/logos/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="../icons/logos/favicon.svg">
    <link rel="shortcut icon" href="../icons/logos/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../icons/logos/apple-touch-icon.png">
    <link rel="manifest" href="../icons/logos/site.webmanifest">
</head>

<body>
    <?php require '../index/header.php'; ?>

    <section class="py-5" style="margin-top:80px; min-height:80vh;">
        <div class="container">

            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Vote</span>
                <h2 class="section-title">Choisissez vos artistes, musiques et groupes préférés</h2>
            </div>

            <ul class="nav nav-pills justify-content-center mb-4" id="voteTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-musique" data-bs-toggle="pill"
                        data-bs-target="#pane-musique" type="button" role="tab">Musiques</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-artiste" data-bs-toggle="pill"
                        data-bs-target="#pane-artiste" type="button" role="tab">Artistes</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-groupe" data-bs-toggle="pill"
                        data-bs-target="#pane-groupe" type="button" role="tab">Groupes</button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- MUSIQUES -->
                <div class="tab-pane fade show active" id="pane-musique" role="tabpanel" aria-labelledby="tab-musique">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_musique.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer une musique
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require 'vote_cards_musique.php'; ?>
                    </div>
                </div>

                <!-- ARTISTES -->
                <div class="tab-pane fade" id="pane-artiste" role="tabpanel" aria-labelledby="tab-artiste">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_artiste.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer un artiste
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require 'vote_cards_artiste.php'; ?>
                    </div>
                </div>

                <!-- GROUPES -->
                <div class="tab-pane fade" id="pane-groupe" role="tabpanel" aria-labelledby="tab-groupe">
                    <?php if ($userCanCreate): ?>
                        <div class="text-center mb-4">
                            <a href="../create/creer_groupe.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Proposer un groupe
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="card-list">
                        <?php require 'vote_cards_groupe.php'; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>

    <audio id="vote-audio-player"></audio>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const audio = document.getElementById('vote-audio-player');
            let currentBtn = null;
            let currentProgressBar = null;
            let currentProgressFill = null;
            let currentPlayPauseBtn = null;

            function setBtnState(btn, isPlaying) {
                const container = btn.closest('.audio-player-container');
                const progressContainer = container.querySelector('.progress-bar-container');
                const playBtn = container.querySelector('.btn-play-audio');

                if (isPlaying) {
                    playBtn.style.display = 'none';
                    progressContainer.style.display = 'flex';
                    currentProgressBar = progressContainer.querySelector('.progress-bar-bg');
                    currentProgressFill = progressContainer.querySelector('.progress-bar-fill');
                    currentPlayPauseBtn = progressContainer.querySelector('.btn-play-pause');
                    currentPlayPauseBtn.textContent = '⏸';
                    // Ajouter les classes du bouton "Écouter" SANS modifier la classe existante
                    currentPlayPauseBtn.classList.add(...playBtn.classList);
                } else {
                    playBtn.style.display = 'block';
                    progressContainer.style.display = 'none';
                }
            }

            function updateProgress() {
                if (currentProgressFill && audio.duration) {
                    const progress = (audio.currentTime / audio.duration) * 100;
                    currentProgressFill.style.width = progress + '%';
                }
            }

            // Audio + transformation en barre de progression
            document.querySelectorAll('.btn-play-audio').forEach(btn => {
                btn.addEventListener('click', () => {
                    const src = btn.getAttribute('data-audio');
                    if (!src) return;

                    if (currentBtn === btn && !audio.paused) {
                        audio.pause();
                        setBtnState(btn, false);
                        return;
                    }

                    if (currentBtn && currentBtn !== btn) {
                        setBtnState(currentBtn, false);
                    }

                    audio.src = src;
                    audio.play().then(() => {
                        setBtnState(btn, true);
                        currentBtn = btn;
                    }).catch(() => {});
                });
            });

            // Gestionnaire pour la barre de progression
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('progress-bar-bg')) {
                    const rect = e.target.getBoundingClientRect();
                    const clickX = e.clientX - rect.left;
                    const width = rect.width;
                    const percentage = (clickX / width) * 100;
                    audio.currentTime = (percentage / 100) * audio.duration;
                }
            });

            // Bouton play/pause dans la barre de progression
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-play-pause') || e.target.closest('.btn-play-pause')) {
                    const btn = e.target.classList.contains('btn-play-pause') ? e.target : e.target.closest('.btn-play-pause');
                    if (audio.paused) {
                        audio.play();
                        btn.textContent = '⏸';
                    } else {
                        audio.pause();
                        btn.textContent = '▶';
                    }
                }
            });

            // Clic en dehors du lecteur audio pour réinitialiser
            document.addEventListener('click', (e) => {
                if (currentBtn && !e.target.closest('.audio-player-container')) {
                    audio.pause();
                    setBtnState(currentBtn, false);
                    currentBtn = null;
                }
            });

            audio.addEventListener('timeupdate', updateProgress);

            audio.addEventListener('ended', () => {
                if (currentBtn) {
                    setBtnState(currentBtn, false);
                    currentBtn = null;
                }
            });

            // Bouton + / - description
            document.querySelectorAll('.toggle-desc-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const card = btn.closest('.content-card');
                    const desc = card.querySelector('.content-card-description');
                    if (!desc) return;

                    const isShown = desc.classList.toggle('show');
                    btn.textContent = isShown ? '−' : '+';
                });
            });

            // Gestion des onglets via URL
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                const tabElement = document.getElementById('tab-' + tab);
                if (tabElement) {
                    tabElement.click();
                }
            }
        });
    </script>
    <script src="../script/modals.js"></script>
    <script src="../script/script.js"></script>
</body>

</html>