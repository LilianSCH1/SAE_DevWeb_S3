<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recrutement - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<?php
require_once '../class/Database.php';
require_once '../class/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::getConnection();

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_certification'])) {
    if (isset($_SESSION['user_id'])) {
        $currentUser = User::findById((int)$_SESSION['user_id']);
        if ($currentUser && $currentUser->role === 'basique') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $age = (int)$_POST['age'];
            $story = trim($_POST['story']);
            $instagram = trim($_POST['instagram']);
            $twitter = trim($_POST['twitter']);
            $facebook = trim($_POST['facebook']);
            $youtube = trim($_POST['youtube']);
            $spotify = trim($_POST['spotify']);
            $deezer = trim($_POST['deezer']);
            $photo_identite = $_FILES['photo_identite'] ?? null;
            $screenshot = $_FILES['screenshot'] ?? null;

            if (!empty($nom) && !empty($prenom) && $age > 0 && !empty($story) && $photo_identite && $screenshot) {
                // Handle file uploads
                $photoPath = null;
                if ($photo_identite && $photo_identite['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../create/uploads/recrutement/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = uniqid() . '_' . basename($photo_identite['name']);
                    $photoPath = $uploadDir . $fileName;
                    if (move_uploaded_file($photo_identite['tmp_name'], $photoPath)) {
                        $photoPath = 'uploads/recrutement/' . $fileName; // Relative path for database
                    } else {
                        $message = "Erreur lors du téléchargement de la photo d'identité.";
                        $messageType = "danger";
                    }
                }

                $screenshotPath = null;
                if ($screenshot && $screenshot['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../create/uploads/recrutement/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = uniqid() . '_screenshot_' . basename($screenshot['name']);
                    $screenshotPath = $uploadDir . $fileName;
                    if (move_uploaded_file($screenshot['tmp_name'], $screenshotPath)) {
                        $screenshotPath = 'uploads/recrutement/' . $fileName; // Relative path for database
                    } else {
                        $message = "Erreur lors du téléchargement de la capture d'écran.";
                        $messageType = "danger";
                    }
                }

                if (!$message) {
                    // Prepare social media data as JSON
                    $reseaux_sociaux = json_encode([
                        'instagram' => $instagram ?: null,
                        'twitter' => $twitter ?: null,
                        'facebook' => $facebook ?: null,
                        'youtube' => $youtube ?: null,
                        'spotify' => $spotify ?: null,
                        'deezer' => $deezer ?: null
                    ]);

                    // Insert into recrutement table
                    $stmt = $pdo->prepare("INSERT INTO recrutement (UserID, Nom, Prenom, Age, Story, MyPulseAccount, PhotoIdentite, Screenshot) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $success = $stmt->execute([$currentUser->id, $nom, $prenom, $age, $story, $reseaux_sociaux, $photoPath, $screenshotPath]);

                    if ($success) {
                        $message = "Votre demande de recrutement a été soumise avec succès. Les administrateurs la examineront sous peu.";
                        $messageType = "success";
                    } else {
                        $message = "Erreur lors de la soumission de la demande. Veuillez réessayer.";
                        $messageType = "danger";
                    }
                }
            } else {
                $message = "Veuillez remplir tous les champs obligatoires.";
                $messageType = "warning";
            }
        } else {
            $message = "Vous n'êtes pas éligible pour cette demande.";
            $messageType = "warning";
        }
    } else {
        $message = "Vous devez être connecté pour faire une demande.";
        $messageType = "warning";
    }
}
?>

<?php require 'header.php'; ?>

<!-- Section Recrutement -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-subtitle">Recrutement</span>
            <h2 class="section-title">Devenir Certifié</h2>
            <p class="section-description">Rejoignez notre équipe de membres certifiés et contribuez à la qualité de notre plateforme. 
        En tant que membre certifié, vous aurez la possibilité de proposer du   contenu (musiques, artistes et groupes).</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h3 class="card-title text-center mb-4">Demande de Certification</h3>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php $currentUser = User::findById((int)$_SESSION['user_id']); ?>
                            <?php if ($currentUser && $currentUser->role === 'basique'): ?>
                                <form method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Votre prénom" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="age" class="form-label">Âge <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="age" name="age" placeholder="Votre âge" min="13" max="100" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="story" class="form-label">Histoire personnelle <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="story" name="story" rows="4" placeholder="Racontez votre histoire, vos motivations, votre parcours..." required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Réseaux sociaux (optionnel)</label>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="instagram" name="instagram" placeholder="Instagram (@username)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="twitter" name="twitter" placeholder="Twitter (@username)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="facebook" name="facebook" placeholder="Facebook (profil ou page)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="youtube" name="youtube" placeholder="YouTube (chaîne)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="spotify" name="spotify" placeholder="Spotify (profil artiste)">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="deezer" name="deezer" placeholder="Deezer (profil artiste)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="photo_identite" class="form-label">Photo d'identité <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="photo_identite" name="photo_identite" accept="image/*" required>
                                        <div class="form-text">Formats acceptés: JPG, PNG, GIF. Taille maximale: 5MB.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="screenshot" class="form-label">Capture d'écran de story <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="screenshot" name="screenshot" accept="image/*" required>
                                        <div class="form-text">Prenez une capture d'écran d'une story sur n'importe quel réseau social où vous mentionnez @MyPulse et votre compte. Formats acceptés: JPG, PNG, GIF. Taille maximale: 5MB.</div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" name="apply_certification" class="btn btn-primary btn-lg">Envoyer la demande</button>
                                    </div>
                                </form>
                            <?php elseif ($currentUser && $currentUser->role === 'certifie'): ?>
                                <div class="alert alert-success text-center">
                                    <h4>Vous êtes déjà certifié !</h4>
                                    <p>Merci pour votre contribution à la communauté MyPulse.</p>
                                </div>
                            <?php elseif ($currentUser && $currentUser->role === 'admin'): ?>
                                <div class="alert alert-info text-center">
                                    <h4>Vous êtes administrateur</h4>
                                    <p>Vous avez déjà tous les droits sur la plateforme.</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                <h4>Connexion requise</h4>
                                <p>Vous devez être connecté pour faire une demande de certification.</p>
                                <a href="../login/connexion.php" class="btn btn-primary">Se connecter</a>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6">
                                <h5>Avantages de la certification</h5>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Accès aux outils de proposition de contenu</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Badge spécial sur votre profil</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Reconnaissance de votre engagement</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Responsabilités</h5>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Proposition récurrente de contenus</li>
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Respect des règles communautaires</li>
                                    <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Engagement actif</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../script/script.js"></script>
</body>
</html>
