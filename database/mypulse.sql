-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 08 déc. 2025 à 13:42
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

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
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneeNaissance` int DEFAULT NULL,
  PRIMARY KEY (`ArtisteID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusArtiste`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `artiste`
--

INSERT INTO `artiste` (`ArtisteID`, `NomArtiste`, `NomReel`, `BiographieCourte`, `CheminFichierMP3`, `ImageProfil`, `StatusArtiste`, `UserID`, `DateProposition`, `AnneeNaissance`) VALUES
(11, 'Gims', 'Gandhi Djuna', 'Gims, stylisé GIMS, anciennement Maître Gims, né Gandhi Djuna le 6 mai 1986 à Kinshasa au Zaïre, est un chanteur et rappeur congolais. Il grandit en France et vit principalement entre la France et le Maroc. Il est membre du groupe de hip-hop Sexion d\'assaut.', 'uploads/artistes/sons/Gims_son.mp3', 'uploads/artistes/profil/Gims_profil.webp', 'valide', 6, '2025-12-08 14:24:48', 1986);

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
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`GroupeID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusGroupe`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`GroupeID`, `NomGroupe`, `AnneeFormation`, `BiographieCourte`, `CheminFichierMP3`, `ImageGroupe`, `StatusGroupe`, `UserID`, `DateProposition`) VALUES
(5, 'Triangle des Bermudes', '2023', 'Le groupe Triangle des bermudes est composé de MC YOSHI, originaire du quartier des Épinettes situé à Évry-Courcouronnes dans l\'Essonne ; Mauvais djo, d\'origine congolaise, lequel a également grandi à Évry-Courcouronnes ; et Kokosvoice, originaire de Draveil, toujours dans l\'Essonne.', 'uploads/groupes/sons/Triangle_des_Bermudes_son.mp3', 'uploads/groupes/profil/Triangle_des_Bermudes_profil.jpg', 'en_attente', 6, '2025-12-08 14:31:17');

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
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AnneePublication` int DEFAULT NULL,
  PRIMARY KEY (`MusiqueID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusMusique`),
  KEY `idx_artiste` (`Artiste`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `Role` enum('admin','invite','certifie','basique') NOT NULL DEFAULT 'invite',
  `DateInscription` date NOT NULL DEFAULT (curdate()),
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserMail` (`UserMail`),
  KEY `idx_role` (`Role`),
  KEY `idx_mail` (`UserMail`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`UserID`, `UserPseudo`, `UserName`, `UserSurname`, `UserMail`, `UserPassword`, `Role`, `DateInscription`) VALUES
(4, 'LilianSCH', 'Lilian', 'Schmitt', 'lilian.schmitt1@etu.univ-lorraine.fr', '$2y$10$MPZaMIrSXZLnY45.0xg49OMQNrOa2X7pkmzsDxPzGjuMer1douuQm', 'basique', '2025-12-01'),
(5, 'LilianSCH2', 'Lilian', 'Schmitt', 'lilians10120@gmail.com', '$2y$10$ILXSdg4l9vAFF7/ZxzFgU.xIT6BIP6HQibg5wiFvBZw9Oc9fmfdAi', 'basique', '2025-12-05'),
(6, 'test', 'testeur', 'sch', 'schmittlilian10@gmail.com', '$2y$10$yV1NGfdQqYIxjX3aaXqAs.rb5gTtxl/qAHRI/PqXyfmEPbR0IvstK', 'admin', '2025-12-06');

-- --------------------------------------------------------

--
-- Structure de la table `vote`
--

DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `VoteID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `TypeContenu` enum('musique','chanteur','groupe') NOT NULL,
  `ContenuID` int NOT NULL COMMENT 'ID dans la table correspondante',
  `DateVote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ValeurVote` int NOT NULL,
  PRIMARY KEY (`VoteID`),
  UNIQUE KEY `unique_vote` (`UserID`,`TypeContenu`,`ContenuID`),
  KEY `idx_contenu` (`TypeContenu`,`ContenuID`),
  KEY `idx_user` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `artiste`
--
ALTER TABLE `artiste`
  ADD CONSTRAINT `artiste_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD CONSTRAINT `groupe_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `musique`
--
ALTER TABLE `musique`
  ADD CONSTRAINT `musique_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
