<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classement - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require 'header.php'; ?>

    <!-- Section des classements -->
    <section class="py-5" style="margin-top: 80px;">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Classements</span>
                <h2 class="section-title">Top musiques & artistes</h2>
                <p class="section-description">Les contenus les plus votés par la communauté</p>
            </div>

            <!-- Onglets de classements -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <ul class="nav nav-pills justify-content-center" id="rankingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="musics-ranking-tab" data-bs-toggle="pill" data-bs-target="#musics-ranking" type="button" role="tab">Top Musiques</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="artists-ranking-tab" data-bs-toggle="pill" data-bs-target="#artists-ranking" type="button" role="tab">Top Artistes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="groups-ranking-tab" data-bs-toggle="pill" data-bs-target="#groups-ranking" type="button" role="tab">Top Groupes</button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contenu des onglets -->
            <div class="tab-content" id="rankingTabsContent">
                <!-- Top Musiques -->
                <div class="tab-pane fade show active" id="musics-ranking" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="ranking-list" id="ranking-musics-full">
                                <!-- Le classement complet sera généré par JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Artistes -->
                <div class="tab-pane fade" id="artists-ranking" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="ranking-list" id="ranking-artists-full">
                                <!-- Le classement complet sera généré par JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Groupes -->
                <div class="tab-pane fade" id="groups-ranking" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="ranking-list" id="ranking-groups-full">
                                <!-- Le classement complet sera généré par JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    
</body>
</html>
