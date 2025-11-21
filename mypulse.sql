-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 20 nov. 2025 à 07:29
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
-- Structure de la table `chanteur`
--

DROP TABLE IF EXISTS `chanteur`;
CREATE TABLE IF NOT EXISTS `chanteur` (
  `ChanteurID` int NOT NULL AUTO_INCREMENT,
  `NomArtiste` varchar(150) NOT NULL,
  `NomReel` varchar(150) DEFAULT NULL,
  `BiographieCourte` text,
  `CheminFichierMP3` varchar(500) NOT NULL,
  `ImageProfil` varchar(500) NOT NULL,
  `DureeMorceau` int DEFAULT NULL,
  `StatusChanteur` enum('en_attente','valide','refusee') NOT NULL DEFAULT 'en_attente',
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ChanteurID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusChanteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `DureeMorceau` int DEFAULT NULL,
  `StatusGroupe` enum('en_attente','valide','refusee') NOT NULL DEFAULT 'en_attente',
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`GroupeID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusGroupe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `DureeMorceau` int DEFAULT NULL,
  `TailleFichier` int DEFAULT NULL,
  `StatusMusique` enum('en_attente','valide','refusee') NOT NULL DEFAULT 'en_attente',
  `UserID` int NOT NULL,
  `DateProposition` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`MusiqueID`),
  KEY `UserID` (`UserID`),
  KEY `idx_status` (`StatusMusique`),
  KEY `idx_artiste` (`Artiste`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Contraintes pour la table `chanteur`
--
ALTER TABLE `chanteur`
  ADD CONSTRAINT `chanteur_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `utilisateur` (`UserID`) ON DELETE CASCADE;

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
