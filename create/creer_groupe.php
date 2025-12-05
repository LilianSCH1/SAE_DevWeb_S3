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
    $nomGroupe = $_POST['nomGroupe'] ?? '';
    $anneeFormation = $_POST['anneeFormation'] ?? null;
    $biographieCourte = $_POST['biographieCourte'] ?? '';
    $dureeMorceau = $_POST['dureeMorceau'] ?? null;

    $uploadDir = 'uploads/groupes/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    $musicPath = '';
    if (isset($_FILES['cheminFichierMP3']) && $_FILES['cheminFichierMP3']['error'] === UPLOAD_ERR_OK) {
        $musicExt = pathinfo($_FILES['cheminFichierMP3']['name'], PATHINFO_EXTENSION);
        $musicPath = $uploadDir . time() . '_musique.' . strtolower($musicExt);
        move_uploaded_file($_FILES['cheminFichierMP3']['tmp_name'], $musicPath);
    }

    $imagePath = '';
    if (isset($_FILES['imageGroupe']) && $_FILES['imageGroupe']['error'] === UPLOAD_ERR_OK) {
        $imageExt = pathinfo($_FILES['imageGroupe']['name'], PATHINFO_EXTENSION);
        $imagePath = $uploadDir . time() . '_groupe.' . strtolower($imageExt);
        move_uploaded_file($_FILES['imageGroupe']['tmp_name'], $imagePath);
    }

    if ($nomGroupe && $musicPath && $imagePath) {
        $stmt = $pdo->prepare("INSERT INTO groupe (NomGroupe, AnneeFormation, BiographieCourte, CheminFichierMP3, ImageGroupe, DureeMorceau, StatusGroupe, UserID) VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?)");
        $stmt->execute([$nomGroupe, $anneeFormation, $biographieCourte, $musicPath, $imagePath, $dureeMorceau, $userId]);

        if ($stmt->rowCount() > 0) {
            $message = 'Groupe ajouté avec succès !';
        } else {
            $message = 'Erreur lors de l\'ajout du groupe.';
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
                        <h3 class="card-title mb-0"><i class="bi bi-people-fill me-2" ></i>Créer un nouveau groupe</h3>
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
                                    <label for="dureeMorceau" class="form-label">Durée du morceau (en secondes)</label>
                                    <input type="number" class="form-control" id="dureeMorceau" name="dureeMorceau">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cheminFichierMP3" class="form-label">Fichier audio <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="cheminFichierMP3" name="cheminFichierMP3" accept="audio/*" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="imageGroupe" class="form-label">Image du groupe <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="imageGroupe" name="imageGroupe" accept="image/*" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Ajouter le groupe
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
