<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/User.php';
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
$userRole = $currentUser ? strtolower(trim($currentUser->role)) : null;
?>

<!-- Pied de page du site -->
<footer class="footer py-5">
    <div class="container">
        <div class="row">
            <!-- Section principale avec logo -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="footer-title">
                    <!-- Logo blanc pour le footer -->
                    <img src="../icons/logos/MyPulse_White-removebg-preview.png" alt="MyPulse" height="30" style="margin-right: 10px;">
                    MyPulse 🎵
                </h5>
                <p class="footer-description">La plateforme communautaire pour voter pour vos contenus musicaux préférés.</p>
                <!-- Liens vers les réseaux sociaux -->
                <div class="social-links">
                    <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <!-- Liens de navigation -->
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-4">
                        <h5>Navigation</h5>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="../index/index.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Accueil</a></li>
                            <!-- Même logique que le bouton "Voter maintenant" : redirection via redir_vote.php -->
                            <li><a href="../login/redir_vote.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Voter</a></li>
                            <li><a href="../vote/classement.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Classement</a></li>
                        </ul>
                    </div>
                    
                    <div class="col-md-4">
                        <h5>Entreprise</h5>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="#" onclick="event.preventDefault(); openPageModal('../pages/about.php', 'À propos')" style="color: rgba(255,255,255,0.7); text-decoration: none;">À propos</a></li>
                            <li><a href="../index/contact.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Contact</a></li>
                            <li><a href="../login/connexion.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Connexion</a></li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <h5>Légal</h5>
                        <ul style="list-style: none; padding: 0%;">
                            <li><a href="#" onclick="event.preventDefault(); openPageModal('../pages/terms_of_use.php', 'Conditions générales d\'utilisation')" style="color: rgba(255,255,255,0.7); text-decoration: none;">Conditions générales d'utilisation</a></li>
                            <li><a href="#" onclick="event.preventDefault(); openPageModal('../pages/privacy_policy.php', 'Politique de confidentialité')" style="color: rgba(255,255,255,0.7); text-decoration: none;">Politique de confidentialité</a></li>
                            <li><a href="#" onclick="event.preventDefault(); openPageModal('../pages/cookies_policy.php', 'Politique de cookie')" style="color: rgba(255,255,255,0.7); text-decoration: none;">Politique de cookie</a></li>
                            <li><a href="#" onclick="event.preventDefault(); openPageModal('../pages/legal_notices.php', 'Mentions légales')" style="color: rgba(255,255,255,0.7); text-decoration: none;">Mentions légales</a></li>
                        </ul>
                    </div>

        </div>
    </div>
</div>

<!-- Floating Cookie Icon -->
<div id="cookie-icon" class="cookie-floating-icon" onclick="openCookiePopup()" style="display: none;">
    🍪
</div>
        <!-- Séparateur -->
        <hr style="background-color: rgba(255,255,255,0.1); margin: 2rem 0;">
        <!-- Copyright -->
        <div class="text-center" style="color: rgba(255,255,255,0.7);">
            <p>© 2025 MyPulse. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="../script/script.js"></script>