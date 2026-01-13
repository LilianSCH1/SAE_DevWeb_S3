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

                        <p><strong>Rôle :</strong> <?php echo htmlspecialchars(roleLabel($currentUser->role)); ?><?php if ($currentUser->role === 'certifie'): ?><i class="bi bi-patch-check-fill text-info ms-1" title="Membre certifié"></i><?php endif; ?><?php if ($currentUser->role === 'admin'): ?> <span class="badge bg-danger ms-1">Admin</span><?php endif; ?></p>
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
                            <div class="input-group">
                                <input type="password" name="currentPassword" id="currentPassword" class="form-control" required>
                                <span class="input-group-text password-toggle" data-target="currentPassword" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="newPassword" id="newPassword" class="form-control" required>
                                <span class="input-group-text password-toggle" data-target="newPassword" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="confirmNewPassword" id="confirmNewPassword" class="form-control" required>
                                <span class="input-group-text password-toggle" data-target="confirmNewPassword" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
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
    <script src="../script/modals.js"></script>
    <script src="../script/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.password-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });
        });
    </script>
</body>

</html>
