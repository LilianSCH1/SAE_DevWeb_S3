-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 05 jan. 2026 à 15:10
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mypulse`
--

-- --------------------------------------------------------

--
-- Structure de la table `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE IF NOT EXISTS `archive` (
  `ArchiveID` int NOT NULL AUTO_INCREMENT,
  `TypeContenu` enum('musique','chanteur','groupe') NOT NULL,
  `ContenuID` int NOT NULL,
  `DateArchivage` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ArchiveID`),
  UNIQUE KEY `uniquearchive` (`TypeContenu`,`ContenuID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `artiste`
--

DROP TABLE IF EXISTS `artiste`;
CREATE TABLE IF NOT EXISTS `artiste` (
  `ArtisteID` int NOT NULL AUTO_INCREMENT,
  `NomArtiste` varchar(150) NOT NULL,
  `NomReel` varchar(150) DEFAULT NULL,
  `BiographieCourte` text,
  `CheminFichierMP3` varchar(500) NOT NULL,
  `ImageProfil` varchar(500) NOT NULL,
  `StatusArtiste` enum('en_attente','valide','refusee','classement','archive_top','archive_suppr') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneeNaissance` int DEFAULT NULL,
  `NombreVotes` int DEFAULT 0,
  PRIMARY KEY (`ArtisteID`),
  UNIQUE KEY `ux_artiste_nom` (`NomArtiste`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusArtiste`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `artiste`
--

INSERT INTO `artiste` (`ArtisteID`, `NomArtiste`, `NomReel`, `BiographieCourte`, `CheminFichierMP3`, `ImageProfil`, `StatusArtiste`, `UserID`, `DateProposition`, `AnneeNaissance`, `NombreVotes`) VALUES
(12, 'SDM', 'Leonard Manzambi', 'SDM, de son vrai nom Leonard Manzambi, né le 28 novembre 1995 à Meudon, est un rappeur français. En 2021, il sort l\'album Ocho, puis, l\'année suivante, Liens du 100 ; ce dernier opus est certifié double disque de platine en treize mois. En 2024, son troisième album, À la vie à la mort, reçoit la même certification en un peu moins de six mois.', 'uploads/artistes/sons/SDM_son.mp3', 'uploads/artistes/profil/SDM_profil.jpg', 'valide', NULL, '2025-12-10 20:01:33', 1995, 2),
(13, 'Koba LaD', 'Marcel Loutarila', 'Koba LaD, nom de scène de Marcel Loutarila, né le 3 avril 2000 à Saint-Denis, en Seine-Saint-Denis, est un rappeur français. En 2018, il sort son premier album, VII, qui est certifié disque de platine sept mois après sa sortie.', 'uploads/artistes/sons/Koba_LaD_son.mp3', 'uploads/artistes/profil/Koba_LaD_profil.jpg', 'valide', NULL, '2025-12-11 12:06:45', 2000, 1),
(15, 'Gims', 'Gandhi Djuna', 'Gandhi Djuna, dit Gims, stylisé GIMS et anciennement Maître Gims, né le 6 mai 1986 à Kinshasa au Zaïre, est un chanteur et rappeur congolais. Il grandit en France et vit principalement entre la France et le Maroc. Il est membre du groupe de hip-hop Sexion d\'assaut.', 'uploads/artistes/sons/Gims_son.mp3', 'uploads/artistes/profil/Gims_profil.jpg', 'valide', 7, '2025-12-22 21:42:23', 1986, 0);

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `CategorieID` int NOT NULL AUTO_INCREMENT,
  `NomCategorie` varchar(100) NOT NULL,
  `Description` text,
  PRIMARY KEY (`CategorieID`),
  UNIQUE KEY `NomCategorie` (`NomCategorie`),
  KEY `idx_nom` (`NomCategorie`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`CategorieID`, `NomCategorie`, `Description`) VALUES
(1, 'Musiques', 'Regroupe tous les morceaux'),
(2, 'Artistes', 'Regroupe tous les artistes'),
(3, 'Groupes', 'Regroupe tous les groupes');

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

DROP TABLE IF EXISTS `groupe`;
CREATE TABLE IF NOT EXISTS `groupe` (
  `GroupeID` int NOT NULL AUTO_INCREMENT,
  `NomGroupe` varchar(150) NOT NULL,
  `AnneeFormation` year DEFAULT NULL,
  `BiographieCourte` text,
  `CheminFichierMP3` varchar(500) NOT NULL,
  `ImageGroupe` varchar(500) NOT NULL,
  `StatusGroupe` enum('en_attente','valide','refusee','classement','archive_top','archive_suppr') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `NombreVotes` int DEFAULT 0,
  PRIMARY KEY (`GroupeID`),
  UNIQUE KEY `ux_groupe_nom` (`NomGroupe`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusGroupe`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`GroupeID`, `NomGroupe`, `AnneeFormation`, `BiographieCourte`, `CheminFichierMP3`, `ImageGroupe`, `StatusGroupe`, `UserID`, `DateProposition`, `NombreVotes`) VALUES
(9, '2Be3', '1996', '2Be3 est un groupe de pop français, originaire de Longjumeau, dans l\'Essonne. Il est l\'un des premiers boys bands français, formé en 1996, et composé de trois amis d\'enfance originaires de Longjumeau : Filip Nikolic, Adel Kachermi et Frank Delay. Inspiré des boys bands anglo-saxons tels Take That ou Worlds Apart, le groupe a produit trois albums studio ainsi que des compilations. Ils ont vendu cinq millions de disques.', 'uploads/groupes/sons/2Be3_son.mp3', 'uploads/groupes/profil/2Be3_profil.jpg', 'valide', NULL, '2025-12-10 20:59:46', 3);

-- --------------------------------------------------------

--
-- Structure de la table `musique`
--

DROP TABLE IF EXISTS `musique`;
CREATE TABLE IF NOT EXISTS `musique` (
  `MusiqueID` int NOT NULL AUTO_INCREMENT,
  `Titre` varchar(200) NOT NULL,
  `Artiste` varchar(150) NOT NULL,
  `CheminFichierMP3` varchar(500) NOT NULL,
  `ImageCouverture` varchar(500) NOT NULL,
  `StatusMusique` enum('en_attente','valide','refusee','classement','archive_top','archive_suppr') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneePublication` int DEFAULT NULL,
  `NombreVotes` int DEFAULT 0,
  PRIMARY KEY (`MusiqueID`),
  UNIQUE KEY `ux_musique_chemin` (`CheminFichierMP3`),
  UNIQUE KEY `ux_musique_titre_artiste` (`Titre`,`Artiste`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusMusique`),
  KEY `idx_artiste` (`Artiste`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `musique`
--

INSERT INTO `musique` (`MusiqueID`, `Titre`, `Artiste`, `CheminFichierMP3`, `ImageCouverture`, `StatusMusique`, `UserID`, `DateProposition`, `AnneePublication`, `NombreVotes`) VALUES
(23, 'FE!N', 'Travis Scott', 'uploads/musiques/sons/FEN_1765387072_musique.mp3', 'uploads/musiques/couvertures/FEN_1765387072_couverture.jpg', 'valide', NULL, '2025-12-10 18:17:52', 2023, 3),
(25, 'Goosebumps', 'Travis Scott', 'uploads/musiques/sons/Goosebumps_1765387736_musique.mp3', 'uploads/musiques/couvertures/Goosebumps_1765387736_couverture.jpg', 'valide', NULL, '2025-12-10 18:28:56', 2016, 2),
(27, 'Soleil Bleu', 'Bleu Soleil et Luiza', 'uploads/musiques/sons/Soleil_Bleu_1765387840_musique.mp3', 'uploads/musiques/couvertures/Soleil_Bleu_1765387840_couverture.jpg', 'valide', NULL, '2025-12-10 18:30:40', 2025, 6),
(30, 'Soleil Levant', 'Orelsan et SDM', 'uploads/musiques/sons/Soleil_Levant_1765389622_musique.mp3', 'uploads/musiques/couvertures/Soleil_Levant_1765389622_couverture.jpg', 'valide', NULL, '2025-12-10 19:00:22', 2025, 1),
(31, 'Un monde à l\'autre', 'GIMS, La Mano 1.9 et SCH', 'uploads/musiques/sons/Un_monde__lautre_1765389819_musique.mp3', 'uploads/musiques/couvertures/Un_monde__lautre_1765389819_couverture.jpg', 'valide', NULL, '2025-12-10 19:03:39', 2025, 4),
(33, 'Ailleurs', 'Orelsan', 'uploads/musiques/sons/Ailleurs_1765390046_musique.mp3', 'uploads/musiques/couvertures/Ailleurs_1765390046_couverture.jpg', 'valide', NULL, '2025-12-10 19:07:26', 2025, 0),
(36, 'Die With a Smile', 'Lady Gaga et Bruno Mars', 'uploads/musiques/sons/Die_With_a_Smile_1765394639_musique.mp3', 'uploads/musiques/couvertures/Die_With_a_Smile_1765394639_couverture.jpg', 'valide', NULL, '2025-12-10 20:23:59', 2024, 0),
(37, 'APT.', 'ROSÉ et Bruno Mars', 'uploads/musiques/sons/APT_1765394922_musique.mp3', 'uploads/musiques/couvertures/APT_1765394922_couverture.jpg', 'valide', NULL, '2025-12-10 20:28:42', 2024, 0),
(38, 'BIRDS OF A FEATHER', 'Billie Eilish', 'uploads/musiques/sons/BIRDS_OF_A_FEATHER_1765395240_musique.mp3', 'uploads/musiques/couvertures/BIRDS_OF_A_FEATHER_1765395240_couverture.jpg', 'valide', NULL, '2025-12-10 20:34:00', 2024, 0);

-- --------------------------------------------------------

--
-- Structure de la table `resultat`
--

DROP TABLE IF EXISTS `resultat`;
CREATE TABLE IF NOT EXISTS `resultat` (
  `ResultatID` int NOT NULL AUTO_INCREMENT,
  `TypeContenu` enum('musique','chanteur','groupe') NOT NULL,
  `ContenuID` int NOT NULL,
  `TotalVotes` int NOT NULL DEFAULT '0',
  `MoyenneVotes` decimal(3,2) DEFAULT NULL,
  `DateCalcul` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ResultatID`),
  UNIQUE KEY `unique_resultat` (`TypeContenu`,`ContenuID`),
  KEY `idx_contenu` (`TypeContenu`,`ContenuID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `UserPseudo` varchar(100) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `UserSurname` varchar(100) NOT NULL,
  `UserMail` varchar(150) NOT NULL,
  `UserPassword` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL,
  `Role` enum('admin','certifie','basique') NOT NULL DEFAULT 'basique',
  `DateInscription` date NOT NULL DEFAULT (curdate()),
  `Token` varchar(255) NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserMail` (`UserMail`),
  UNIQUE KEY `Token` (`Token`),
  KEY `idx_role` (`Role`),
  KEY `idx_mail` (`UserMail`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`UserID`, `UserPseudo`, `UserName`, `UserSurname`, `UserMail`, `UserPassword`, `reset_token`, `reset_expire`, `Role`, `DateInscription`, `Token`) VALUES
(5, 'MyPulse_User', 'MyPulse', 'User', 'mypulse_user@gmail.com', '$2y$10$AeeEBJwgVf4K.suT156D4uD2TsZiVbwFle2e/OF1ryQzi4wRFxhKe', NULL, NULL, 'basique', '2025-12-21', '7ce1d0136d92bab45db33323f99716c9e9398a03e507058ccf357ec3cc992240'),
(6, 'MyPulse_Certif', 'MyPulse', 'Certif', 'mypulse_certif@gmail.com', '$2y$10$frfZSMSLnq/q6sHXJ4LlnuU7lSv2UmOvh1zGlOCtsHzssC1tekdSq', NULL, NULL, 'certifie', '2025-12-21', 'd04046e68569c9fe7fce3047a1ef32844efd6bd9103392352f9cda4d0bf42dae'),
(7, 'MyPulse_Admin', 'MyPulse', 'Admin', 'mypulse_admin@gmail.com', '$2y$10$NmpKpMO48plTQddYBXfCru2zaGFy7vPgjF7OtYjSYsx8D3jdcPrMe', NULL, NULL, 'admin', '2025-12-21', '817acb72a6c701562cb2ff2f1d77b59e31e86c8098bcb926d33928c9f05c2cf8');

-- --------------------------------------------------------

--
-- Structure de la table `vote`
--

DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `VoteID` int NOT NULL AUTO_INCREMENT,
  `TypeContenu` enum('musique','chanteur','groupe') NOT NULL,
  `ContenuID` int NOT NULL COMMENT 'ID dans la table correspondante',
  `DateVote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ValeurVote` int NOT NULL,
  `Token` varchar(255) NOT NULL,
  PRIMARY KEY (`VoteID`),
  UNIQUE KEY `unique_vote_par_type` (`Token`,`TypeContenu`),
  KEY `idx_contenu` (`TypeContenu`,`ContenuID`)
) ENGINE=InnoDB AUTO_INCREMENT=626 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `vote`
--

INSERT INTO `vote` (`VoteID`, `TypeContenu`, `ContenuID`, `DateVote`, `ValeurVote`, `Token`) VALUES
(16, 'musique', 31, '2026-01-05 11:20:58', 1, '7ce1d0136d92bab45db33323f99716c9e9398a03e507058ccf357ec3cc992240'),
(20, 'groupe', 9, '2026-01-05 11:34:37', 1, '7ce1d0136d92bab45db33323f99716c9e9398a03e507058ccf357ec3cc992240'),
(21, 'chanteur', 12, '2026-01-05 11:34:37', 1, '7ce1d0136d92bab45db33323f99716c9e9398a03e507058ccf357ec3cc992240'),
(22, 'musique', 31, '2026-01-05 11:20:58', 1, 'd04046e68569c9fe7fce3047a1ef32844efd6bd9103392352f9cda4d0bf42dae'),
(23, 'chanteur', 13, '2026-01-05 11:34:37', 1, 'd04046e68569c9fe7fce3047a1ef32844efd6bd9103392352f9cda4d0bf42dae'),
(24, 'groupe', 9, '2026-01-05 11:34:37', 1, 'd04046e68569c9fe7fce3047a1ef32844efd6bd9103392352f9cda4d0bf42dae'),
(27, 'musique', 27, '2026-01-05 11:20:58', 1, '817acb72a6c701562cb2ff2f1d77b59e31e86c8098bcb926d33928c9f05c2cf8'),
(45, 'musique', 27, '2026-01-05 11:20:58', 1, 'f1234567890abcdef1234567890abcdef1234567890abcdef1234567890ab'),
(48, 'chanteur', 12, '2026-01-05 11:34:37', 1, '817acb72a6c701562cb2ff2f1d77b59e31e86c8098bcb926d33928c9f05c2cf8'),
(53, 'groupe', 9, '2026-01-05 11:34:37', 1, '817acb72a6c701562cb2ff2f1d77b59e31e86c8098bcb926d33928c9f05c2cf8'),
(344, 'musique', 23, '2026-01-05 11:30:31', 1, 'token_musique_23_1'),
(345, 'musique', 23, '2026-01-05 11:34:37', 1, 'token_musique_23_2'),
(346, 'musique', 23, '2026-01-05 11:31:22', 1, 'token_musique_23_3'),
(347, 'musique', 25, '2026-01-05 11:34:37', 1, 'token_musique_25_1'),
(348, 'musique', 25, '2026-01-05 11:34:37', 1, 'token_musique_25_2'),
(349, 'musique', 27, '2026-01-05 11:28:18', 1, 'token_musique_27_1'),
(350, 'musique', 27, '2026-01-05 11:34:37', 1, 'token_musique_27_2'),
(351, 'musique', 27, '2026-01-05 11:34:37', 1, 'token_musique_27_3'),
(352, 'musique', 27, '2026-01-05 11:34:37', 1, 'token_musique_27_4'),
(353, 'musique', 28, '2026-01-05 11:34:37', 1, 'token_musique_28_1'),
(354, 'musique', 28, '2026-01-05 11:34:37', 1, 'token_musique_28_2'),
(355, 'musique', 29, '2026-01-05 11:34:37', 1, 'token_musique_29_1'),
(356, 'musique', 30, '2026-01-05 11:34:37', 1, 'token_musique_30_1'),
(357, 'musique', 31, '2026-01-05 11:34:37', 1, 'token_musique_31_1'),
(358, 'musique', 31, '2026-01-05 11:34:37', 1, 'token_musique_31_2');

-- --------------------------------------------------------

--
-- Structure de la table `weekly_winners`
--

DROP TABLE IF EXISTS `weekly_winners`;
CREATE TABLE IF NOT EXISTS `weekly_winners` (
  `WinnerID` int NOT NULL AUTO_INCREMENT,
  `WeekStart` date NOT NULL COMMENT 'Monday of the week',
  `TypeContenu` enum('musique','chanteur','groupe') NOT NULL,
  `Rank` int NOT NULL COMMENT '1 for 1st, 2 for 2nd, 3 for 3rd',
  `ContenuID` int NOT NULL,
  `Votes` int NOT NULL,
  PRIMARY KEY (`WinnerID`),
  UNIQUE KEY `unique_winner` (`WeekStart`,`TypeContenu`,`Rank`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `weekly_winners`
--

INSERT INTO `weekly_winners` (`WinnerID`, `WeekStart`, `TypeContenu`, `Rank`, `ContenuID`, `Votes`) VALUES
(9, '2026-01-05', 'musique', 1, 27, 6),
(10, '2026-01-05', 'musique', 2, 31, 4),
(11, '2026-01-05', 'musique', 3, 23, 3),
(12, '2026-01-05', 'musique', 4, 25, 2),
(13, '2026-01-05', 'musique', 5, 30, 1),
(14, '2026-01-05', 'chanteur', 1, 12, 2),
(15, '2026-01-05', 'chanteur', 2, 13, 1),
(16, '2026-01-05', 'groupe', 1, 9, 3);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `artiste`
--
ALTER TABLE `artiste`
  ADD CONSTRAINT `artiste_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE SET NULL;

--
-- Contraintes pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD CONSTRAINT `groupe_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE SET NULL;

--
-- Contraintes pour la table `musique`
--
ALTER TABLE `musique`
  ADD CONSTRAINT `musique_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
