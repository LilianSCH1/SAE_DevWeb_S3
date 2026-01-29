<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle success/error messages from email sending
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $alertMessage = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Message envoyé avec succès ! Nous vous répondrons bientôt.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    } elseif ($_GET['status'] == 'error') {
        $errorMessage = isset($_GET['message']) ? urldecode($_GET['message']) : 'Une erreur est survenue lors de l\'envoi du message.';
        $alertMessage = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Erreur : ' . htmlspecialchars($errorMessage) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    } elseif ($_GET['status'] == 'invalid') {
        $alertMessage = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>Méthode non autorisée.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <?php require '../index/header.php'; ?>

    <?php if (isset($alertMessage)) echo $alertMessage; ?>

    <!-- Section de contact -->
    <section class="py-5" style="margin-top: 80px;">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Contactez-nous</span>
                <h2 class="section-title">Nous sommes là pour vous aider</h2>
                <p class="section-description">Une question ? Un problème ? N'hésitez pas à nous contacter</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row g-4">
                        <!-- Informations de contact -->
                        <div class="col-md-6">
                            <div class="contact-info">
                                <h4 class="mb-4">Informations de contact</h4>

                                <div class="contact-item d-flex align-items-center mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-envelope-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Email</h6>
                                        <p class="mb-0 text-muted">mypulse.company@gmail.com</p>
                                    </div>
                                </div>

                                <div class="contact-item d-flex align-items-center mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-telephone-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Téléphone</h6>
                                        <p class="mb-0 text-muted">01 23 45 67 89</p>
                                    </div>
                                </div>

                                <div class="contact-item d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Adresse</h6>
                                        <p class="mb-0 text-muted">123 Rue de la Musique<br>75000 Paris, France</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de contact -->
                        <div class="col-md-6">
                            <div class="contact-form">
                                <h4 class="mb-4">Envoyez-nous un message</h4>
                                <form id="contactForm" action="send_email.php" method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom complet</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Sujet</label>
                                        <select class="form-control" id="subject" name="subject" required>
                                            <option value="">Choisissez un sujet</option>
                                            <option value="support">Support technique</option>
                                            <option value="bug">Signalement de bug</option>
                                            <option value="suggestion">Suggestion</option>
                                            <option value="partnership">Partenariat</option>
                                            <option value="other">Autre</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-send me-2"></i>Envoyer le message
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
    
</body>
</html>
