<?php
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$pdo = dbconnect();

$stmt = $pdo->prepare("
    SELECT UserPseudo, UserName, UserSurname, UserMail, Role, DateInscription
    FROM utilisateur
    WHERE UserID = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Compte supprimé ou problème
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

    <section class="py-5" style="margin-top:80px;">
        <div class="container">
            <h2 class="mb-4">Mon compte</h2>

            <div class="card">
                <div class="card-body">
                    <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['UserPseudo']); ?></p>
                    <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['UserName'] . ' ' . $user['UserSurname']); ?></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($user['UserMail']); ?></p>
                    <p><strong>Rôle :</strong> <?php echo htmlspecialchars($user['Role']); ?></p>
                    <p><strong>Date d’inscription :</strong> <?php echo htmlspecialchars($user['DateInscription']); ?></p>
                </div>
            </div>

            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Retour à l’accueil</a>
                <a href="logout.php" class="btn btn-outline-danger ms-2">Déconnexion</a>
            </div>
        </div>
    </section>

    <?php require 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>