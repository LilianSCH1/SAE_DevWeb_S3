<?php
session_start();
require_once '../database/dbconnect.php';

$pdo = dbconnect();
$errors = [];

// Debug erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// TRAITEMENT INSCRIPTION
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'register'
) {

    $pseudo    = trim($_POST['registerPseudo'] ?? '');
    $prenom    = trim($_POST['registerFirstName'] ?? '');
    $nom       = trim($_POST['registerLastName'] ?? '');
    $email     = trim($_POST['registerEmail'] ?? '');
    $password  = $_POST['registerPassword'] ?? '';
    $password2 = $_POST['registerConfirmPassword'] ?? '';

    if ($password !== $password2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (empty($errors)) {
        // Vérifier que l'email n'existe pas déjà
        $stmt = $pdo->prepare("SELECT UserID FROM utilisateur WHERE UserMail = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Un compte existe déjà avec cet email.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // INSERT : laisser UserID et DateInscription en auto
            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (UserPseudo, UserName, UserSurname, UserMail, UserPassword, Role)
                VALUES (?, ?, ?, ?, ?, 'basique')
            ");
            $stmt->execute([$pseudo, $prenom, $nom, $email, $hash]);


            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_email'] = $email;

            header('Location: ../index/index.php');
            exit;
        }
    }
}

// TRAITEMENT CONNEXION
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'login'
) {

    $email    = trim($_POST['loginEmail'] ?? '');
    $password = $_POST['loginPassword'] ?? '';

    $stmt = $pdo->prepare("SELECT UserID, UserPassword FROM utilisateur WHERE UserMail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['UserPassword'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['user_email'] = $email;
        header('Location: ../index/index.php');
        exit;
    } else {
        $errors[] = "Email ou mot de passe incorrect.";
    }
}

if (isset($_SESSION['user_id'])) {
    header('Location: ../login/mon_compte.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
    <?php require '../index/header.php'; ?>

    <section class="py-5" style="margin-top: 80px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <img src="../icons/logos/MyPulse_Black-removebg-preview.png" alt="MyPulse" height="60" class="mb-3">
                                <h3 class="card-title">Connexion à MyPulse</h3>
                                <p class="text-muted">Connectez-vous pour voter et participer</p>
                            </div>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $e): ?>
                                        <div><?php echo htmlspecialchars($e); ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <ul class="nav nav-tabs justify-content-center mb-4" id="authTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab"
                                        data-bs-target="#login" type="button" role="tab">Connexion</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="register-tab" data-bs-toggle="tab"
                                        data-bs-target="#register" type="button" role="tab">Inscription</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Connexion -->
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <form id="loginForm" method="POST">
                                        <input type="hidden" name="action" value="login">

                                        <div class="mb-3">
                                            <label for="loginEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control"
                                                id="loginEmail" name="loginEmail" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="loginPassword" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control"
                                                id="loginPassword" name="loginPassword" required>
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="rememberMe">
                                            <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 mb-3">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                        </button>

                                        <div class="text-center">
                                            <a href="#" class="text-decoration-none">Mot de passe oublié ?</a>
                                        </div>
                                    </form>
                                </div>

                                <!-- Inscription -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <form id="registerForm" method="POST">
                                        <input type="hidden" name="action" value="register">

                                        <div class="mb-3">
                                            <label for="registerPseudo" class="form-label">Pseudo</label>
                                            <input type="text" class="form-control"
                                                id="registerPseudo" name="registerPseudo" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="registerFirstName" class="form-label">Prénom</label>
                                                <input type="text" class="form-control"
                                                    id="registerFirstName" name="registerFirstName" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="registerLastName" class="form-label">Nom</label>
                                                <input type="text" class="form-control"
                                                    id="registerLastName" name="registerLastName" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="registerEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control"
                                                id="registerEmail" name="registerEmail" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="registerPassword" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control"
                                                id="registerPassword" name="registerPassword" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="registerConfirmPassword" class="form-label">Confirmer le mot de passe</label>
                                            <input type="password" class="form-control"
                                                id="registerConfirmPassword" name="registerConfirmPassword" required>
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="acceptTerms" required>
                                            <label class="form-check-label" for="acceptTerms">
                                                J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a>
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 mb-3">
                                            <i class="bi bi-person-plus me-2"></i>S'inscrire
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted mb-3">Ou continuer avec</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-outline-secondary flex-fill">
                                        <i class="bi bi-google me-2"></i>Google
                                    </button>
                                    <button class="btn btn-outline-secondary flex-fill">
                                        <i class="bi bi-instagram me-2"></i>Instagram
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/script.js"></script>
</body>

</html>