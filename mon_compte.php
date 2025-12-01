<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$pdo = dbconnect();
$errors = [];
$success = [];

// Mise à jour des infos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_info') {
    $pseudo = trim($_POST['UserPseudo'] ?? '');
    $prenom = trim($_POST['UserName'] ?? '');
    $nom    = trim($_POST['UserSurname'] ?? '');
    $email  = trim($_POST['UserMail'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET UserPseudo = ?, UserName = ?, UserSurname = ?, UserMail = ?
            WHERE UserID = ?
        ");
        $stmt->execute([$pseudo, $prenom, $nom, $email, $_SESSION['user_id']]);
        $success[] = "Informations mises à jour.";
        $_SESSION['user_email'] = $email;
    }
}

// Changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_password') {
    $current = $_POST['currentPassword'] ?? '';
    $new1    = $_POST['newPassword'] ?? '';
    $new2    = $_POST['confirmNewPassword'] ?? '';

    if ($new1 !== $new2) {
        $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT UserPassword FROM utilisateur WHERE UserID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userPwd = $stmt->fetchColumn();

        if (!$userPwd || !password_verify($current, $userPwd)) {
            $errors[] = "Mot de passe actuel incorrect.";
        } else {
            $hash = password_hash($new1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateur SET UserPassword = ? WHERE UserID = ?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $success[] = "Mot de passe mis à jour.";
        }
    }
}

// Suppression du compte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE UserID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Récupération des infos
$stmt = $pdo->prepare("
    SELECT UserPseudo, UserName, UserSurname, UserMail, Role, DateInscription
    FROM utilisateur
    WHERE UserID = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon compte - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require 'header.php'; ?>

    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">Mon compte</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?php echo htmlspecialchars($e); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php foreach ($success as $s): ?>
                        <div><?php echo htmlspecialchars($s); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Infos du compte -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Informations du compte</h4>
                    <form method="post">
                        <input type="hidden" name="action" value="update_info">

                        <div class="mb-3">
                            <label class="form-label">Pseudo</label>
                            <input type="text" name="UserPseudo" class="form-control"
                                value="<?php echo htmlspecialchars($user['UserPseudo']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="UserName" class="form-control"
                                    value="<?php echo htmlspecialchars($user['UserName']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="UserSurname" class="form-control"
                                    value="<?php echo htmlspecialchars($user['UserSurname']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="UserMail" class="form-control"
                                value="<?php echo htmlspecialchars($user['UserMail']); ?>" required>
                        </div>

                        <p><strong>Rôle :</strong> <?php echo htmlspecialchars($user['Role']); ?></p>
                        <p><strong>Date d’inscription :</strong> <?php echo htmlspecialchars($user['DateInscription']); ?></p>

                        <button type="submit" class="btn account-btn-primary">Enregistrer les modifications</button>
                    </form>
                </div>
            </div>

            <!-- Changement de mot de passe -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Changer le mot de passe</h4>
                    <form method="post">
                        <input type="hidden" name="action" value="update_password">

                        <div class="mb-3">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="currentPassword" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="newPassword" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="confirmNewPassword" class="form-control" required>
                        </div>

                        <button type="submit" class="btn account-btn-primary">Mettre à jour le mot de passe</button>
                    </form>
                </div>
            </div>

            <!-- Suppression du compte -->
            <div class="card border-danger">
                <div class="card-body">
                    <h4 class="mb-3 text-danger">Supprimer mon compte</h4>
                    <p class="text-muted">
                        Cette action est définitive. Toutes vos données liées au compte pourront être supprimées.
                    </p>
                    <form method="post" onsubmit="return confirm('Supprimer définitivement votre compte ?');">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="submit" class="btn account-btn-danger">Supprimer mon compte</button>
                    </form>
                </div>
            </div>

            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Retour à l’accueil</a>
            </div>
        </div>
    </section>

    <?php require 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>