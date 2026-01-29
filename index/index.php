<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyPulse - Votez pour vos musiques préférées</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>

<body>
    <?php require '../index/header.php'; ?>
    <?php require '../index/body.php'; ?>
    <?php require '../index/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../script/modals.js"></script>
    <script>
        // Pass PHP session data to JavaScript
        window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../script/script.js"></script>

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
</body>

</html>
