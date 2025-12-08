<?php
date_default_timezone_set('Europe/Paris');
session_start();
require_once __DIR__ . '/../class/Database.php';

$pdo = Database::getConnection();
$step = 'forgot';
$message = '';
$messageClass = '';
$email = '';
$token = '';
$user_id = 0;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    // ÉTAPE 2 : Vérifier token reset
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT UserID, reset_expire, UserMail FROM utilisateur WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $expire = strtotime($user['reset_expire']);
        if ($expire >= time()) {
            $step = 'reset';
            $email = $user['UserMail'];
            $user_id = $user['UserID']; // ← SAUVEGARDER l'ID ICI
        } else {
            $message = "Le lien a expiré. Demandez un nouveau lien.";
            $messageClass = 'alert-danger';
        }
    } else {
        $message = "Lien invalide. Vérifiez l'URL.";
        $messageClass = 'alert-danger';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_reset'])) {
        // ÉTAPE 1 : Demander email
        $email = trim($_POST['email']);
        $stmt = $pdo->prepare("SELECT UserID FROM utilisateur WHERE UserMail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expire = ? WHERE UserID = ?");
            $stmt->execute([$token, $expire, $user['UserID']]);

            $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

            $message = "Lien généré ! Copiez et testez :<br><strong>" . htmlspecialchars($resetUrl) . "</strong>";
            $messageClass = 'alert-success';
        } else {
            $message = "Aucun compte trouvé avec cet email.";
            $messageClass = 'alert-danger';
        }
    } elseif (isset($_POST['reset_password'])) {
        // ÉTAPE 3 : Changer mot de passe ← CORRIGÉ ICI
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        if ($password === $password_confirm && strlen($password) >= 8) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // CORRIGÉ : utiliser UserID sauvegardé + vérifier impact
            $stmt = $pdo->prepare("UPDATE utilisateur SET UserPassword = ?, reset_token = NULL, reset_expire = NULL WHERE UserID = ?");
            $result = $stmt->execute([$hash, $user_id]);

            if ($result && $stmt->rowCount() > 0) {
                $step = 'success';
            } else {
                $message = "Erreur lors de la mise à jour. Réessayez.";
                $messageClass = 'alert-danger';
            }
        } else {
            $message = strlen($password) < 8 ? "Mot de passe trop court (8 caractères min)." : "Mots de passe différents.";
            $messageClass = 'alert-danger';
        }
    }
}

// DEBUG : Vérifier les colonnes
$stmt = $pdo->query("DESCRIBE utilisateur");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $step === 'success' ? 'Succès' : 'Reset mot de passe' ?> - MyPulse</title>
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

                                <?php if ($step === 'forgot'): ?>
                                    <h3>Mot de passe oublié ?</h3>
                                    <p class="text-muted">Entrez votre email</p>
                                <?php elseif ($step === 'reset'): ?>
                                    <h3>Nouveau mot de passe</h3>
                                    <p class="text-muted">Pour <?= htmlspecialchars($email) ?></p>
                                <?php elseif ($step === 'success'): ?>
                                    <h3 class="text-success">Mot de passe changé !</h3>
                                    <p class="text-muted">Connexion possible maintenant.</p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($message)): ?>
                                <div class="alert <?= $messageClass ?>"><?= $message ?></div>
                            <?php endif; ?>

                            <?php if ($step === 'forgot'): ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                    </div>
                                    <button type="submit" name="request_reset" class="btn btn-primary w-100">
                                        <i class="bi bi-envelope me-2"></i>Générer lien reset
                                    </button>
                                </form>

                            <?php elseif ($step === 'reset'): ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nouveau mot de passe (8+ caractères)</label>
                                        <div class="input-container">
                                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                            <span class="toggle-password" onclick="togglePassword('password')"><i class="bi bi-eye"></i></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password_confirm" class="form-label">Confirmer</label>
                                        <div class="input-container">
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
                                            <span class="toggle-password" onclick="togglePassword('password_confirm')"><i class="bi bi-eye"></i></span>
                                        </div>
                                    </div>
                                    <button type="submit" name="reset_password" class="btn btn-primary w-100">
                                        <i class="bi bi-lock-fill me-2"></i>Changer mot de passe
                                    </button>
                                </form>
                                <!-- DEBUG INFO -->
                                <small class="text-muted d-block mt-2">
                                    Debug: UserID=<?= $user_id ?>, Token=<?= substr($token, 0, 8) ?>...
                                </small>

                            <?php elseif ($step === 'success'): ?>
                                <div class="text-center">
                                    <i class="bi bi-check-circle-fill text-success mb-3" style="font-size: 4rem;"></i>
                                    <a href="../login/connexion.php" class="btn btn-primary w-100">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="text-center mt-4">
                                <a href="../login/connexion.php" class="btn btn-outline-secondary w-100">Retour connexion</a>
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