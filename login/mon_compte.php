<?php
session_start();
require_once '../database/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/connexion.php');
    exit;
}

$pdo = dbconnect();
$errors = [];
$success = [];

/* ... traitements update_info / update_password / delete_account ... */

// Récupération des infos APRES les traitements
$stmt = $pdo->prepare("
    SELECT UserPseudo, UserName, UserSurname, UserMail, Role, DateInscription
    FROM utilisateur
    WHERE UserID = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Au cas où le compte aurait été supprimé
    header('Location: ../login/logout.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mon compte - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>
    <?php require '../index/header.php'; ?>

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
                <a href="../index/index.php" class="btn btn-secondary">Retour à l’accueil</a>
            </div>
        </div>
    </section>

    <?php require '../index/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>