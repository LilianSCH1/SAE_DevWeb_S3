<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require '../class/Database.php';
    $pdo = Database::getConnection();

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login/connexion.php');
        exit;
    }

    $userId = $_SESSION['user_id'];

    // Vérifier que l'utilisateur est connecté
    $stmt = $pdo->prepare("SELECT UserID FROM utilisateur WHERE UserID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: ../login/connexion.php');
        exit;
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomGroupe = $_POST['nomGroupe'] ?? '';
        $anneeFormation = $_POST['anneeFormation'] ?? null;
        $biographieCourte = $_POST['biographieCourte'] ?? '';

        // Dossiers organisés
        $profilDir = 'uploads/groupes/profil/';
        $sonsDir = 'uploads/groupes/sons/';
        if (!file_exists($profilDir)) mkdir($profilDir, 0777, true);
        if (!file_exists($sonsDir)) mkdir($sonsDir, 0777, true);

        $nomGroupeClean = str_replace(' ', '_', $nomGroupe);
        $nomGroupeClean = preg_replace('/[^A-Za-z0-9_-]/', '', $nomGroupeClean);

        $imagePath = '';
        if (isset($_FILES['imageGroupe']) && $_FILES['imageGroupe']['error'] === UPLOAD_ERR_OK) {
            $imageExt = pathinfo($_FILES['imageGroupe']['name'], PATHINFO_EXTENSION);
            $imagePath = $profilDir . $nomGroupeClean . '_profil.' . strtolower($imageExt);
            move_uploaded_file($_FILES['imageGroupe']['tmp_name'], $imagePath);
        }

        $soundPath = '';
        if (isset($_FILES['cheminFichierMP3']) && $_FILES['cheminFichierMP3']['error'] === UPLOAD_ERR_OK) {
            $soundExt = pathinfo($_FILES['cheminFichierMP3']['name'], PATHINFO_EXTENSION);
            $soundPath = $sonsDir . $nomGroupeClean . '_son.' . strtolower($soundExt);
            move_uploaded_file($_FILES['cheminFichierMP3']['tmp_name'], $soundPath);
        }

        if ($nomGroupe && $soundPath && $imagePath) {
            $stmt = $pdo->prepare("SELECT GroupeID FROM groupe WHERE NomGroupe = ?");
            $stmt->execute([$nomGroupe]);
            $existingGroup = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingGroup) {
                $message = 'Ce groupe existe déjà !';
            } else {
                $stmt = $pdo->prepare("INSERT INTO groupe (NomGroupe, AnneeFormation, BiographieCourte, CheminFichierMP3, ImageGroupe, StatusGroupe, UserID, DateProposition) VALUES (?, ?, ?, ?, ?, 'en_attente', ?, NOW())");
                $stmt->execute([$nomGroupe, $anneeFormation, $biographieCourte, $soundPath, $imagePath, $userId]);

                if ($stmt->rowCount() > 0) {
                    $message = 'Groupe ajouté avec succès !';
                } else {
                    $message = 'Erreur lors de l\'ajout du groupe.';
                }
            }
        } else {
            $message = 'Veuillez remplir tous les champs obligatoires.';
        }
    }
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un groupe - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../icons/logos/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="../icons/logos/favicon.svg">
    <link rel="shortcut icon" href="../icons/logos/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../icons/logos/apple-touch-icon.png">
    <link rel="manifest" href="../icons/logos/site.webmanifest">
</head>

<body>
    <?php require '../index/header.php'; ?>

    <section class="py-5" style="margin-top: 80px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header btn btn-primary btn-lg">
                            <h3 class="card-title mb-0"><i class="bi bi-people-fill me-2"></i>Créer un nouveau groupe</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($message): ?>
                                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nomGroupe" class="form-label">Nom du groupe <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nomGroupe" name="nomGroupe" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="anneeFormation" class="form-label">Année de formation</label>
                                        <input type="number" class="form-control" id="anneeFormation" name="anneeFormation" min="1900" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="biographieCourte" class="form-label">Biographie courte</label>
                                    <textarea class="form-control" id="biographieCourte" name="biographieCourte" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cheminFichierMP3" class="form-label">Fichier audio <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="cheminFichierMP3" name="cheminFichierMP3" accept="audio/*" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="imageGroupe" class="form-label">Image de profil <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="imageGroupe" name="imageGroupe" accept="image/*" required>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                        <div class="col text-end">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="bi bi-plus-circle me-2"></i>Ajouter le groupe
                                            </button>
                                        </div>
                                        <div class="col">
                                            <a href="../vote/voter.php?tab=groupe" class="btn btn-secondary btn-lg">
                                                <i class="bi bi-arrow-left-circle me-2"></i>Retour au vote
                                            </a>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>

    <!-- Cookie Pop-up -->
    <div id="cookie-popup" class="cookie-popup">
        <div class="cookie-popup-content">
            <div class="cookie-popup-header">
                <h5>🍪 Gestion des Cookies</h5>
                <button type="button" class="btn-close" aria-label="Fermer" onclick="closeCookiePopup()"></button>
            </div>
            <div class="cookie-popup-body">
                <h6>TYPES DE COOKIES UTILISÉS</h6>
                <p>Nous utilisons différents types de cookies pour améliorer votre expérience sur MyPulse :</p>
                <ul>
                    <li><strong>Cookies essentiels :</strong> Indispensables au fonctionnement, ils gèrent l'authentification, les votes uniques par catégorie et les sessions utilisateur. Aucun consentement n'est requis.</li>
                    <li><strong>Cookies analytiques :</strong> Anonymes, ils mesurent l'audience (pages vues, classements consultés) pour optimiser la plateforme. Consentement préalable via notre bandeau.</li>
                    <li><strong>Cookies fonctionnels :</strong> Personnalisent l'interface (thèmes sombre/clair, notifications) et intègrent les partages sociaux pour les résultats de concours. Aucun cookie publicitaire tiers n'est utilisé ; durée maximale de 6 mois, renouvelable avec consentement.</li>
                </ul>

                <h6>GESTION ET CONSENTEMENT</h6>
                <p>Lors de votre première visite, un bandeau collecte votre consentement exprès pour les cookies non essentiels. Modifiez vos préférences via l'icône en bas d'écran ou les paramètres de votre navigateur. Refuser les cookies analytiques n'empêche pas l'accès aux votes ou classements.</p>

                <h6>VOS DROITS</h6>
                <p>Conformément au RGPD, contactez mypulse.company@gmail.com pour accéder, rectifier ou supprimer les données cookies.</p>
            </div>
            <div class="cookie-popup-footer">
                <button type="button" class="btn btn-outline-primary me-2" onclick="manageCookiePreferences()">Gérer les préférences</button>
                <button type="button" class="btn btn-outline-secondary me-2" onclick="rejectNonEssentialCookies()">Refuser non-essentiels</button>
                <button type="button" class="btn btn-primary" onclick="acceptAllCookies()">Accepter tout</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>