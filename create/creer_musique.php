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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $artiste = $_POST['artiste'] ?? '';

    // Dossiers organisés
    $sonsDir = 'uploads/musiques/sons/';
    $couverturesDir = 'uploads/musiques/couvertures/';
    if (!file_exists($sonsDir)) mkdir($sonsDir, 0777, true);
    if (!file_exists($couverturesDir)) mkdir($couverturesDir, 0777, true);

    $titreClean = str_replace(' ', '_', $titre);

    $musicPath = '';
    if (isset($_FILES['cheminFichierMP3']) && $_FILES['cheminFichierMP3']['error'] === UPLOAD_ERR_OK) {
        $musicExt = pathinfo($_FILES['cheminFichierMP3']['name'], PATHINFO_EXTENSION);
        $musicPath = $sonsDir . $titreClean . '_' . time() . '_musique.' . strtolower($musicExt);
        move_uploaded_file($_FILES['cheminFichierMP3']['tmp_name'], $musicPath);
    }

    $imagePath = '';
    if (isset($_FILES['imageCouverture']) && $_FILES['imageCouverture']['error'] === UPLOAD_ERR_OK) {
        $imageExt = pathinfo($_FILES['imageCouverture']['name'], PATHINFO_EXTENSION);
        $imagePath = $couverturesDir . $titreClean . '_' . time() . '_couverture.' . strtolower($imageExt);
        move_uploaded_file($_FILES['imageCouverture']['tmp_name'], $imagePath);
    }

    if ($titre && $artiste && $musicPath && $imagePath) {
        $tailleFichier = filesize($musicPath);
        $stmt = $pdo->prepare("INSERT INTO musique (Titre, Artiste, CheminFichierMP3, ImageCouverture, TailleFichier, StatusMusique, UserID, DateProposition) VALUES (?, ?, ?, ?, ?, 'en_attente', ?, NOW())");
        $stmt->execute([$titre, $artiste, $musicPath, $imagePath, $tailleFichier, $userId]);

        if ($stmt->rowCount() > 0) {
            $message = 'Musique ajoutée avec succès !';
        } else {
            $message = 'Erreur lors de l\'ajout de la musique.';
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
    <title>Créer une musique - MyPulse</title>
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
                        <h3 class="card-title mb-0"><i class="bi bi-music-note-beamed me-2"></i>Créer une nouvelle musique</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titre" class="form-label">Titre de la musique <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titre" name="titre" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="artiste" class="form-label">Artiste <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="artiste" name="artiste" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cheminFichierMP3" class="form-label">Fichier audio <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="cheminFichierMP3" name="cheminFichierMP3" accept="audio/*" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="imageCouverture" class="form-label">Image de couverture <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="imageCouverture" name="imageCouverture" accept="image/*" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter la musique
                                </button>
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
