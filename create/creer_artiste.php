<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require '../database/dbconnect.php';
    $pdo = dbconnect();

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
    $messageType = 'info';

    if (isset($_GET['success'])) {
        $message = 'Artiste ajouté avec succès !';
        $messageType = 'success';
    } elseif (isset($_GET['error'])) {
        if ($_GET['error'] == 'exists') {
            $message = 'Cet artiste existe déjà !';
            $messageType = 'danger';
        } elseif ($_GET['error'] == 'insert') {
            $message = 'Erreur lors de l\'ajout de l\'artiste.';
            $messageType = 'danger';
        } elseif ($_GET['error'] == 'fields') {
            $message = 'Veuillez remplir tous les champs obligatoires.';
            $messageType = 'danger';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomArtiste = $_POST['nomArtiste'] ?? '';
        $nomReel = $_POST['nomReel'] ?? '';
        $bio = $_POST['biographieCourte'] ?? '';
        $anneeNaissance = $_POST['anneeNaissance'] ?? '';

        $profilDir = 'uploads/artistes/profil/';
        $sonsDir = 'uploads/artistes/sons/';
        if (!file_exists($profilDir)) mkdir($profilDir, 0777, true);
        if (!file_exists($sonsDir)) mkdir($sonsDir, 0777, true);

        $nomArtisteClean = str_replace(' ', '_', $nomArtiste);
        $nomArtisteClean = preg_replace('/[^A-Za-z0-9_-]/', '', $nomArtisteClean);

        $imagePath = '';
        if (isset($_FILES['imageProfil']) && $_FILES['imageProfil']['error'] === UPLOAD_ERR_OK) {
            $imageExt = pathinfo($_FILES['imageProfil']['name'], PATHINFO_EXTENSION);
            $imagePath = $profilDir . $nomArtisteClean . '_profil.' . strtolower($imageExt);
            move_uploaded_file($_FILES['imageProfil']['tmp_name'], $imagePath);
        }

        $soundPath = '';
        if (isset($_FILES['cheminFichierMP3']) && $_FILES['cheminFichierMP3']['error'] === UPLOAD_ERR_OK) {
            $soundExt = pathinfo($_FILES['cheminFichierMP3']['name'], PATHINFO_EXTENSION);
            $soundPath = $sonsDir . $nomArtisteClean . '_son.' . strtolower($soundExt);
            move_uploaded_file($_FILES['cheminFichierMP3']['tmp_name'], $soundPath);
        }

        if ($nomArtiste && $soundPath && $imagePath) {
            $stmt = $pdo->prepare("SELECT ArtisteID FROM artiste WHERE NomArtiste = ?");
            $stmt->execute([$nomArtiste]);
            $existingArtist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingArtist) {
                header('Location: creer_artiste.php?error=exists');
                exit;
            } else {
                $stmt = $pdo->prepare("INSERT INTO artiste (NomArtiste, NomReel, BiographieCourte, AnneeNaissance, CheminFichierMP3, ImageProfil, StatusArtiste, UserID, DateProposition) VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?, NOW())");
                $stmt->execute([$nomArtiste, $nomReel, $bio, $anneeNaissance, $soundPath, $imagePath, $userId]);

                if ($stmt->rowCount() > 0) {
                    header('Location: creer_artiste.php?success=1');
                    exit;
                } else {
                    header('Location: creer_artiste.php?error=insert');
                    exit;
                }
            }
        } else {
            header('Location: creer_artiste.php?error=fields');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Créer un artiste - MyPulse</title>
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
                                <h3 class="card-title mb-0"><i class="bi bi-person-fill me-2"></i>Créer un nouvel artiste</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nomArtiste" class="form-label">Nom de l'artiste <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nomArtiste" name="nomArtiste" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nomReel" class="form-label">Nom réel</label>
                                            <input type="text" class="form-control" id="nomReel" name="nomReel">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="biographieCourte" class="form-label">Biographie courte</label>
                                        <textarea class="form-control" id="biographieCourte" name="biographieCourte" rows="3"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="anneeNaissance" class="form-label">Année de naissance</label>
                                        <input type="number" class="form-control" id="anneeNaissance" name="anneeNaissance" min="1900" max="2023">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="cheminFichierMP3" class="form-label">Fichier audio <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="cheminFichierMP3" name="cheminFichierMP3" accept="audio/*" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="imageProfil" class="form-label">Image de profil <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="imageProfil" name="imageProfil" accept="image/*" required>
                                        </div>
                                    </div>

                                     <div class="row mt-4">
                                    <div class="col text-end">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-plus-circle me-2"></i>Ajouter l'artiste
                                        </button>
                                    </div>
                                    <div class="col">
                                        <a href="../vote/voter.php?tab=artiste" class="btn btn-secondary btn-lg">
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>