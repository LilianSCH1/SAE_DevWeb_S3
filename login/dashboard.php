<?php
session_start();

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/connexion.php');
    exit;
}

$currentUser = User::findById((int)$_SESSION['user_id']);
if (!$currentUser) {
    header('Location: ../login/connexion.php');
    exit;
}

require_once '../database/dbconnect.php';
$pdo = dbconnect();

// Traitement des actions de validation/refus/suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    // Gestion des recrutements
    if ($action === 'accepter_recrutement' && $id > 0) {
        // Accepter la demande de recrutement
        $stmt = $pdo->prepare("UPDATE recrutement SET Status = 'accepte', DateDecision = NOW(), AdminID = ? WHERE RecrutementID = ?");
        $stmt->execute([$currentUser->id, $id]);

        // Changer le rôle de l'utilisateur en 'certifie'
        $stmt = $pdo->prepare("SELECT UserID FROM recrutement WHERE RecrutementID = ?");
        $stmt->execute([$id]);
        $userId = $stmt->fetchColumn();
        if ($userId) {
            $stmt = $pdo->prepare("UPDATE utilisateur SET Role = 'certifie' WHERE UserID = ?");
            $stmt->execute([$userId]);
        }
    } elseif ($action === 'refuser_recrutement' && $id > 0) {
        // Refuser la demande de recrutement (supprimer)
        $stmt = $pdo->prepare("DELETE FROM recrutement WHERE RecrutementID = ?");
        $stmt->execute([$id]);
    }

    if (($action === 'valider' || $action === 'refuser') && $id > 0) {
        $newStatus = $action === 'valider' ? 'valide' : 'refusee';

        if ($type === 'artiste') {
            $stmt = $pdo->prepare("UPDATE artiste SET StatusArtiste = ? WHERE ArtisteID = ?");
            $stmt->execute([$newStatus, $id]);
        } elseif ($type === 'musique') {
            $stmt = $pdo->prepare("UPDATE musique SET StatusMusique = ? WHERE MusiqueID = ?");
            $stmt->execute([$newStatus, $id]);
        } elseif ($type === 'groupe') {
            $stmt = $pdo->prepare("UPDATE groupe SET StatusGroupe = ? WHERE GroupeID = ?");
            $stmt->execute([$newStatus, $id]);
        }
    } elseif ($action === 'supprimer' && $id > 0) {
        // Récupérer le statut actuel
        $currentStatus = null;
        if ($type === 'artiste') {
            $stmt = $pdo->prepare("SELECT StatusArtiste FROM artiste WHERE ArtisteID = ?");
            $stmt->execute([$id]);
            $currentStatus = $stmt->fetchColumn();
        } elseif ($type === 'musique') {
            $stmt = $pdo->prepare("SELECT StatusMusique FROM musique WHERE MusiqueID = ?");
            $stmt->execute([$id]);
            $currentStatus = $stmt->fetchColumn();
        } elseif ($type === 'groupe') {
            $stmt = $pdo->prepare("SELECT StatusGroupe FROM groupe WHERE GroupeID = ?");
            $stmt->execute([$id]);
            $currentStatus = $stmt->fetchColumn();
        }

        if ($currentStatus === 'archive_suppr') {
            // Suppression définitive
            if ($type === 'artiste') {
                $stmt = $pdo->prepare("DELETE FROM artiste WHERE ArtisteID = ?");
                $stmt->execute([$id]);
            } elseif ($type === 'musique') {
                $stmt = $pdo->prepare("DELETE FROM musique WHERE MusiqueID = ?");
                $stmt->execute([$id]);
            } elseif ($type === 'groupe') {
                $stmt = $pdo->prepare("DELETE FROM groupe WHERE GroupeID = ?");
                $stmt->execute([$id]);
            }
        } else {
            // Archiver pour suppression
            if ($type === 'artiste') {
                $stmt = $pdo->prepare("UPDATE artiste SET StatusArtiste = 'archive_suppr' WHERE ArtisteID = ?");
                $stmt->execute([$id]);
            } elseif ($type === 'musique') {
                $stmt = $pdo->prepare("UPDATE musique SET StatusMusique = 'archive_suppr' WHERE MusiqueID = ?");
                $stmt->execute([$id]);
            } elseif ($type === 'groupe') {
                $stmt = $pdo->prepare("UPDATE groupe SET StatusGroupe = 'archive_suppr' WHERE GroupeID = ?");
                $stmt->execute([$id]);
            }
        }
    } elseif ($action === 'clear_all') {
        $status = $_POST['status'] ?? 'archive_suppr';

        // Suppression sécurisée de tous les contenus pour un statut donné
        $stmt = $pdo->prepare("DELETE FROM artiste WHERE StatusArtiste = ?");
        $stmt->execute([$status]);

        $stmt = $pdo->prepare("DELETE FROM musique WHERE StatusMusique = ?");
        $stmt->execute([$status]);

        $stmt = $pdo->prepare("DELETE FROM groupe WHERE StatusGroupe = ?");
        $stmt->execute([$status]);
    } elseif ($action === 'reset_archive') {
        // Remettre tous les contenus archivés en valide
        $pdo->exec("UPDATE artiste SET StatusArtiste = 'valide' WHERE StatusArtiste = 'archive_top'");
        $pdo->exec("UPDATE musique SET StatusMusique = 'valide' WHERE StatusMusique = 'archive_top'");
        $pdo->exec("UPDATE groupe SET StatusGroupe = 'valide' WHERE StatusGroupe = 'archive_top'");
    } elseif ($action === 'retablir' && $id > 0) {
        // Rétablir le contenu en attente
        if ($type === 'artiste') {
            $stmt = $pdo->prepare("UPDATE artiste SET StatusArtiste = 'en_attente' WHERE ArtisteID = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'musique') {
            $stmt = $pdo->prepare("UPDATE musique SET StatusMusique = 'en_attente' WHERE MusiqueID = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'groupe') {
            $stmt = $pdo->prepare("UPDATE groupe SET StatusGroupe = 'en_attente' WHERE GroupeID = ?");
            $stmt->execute([$id]);
        }
    }

    // Pour éviter le repost en cas de F5
    header('Location: dashboard.php');
    exit;
}

// Fonction pour récupérer les contenus par statut
function getContentsByStatus(PDO $pdo, string $status): array
{
    $contents = [];

    // Musiques
    $musiques = $pdo->prepare("
        SELECT 'musique' as type, MusiqueID as id, Titre as nom, Artiste as sous_nom, ImageCouverture as image, CheminFichierMP3 as audio, DateProposition, AnneePublication as annee, NULL as biographie
        FROM musique
        WHERE StatusMusique = ?
    ");
    $musiques->execute([$status]);
    $contents = array_merge($contents, $musiques->fetchAll(PDO::FETCH_ASSOC));

    // Artistes
    $artistes = $pdo->prepare("
        SELECT 'artiste' as type, ArtisteID as id, NomArtiste as nom, NomReel as sous_nom, ImageProfil as image, CheminFichierMP3 as audio, DateProposition, AnneeNaissance as annee, BiographieCourte as biographie
        FROM artiste
        WHERE StatusArtiste = ?
    ");
    $artistes->execute([$status]);
    $contents = array_merge($contents, $artistes->fetchAll(PDO::FETCH_ASSOC));

    // Groupes
    $groupes = $pdo->prepare("
        SELECT 'groupe' as type, GroupeID as id, NomGroupe as nom, NULL as sous_nom, ImageGroupe as image, CheminFichierMP3 as audio, DateProposition, AnneeFormation as annee, BiographieCourte as biographie
        FROM groupe
        WHERE StatusGroupe = ?
    ");
    $groupes->execute([$status]);
    $contents = array_merge($contents, $groupes->fetchAll(PDO::FETCH_ASSOC));

    // Trier par DateProposition DESC
    usort($contents, function ($a, $b) {
        return strtotime($b['DateProposition']) - strtotime($a['DateProposition']);
    });

    return $contents;
}

// Récupération des contenus par statut
$en_attente          = getContentsByStatus($pdo, 'en_attente');
$archive_classement  = getContentsByStatus($pdo, 'archive_top');
$archive_suppression = getContentsByStatus($pdo, 'archive_suppr');
$refuses             = getContentsByStatus($pdo, 'refusee');

// Récupération des demandes de recrutement
$recrutements = $pdo->prepare("
    SELECT r.*, u.UserPseudo as pseudo, u.UserMail as email
    FROM recrutement r
    JOIN utilisateur u ON r.UserID = u.UserID
    WHERE r.Status = 'en_attente'
    ORDER BY r.DateSoumission DESC
");
$recrutements->execute();
$recrutements = $recrutements->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - MyPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/dashboard-cards.css">
    <link rel="icon" type="image/png" href="../icons/logos/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="../icons/logos/favicon.svg">
    <link rel="shortcut icon" href="../icons/logos/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../icons/logos/apple-touch-icon.png">
    <link rel="manifest" href="../icons/logos/site.webmanifest">
</head>

<body>
    <?php require_once '../index/header.php'; ?>

    <section class="py-5" style="margin-top:80px; min-height:80vh;">
        <div class="container">
            <div class="section-header text-center mb-5">
                <span class="section-subtitle">Administration</span>
                <h2 class="section-title">Dashboard - Gestion des contenus</h2>
            </div>

            <ul class="nav nav-pills justify-content-center mb-4" id="dashboardTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-en-attente" data-bs-toggle="pill"
                        data-bs-target="#pane-en-attente" type="button" role="tab">En attente</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-recrutement" data-bs-toggle="pill"
                        data-bs-target="#pane-recrutement" type="button" role="tab">Recrutement</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-refuses" data-bs-toggle="pill"
                        data-bs-target="#pane-refuses" type="button" role="tab">Refusés</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-archive-classement" data-bs-toggle="pill"
                        data-bs-target="#pane-archive-classement" type="button" role="tab">Archive classement</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-archive-suppression" data-bs-toggle="pill"
                        data-bs-target="#pane-archive-suppression" type="button" role="tab">Archive suppression</button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- EN ATTENTE -->
                <div class="tab-pane fade show active" id="pane-en-attente" role="tabpanel" aria-labelledby="tab-en-attente">
                    <?php if (!empty($en_attente)) { ?>
                        <div class="d-flex justify-content-end mb-3">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="clear_all">
                                <input type="hidden" name="status" value="en_attente">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer tous les contenus en attente ?')">Clear All</button>
                            </form>
                        </div>
                    <?php } ?>

                    <div class="card-list">
                        <?php if (empty($en_attente)) { ?>
                            <div class="empty-state py-5">
                                <p class="text-muted text-nowrap">Aucun contenu en attente de validation</p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($en_attente as $content) { ?>
                                <div class="content-card">

                                    <!-- HEADER -->
                                    <div class="content-card-header">
                                        <div class="content-card-info">
                                            <h3 class="content-card-title">
                                                <?php echo htmlspecialchars($content['nom']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($content['type']); ?>)</small>
                                            </h3>
                                            <?php if (!empty($content['sous_nom'])) { ?>
                                                <p class="content-card-artist">
                                                    <?php echo htmlspecialchars($content['sous_nom']); ?>
                                                </p>
                                            <?php } ?>
                                        </div>

                                        <!-- Bouton + / - description -->
                                        <button type="button" class="toggle-desc-btn">+</button>

                                        <span class="content-card-date">
                                            Proposé le <?php echo htmlspecialchars($content['DateProposition']); ?>
                                        </span>
                                    </div>

                                    <!-- IMAGE -->
                                    <div class="content-card-image">
                                        <img src="../create/<?php echo htmlspecialchars($content['image']); ?>"
                                            alt="<?php echo htmlspecialchars($content['type']); ?>">
                                    </div>

                                    <!-- BODY : description sous l'image -->
                                    <div class="content-card-body">
                                        <div class="content-card-separator"></div>

                                        <?php if (!empty($content['annee'])) { ?>
                                            <p class="content-card-subtitle">
                                                <?php
                                                echo $content['type'] === 'musique'
                                                    ? 'Année de publication'
                                                    : ($content['type'] === 'artiste'
                                                        ? 'Année de naissance'
                                                        : 'Année de formation');
                                                ?>
                                                : <?php echo htmlspecialchars($content['annee']); ?>
                                            </p>
                                        <?php } ?>

                                        <p class="content-card-description">
                                            <?php echo htmlspecialchars($content['biographie'] ?? 'Aucune description fournie.'); ?>
                                        </p>

                                        <?php if (!empty($content['audio'])) { ?>
                                            <audio controls class="content-card-audio">
                                                <source src="../create/<?php echo htmlspecialchars($content['audio']); ?>" type="audio/mpeg">
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                        <?php } ?>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="content-card-actions">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="valider">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-success">Valider</button>
                                        </form>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="refuser">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Refuser</button>
                                        </form>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir archiver ce contenu pour suppression ?')">Archiver</button>
                                        </form>
                                    </div>

                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- RECRUTEMENT -->
                <div class="tab-pane fade" id="pane-recrutement" role="tabpanel" aria-labelledby="tab-recrutement">
                    <div class="card-list">
                        <?php if (empty($recrutements)) { ?>
                            <div class="empty-state py-5">
                                <p class="text-muted text-nowrap">Aucune demande de recrutement en attente</p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($recrutements as $recrutement) { ?>
                                <div class="content-card">
                                    <!-- HEADER -->
                                    <div class="content-card-header">
                                        <div class="content-card-info">
                                            <h3 class="content-card-title">
                                                Demande de <?php echo htmlspecialchars($recrutement['pseudo']); ?>
                                                <small class="text-muted">(Recrutement)</small>
                                            </h3>
                                            <p class="content-card-artist">
                                                <?php echo htmlspecialchars($recrutement['email']); ?>
                                            </p>
                                        </div>
                                        <span class="content-card-date">
                                            Soumise le <?php echo htmlspecialchars($recrutement['DateSoumission']); ?>
                                        </span>
                                    </div>

                                    <!-- BODY -->
                                    <div class="content-card-body">
                                        <div class="content-card-separator"></div>
                                        <p class="content-card-subtitle">
                                            Nom : <?php echo htmlspecialchars($recrutement['Nom']); ?> <?php echo htmlspecialchars($recrutement['Prenom']); ?>
                                        </p>
                                        <p class="content-card-subtitle">
                                            Âge : <?php echo htmlspecialchars($recrutement['Age']); ?> ans
                                        </p>
                                        <p class="content-card-subtitle">
                                            Comptes sociaux :
                                            <?php
                                            $links = [];

                                            // Helper function to build social link
                                            function buildSocialLink($value, $platform, $baseUrl) {
                                                if (empty($value)) return null;
                                                // If value already contains a full URL, use it directly
                                                if (preg_match('/^https?:\/\//', $value)) {
                                                    return '<a href="' . htmlspecialchars($value) . '" target="_blank">' . htmlspecialchars($platform) . '</a>';
                                                }
                                                // Otherwise, prepend the base URL
                                                return '<a href="' . $baseUrl . htmlspecialchars($value) . '" target="_blank">' . htmlspecialchars($platform) . '</a>';
                                            }

                                            // Check separate columns first
                                            $instagramLink = buildSocialLink($recrutement['Instagram'], 'Instagram', 'https://instagram.com/');
                                            if ($instagramLink) $links[] = $instagramLink;

                                            $twitterLink = buildSocialLink($recrutement['Twitter'], 'Twitter', 'https://twitter.com/');
                                            if ($twitterLink) $links[] = $twitterLink;

                                            $facebookLink = buildSocialLink($recrutement['Facebook'], 'Facebook', 'https://facebook.com/');
                                            if ($facebookLink) $links[] = $facebookLink;

                                            $youtubeLink = buildSocialLink($recrutement['Youtube'], 'YouTube', 'https://youtube.com/');
                                            if ($youtubeLink) $links[] = $youtubeLink;

                                            $spotifyLink = buildSocialLink($recrutement['Spotify'], 'Spotify', 'https://open.spotify.com/artist/');
                                            if ($spotifyLink) $links[] = $spotifyLink;

                                            $deezerLink = buildSocialLink($recrutement['Deezer'], 'Deezer', 'https://www.deezer.com/artist/');
                                            if ($deezerLink) $links[] = $deezerLink;

                                            // If no links from separate columns, try JSON fallback
                                            if (empty($links) && !empty($recrutement['MyPulseAccount'])) {
                                                $socialData = json_decode($recrutement['MyPulseAccount'], true);
                                                if (is_array($socialData)) {
                                                    foreach ($socialData as $platform => $link) {
                                                        if (!empty($link)) {
                                                            if ($platform === 'instagram') {
                                                                $url = 'https://instagram.com/' . $link;
                                                            } elseif ($platform === 'twitter') {
                                                                $url = 'https://twitter.com/' . $link;
                                                            } elseif ($platform === 'facebook') {
                                                                $url = 'https://facebook.com/' . $link;
                                                            } elseif ($platform === 'youtube') {
                                                                $url = 'https://youtube.com/' . $link;
                                                            } elseif ($platform === 'spotify') {
                                                                $url = 'https://open.spotify.com/artist/' . $link;
                                                            } elseif ($platform === 'deezer') {
                                                                $url = 'https://www.deezer.com/artist/' . $link;
                                                            } else {
                                                                $url = $link;
                                                            }
                                                            $links[] = '<a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($platform) . '</a>';
                                                        }
                                                    }
                                                }
                                            }

                                            if (!empty($links)) {
                                                echo implode(', ', $links);
                                            } else {
                                                echo 'Aucun compte social fourni';
                                            }
                                            ?>
                                        </p>
                                        <p class="content-card-description">
                                            Histoire : <?php echo htmlspecialchars($recrutement['Story'] ?? 'Aucune histoire fournie.'); ?>
                                        </p>
                                        <?php if (!empty($recrutement['PhotoIdentite'])) { ?>
                                            <p class="content-card-subtitle">
                                                Photo d'identité : <a href="../create/<?php echo htmlspecialchars($recrutement['PhotoIdentite']); ?>" target="_blank">Voir</a>
                                            </p>
                                        <?php } ?>
                                        <?php if (!empty($recrutement['Screenshot'])) { ?>
                                            <p class="content-card-subtitle">
                                                Screenshot : <a href="../create/<?php echo htmlspecialchars($recrutement['Screenshot']); ?>" target="_blank">Voir</a>
                                            </p>
                                        <?php } ?>
                                        <?php if (!empty($recrutement['Motivation'])) { ?>
                                            <p class="content-card-subtitle">
                                                Motivation : <?php echo htmlspecialchars($recrutement['Motivation']); ?>
                                            </p>
                                        <?php } ?>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="content-card-actions">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="accepter_recrutement">
                                            <input type="hidden" name="id" value="<?php echo $recrutement['RecrutementID']; ?>">
                                            <button type="submit" class="btn btn-success">Accepter</button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="refuser_recrutement">
                                            <input type="hidden" name="id" value="<?php echo $recrutement['RecrutementID']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette demande de recrutement ?')">Refuser</button>
                                        </form>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- REFUSES -->
                <div class="tab-pane fade" id="pane-refuses" role="tabpanel" aria-labelledby="tab-refuses">
                    <?php if (!empty($refuses)) { ?>
                        <div class="d-flex justify-content-end mb-3">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="clear_all">
                                <input type="hidden" name="status" value="refusee">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer tous les contenus refusés ?')">Clear All</button>
                            </form>
                        </div>
                    <?php } ?>

                    <div class="card-list">
                        <?php if (empty($refuses)) { ?>
                            <div class="empty-state py-5">
                                <p class="text-muted text-nowrap">Aucun contenu refusé</p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($refuses as $content) { ?>
                                <div class="content-card">

                                    <!-- HEADER -->
                                    <div class="content-card-header">
                                        <div class="content-card-info">
                                            <h3 class="content-card-title">
                                                <?php echo htmlspecialchars($content['nom']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($content['type']); ?>)</small>
                                            </h3>
                                            <?php if (!empty($content['sous_nom'])) { ?>
                                                <p class="content-card-artist">
                                                    <?php echo htmlspecialchars($content['sous_nom']); ?>
                                                </p>
                                            <?php } ?>
                                        </div>

                                        <!-- Bouton + / - description -->
                                        <button type="button" class="toggle-desc-btn">+</button>

                                        <span class="content-card-date">
                                            Proposé le <?php echo htmlspecialchars($content['DateProposition']); ?>
                                        </span>
                                    </div>

                                    <!-- IMAGE -->
                                    <div class="content-card-image">
                                        <img src="../create/<?php echo htmlspecialchars($content['image']); ?>"
                                            alt="<?php echo htmlspecialchars($content['type']); ?>">
                                    </div>

                                    <!-- BODY : description sous l'image -->
                                    <div class="content-card-body">
                                        <div class="content-card-separator"></div>

                                        <?php if (!empty($content['annee'])) { ?>
                                            <p class="content-card-subtitle">
                                                <?php
                                                echo $content['type'] === 'musique'
                                                    ? 'Année de publication'
                                                    : ($content['type'] === 'artiste'
                                                        ? 'Année de naissance'
                                                        : 'Année de formation');
                                                ?>
                                                : <?php echo htmlspecialchars($content['annee']); ?>
                                            </p>
                                        <?php } ?>

                                        <p class="content-card-description">
                                            <?php echo htmlspecialchars($content['biographie'] ?? 'Aucune description fournie.'); ?>
                                        </p>

                                        <?php if (!empty($content['audio'])) { ?>
                                            <audio controls class="content-card-audio">
                                                <source src="../create/<?php echo htmlspecialchars($content['audio']); ?>" type="audio/mpeg">
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                        <?php } ?>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="content-card-actions">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="retablir">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-warning">Rétablir</button>
                                        </form>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir archiver ce contenu pour suppression ?')">Supprimer</button>
                                        </form>
                                    </div>

                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- ARCHIVE CLASSEMENT -->
                <div class="tab-pane fade" id="pane-archive-classement" role="tabpanel" aria-labelledby="tab-archive-classement">
                    <?php if (!empty($archive_classement)) { ?>
                        <div class="d-flex justify-content-start mb-3">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="reset_archive">
                                <button type="submit" class="btn btn-warning me-3" onclick="return confirm('Êtes-vous sûr de vouloir remettre tous les éléments archivés en valide ?')">Remettre Archives en Valide</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="clear_all">
                                <input type="hidden" name="status" value="archive_top">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer tous les contenus archivés pour classement ?')">Clear All</button>
                            </form>
                        </div>
                    <?php } ?>

                    <div class="card-list">
                        <?php if (empty($archive_classement)) { ?>
                            <div class="empty-state py-5">
                                <p class="text-muted text-nowrap">Aucun contenu archivé pour classement</p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($archive_classement as $content) { ?>
                                <div class="content-card">

                                    <!-- HEADER -->
                                    <div class="content-card-header">
                                        <div class="content-card-info">
                                            <h3 class="content-card-title">
                                                <?php echo htmlspecialchars($content['nom']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($content['type']); ?>)</small>
                                            </h3>
                                            <?php if (!empty($content['sous_nom'])) { ?>
                                                <p class="content-card-artist">
                                                    <?php echo htmlspecialchars($content['sous_nom']); ?>
                                                </p>
                                            <?php } ?>
                                        </div>

                                        <!-- Bouton + / - description -->
                                        <button type="button" class="toggle-desc-btn">+</button>

                                        <span class="content-card-date">
                                            Proposé le <?php echo htmlspecialchars($content['DateProposition']); ?>
                                        </span>
                                    </div>

                                    <!-- IMAGE -->
                                    <div class="content-card-image">
                                        <img src="../create/<?php echo htmlspecialchars($content['image']); ?>"
                                            alt="<?php echo htmlspecialchars($content['type']); ?>">
                                    </div>

                                    <!-- BODY : description sous l'image -->
                                    <div class="content-card-body">
                                        <div class="content-card-separator"></div>

                                        <?php if (!empty($content['annee'])) { ?>
                                            <p class="content-card-subtitle">
                                                <?php
                                                echo $content['type'] === 'musique'
                                                    ? 'Année de publication'
                                                    : ($content['type'] === 'artiste'
                                                        ? 'Année de naissance'
                                                        : 'Année de formation');
                                                ?>
                                                : <?php echo htmlspecialchars($content['annee']); ?>
                                            </p>
                                        <?php } ?>

                                        <p class="content-card-description">
                                            <?php echo htmlspecialchars($content['biographie'] ?? 'Aucune description fournie.'); ?>
                                        </p>

                                        <?php if (!empty($content['audio'])) { ?>
                                            <audio controls class="content-card-audio">
                                                <source src="../create/<?php echo htmlspecialchars($content['audio']); ?>" type="audio/mpeg">
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                        <?php } ?>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="content-card-actions">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="retablir">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-warning">Rétablir</button>
                                        </form>

                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir archiver ce contenu pour suppression ?')">Supprimer</button>
                                        </form>
                                    </div>

                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <!-- ARCHIVE SUPPRESSION -->
                <div class="tab-pane fade" id="pane-archive-suppression" role="tabpanel" aria-labelledby="tab-archive-suppression">
                    <?php if (!empty($archive_suppression)) { ?>
                        <div class="d-flex justify-content-end mb-3">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="clear_all">
                                <input type="hidden" name="status" value="archive_suppr">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement tous les contenus archivés pour suppression ?')">Clear All</button>
                            </form>
                        </div>
                    <?php } ?>

                    <div class="card-list">
                        <?php if (empty($archive_suppression)) { ?>
                            <div class="empty-state py-5">
                                <p class="text-muted text-nowrap">Aucun contenu archivé pour suppression</p>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($archive_suppression as $content) { ?>
                                <div class="content-card">

                                    <!-- HEADER -->
                                    <div class="content-card-header">
                                        <div class="content-card-info">
                                            <h3 class="content-card-title">
                                                <?php echo htmlspecialchars($content['nom']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($content['type']); ?>)</small>
                                            </h3>
                                            <?php if (!empty($content['sous_nom'])) { ?>
                                                <p class="content-card-artist">
                                                    <?php echo htmlspecialchars($content['sous_nom']); ?>
                                                </p>
                                            <?php } ?>
                                        </div>

                                        <!-- Bouton + / - description -->
                                        <button type="button" class="toggle-desc-btn">+</button>

                                        <span class="content-card-date">
                                            Proposé le <?php echo htmlspecialchars($content['DateProposition']); ?>
                                        </span>
                                    </div>

                                    <!-- IMAGE -->
                                    <div class="content-card-image">
                                        <img src="../create/<?php echo htmlspecialchars($content['image']); ?>"
                                            alt="<?php echo htmlspecialchars($content['type']); ?>">
                                    </div>

                                    <!-- BODY : description sous l'image -->
                                    <div class="content-card-body">
                                        <div class="content-card-separator"></div>

                                        <?php if (!empty($content['annee'])) { ?>
                                            <p class="content-card-subtitle">
                                                <?php
                                                echo $content['type'] === 'musique'
                                                    ? 'Année de publication'
                                                    : ($content['type'] === 'artiste'
                                                        ? 'Année de naissance'
                                                        : 'Année de formation');
                                                ?>
                                                : <?php echo htmlspecialchars($content['annee']); ?>
                                            </p>
                                        <?php } ?>

                                        <p class="content-card-description">
                                            <?php echo htmlspecialchars($content['biographie'] ?? 'Aucune description fournie.'); ?>
                                        </p>

                                        <?php if (!empty($content['audio'])) { ?>
                                            <audio controls class="content-card-audio">
                                                <source src="../create/<?php echo htmlspecialchars($content['audio']); ?>" type="audio/mpeg">
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                        <?php } ?>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="content-card-actions">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="type" value="<?php echo $content['type']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce contenu ?')">Supprimer définitivement</button>
                                        </form>
                                    </div>

                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <?php require_once '../index/footer.php'; ?>

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
    
</body>
</html>
