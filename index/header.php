<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <!-- ... autres liens ... -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="../index/index.php">Accueil</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="../vote/voter.php">Voter</a></li>
            <li class="nav-item"><a class="nav-link" href="../vote/classement.php">Classement</a></li>
            <li class="nav-item"><a class="nav-link" href="../index/contact.php">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../login/mon_compte.php">Mon compte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../login/logout.php">Déconnexion</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="../login/connexion.php">Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
