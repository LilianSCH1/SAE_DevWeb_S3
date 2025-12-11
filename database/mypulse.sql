-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 11 déc. 2025 à 14:08
-- Version du serveur : 8.4.7
-- Version de PHP : 8.5.0

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
  `StatusArtiste` enum('en_attente','valide','refusee','classement','archive') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneeNaissance` int DEFAULT NULL,
  PRIMARY KEY (`ArtisteID`),
  UNIQUE KEY `ux_artiste_nom` (`NomArtiste`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusArtiste`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `artiste`
--

INSERT INTO `artiste` (`ArtisteID`, `NomArtiste`, `NomReel`, `BiographieCourte`, `CheminFichierMP3`, `ImageProfil`, `StatusArtiste`, `UserID`, `DateProposition`, `AnneeNaissance`) VALUES
(12, 'SDM', 'Leonard Manzambi', 'SDM, de son vrai nom Leonard Manzambi, né le 28 novembre 1995 à Meudon, est un rappeur français. En 2021, il sort l\'album Ocho, puis, l\'année suivante, Liens du 100 ; ce dernier opus est certifié double disque de platine en treize mois. En 2024, son troisième album, À la vie à la mort, reçoit la même certification en un peu moins de six mois.', 'uploads/artistes/sons/SDM_son.mp3', 'uploads/artistes/profil/SDM_profil.jpg', 'valide', 1, '2025-12-10 20:01:33', 1995),
(13, 'Koba LaD', 'Marcel Loutarila', 'Koba LaD, nom de scène de Marcel Loutarila, né le 3 avril 2000 à Saint-Denis, en Seine-Saint-Denis, est un rappeur français. En 2018, il sort son premier album, VII, qui est certifié disque de platine sept mois après sa sortie.', 'uploads/artistes/sons/Koba_LaD_son.mp3', 'uploads/artistes/profil/Koba_LaD_profil.jpg', 'valide', 1, '2025-12-11 12:06:45', 2000);

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
  `StatusGroupe` enum('en_attente','valide','refusee','classement','archive') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`GroupeID`),
  UNIQUE KEY `ux_groupe_nom` (`NomGroupe`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusGroupe`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`GroupeID`, `NomGroupe`, `AnneeFormation`, `BiographieCourte`, `CheminFichierMP3`, `ImageGroupe`, `StatusGroupe`, `UserID`, `DateProposition`) VALUES
(9, '2Be3', '1996', '2Be3 est un groupe de pop français, originaire de Longjumeau, dans l\'Essonne. Il est l\'un des premiers boys bands français, formé en 1996, et composé de trois amis d\'enfance originaires de Longjumeau : Filip Nikolic, Adel Kachermi et Frank Delay. Inspiré des boys bands anglo-saxons tels Take That ou Worlds Apart, le groupe a produit trois albums studio ainsi que des compilations. Ils ont vendu cinq millions de disques.', 'uploads/groupes/sons/2Be3_son.mp3', 'uploads/groupes/profil/2Be3_profil.jpg', 'valide', 1, '2025-12-10 20:59:46');

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
  `StatusMusique` enum('en_attente','valide','refusee','classement','archive') NOT NULL DEFAULT 'en_attente',
  `UserID` int DEFAULT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneePublication` int DEFAULT NULL,
  PRIMARY KEY (`MusiqueID`),
  UNIQUE KEY `ux_musique_chemin` (`CheminFichierMP3`),
  UNIQUE KEY `ux_musique_titre_artiste` (`Titre`,`Artiste`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusMusique`),
  KEY `idx_artiste` (`Artiste`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `musique`
--

INSERT INTO `musique` (`MusiqueID`, `Titre`, `Artiste`, `CheminFichierMP3`, `ImageCouverture`, `StatusMusique`, `UserID`, `DateProposition`, `AnneePublication`) VALUES
(23, 'FE!N', 'Travis Scott', 'uploads/musiques/sons/FEN_1765387072_musique.mp3', 'uploads/musiques/couvertures/FEN_1765387072_couverture.jpg', 'valide', 1, '2025-12-10 18:17:52', 2023),
(25, 'Goosebumps', 'Travis Scott', 'uploads/musiques/sons/Goosebumps_1765387736_musique.mp3', 'uploads/musiques/couvertures/Goosebumps_1765387736_couverture.jpg', 'valide', 1, '2025-12-10 18:28:56', 2016),
(27, 'Soleil Bleu', 'Bleu Soleil et Luiza', 'uploads/musiques/sons/Soleil_Bleu_1765387840_musique.mp3', 'uploads/musiques/couvertures/Soleil_Bleu_1765387840_couverture.jpg', 'valide', 1, '2025-12-10 18:30:40', 2025),
(30, 'Soleil Levant', 'Orelsan et SDM', 'uploads/musiques/sons/Soleil_Levant_1765389622_musique.mp3', 'uploads/musiques/couvertures/Soleil_Levant_1765389622_couverture.jpg', 'valide', 1, '2025-12-10 19:00:22', 2025),
(31, 'Un monde à l\'autre', 'GIMS, La Mano 1.9 et SCH', 'uploads/musiques/sons/Un_monde__lautre_1765389819_musique.mp3', 'uploads/musiques/couvertures/Un_monde__lautre_1765389819_couverture.jpg', 'valide', 1, '2025-12-10 19:03:39', 2025),
(33, 'Ailleurs', 'Orelsan', 'uploads/musiques/sons/Ailleurs_1765390046_musique.mp3', 'uploads/musiques/couvertures/Ailleurs_1765390046_couverture.jpg', 'valide', 1, '2025-12-10 19:07:26', 2025),
(36, 'Die With a Smile', 'Lady Gaga et Bruno Mars', 'uploads/musiques/sons/Die_With_a_Smile_1765394639_musique.mp3', 'uploads/musiques/couvertures/Die_With_a_Smile_1765394639_couverture.jpg', 'valide', 1, '2025-12-10 20:23:59', 2024),
(37, 'APT.', 'ROSÉ et Bruno Mars', 'uploads/musiques/sons/APT_1765394922_musique.mp3', 'uploads/musiques/couvertures/APT_1765394922_couverture.jpg', 'valide', 1, '2025-12-10 20:28:42', 2024),
(38, 'BIRDS OF A FEATHER', 'Billie Eilish', 'uploads/musiques/sons/BIRDS_OF_A_FEATHER_1765395240_musique.mp3', 'uploads/musiques/couvertures/BIRDS_OF_A_FEATHER_1765395240_couverture.jpg', 'valide', 1, '2025-12-10 20:34:00', 2024);

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
  `Role` enum('admin','invite','certifie','basique') NOT NULL DEFAULT 'invite',
  `DateInscription` date NOT NULL DEFAULT (curdate()),
  `Token` varchar(255) NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserMail` (`UserMail`),
  UNIQUE KEY `Token` (`Token`),
  KEY `idx_role` (`Role`),
  KEY `idx_mail` (`UserMail`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`UserID`, `UserPseudo`, `UserName`, `UserSurname`, `UserMail`, `UserPassword`, `reset_token`, `reset_expire`, `Role`, `DateInscription`, `Token`) VALUES
(1, 'admin', 'Valentin', 'Hoja', 'hoja.valentin@gmail.com', '$2y$12$c7CvWyvQC2GPYIZ8bvgtFOlZtSp5O239GvkqvkgcpXRV.5cvmWrd6', NULL, NULL, 'admin', '2025-12-08', 'cb35a570501860e747fa4598e851a7c58ba4b512af285466cbc111ea33708e72'),
(3, 'certif', 'Valentin', 'Hoja', 'certif.valentin@gmail.com', '$2y$12$qbI8KDOH2EFdNsg/qrnsxuKBOREh1nIdPg.Opy/tfQHk3vTYbyfmC', NULL, NULL, 'basique', '2025-12-11', 'a97809e05d129aaf8f8824a47595644b1d3625466fd41a7d34ceacadfee62f36'),
(4, 'basique', 'Valentin', 'Hoja', 'basique.valentin@gmail.com', '$2y$12$aszERhrsSwuD7hahlifxx.0rW9hXwdo6jkb3sF0Jg9Xc9hRyPiD4G', NULL, NULL, 'basique', '2025-12-11', '9f6196e46566c8751edabb6b3016c74a2e5bd597a641add5cbd16208f2c2479b');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
