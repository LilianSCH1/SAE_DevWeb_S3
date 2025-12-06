<?php
session_start();

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';
require_once __DIR__ . '/../controllers/UserController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/connexion.php');
    exit;
}

// créer le contrôleur AVANT de l'utiliser
$controller  = new UserController();
$currentUser = User::findById((int)$_SESSION['user_id']);
if (!$currentUser) {
    header('Location: ../login/logout.php');
    exit;
}

$errors  = [];
$success = [];

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $errors = $controller->updateProfile($currentUser, $_POST);

        if (empty($errors)) {
            $success[] = "Informations mises à jour.";
            $currentUser = User::findById((int)$_SESSION['user_id']);
        }
    } elseif ($action === 'update_password') {
        $errors = $controller->updatePassword($currentUser, $_POST);

        if (empty($errors)) {
            $success[] = "Mot de passe mis à jour.";
        }
    } elseif ($action === 'delete_account') {
        $controller->deleteAccount($currentUser);
        header('Location: ../index/index.php');
        exit;
    }
}

function roleLabel(string $role): string {
    switch ($role) {
        case 'admin':    return 'Administrateur';
        case 'certifie': return 'Utilisateur certifié';
        case 'basique':  return 'Utilisateur';
        case 'invite':   return 'Invité';
        default:         return $role;
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon compte - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
    <?php require '../index/header.php'; ?>

    <section class="py-5" style="margin-top: 60px;">
        <div class="container" >
            <h2 class="mb-4" >Mon compte</h2>

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
                                value="<?php echo htmlspecialchars($currentUser->pseudo); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="UserName" class="form-control"
                                    value="<?php echo htmlspecialchars($currentUser->firstName); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="UserSurname" class="form-control"
                                    value="<?php echo htmlspecialchars($currentUser->lastName); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="UserMail" class="form-control"
                                value="<?php echo htmlspecialchars($currentUser->email); ?>" required>
                        </div>

                        <p><strong>Rôle :</strong> <?php echo htmlspecialchars(roleLabel($currentUser->role)); ?></p>
                        <?php if ($currentUser->dateInscription): ?>
                            <p><strong>Date d’inscription :</strong>
                                <?php echo htmlspecialchars($currentUser->dateInscription); ?>
                            </p>
                        <?php endif; ?>

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
                <a href="../index/index.php" class="btn btn-secondary">Retour à l’accueil</a>
            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>