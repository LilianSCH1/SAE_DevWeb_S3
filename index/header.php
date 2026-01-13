<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../class/User.php';
$currentUser = isset($_SESSION['user_id']) ? User::findById((int)$_SESSION['user_id']) : null;
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <!-- Logo et nom du site -->
        <a class="navbar-brand d-flex align-items-center" href="../index/index.php">
            <img id="logo" src="../icons/logos/MyPulse-removebg-preview.png" alt="My Pulse Logo" height="40" class="me-2">
            <span class="fw-bold">MyPulse</span>
        </a>
        <!-- Bouton toggle pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Menu collapsible -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link active" href="../index/index.php">Accueil</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($currentUser && in_array($currentUser->role, ['basique', 'admin', 'certifie'])): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="../vote/voter.php">Voter</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="../vote/classement.php">Classement</a></li>
            <?php if (isset($_SESSION['user_id']) && $currentUser && $currentUser->role === 'basique'): ?>
                <li class="nav-item"><a class="nav-link" href="../index/recrutement.php">Recrutement</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="../index/contact.php">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($currentUser && $currentUser->role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../login/dashboard.php">Dashboard</a>
                    </li>
                <?php endif; ?>

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