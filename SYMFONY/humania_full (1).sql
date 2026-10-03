-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 04 mai 2026 à 13:24
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
-- Base de données : `humania_full`
--

-- --------------------------------------------------------

--
-- Structure de la table `absence`
--

DROP TABLE IF EXISTS `absence`;
CREATE TABLE IF NOT EXISTS `absence` (
  `id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nbr_jours` int NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_absence_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `heure_debut` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `heure_fin` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duree_minutes` int NOT NULL,
  `motif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `absence`
--

INSERT INTO `absence` (`id`, `date_debut`, `date_fin`, `nbr_jours`, `statut`, `type_absence_id`, `utilisateur_id`, `heure_debut`, `heure_fin`, `duree_minutes`, `motif`) VALUES
(1, '2025-02-01', '2025-02-02', 2, 'Justifiée', 1, 12, '', '', 0, ''),
(2, '2025-02-10', '2025-02-10', 1, 'Approuvé', 3, 12, '', '', 0, ''),
(4, '2026-02-18', '2026-02-22', 5, 'Approuvé', 1, 12, '', '', 0, ''),
(5, '2026-02-17', '2026-02-20', 3, 'Approuvé', 1, 12, '', '', 0, ''),
(6, '2026-02-17', '2026-02-18', 1, 'Approuvé', 1, 12, '', '', 0, ''),
(7, '2026-02-18', '2026-02-19', 1, 'Refusé', 3, 12, '', '', 0, ''),
(8, '2026-02-20', '2026-02-22', 2, 'Approuvé', 1, 12, '', '', 0, ''),
(14, '2026-02-28', '2026-02-28', 120, 'Refusé', 2, 12, '', '', 0, ''),
(15, '2026-02-27', '2026-02-27', 30, 'Refusé', 2, 12, '', '', 0, ''),
(16, '2026-02-27', '2026-02-27', 60, 'Approuvé', 1, 12, '', '', 0, ''),
(17, '2026-03-01', '2026-03-01', 1, 'Refusé', 2, 12, '08:00', '11:30', 210, 'qss'),
(18, '2026-03-02', '2026-03-02', 1, 'Approuvé', 2, 12, '10:00', '11:45', 105, 'hgh'),
(19, '2026-03-05', '2026-03-05', 1, 'Approuvé', 1, 3, '09:00', '10:00', 60, 'trtrtr'),
(20, '2026-03-15', '2026-03-15', 75, 'Approuvé', 1, 3, '10:00', '11:15', 75, 'aaa'),
(21, '2026-04-25', '2026-04-25', 60, 'En attente', 1, 1, '07:15', '08:15', 60, 'rendez vous'),
(22, '2026-04-25', '2026-04-25', 180, 'Approuvé', 1, 11, '07:00', '10:00', 180, 'rendez vous');

-- --------------------------------------------------------

--
-- Structure de la table `actionpdi`
--

DROP TABLE IF EXISTS `actionpdi`;
CREATE TABLE IF NOT EXISTS `actionpdi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priorite` int NOT NULL,
  `pdi_id` int DEFAULT NULL,
  `formation_id` int DEFAULT NULL,
  `typeAction` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFinPrevue` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_653CF31897CD944` (`pdi_id`),
  KEY `IDX_653CF3185200282E` (`formation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `actionpdi`
--

INSERT INTO `actionpdi` (`id`, `statut`, `priorite`, `pdi_id`, `formation_id`, `typeAction`, `dateDebut`, `dateFinPrevue`) VALUES
(11, 'Pending', 3, NULL, 6, 'Formation : Angular Advanced', '2026-04-20', '2026-05-20'),
(12, 'Completed', 3, 11, 2, 'Formation : AWS Certification', '2026-04-25', '2026-04-22'),
(13, 'Pending', 3, 11, 20, 'Formation : Coaching & Développement d\'équipe', '2026-04-20', '2026-05-20'),
(15, 'Completed', 3, 11, 6, 'Formation : Angular Advanced', '2026-04-20', '2026-05-20'),
(16, 'In Progress', 3, 11, NULL, 'Certification', '2026-04-20', '2026-05-20'),
(17, 'In Progress', 3, 11, 19, 'Formation : Apache Kafka pour développeurs', '2026-04-20', '2026-05-20'),
(18, 'Completed', 3, 11, 15, 'Formation : Agile & Scrum Practitioner', '2026-04-20', '2026-05-20');

-- --------------------------------------------------------

--
-- Structure de la table `candidature`
--

DROP TABLE IF EXISTS `candidature`;
CREATE TABLE IF NOT EXISTS `candidature` (
  `id` int NOT NULL,
  `poste_interne_id` int NOT NULL,
  `candidat_id` int NOT NULL,
  `employe_id` int NOT NULL,
  `type_candidat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_depot` date NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `etape_pipeline` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `scoring_ia` double NOT NULL,
  `cv_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lettre_motivation_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `derniere_modification` date NOT NULL,
  `commentaires_rh` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `salaire_pretendu` double NOT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `candidature`
--

INSERT INTO `candidature` (`id`, `poste_interne_id`, `candidat_id`, `employe_id`, `type_candidat`, `date_depot`, `statut`, `etape_pipeline`, `scoring_ia`, `cv_url`, `lettre_motivation_url`, `derniere_modification`, `commentaires_rh`, `salaire_pretendu`, `nom`, `prenom`, `email`) VALUES
(1665549715, 1, 0, 3, 'Externe', '2026-02-10', 'Accepté', 'Entretien', 0, 'C:\\Users\\USER\\Downloads\\style-AjoutEsp.css', 'C:\\Users\\USER\\Downloads\\pi-dev.sql', '2026-02-16', '', 111000, '', '', ''),
(1665549716, 1, 1, 6, 'Externe', '2026-02-16', 'Accepté', 'Candidature reçue', 0, 'CV', 'vd', '2026-02-21', '', 12345678, 'nossaf', 'elwess', 'insafweslati0@gmail.com'),
(1665549717, 1, 1, 5, 'Externe', '2026-02-10', 'Accepté', 'Candidature reçue', 0, 'C:\\Users\\USER\\Downloads\\candidature.sql', 'C:\\Users\\USER\\Desktop\\projet pfsense\\CAP 1.png', '2026-02-16', '', 123, 'aha', 'oha', 'aminezaaraouilb@gmail.com'),
(1665549718, 1, 1, 7, 'Interne', '2026-02-04', 'Accepté', 'Entretien', 0, 'C:\\Users\\USER\\Desktop\\projet pfsense\\CAP 2.png', 'C:\\Users\\USER\\Desktop\\projet pfsense\\cap2.png', '2026-02-16', '', 1234, '', '', 'aminezaaraoui95@gmail.com'),
(1665549719, 1, 1, 1, 'Interne', '2026-02-10', 'En cours', 'Offre', 0, 'https://chatgpt.com/c/69936616-7e40-832a-9ae0-5969de599704', 'https://chatgpt.com/c/69936616-7e40-832a-9ae0-5969de599704', '2026-02-16', '', 1324, '', '', ''),
(1665549720, 1, 1, 1, 'Interne', '2025-12-24', 'Nouvelle', 'Offre', 0, 'C:\\Users\\USER\\Downloads\\traitemnt paro non chirug (1).docx', 'C:\\Users\\USER\\Downloads\\sdgs.docx', '2026-02-17', '', 2500, '', '', ''),
(1665549721, 1, 1, 1, 'Interne', '2026-02-17', 'Entretien', 'Pré-sélection', 0, 'C:\\Users\\USER\\Pictures\\page web.png', 'C:\\Users\\USER\\Pictures\\pat.png', '2026-02-17', '', 12222, '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `candidature_externe`
--

DROP TABLE IF EXISTS `candidature_externe`;
CREATE TABLE IF NOT EXISTS `candidature_externe` (
  `id` int NOT NULL,
  `poste_externe_id` int NOT NULL,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_depot` date NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `etape_pipeline` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scoring_ia` double NOT NULL,
  `cv_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lettre_motivation_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `derniere_modification` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidature_externe`
--

INSERT INTO `candidature_externe` (`id`, `poste_externe_id`, `nom`, `prenom`, `date_depot`, `statut`, `etape_pipeline`, `scoring_ia`, `cv_url`, `lettre_motivation_url`, `derniere_modification`) VALUES
(3, 0, 'Khlelifa', 'Amine', '2026-02-28', 'Refusée', 'Finalisé', 0, 'myCV.pdf', 'lettre_de_motivation.pdf', '2026-03-03 03:31:27'),
(4, 0, 'Bouassida', 'Amine', '2026-02-12', 'Acceptée', 'Finalisé', 0, 'dza', 'czdq', '2026-03-03 01:21:18'),
(5, 0, 'Cherif', 'Imen', '2026-03-02', 'En attente', 'Réception', 0, 'cdxq', 'dcsq', '2026-03-02 23:11:49'),
(6, 0, 'Cherif', 'Imen', '2026-03-02', 'En attente', 'Réception', 0, 'cdzqvq', 'ecdqvcdq', '2026-03-02 23:56:40'),
(7, 0, 'Bensalem', 'Ahmed', '2025-01-12', 'En attente', 'Réception', 0, '/cvs/ahmed_bensalem.pdf', '', '2025-01-12 09:00:00'),
(8, 0, 'Martin', 'Sophie', '2025-01-18', 'Acceptée', 'Finalisé', 8.5, '/cvs/sophie_martin.pdf', '/lettres/sophie_martin.pdf', '2025-02-05 14:30:00'),
(9, 0, 'Trabelsi', 'Mohamed', '2025-01-25', 'En attente', 'Présélection', 6.2, '/cvs/mohamed_trabelsi.pdf', '/lettres/mohamed_trabelsi.pdf', '2025-01-26 11:00:00'),
(10, 0, 'Dupont', 'Camille', '2025-02-03', 'Refusée', 'Test Technique', 3.1, '/cvs/camille_dupont.pdf', '', '2025-02-20 16:00:00'),
(11, 0, 'Gharbi', 'Youssef', '2025-02-10', 'En attente', 'Entretien', 7.8, '/cvs/youssef_gharbi.pdf', '/lettres/youssef_gharbi.pdf', '2025-02-15 10:30:00'),
(12, 0, 'Bernard', 'Léa', '2025-02-15', 'Acceptée', 'Offre', 9.2, '/cvs/lea_bernard.pdf', '/lettres/lea_bernard.pdf', '2025-03-01 09:00:00'),
(13, 0, 'Mansouri', 'Karim', '2025-02-20', 'En attente', 'Réception', 0, '/cvs/karim_mansouri.pdf', '', '2025-02-20 08:00:00'),
(14, 0, 'Lefevre', 'Julie', '2025-02-28', 'Refusée', 'Présélection', 2.5, '/cvs/julie_lefevre.pdf', '', '2025-03-05 13:00:00'),
(15, 0, 'Kahouli', 'Sami', '2025-03-01', 'En attente', 'Test Technique', 7, '/cvs/sami_kahouli.pdf', '/lettres/sami_kahouli.pdf', '2025-03-01 11:00:00'),
(16, 0, 'Rousseau', 'Emma', '2025-03-05', 'Acceptée', 'Finalisé', 9.8, '/cvs/emma_rousseau.pdf', '/lettres/emma_rousseau.pdf', '2025-03-10 15:00:00'),
(17, 0, 'Chermiti', 'Anas', '2025-03-08', 'En attente', 'Entretien', 6.9, '/cvs/anas_chermiti.pdf', '/lettres/anas_chermiti.pdf', '2025-03-08 09:30:00'),
(18, 0, 'Fontaine', 'Clara', '2025-03-10', 'Refusée', 'Réception', 1.8, '/cvs/clara_fontaine.pdf', '', '2025-03-11 10:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `candidature_interne`
--

DROP TABLE IF EXISTS `candidature_interne`;
CREATE TABLE IF NOT EXISTS `candidature_interne` (
  `id` int NOT NULL,
  `poste_actuel` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nouveau_poste` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nouveau_salaire` double NOT NULL,
  `date_demande` date NOT NULL,
  `motif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `derniere_modification` datetime NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `candidature_interne`
--

INSERT INTO `candidature_interne` (`id`, `poste_actuel`, `nouveau_poste`, `nouveau_salaire`, `date_demande`, `motif`, `derniere_modification`, `statut`) VALUES
(4, 'Développeur Junior', 'Développeur Senior', 52000, '2025-01-10', 'Après 3 ans en développement Java/Angular, je souhaite évoluer vers plus de responsabilités techniques.', '2025-01-10 09:00:00', NULL),
(5, 'Analyste Fonctionnel', 'Chef de Projet', 65000, '2025-01-20', 'Ma maîtrise des processus métier et mon expérience de coordination me permettent d assumer un rôle de chef de projet.', '2025-01-20 11:00:00', NULL),
(6, 'Technicien Support N1', 'Administrateur Système', 40000, '2025-02-05', 'J ai développé mes compétences en administration Linux en auto-formation et je souhaite officialiser cette évolution.', '2025-02-05 10:00:00', NULL),
(7, 'Designer Junior', 'Lead UX/UI Designer', 48000, '2025-02-12', 'J ai piloté plusieurs refontes d interfaces avec succès et je me sens prêt à encadrer une équipe de designers.', '2025-02-12 14:00:00', NULL),
(8, 'Développeur Backend Python', 'Data Engineer', 58000, '2025-02-18', 'Mon expérience avec les pipelines de données et les outils big data (Spark, Kafka) m a convaincu de me spécialiser.', '2025-02-18 09:30:00', NULL),
(9, 'Commercial Terrain', 'Responsable Compte Clé', 72000, '2025-03-01', 'J ai dépassé mes objectifs commerciaux 3 années consécutives et développé des relations solides avec des clients stratégiques.', '2025-03-01 08:00:00', NULL),
(10, 'RH Généraliste', 'Responsable Ressources Humaines', 60000, '2025-03-05', 'Après 5 ans en RH opérationnel, je souhaite prendre en charge la stratégie RH globale et le développement des talents.', '2025-03-05 10:00:00', NULL),
(11, 'Stagiaire Développeur', 'Développeur Junior CDI', 36000, '2025-03-10', 'Mon stage de 6 mois m a permis de m\'intégrer pleinement et de livrer des fonctionnalités en production. Je souhaite rester.', '2025-03-10 11:00:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `categoriecompetence`
--

DROP TABLE IF EXISTS `categoriecompetence`;
CREATE TABLE IF NOT EXISTS `categoriecompetence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categoriecompetence`
--

INSERT INTO `categoriecompetence` (`id`, `libelle`, `couleur`) VALUES
(1, 'Technical', '#3B82F6'),
(2, 'Behavioral', '#8B5CF6'),
(3, 'Business', '#10B981'),
(4, 'Transversal', '#F59E0B');

-- --------------------------------------------------------

--
-- Structure de la table `categorieformation`
--

DROP TABLE IF EXISTS `categorieformation`;
CREATE TABLE IF NOT EXISTS `categorieformation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `couleur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorieformation`
--

INSERT INTO `categorieformation` (`id`, `libelle`, `couleur`) VALUES
(1, 'Technical Training', '#3B82F6'),
(2, 'Management Training', '#10B981'),
(3, 'Soft Skills', '#8B5CF6'),
(4, 'DevOps', '#F59E0B'),
(5, 'Frontend', '#0EA5E9'),
(6, 'Cloud', '#6366F1'),
(7, 'Soft Skills Avancées', '#EC4899'),
(8, 'Autre', '#6B7280');

-- --------------------------------------------------------

--
-- Structure de la table `chaise`
--

DROP TABLE IF EXISTS `chaise`;
CREATE TABLE IF NOT EXISTS `chaise` (
  `id` int NOT NULL AUTO_INCREMENT,
  `espace_id` int DEFAULT NULL,
  `chair_number` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `espace_id` (`espace_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE IF NOT EXISTS `commentaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publicationId` int NOT NULL,
  `authorId` int NOT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `statut` enum('ACTIF','SUPPRIME','ARCHIVE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIF',
  `nombreReactions` int DEFAULT '0',
  `gifUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_comment_pub` (`publicationId`),
  KEY `idx_comment_author` (`authorId`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `contenu`, `publicationId`, `authorId`, `dateCreation`, `dateModification`, `statut`, `nombreReactions`, `gifUrl`) VALUES
(47, ',;nnkj', 78, 9, '2026-03-03 09:57:41', '2026-03-03 10:10:03', 'SUPPRIME', 0, NULL),
(48, 'fuck', 78, 9, '2026-03-03 10:01:07', '2026-03-03 10:09:59', 'SUPPRIME', 1, 'https://media3.giphy.com/media/v1.Y2lkPWU1Nzc0OWRmZThpMHNzMHRuNGJsbjcxOGkyc2VvaWdlMjVoNm5qc2RqNmE1aDZnZiZlcD12MV9naWZzX3RyZW5kaW5nJmN0PWc/OuQmhmAAdJFLi/giphy.gif'),
(49, 'lkmk', 81, 9, '2026-03-03 10:09:50', NULL, 'ACTIF', 0, NULL),
(50, 'aaaa', 84, 3, '2026-04-06 17:50:51', NULL, 'ACTIF', 0, NULL),
(51, 'bonjour', 86, 3, '2026-04-07 09:38:57', '2026-04-07 10:39:46', 'ACTIF', 0, NULL),
(52, 'ysakhef lkhra', 91, 11, '2026-05-01 11:17:13', NULL, 'ACTIF', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `competence`
--

DROP TABLE IF EXISTS `competence`;
CREATE TABLE IF NOT EXISTS `competence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveauMax` int NOT NULL,
  `typeCompetence` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie_id` int DEFAULT NULL,
  `statutCompetence` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIF',
  PRIMARY KEY (`id`),
  KEY `categorie_id` (`categorie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `competence`
--

INSERT INTO `competence` (`id`, `libelle`, `niveauMax`, `typeCompetence`, `categorie_id`, `statutCompetence`) VALUES
(1, 'JavaScript', 5, 'CRITIQUE', 1, 'ACTIF'),
(2, 'React', 5, 'CRITIQUE', 1, 'ACTIF'),
(3, 'TypeScript', 5, 'IMPORTANTE', 1, 'ACTIF'),
(4, 'Node.js', 5, 'IMPORTANTE', 1, 'ACTIF'),
(5, 'Kubernetes', 5, 'CRITIQUE', 1, 'ACTIF'),
(6, 'AWS', 5, 'CRITIQUE', 1, 'ACTIF'),
(7, 'Leadership', 5, 'IMPORTANTE', 2, 'ACTIF'),
(8, 'Communication', 5, 'CRITIQUE', 2, 'ACTIF'),
(9, 'Problem Solving', 5, 'CRITIQUE', 2, 'ACTIF'),
(10, 'Agile/Scrum', 5, 'CRITIQUE', 3, 'ACTIF'),
(11, 'Project Management', 5, 'CRITIQUE', 3, 'ACTIF'),
(12, 'Data Analysis', 5, 'CRITIQUE', 4, 'ACTIF'),
(13, 'Python', 5, 'CRITIQUE', 1, 'ACTIF'),
(14, 'Docker', 5, 'CRITIQUE', 1, 'ACTIF'),
(15, 'CI/CD', 5, 'IMPORTANTE', 1, 'ACTIF'),
(16, 'Machine Learning', 5, 'CRITIQUE', 4, 'ACTIF'),
(17, 'Power BI', 5, 'IMPORTANTE', 4, 'ACTIF'),
(18, 'Risk Management', 5, 'IMPORTANTE', 3, 'ACTIF'),
(19, 'Cybersecurity Audit', 5, 'CRITIQUE', 1, 'ACTIF'),
(20, 'SQL Advanced', 5, 'CRITIQUE', 4, 'ACTIF'),
(21, 'Linux Administration', 5, 'IMPORTANTE', 1, 'ACTIF'),
(22, 'Public Speaking', 5, 'IMPORTANTE', 2, 'ACTIF'),
(23, 'Spring Boot', 5, 'CRITIQUE', 1, 'ACTIF'),
(24, 'SQL', 5, 'IMPORTANTE', 1, 'ACTIF'),
(25, 'Java', 5, 'CRITIQUE', 1, 'ACTIF'),
(26, 'React Native', 5, 'IMPORTANTE', 1, 'ACTIF'),
(27, 'Git', 5, 'IMPORTANTE', 1, 'ACTIF'),
(28, 'Selenium', 5, 'IMPORTANTE', 1, 'ACTIF'),
(29, 'JUnit', 5, 'IMPORTANTE', 1, 'ACTIF'),
(30, 'Postman', 5, 'IMPORTANTE', 1, 'ACTIF'),
(31, 'Travail en équipe', 5, 'CRITIQUE', 2, 'ACTIF'),
(32, 'Gestion du stress', 5, 'IMPORTANTE', 2, 'ACTIF'),
(33, 'Adaptabilité', 6, 'IMPORTANTE', 2, 'ACTIF'),
(34, 'Analyse des risques', 5, 'CRITIQUE', 3, 'ACTIF'),
(35, 'Gestion de budget', 5, 'IMPORTANTE', 3, 'ACTIF'),
(36, 'Négociation', 5, 'IMPORTANTE', 3, 'ACTIF'),
(37, 'Excel avancé', 5, 'IMPORTANTE', 4, 'ACTIF'),
(38, 'Présentation', 5, 'IMPORTANTE', 4, 'ACTIF'),
(39, 'Veille technologique', 5, 'IMPORTANTE', 4, 'ACTIF'),
(40, 'GraphQL', 5, 'CRITIQUE', 1, 'ACTIF'),
(41, 'Redis', 5, 'IMPORTANTE', 1, 'ACTIF'),
(42, 'Terraform', 5, 'CRITIQUE', 1, 'ACTIF'),
(43, 'Flutter', 5, 'IMPORTANTE', 1, 'ACTIF'),
(44, 'Kafka', 5, 'CRITIQUE', 1, 'ACTIF'),
(45, 'Coaching d\'équipe', 5, 'CRITIQUE', 2, 'INACTIF'),
(46, 'Gestion du stress', 5, 'IMPORTANTE', 2, 'ACTIF'),
(47, 'Design Thinking', 5, 'IMPORTANTE', 3, 'ACTIF'),
(48, 'Excel Avancé', 5, 'IMPORTANTE', 4, 'ACTIF'),
(49, 'Power Automate', 5, 'IMPORTANTE', 4, 'ACTIF'),
(51, 'Angular', 5, 'IMPORTANTE', 1, 'ACTIF'),
(58, 'Angular', 5, 'IMPORTANTE', 1, 'ACTIF');

-- --------------------------------------------------------

--
-- Structure de la table `competenceemploye`
--

DROP TABLE IF EXISTS `competenceemploye`;
CREATE TABLE IF NOT EXISTS `competenceemploye` (
  `id` int NOT NULL AUTO_INCREMENT,
  `niveauActuel` int NOT NULL,
  `niveauValide` tinyint(1) DEFAULT '0',
  `preuveUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateEvaluation` date DEFAULT NULL,
  `employe_id` int NOT NULL,
  `competence_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employe_competence` (`employe_id`,`competence_id`),
  KEY `employe_id` (`employe_id`),
  KEY `competence_id` (`competence_id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `competenceemploye`
--

INSERT INTO `competenceemploye` (`id`, `niveauActuel`, `niveauValide`, `preuveUrl`, `dateEvaluation`, `employe_id`, `competence_id`) VALUES
(1, 4, 1, NULL, '2026-02-20', 13, 1),
(2, 5, 1, NULL, '2026-02-20', 13, 13),
(3, 4, 1, NULL, '2026-02-20', 13, 14),
(4, 3, 1, NULL, '2026-02-20', 13, 6),
(5, 4, 1, NULL, '2026-02-20', 13, 11),
(6, 3, 0, NULL, '2026-02-20', 13, 20),
(7, 2, 0, NULL, '2026-02-20', 13, 5),
(8, 5, 1, NULL, '2026-02-20', 16, 19),
(9, 4, 1, NULL, '2026-02-20', 16, 9),
(10, 4, 1, NULL, '2026-02-20', 16, 21),
(11, 3, 1, NULL, '2026-02-20', 16, 13),
(12, 3, 1, NULL, '2026-02-20', 16, 8),
(13, 2, 0, NULL, '2026-02-20', 16, 1),
(14, 1, 0, NULL, '2026-02-20', 16, 14),
(15, 5, 1, NULL, '2026-02-20', 17, 1),
(16, 5, 1, NULL, '2026-02-20', 17, 2),
(17, 4, 1, NULL, '2026-02-20', 17, 3),
(18, 4, 1, NULL, '2026-02-20', 17, 18),
(19, 3, 1, NULL, '2026-02-20', 17, 4),
(20, 2, 1, NULL, '2026-02-20', 17, 15),
(21, 2, 0, NULL, '2026-02-20', 17, 14),
(22, 5, 1, NULL, '2026-02-20', 18, 15),
(23, 5, 1, NULL, '2026-02-20', 18, 5),
(24, 4, 1, NULL, '2026-02-20', 18, 6),
(25, 4, 1, NULL, '2026-02-20', 18, 14),
(26, 4, 1, NULL, '2026-02-20', 18, 21),
(27, 3, 1, NULL, '2026-02-20', 18, 20),
(28, 2, 0, NULL, '2026-02-20', 18, 13),
(29, 4, 1, NULL, '2026-02-20', 24, 21),
(30, 3, 1, NULL, '2026-02-20', 24, 8),
(31, 3, 1, NULL, '2026-02-20', 24, 9),
(32, 3, 1, NULL, '2026-02-20', 24, 20),
(33, 2, 0, NULL, '2026-02-20', 24, 14),
(34, 2, 0, NULL, '2026-02-20', 24, 1),
(35, 3, 1, NULL, '2026-02-20', 25, 13),
(36, 2, 0, NULL, '2026-02-20', 25, 1),
(37, 2, 0, NULL, '2026-02-20', 25, 21),
(38, 1, 0, NULL, '2026-02-20', 25, 14),
(39, 1, 0, NULL, '2026-02-20', 25, 5),
(40, 3, 0, NULL, '2026-02-20', 27, 19),
(41, 3, 1, NULL, '2026-02-20', 27, 21),
(42, 2, 0, NULL, '2026-02-20', 27, 15),
(43, 2, 0, NULL, '2026-02-20', 27, 6),
(44, 1, 0, NULL, '2026-02-20', 27, 9),
(45, 4, 1, NULL, '2026-02-20', 8, 1),
(46, 4, 1, NULL, '2026-02-20', 8, 2),
(47, 3, 1, NULL, '2026-02-20', 8, 13),
(48, 3, 1, NULL, '2026-02-20', 8, 14),
(49, 3, 1, NULL, '2026-02-20', 8, 9),
(50, 2, 0, NULL, '2026-02-20', 8, 5),
(51, 2, 0, NULL, '2026-02-20', 8, 20),
(57, 5, 1, NULL, '2026-02-20', 15, 10),
(58, 5, 1, NULL, '2026-02-20', 15, 11),
(59, 4, 1, NULL, '2026-02-20', 15, 7),
(60, 4, 1, NULL, '2026-02-20', 15, 8),
(61, 3, 1, NULL, '2026-02-20', 15, 18),
(62, 3, 1, NULL, '2026-02-20', 15, 22),
(63, 2, 0, NULL, '2026-02-20', 15, 20),
(64, 5, 1, NULL, '2026-02-20', 20, 10),
(65, 5, 1, NULL, '2026-02-20', 20, 8),
(66, 4, 1, NULL, '2026-02-20', 20, 11),
(67, 3, 1, NULL, '2026-04-04', 20, 7),
(68, 4, 1, NULL, '2026-02-20', 20, 9),
(69, 3, 1, NULL, '2026-02-20', 20, 22),
(70, 2, 0, NULL, '2026-02-20', 20, 17),
(71, 4, 1, NULL, '2026-02-20', 22, 12),
(72, 4, 1, NULL, '2026-02-20', 22, 20),
(73, 4, 1, NULL, '2026-02-20', 22, 17),
(74, 3, 1, NULL, '2026-02-20', 22, 8),
(75, 3, 1, NULL, '2026-02-20', 22, 10),
(76, 2, 0, NULL, '2026-02-20', 22, 16),
(77, 2, 0, NULL, '2026-02-20', 22, 18),
(78, 5, 1, NULL, '2026-02-20', 23, 10),
(79, 4, 1, NULL, '2026-02-20', 23, 11),
(80, 4, 1, NULL, '2026-02-20', 23, 7),
(81, 4, 1, NULL, '2026-02-20', 23, 22),
(82, 3, 1, NULL, '2026-02-20', 23, 8),
(83, 3, 1, NULL, '2026-02-20', 23, 9),
(84, 5, 1, NULL, '2026-02-20', 28, 8),
(85, 4, 1, NULL, '2026-02-20', 28, 17),
(86, 4, 1, NULL, '2026-02-20', 28, 12),
(87, 3, 1, NULL, '2026-02-20', 28, 18),
(88, 3, 1, NULL, '2026-02-20', 28, 11),
(89, 2, 0, NULL, '2026-02-20', 28, 10),
(90, 5, 1, NULL, '2026-02-20', 29, 10),
(91, 4, 1, NULL, '2026-02-20', 29, 7),
(92, 4, 1, NULL, '2026-02-20', 29, 11),
(93, 4, 1, NULL, '2026-02-20', 29, 8),
(94, 3, 1, NULL, '2026-02-20', 29, 18),
(95, 3, 1, NULL, '2026-02-20', 29, 22),
(96, 2, 0, NULL, '2026-02-20', 29, 20),
(97, 3, 1, NULL, '2026-02-20', 9, 12),
(98, 2, 1, NULL, '2026-02-20', 9, 17),
(99, 2, 0, NULL, '2026-02-20', 9, 20),
(100, 2, 0, NULL, '2026-02-20', 9, 8),
(101, 1, 0, NULL, '2026-02-20', 9, 10),
(102, 5, 1, NULL, '2026-02-20', 14, 17),
(103, 5, 1, NULL, '2026-02-20', 14, 12),
(104, 4, 1, NULL, '2026-02-20', 14, 13),
(105, 4, 1, NULL, '2026-02-20', 14, 16),
(106, 3, 1, NULL, '2026-02-20', 14, 20),
(107, 3, 1, NULL, '2026-02-20', 14, 6),
(108, 2, 0, NULL, '2026-02-20', 14, 9),
(109, 5, 1, NULL, '2026-02-20', 19, 7),
(110, 5, 1, NULL, '2026-02-20', 19, 8),
(111, 4, 1, NULL, '2026-02-20', 19, 22),
(112, 4, 1, NULL, '2026-02-20', 19, 11),
(113, 3, 1, NULL, '2026-02-20', 19, 9),
(114, 2, 0, NULL, '2026-02-20', 19, 12),
(115, 5, 1, NULL, '2026-02-20', 21, 19),
(116, 4, 1, NULL, '2026-02-20', 21, 21),
(117, 4, 1, NULL, '2026-02-20', 21, 18),
(118, 3, 1, NULL, '2026-02-20', 21, 20),
(119, 3, 1, NULL, '2026-02-20', 21, 9),
(120, 3, 0, NULL, '2026-02-20', 21, 6),
(121, 2, 0, NULL, '2026-02-20', 21, 13),
(122, 3, 1, NULL, '2026-02-20', 26, 13),
(123, 2, 0, NULL, '2026-02-20', 26, 12),
(124, 2, 0, NULL, '2026-02-20', 26, 17),
(125, 1, 0, NULL, '2026-02-20', 26, 16),
(126, 1, 0, NULL, '2026-02-20', 26, 20),
(127, 4, 1, NULL, '2026-02-20', 6, 13),
(128, 4, 1, NULL, '2026-02-20', 6, 12),
(129, 3, 1, NULL, '2026-02-20', 6, 6),
(130, 3, 1, NULL, '2026-02-20', 6, 16),
(131, 3, 1, NULL, '2026-02-20', 6, 20),
(132, 5, 0, NULL, '2026-03-02', 6, 17),
(133, 2, 1, NULL, '2026-02-20', 7, 8),
(134, 2, 1, NULL, '2026-02-20', 7, 7),
(135, 1, 0, NULL, '2026-02-20', 7, 22),
(136, 1, 0, NULL, '2026-02-20', 7, 9),
(137, 5, 1, NULL, '2026-02-20', 3, 7),
(138, 5, 1, NULL, '2026-02-20', 3, 15),
(139, 4, 1, NULL, '2026-02-20', 3, 5),
(140, 4, 1, NULL, '2026-02-20', 3, 6),
(141, 4, 1, NULL, '2026-02-20', 3, 11),
(142, 3, 1, NULL, '2026-02-20', 3, 8),
(143, 5, 1, NULL, '2026-02-20', 10, 7),
(144, 5, 1, NULL, '2026-02-20', 10, 10),
(145, 5, 1, NULL, '2026-02-20', 10, 11),
(146, 4, 1, NULL, '2026-02-20', 10, 8),
(147, 4, 1, NULL, '2026-02-20', 10, 18),
(148, 3, 1, NULL, '2026-02-20', 10, 22),
(149, 5, 1, NULL, '2026-02-20', 11, 7),
(150, 5, 1, NULL, '2026-02-20', 11, 8),
(151, 4, 1, NULL, '2026-02-20', 11, 12),
(152, 3, 1, NULL, '2026-03-26', 11, 22),
(153, 4, 1, NULL, '2026-03-26', 11, 11),
(154, 3, 1, NULL, '2026-02-20', 11, 9),
(155, 3, 0, NULL, '2026-03-02', 6, 24),
(156, 5, 0, NULL, '2026-03-02', 6, 22),
(158, 2, 0, NULL, '2026-04-05', 25, 33),
(159, 3, 0, NULL, '2026-03-23', 6, 33),
(160, 2, 0, NULL, '2026-03-26', 11, 10),
(161, 5, 0, NULL, '2026-03-26', 11, 34),
(162, 2, 0, NULL, '2026-03-26', 11, 1),
(163, 1, 0, NULL, '2026-03-26', 11, 2),
(164, 2, 0, NULL, '2026-03-27', 11, 37),
(165, 2, 0, NULL, '2026-03-27', 12, 6),
(166, 1, 0, NULL, '2026-04-18', 12, 33),
(167, 3, 0, NULL, '2026-04-18', 12, 23),
(168, 2, 0, NULL, '2026-03-27', 12, 7),
(169, 1, 0, NULL, '2026-03-27', 12, 14),
(170, 3, 1, NULL, '2026-04-04', 13, 10),
(171, 3, 0, NULL, '2026-04-20', 13, 33),
(174, 2, 0, NULL, '2026-04-06', 19, 32),
(177, 5, 0, NULL, '2026-04-18', 12, 51),
(178, 1, 0, NULL, '2026-04-18', 11, 43),
(179, 1, 0, NULL, '2026-04-18', 12, 19),
(182, 1, 0, NULL, '2026-04-18', 12, 46),
(183, 1, 0, NULL, '2026-04-18', 11, 46);

-- --------------------------------------------------------

--
-- Structure de la table `competence_niveau_history`
--

DROP TABLE IF EXISTS `competence_niveau_history`;
CREATE TABLE IF NOT EXISTS `competence_niveau_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employe_id` int NOT NULL,
  `competence_id` int NOT NULL,
  `niveau` int NOT NULL,
  `date_snapshot` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_emp_comp` (`employe_id`,`competence_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `competence_niveau_history`
--

INSERT INTO `competence_niveau_history` (`id`, `employe_id`, `competence_id`, `niveau`, `date_snapshot`) VALUES
(1, 11, 7, 5, '2026-03-22'),
(2, 11, 8, 5, '2026-03-22'),
(3, 11, 9, 3, '2026-03-22'),
(4, 11, 11, 3, '2026-03-22'),
(5, 11, 12, 4, '2026-03-22'),
(6, 11, 22, 4, '2026-03-22');

-- --------------------------------------------------------

--
-- Structure de la table `conge`
--

DROP TABLE IF EXISTS `conge`;
CREATE TABLE IF NOT EXISTS `conge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nbr_jours` int NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  `type_conge_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_conge_id` (`type_conge_id`),
  KEY `fk_conge_user` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `conge`
--

INSERT INTO `conge` (`id`, `date_debut`, `date_fin`, `nbr_jours`, `statut`, `type_conge_id`, `utilisateur_id`) VALUES
(3, '2026-02-17', '2026-02-21', 5, 'refuse', 1, 3),
(10, '2026-03-01', '2026-03-10', 10, 'Approuvé', 1, 10),
(11, '2026-04-14', '2026-04-18', 5, 'En attente', 2, 10),
(12, '2026-02-18', '2026-02-19', 2, 'Approuvé', 1, 6),
(13, '2026-03-20', '2026-03-25', 5, 'Approuvé', 5, 6),
(14, '2026-02-28', '2026-03-02', 3, 'Approuvé', 2, 7),
(15, '2026-04-01', '2026-04-03', 3, 'En attente', 1, 7),
(16, '2026-03-05', '2026-03-06', 2, 'Approuvé', 5, 8),
(17, '2026-05-10', '2026-05-15', 6, 'En attente', 1, 8),
(18, '2026-02-19', '2026-02-20', 2, 'Approuvé', 5, 9),
(19, '2026-03-15', '2026-03-20', 6, 'Approuvé', 1, 9),
(20, '2026-03-09', '2026-03-10', 2, 'Approuvé', 8, 13),
(21, '2026-04-20', '2026-04-25', 6, 'En attente', 1, 13),
(22, '2026-03-19', '2026-03-31', 13, 'Approuvé', 8, 14),
(23, '2026-05-01', '2026-05-05', 5, 'En attente', 1, 14),
(24, '2026-04-07', '2026-04-11', 5, 'Approuvé', 1, 18),
(25, '2026-03-23', '2026-03-27', 5, 'Approuvé', 3, 20),
(26, '2026-04-17', '2026-04-19', 3, 'En attente', 5, 1),
(27, '2026-04-08', '2026-04-10', 3, 'En attente', 1, 1),
(28, '2026-04-10', '2026-04-12', 3, 'En attente', 2, 1),
(29, '2026-04-07', '2026-04-12', 6, 'Approuvé', 3, 1),
(30, '2026-04-22', '2026-04-25', 4, 'Refusé', 2, 1),
(31, '2026-04-09', '2026-04-12', 4, 'Archivé', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `coworking_reservations`
--

DROP TABLE IF EXISTS `coworking_reservations`;
CREATE TABLE IF NOT EXISTS `coworking_reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `espace_id` int NOT NULL,
  `chair_number` int NOT NULL,
  `user_id` int NOT NULL,
  `reservation_date` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_espace_chair_date` (`espace_id`,`chair_number`,`reservation_date`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `coworking_reservations`
--

INSERT INTO `coworking_reservations` (`id`, `espace_id`, `chair_number`, `user_id`, `reservation_date`, `created_at`) VALUES
(41, 27, 28, 1, '2026-02-20', '2026-02-20 15:35:22'),
(40, 27, 27, 1, '2026-02-20', '2026-02-20 15:35:22'),
(39, 27, 25, 1, '2026-02-20', '2026-02-20 15:35:22'),
(38, 27, 20, 1, '2026-02-20', '2026-02-20 15:35:21'),
(37, 27, 19, 1, '2026-02-20', '2026-02-20 15:35:21'),
(36, 27, 17, 1, '2026-02-20', '2026-02-20 15:35:21'),
(35, 27, 15, 1, '2026-02-20', '2026-02-20 15:35:20'),
(34, 27, 10, 1, '2026-02-20', '2026-02-20 15:35:17'),
(24, 27, 9, 1, '2026-02-20', '2026-02-20 14:30:23'),
(33, 27, 11, 1, '2026-02-20', '2026-02-20 15:35:16'),
(32, 27, 8, 1, '2026-02-20', '2026-02-20 15:34:02'),
(48, 12, 4, 1, '2026-02-20', '2026-02-20 16:32:42'),
(49, 12, 3, 1, '2026-02-20', '2026-02-20 16:32:42'),
(50, 12, 1, 1, '2026-02-20', '2026-02-20 16:32:43'),
(42, 27, 29, 1, '2026-02-20', '2026-02-20 15:35:22'),
(43, 27, 30, 1, '2026-02-20', '2026-02-20 15:35:23'),
(44, 27, 31, 1, '2026-02-20', '2026-02-20 15:35:23'),
(45, 27, 32, 1, '2026-02-20', '2026-02-20 15:35:23'),
(46, 27, 33, 1, '2026-02-20', '2026-02-20 15:35:23'),
(51, 12, 7, 1, '2026-02-20', '2026-02-20 16:59:36'),
(52, 12, 6, 1, '2026-02-20', '2026-02-20 16:59:36'),
(53, 12, 1, 1, '2026-02-21', '2026-02-21 02:55:24'),
(54, 12, 2, 1, '2026-02-21', '2026-02-21 02:55:24'),
(55, 12, 3, 1, '2026-02-21', '2026-02-21 02:55:26'),
(56, 12, 4, 1, '2026-02-21', '2026-02-21 16:16:51'),
(57, 12, 5, 1, '2026-02-21', '2026-02-21 16:17:06'),
(58, 27, 11, 1, '2026-02-21', '2026-02-21 16:59:17'),
(59, 12, 17, 1, '2026-02-22', '2026-02-22 11:29:23'),
(60, 12, 16, 1, '2026-02-22', '2026-02-22 11:29:24'),
(61, 12, 2, 1, '2026-02-22', '2026-02-22 22:00:38'),
(62, 12, 3, 1, '2026-02-22', '2026-02-22 22:16:54'),
(63, 12, 4, 1, '2026-02-23', '2026-02-23 13:03:51'),
(64, 12, 5, 1, '2026-02-23', '2026-02-23 13:03:52'),
(65, 27, 11, 1, '2026-02-24', '2026-02-24 09:02:30'),
(66, 27, 7, 1, '2026-02-24', '2026-02-24 09:24:10'),
(67, 27, 14, 1, '2026-02-24', '2026-02-24 09:26:24'),
(68, 27, 13, 1, '2026-02-24', '2026-02-24 09:26:25'),
(69, 12, 1, 1, '2026-02-24', '2026-02-24 09:50:38'),
(70, 12, 2, 1, '2026-02-24', '2026-02-24 09:50:41'),
(71, 12, 3, 1, '2026-02-24', '2026-02-24 09:50:42'),
(73, 12, 5, 1, '2026-02-24', '2026-02-24 09:51:10'),
(74, 12, 4, 1, '2026-02-24', '2026-02-24 09:51:24'),
(75, 12, 17, 1, '2026-02-24', '2026-02-24 09:51:25'),
(76, 12, 1, 1, '2026-02-26', '2026-02-26 15:40:17'),
(83, 12, 2, 1, '2026-03-01', '2026-03-01 13:08:37'),
(82, 12, 1, 1, '2026-03-01', '2026-03-01 13:08:36'),
(79, 31, 3, 1, '2026-02-28', '2026-02-28 14:52:26'),
(80, 31, 4, 1, '2026-02-28', '2026-02-28 14:52:26'),
(84, 12, 4, 1, '2026-03-01', '2026-03-01 14:08:49'),
(88, 12, 2, 11, '2026-04-28', '2026-04-28 09:12:50'),
(89, 12, 6, 11, '2026-04-28', '2026-04-28 09:13:36'),
(90, 12, 4, 11, '2026-04-28', '2026-04-28 09:21:21'),
(91, 12, 1, 11, '2026-04-28', '2026-04-28 09:27:34'),
(92, 12, 5, 6, '2026-04-28', '2026-04-28 10:28:24'),
(94, 27, 1, 11, '2026-04-28', '2026-04-28 09:29:23'),
(95, 12, 3, 6, '2026-04-28', '2026-04-28 11:08:46'),
(97, 12, 5, 3, '2026-05-02', '2026-05-02 15:18:32'),
(98, 12, 3, 11, '2026-05-02', '2026-05-02 15:19:19');

-- --------------------------------------------------------

--
-- Structure de la table `demande_absence`
--

DROP TABLE IF EXISTS `demande_absence`;
CREATE TABLE IF NOT EXISTS `demande_absence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_demande` date NOT NULL,
  `motif` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  `absence_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `absence_id` (`absence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_conge`
--

DROP TABLE IF EXISTS `demande_conge`;
CREATE TABLE IF NOT EXISTS `demande_conge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_demande` date NOT NULL,
  `motif` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  `conge_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conge_id` (`conge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260406102301', '2026-04-06 17:48:11', 90),
('DoctrineMigrations\\Version20260406103349', '2026-04-06 17:48:11', 1),
('DoctrineMigrations\\Version20260406103856', '2026-04-06 17:48:11', 4),
('DoctrineMigrations\\Version20260406104424', '2026-04-06 17:48:11', 33),
('DoctrineMigrations\\Version20260406104742', '2026-04-06 17:48:11', 145),
('DoctrineMigrations\\Version20260406164957', NULL, NULL),
('DoctrineMigrations\\Version20260406174800', NULL, NULL),
('DoctrineMigrations\\Version20260412000000', NULL, NULL),
('DoctrineMigrations\\Version20260414000000', '2026-04-28 08:58:19', 61),
('DoctrineMigrations\\Version20260419145712', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

DROP TABLE IF EXISTS `document`;
CREATE TABLE IF NOT EXISTS `document` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `document`
--

INSERT INTO `document` (`id`, `name`, `path`, `type`, `uploaded_at`) VALUES
(1, 'bike_sharing_data.txt', 'uploaded_documents\\1772031452682_bike_sharing_data.txt', 'Fichier', '2026-02-25 15:57:33'),
(2, 'pat.png', 'uploaded_documents\\1772033381275_pat.png', 'Image', '2026-02-25 16:29:41'),
(3, 'rapport bilan personnel.pdf', 'uploaded_documents\\1772227678852_rapport bilan personnel.pdf', 'PDF', '2026-02-27 22:27:59'),
(4, 'TP8 Firewall PFSENSE.pdf', 'uploaded_documents\\1772227725143_TP8 Firewall PFSENSE.pdf', 'PDF', '2026-02-27 22:28:45'),
(5, 'Modele-contrat-de-travail.pdf', 'uploaded_documents\\1772281812856_Modele-contrat-de-travail.pdf', 'PDF', '2026-02-28 13:30:13'),
(6, 'Modele-contrat-de-travail.pdf', 'uploaded_documents\\1772281948630_Modele-contrat-de-travail.pdf', 'PDF', '2026-02-28 13:32:29'),
(7, 'Prosit 8.pdf', 'uploaded_documents\\1772368721602_Prosit 8.pdf', 'PDF', '2026-03-01 13:38:42');

-- --------------------------------------------------------

--
-- Structure de la table `employe`
--

DROP TABLE IF EXISTS `employe`;
CREATE TABLE IF NOT EXISTS `employe` (
  `utilisateur_id` int NOT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `poste_actuel` varchar(100) DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `departement` varchar(100) DEFAULT NULL,
  `manager_id` int DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'applied',
  PRIMARY KEY (`utilisateur_id`),
  UNIQUE KEY `matricule` (`matricule`),
  KEY `manager_id` (`manager_id`)
) ;

--
-- Déchargement des données de la table `employe`
--

INSERT INTO `employe` (`utilisateur_id`, `matricule`, `poste_actuel`, `date_embauche`, `departement`, `manager_id`, `status`) VALUES
(2, '1122', 'ing', NULL, 'ing', NULL, 'applied'),
(3, NULL, NULL, NULL, NULL, NULL, 'applied'),
(4, '122233', 'ing', NULL, 'it', 3, 'applied'),
(5, NULL, NULL, NULL, NULL, 3, 'applied'),
(6, NULL, NULL, NULL, NULL, 3, 'applied'),
(7, 'azazezzeze', 'ezezaaaaaaaaaaaaaaaa', NULL, 'ooo', 3, 'applied');

-- --------------------------------------------------------

--
-- Structure de la table `employe_competence`
--

DROP TABLE IF EXISTS `employe_competence`;
CREATE TABLE IF NOT EXISTS `employe_competence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `posteActuel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dateEmbauche` date DEFAULT NULL,
  `Departement` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`),
  KEY `idx_employe_matricule` (`matricule`),
  KEY `idx_employe_departement` (`Departement`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `employe_competence`
--

INSERT INTO `employe_competence` (`id`, `matricule`, `posteActuel`, `dateEmbauche`, `Departement`, `nom`) VALUES
(1, 'EMP001', 'Développeur Backend', '2021-03-15', 'IT', 'Karim Ben Ali'),
(2, 'EMP002', 'Data Analyst', '2022-07-01', 'Data', 'Yasmine Trabelsi'),
(3, 'EMP003', 'Chef de projet', '2020-01-10', 'Management', 'Omar Mansouri'),
(4, 'EMP004', 'Ingénieur QA', '2023-02-20', 'IT', 'Nadia El Amrani'),
(5, 'EMP005', 'Développeur Frontend', '2022-05-10', 'IT', 'Sami Haddad'),
(6, 'EMP006', 'DevOps Engineer', '2021-11-18', 'IT', 'Rania Khelifi'),
(7, 'EMP007', 'Responsable RH', '2019-09-01', 'HR', 'Hassan Alami'),
(8, 'EMP008', 'Product Owner', '2020-06-12', 'Management', 'Leila Ben Youssef'),
(9, 'EMP009', 'Ingénieur Sécurité', '2023-01-05', 'Cybersecurity', 'Amine Zahraoui'),
(10, 'EMP010', 'Business Analyst', '2021-04-22', 'Business', 'Salma Idrissi'),
(11, 'EMP011', 'Scrum Master', '2020-08-30', 'Management', 'Youssef Chikhi'),
(12, 'EMP012', 'Support Technique', '2024-02-15', 'IT', 'Fatima Noor'),
(13, 'EMP013', 'Stagiaire', '2022-09-10', 'IT', 'Adel Boussaid'),
(14, 'EMP014', 'Stagiaire', '2023-03-18', 'Data', 'Meryem Chaoui'),
(15, 'EMP015', 'Stagiaire', '2021-12-01', 'Cybersecurity', 'Bilal Najjar'),
(16, 'EMP016', 'Consultant', '2024-01-08', 'Business', 'Sara Mahfoudh'),
(17, 'EMP017', 'Chef de projet', '2020-11-11', 'Management', 'Tarek Ben Salah');

-- --------------------------------------------------------

--
-- Structure de la table `entretien_recrutement`
--

DROP TABLE IF EXISTS `entretien_recrutement`;
CREATE TABLE IF NOT EXISTS `entretien_recrutement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `candidature_externe_id` int DEFAULT NULL,
  `type_entretien` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_entretien` date DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `intervieweurs_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `salle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_visio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut_entretien` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note_entretien` double DEFAULT NULL,
  `duree` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_entretien_candidature` (`candidature_externe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipement`
--

DROP TABLE IF EXISTS `equipement`;
CREATE TABLE IF NOT EXISTS `equipement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `equipement`
--

INSERT INTO `equipement` (`id`, `nom`) VALUES
(3, 'Climatisation'),
(2, 'Projecteur'),
(5, 'Sonorisation'),
(4, 'Tableau'),
(1, 'WiFi');

-- --------------------------------------------------------

--
-- Structure de la table `espaces`
--

DROP TABLE IF EXISTS `espaces`;
CREATE TABLE IF NOT EXISTS `espaces` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `capacite` int NOT NULL,
  `etage` int NOT NULL,
  `listeEquipements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `urlImage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `disponible` tinyint(1) DEFAULT '1',
  `typeEspace` enum('REUNION','COWORKING') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `espaces`
--

INSERT INTO `espaces` (`id`, `nom`, `capacite`, `etage`, `listeEquipements`, `urlImage`, `disponible`, `typeEspace`) VALUES
(12, 'Salle de reunion', 6, 2, '[\"Projecteur\",\"Ordinateur\",\"Wifi\"]', 'https://image.workin.space/wijpeg-at1jhb92ubk07gxev1a2p5w6e/img-6025_standard.jpg?crop=100%2C0%2C1600%2C1200&width=700', 1, 'COWORKING'),
(27, 'Coworking Space', 16, 0, '[\"Projecteur\",\"Ordinateur\"]', 'https://workzone.tn/wp-content/uploads/2022/07/Optimized-IMG_12711.jpg', 1, 'COWORKING'),
(28, 'Work Zone', 10, 1, '', 'https://workzone.tn/wp-content/uploads/2022/06/IMG_0868-scaled.jpg', 1, ''),
(29, 'Work Hard and never says over', 10, 2, '[\"Projecteur\",\"Téléphone\"]', 'https://workzone.tn/wp-content/uploads/2022/07/Optimized-IMG_12641.jpg', 1, 'REUNION');

-- --------------------------------------------------------

--
-- Structure de la table `evaluationformation`
--

DROP TABLE IF EXISTS `evaluationformation`;
CREATE TABLE IF NOT EXISTS `evaluationformation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree` int DEFAULT NULL,
  `session_id` int NOT NULL,
  `score_requis` int DEFAULT '70',
  `niveau_succes` int DEFAULT '1',
  `niveau_echec` int DEFAULT '-1',
  `competence_id` int DEFAULT NULL,
  `dateDebut` date DEFAULT NULL,
  `dateFin` date DEFAULT NULL,
  `niveau_requis` int DEFAULT '0',
  `difficulte` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MOYEN',
  `statut_eval` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  KEY `idx_evaluation_session` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evaluationformation`
--

INSERT INTO `evaluationformation` (`id`, `titre`, `type`, `duree`, `session_id`, `score_requis`, `niveau_succes`, `niveau_echec`, `competence_id`, `dateDebut`, `dateFin`, `niveau_requis`, `difficulte`, `statut_eval`) VALUES
(7, 'Évaluation Leadership Final', 'QCM', 20, 17, 80, 3, -2, 7, '2025-02-01', '2025-02-04', 0, 'DIFFICILE', 'ACTIVE'),
(18, 'Évaluation Gestion du Stress', 'QCM', 20, 48, 60, 1, -1, 46, '2026-04-18', '2026-04-30', 0, 'FACILE', 'ACTIVE'),
(21, 'Évaluation Power BI Avancé', 'QCM', 30, 51, 80, 3, -2, 17, '2025-12-12', '2025-12-16', 2, 'DIFFICILE', 'ACTIVE'),
(22, 'Évaluation Flutter Fondamentaux', 'QCM', 30, 60, 60, 1, -1, 43, '2026-04-13', '2026-04-30', 0, 'FACILE', 'INACTIVE'),
(28, 'Évaluation Cybersecurity Audit', 'QCM', 30, 42, 70, 2, -2, 19, '2026-04-06', '2026-04-19', 0, 'MOYEN', 'ACTIVE');

-- --------------------------------------------------------

--
-- Structure de la table `evaluation_candidat`
--

DROP TABLE IF EXISTS `evaluation_candidat`;
CREATE TABLE IF NOT EXISTS `evaluation_candidat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `candidature_externe_id` int DEFAULT NULL,
  `entretien_id` int DEFAULT NULL,
  `note_technique` int DEFAULT NULL,
  `note_savoir_etre` int DEFAULT NULL,
  `note_motivation` int DEFAULT NULL,
  `note_culture_fit` int DEFAULT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `recommandation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `point_fort` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `axe_amelioration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_evaluation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_evaluation_candidature` (`candidature_externe_id`),
  KEY `fk_evaluation_entretien` (`entretien_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluation_question`
--

DROP TABLE IF EXISTS `evaluation_question`;
CREATE TABLE IF NOT EXISTS `evaluation_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evaluation_id` int NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_d` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonne_reponse` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explication` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_evalq_evaluation` (`evaluation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evaluation_question`
--

INSERT INTO `evaluation_question` (`id`, `evaluation_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`) VALUES
(12, 7, 'Le leadership situationnel selon Hersey & Blanchard adapte le style à :', 'La personnalité du leader', 'Le niveau de maturité/compétence du collaborateur', 'L\'humeur du jour', 'La taille de l\'équipe', 'B', 'Le leadership situationnel adapte le style (directif, persuasif, participatif, délégatif) au niveau de développement du collaborateur.'),
(13, 7, 'La technique du \"sandwich feedback\" consiste à :', 'Trois critiques simultanées', 'Positif + critique + positif pour atténuer le message difficile', 'Ignorer les problèmes', 'Feedback uniquement écrit', 'B', 'Le sandwich enveloppe la critique entre deux messages positifs pour préserver la motivation.'),
(26, 7, 'La communication non-violente (CNV) repose sur :', 'Critiquer pour améliorer', 'Observer les faits, exprimer ses sentiments, identifier les besoins, formuler une demande concrète', 'Éviter tout conflit', 'Imposer ses idées', 'B', 'La CNV de Rosenberg distingue observation (sans jugement), sentiment, besoin et demande pour favoriser une relation authentique.'),
(27, 7, 'Dans un modèle DISC, le profil \"D\" (Dominance) est caractérisé par :', 'Beaucoup d\'empathie et d\'écoute', 'Orientation résultats, décision rapide, directivité', 'Créativité et innovation', 'Conformité aux règles', 'B', 'Le profil D est axé sur les résultats, aime les défis, décide vite et peut parfois paraître trop direct.'),
(52, 18, 'Le stress chronique se distingue du stress aigu par :', 'Son intensité plus forte', 'Sa durée prolongée dans le temps et ses effets délétères sur la santé', 'Ses causes toujours professionnelles', 'Sa facile résolution', 'B', 'Le stress aigu est ponctuel et adaptatif ; le stress chronique persiste et peut provoquer burn-out, maladies cardiovasculaires, etc.'),
(53, 18, 'La technique de respiration 4-7-8 consiste à :', 'Respirer 4 fois, 7 fois, 8 fois', 'Inspirer 4s, retenir 7s, expirer 8s pour activer le système parasympathique', 'Faire 4 exercices en 7 minutes', 'Répéter 8 cycles de respiration profonde', 'B', 'Cette technique active le nerf vague et le système nerveux parasympathique, induisant un état de calme rapide.'),
(54, 18, 'La matrice d\'Eisenhower aide à :', 'Évaluer les compétences', 'Prioriser les tâches selon leur urgence et leur importance pour réduire la surcharge', 'Planifier un projet', 'Mesurer la performance', 'B', 'Les 4 quadrants (Urgent+Important, Important+Non urgent, Urgent+Non important, Ni urgent ni important) structurent les priorités.'),
(55, 18, 'Le burn-out se caractérise par la triade :', 'Dépression, anxiété, insomnie', 'Épuisement émotionnel, dépersonnalisation, sentiment d\'inefficacité professionnelle', 'Colère, tristesse, peur', 'Surmenage, absentéisme, erreurs', 'B', 'La définition de Maslach identifie ces 3 dimensions comme constitutives du burn-out.'),
(56, 18, 'La pleine conscience (mindfulness) réduit le stress en :', 'Évitant les pensées négatives', 'Entraînant l\'attention sur l\'instant présent sans jugement, réduisant les ruminations', 'Augmentant la productivité', 'Remplaçant la médecine', 'B', 'Les études montrent que 8 semaines de pratique MBSR réduisent significativement le cortisol et l\'anxiété perçue.'),
(67, 21, 'DAX (Data Analysis Expressions) est utilisé dans Power BI pour :', 'Importer des données', 'Créer des mesures et colonnes calculées pour l\'analyse avancée', 'Dessiner des visuels', 'Configurer des gateways', 'B', 'DAX est le langage de formule de Power BI, Analysis Services et Power Pivot pour des calculs analytiques puissants.'),
(68, 21, 'La fonction CALCULATE dans DAX sert à :', 'Afficher un résultat dans un visuel', 'Modifier le contexte de filtre d\'une expression DAX', 'Joindre deux tables', 'Créer un rapport', 'B', 'CALCULATE est la fonction la plus puissante de DAX : elle évalue une expression dans un contexte de filtre modifié.'),
(69, 21, 'La différence entre une mesure et une colonne calculée dans Power BI est :', 'Il n\'y a aucune différence', 'La mesure est calculée dynamiquement selon le contexte de rapport ; la colonne calculée est stockée dans le modèle', 'La colonne calculée est plus rapide', 'La mesure ne peut pas utiliser DAX', 'B', 'Les mesures sont agrégées à la volée et sont sensibles aux filtres ; les colonnes calculées sont évaluées au rafraîchissement.'),
(70, 21, 'Le Row-Level Security (RLS) dans Power BI permet de :', 'Accélérer les rapports', 'Restreindre l\'accès aux données selon le profil de l\'utilisateur connecté', 'Créer des visuels personnalisés', 'Gérer les licences', 'B', 'RLS définit des rôles avec des filtres DAX qui s\'appliquent automatiquement selon l\'identité de l\'utilisateur.'),
(71, 21, 'Un modèle en étoile (star schema) dans Power BI favorise :', 'Des requêtes plus complexes', 'Des performances optimales grâce à une table de faits centrale liée à des tables de dimensions', 'L\'import de données CSV', 'La création de mesures DAX', 'B', 'Le modèle en étoile minimise les jointures et optimise le moteur Vertipaq de Power BI.'),
(72, 22, 'Flutter utilise quel langage de programmation ?', 'JavaScript', 'Dart', 'Kotlin', 'Swift', 'B', 'Flutter est développé par Google et utilise Dart, un langage optimisé pour les interfaces utilisateur rapides.'),
(73, 22, 'Dans Flutter, la différence entre StatelessWidget et StatefulWidget est :', 'StatefulWidget est plus rapide', 'StatelessWidget est immuable ; StatefulWidget peut reconstruire son interface quand son state change', 'StatelessWidget supporte les animations', 'Il n\'y a aucune différence', 'B', 'StatefulWidget utilise setState() pour déclencher la reconstruction du widget quand les données changent.'),
(74, 22, 'Le Widget Tree dans Flutter représente :', 'La structure du code Dart', 'La hiérarchie des composants UI qui décrivent l\'interface de l\'application', 'Les dépendances du projet', 'L\'arborescence des fichiers', 'B', 'Tout dans Flutter est un Widget : l\'interface est construite en imbriquant des widgets qui forment un arbre.'),
(75, 22, 'Provider dans Flutter est utilisé pour :', 'Naviguer entre les écrans', 'Gérer et partager l\'état de l\'application entre widgets sans drilling de props', 'Appeler des APIs REST', 'Styliser les widgets', 'B', 'Provider implémente le pattern InheritedWidget de manière ergonomique pour la gestion d\'état.'),
(76, 22, 'La commande flutter pub get sert à :', 'Démarrer le serveur de développement', 'Télécharger et installer les dépendances déclarées dans pubspec.yaml', 'Compiler l\'application', 'Tester les widgets', 'B', 'pubspec.yaml est le fichier de configuration Flutter ; pub get résout et installe les packages déclarés.');

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

DROP TABLE IF EXISTS `evenement`;
CREATE TABLE IF NOT EXISTS `evenement` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `dateEvenement` date NOT NULL,
  `dateHeureDebut` datetime NOT NULL,
  `dateHeureFin` datetime NOT NULL,
  `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nbParticipantsMax` int NOT NULL,
  `participantsInscrits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `creePar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creeLe` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `exercice_soumission`
--

DROP TABLE IF EXISTS `exercice_soumission`;
CREATE TABLE IF NOT EXISTS `exercice_soumission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employe_id` int NOT NULL,
  `module_id` int NOT NULL,
  `sous_section_id` int DEFAULT NULL,
  `contenu_rendu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_ia` int DEFAULT NULL,
  `feedback_ia` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` enum('soumis','corrige','en_attente') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'soumis',
  `date_soumission` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_correction` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sous_section_id` (`sous_section_id`),
  KEY `idx_soumission_emp` (`employe_id`),
  KEY `idx_soumission_mod` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `follow`
--

DROP TABLE IF EXISTS `follow`;
CREATE TABLE IF NOT EXISTS `follow` (
  `id` int NOT NULL AUTO_INCREMENT,
  `followerId` int NOT NULL,
  `followingId` int NOT NULL,
  `followedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('ACTIVE','PENDING') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_follow` (`followerId`,`followingId`),
  KEY `idx_follow_follower` (`followerId`),
  KEY `idx_follow_following` (`followingId`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `follow`
--

INSERT INTO `follow` (`id`, `followerId`, `followingId`, `followedAt`, `status`) VALUES
(119, 11, 3, '2026-03-02 21:36:18', 'ACTIVE'),
(120, 9, 6, '2026-03-03 01:46:40', 'ACTIVE'),
(121, 6, 9, '2026-03-03 01:48:01', 'ACTIVE'),
(122, 10, 9, '2026-03-03 01:50:38', 'ACTIVE'),
(123, 10, 6, '2026-03-03 01:51:31', 'ACTIVE'),
(124, 11, 6, '2026-03-03 02:17:23', 'ACTIVE'),
(125, 11, 9, '2026-03-03 02:17:24', 'ACTIVE'),
(126, 9, 3, '2026-03-03 10:01:45', 'PENDING'),
(127, 9, 5, '2026-03-03 10:01:46', 'PENDING'),
(128, 9, 10, '2026-03-03 10:01:48', 'PENDING'),
(129, 9, 11, '2026-03-03 10:01:48', 'PENDING'),
(130, 3, 11, '2026-05-01 10:36:36', 'ACTIVE');

-- --------------------------------------------------------

--
-- Structure de la table `formateur`
--

DROP TABLE IF EXISTS `formateur`;
CREATE TABLE IF NOT EXISTS `formateur` (
  `utilisateur_id` int NOT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`utilisateur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

DROP TABLE IF EXISTS `formation`;
CREATE TABLE IF NOT EXISTS `formation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duree` int NOT NULL,
  `cout` double DEFAULT NULL,
  `statutFormation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie_id` int DEFAULT NULL,
  `typeFormation` enum('CLASSROOM','E_LEARNING','BLENDED','COACHING','MENTORING') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `formateur_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_formation_categorie` (`categorie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formation`
--

INSERT INTO `formation` (`id`, `titre`, `duree`, `cout`, `statutFormation`, `categorie_id`, `typeFormation`, `description`, `formateur_id`) VALUES
(2, 'AWS Certification', 7, NULL, 'Active', 1, 'E_LEARNING', 'Préparation complète à la certification AWS : cloud computing, services AWS et architecture.', 9),
(3, 'Leadership Essentials', 3, 900, 'Active', 2, 'COACHING', 'Développer le leadership, la prise de décision et la gestion d\'équipe.', NULL),
(4, 'Effective Communication', 2, 600, 'Active', 3, 'CLASSROOM', 'Améliorer la communication professionnelle, écoute active et travail en équipe.', NULL),
(5, 'Spring Boot Mastery', 6, 500, 'Active', 1, 'E_LEARNING', 'Maîtriser Spring Boot : API REST, sécurité, JPA et architecture microservices.', NULL),
(6, 'Angular Advanced', 5, NULL, 'Active', 1, 'E_LEARNING', 'Approfondissement Angular : RxJS, performance, architecture modulaire et bonnes pratiques.', 9),
(7, 'Project Management Professional', 8, NULL, 'Active', 2, 'COACHING', 'Méthodologies de gestion de projet : Agile, Scrum, planification et gestion des risques.', NULL),
(8, 'Public Speaking', 2, 700, 'Active', 3, 'CLASSROOM', 'Techniques de prise de parole en public, communication claire et gestion du stress.', NULL),
(9, 'Docker & Kubernetes', 6, 700, 'Active', 1, 'E_LEARNING', 'Conteneurisation avec Docker et orchestration avec Kubernetes pour applications cloud.', NULL),
(10, 'Time Management', 1, NULL, 'Active', 2, 'COACHING', 'Optimiser la gestion du temps, priorisation des tâches et productivité personnelle.', NULL),
(11, 'Cybersecurity Basics', 4, 500, 'Active', 1, 'E_LEARNING', 'Principes fondamentaux de la cybersécurité : menaces, protection des données et bonnes pratiques.', 10),
(12, 'Team Building Workshop', 2, 800, 'Active', 3, 'CLASSROOM', 'Renforcer la cohésion d\'équipe, collaboration et résolution de conflits.', NULL),
(13, 'Python for Data Science', 5, NULL, 'Active', 1, 'E_LEARNING', 'Maîtriser Python pour l\'analyse de données : NumPy, Pandas, Matplotlib et Machine Learning de base.', NULL),
(14, 'Git & DevOps Essentials', 3, NULL, 'Active', 4, 'E_LEARNING', 'Maîtriser Git (branches, merge, rebase) et les outils DevOps : CI/CD, pipelines et bonnes pratiques.', NULL),
(15, 'Agile & Scrum Practitioner', 3, NULL, 'Active', 2, 'E_LEARNING', 'Comprendre et appliquer les méthodes Agile et Scrum dans des projets réels.', 25),
(17, 'GraphQL & API Moderne', 4, NULL, 'Active', 1, 'E_LEARNING', 'Concevoir et consommer des APIs GraphQL avec Apollo Server et Apollo Client.', NULL),
(18, 'Terraform & Infrastructure as Code', 5, NULL, 'Active', 1, 'E_LEARNING', 'Automatiser le provisionnement d\'infrastructure cloud avec Terraform (AWS/Azure).', NULL),
(19, 'Apache Kafka pour développeurs', 4, NULL, 'Active', 1, 'E_LEARNING', 'Architecture event-driven, producteurs/consommateurs, Kafka Streams et connecteurs.', NULL),
(20, 'Coaching & Développement d\'équipe', 3, 800, 'Active', 2, 'COACHING', 'Techniques de coaching GROW, feedback constructif et accompagnement des talents.', 22),
(21, 'Gestion du stress et résilience', 2, 500, 'Active', 2, 'CLASSROOM', 'Identifier les sources de stress, développer la résilience et les stratégies de coping.', NULL),
(22, 'Design Thinking & Innovation', 3, 600, 'Active', 3, 'CLASSROOM', 'Processus d\'innovation centrée utilisateur : empathie, idéation, prototypage, test.', NULL),
(23, 'Excel Avancé & Power Query', 2, NULL, 'Active', 4, 'E_LEARNING', 'Maîtriser les tableaux croisés dynamiques, Power Query, formules avancées et macros.', NULL),
(24, 'Power BI Avancé', 3, NULL, 'Active', 4, 'E_LEARNING', 'DAX avancé, modélisation de données, visualisations interactives et publication de rapports.', NULL),
(25, 'Flutter Mobile Development', 5, 500, 'Active', 1, 'E_LEARNING', 'Développer des applications cross-platform iOS/Android avec Flutter et Dart.', NULL),
(26, 'Redis & Caching Strategies', 3, NULL, 'Active', 1, 'E_LEARNING', 'Intégration de Redis pour le cache applicatif, sessions et files de messages.', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `formation_exam_result`
--

DROP TABLE IF EXISTS `formation_exam_result`;
CREATE TABLE IF NOT EXISTS `formation_exam_result` (
  `id` int NOT NULL AUTO_INCREMENT,
  `score_pct` int NOT NULL,
  `nb_correct` int NOT NULL,
  `nb_total` int NOT NULL,
  `passed` tinyint NOT NULL,
  `attempt_number` int NOT NULL,
  `date_passage` datetime NOT NULL,
  `competence_updated` tinyint DEFAULT NULL,
  `formation_id` int NOT NULL,
  `employe_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_87A4B7425200282E` (`formation_id`),
  KEY `IDX_87A4B7421B65292` (`employe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formation_final_exam_question`
--

DROP TABLE IF EXISTS `formation_final_exam_question`;
CREATE TABLE IF NOT EXISTS `formation_final_exam_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_d` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonne_reponse` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explication` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `module_ref` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `formation_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9D4EE9EB5200282E` (`formation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1221 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formation_final_exam_question`
--

INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(110, 'What is the main benefit of cloud computing according to AWS?', 'Fixed upfront capital expenses', 'Pay only for what you use with variable costs', 'Long-term hardware ownership', 'Limited global reach', 'B', 'Easy - AWS emphasizes pay-as-you-go pricing.', 'Cloud Concepts', '2026-04-19 16:47:50', 2),
(3, 'Quel service AWS gere les identites et les acces ?', 'Amazon S3', 'AWS IAM', 'Amazon EC2', 'Amazon RDS', 'B', 'IAM gere utilisateurs, groupes, roles et politiques d\'acces.', 'Quiz de certification AWS', '0000-00-00 00:00:00', 2),
(4, 'Quelle classe de stockage S3 est la moins chere pour des archives ?', 'S3 Standard', 'S3 Intelligent-Tiering', 'S3 Glacier', 'S3 Standard-IA', 'C', 'S3 Glacier est optimise pour l\'archivage long terme a tres faible cout.', 'Quiz de certification AWS', '0000-00-00 00:00:00', 2),
(5, 'Qu\'est-ce qu\'une AMI dans AWS ?', 'Un service de messagerie', 'Une image machine Amazon pour lancer des instances EC2', 'Un outil de monitoring', 'Un service de base de donnees', 'B', 'Amazon Machine Image est un modele pre-configure pour lancer des instances EC2.', 'Quiz de certification AWS', '0000-00-00 00:00:00', 2),
(6, 'Quel service permet une base de donnees MySQL entierement managee ?', 'Amazon DynamoDB', 'Amazon Redshift', 'Amazon RDS', 'Amazon ElastiCache', 'C', 'Amazon RDS supporte MySQL, PostgreSQL, MariaDB, Oracle et SQL Server.', 'Quiz de certification AWS', '0000-00-00 00:00:00', 2),
(7, 'A quoi sert l\'Auto Scaling Group ?', 'Sauvegarder automatiquement les donnees', 'Ajuster automatiquement le nombre d\'instances EC2', 'Chiffrer les donnees S3', 'Creer des VPCs automatiquement', 'B', 'L\'Auto Scaling ajuste la capacite en fonction de la demande pour optimiser couts et performances.', 'Quiz de certification AWS', '0000-00-00 00:00:00', 2),
(8, 'Quel style de leadership laisse le maximum d\'autonomie a l\'equipe ?', 'Autoritaire', 'Participatif', 'Delegatif', 'Transactionnel', 'C', 'Le style delegatif offre une totale autonomie aux membres competents et autonomes.', 'Quiz Leadership', '0000-00-00 00:00:00', 3),
(9, 'La communication assertive consiste a :', 'Imposer son point de vue', 'Exprimer ses besoins clairement tout en respectant autrui', 'Eviter les conflits a tout prix', 'Etre toujours d\'accord', 'B', 'L\'assertivite equilibre affirmation de soi et respect de l\'autre.', 'Quiz Leadership', '0000-00-00 00:00:00', 3),
(10, 'Selon Herzberg, quels sont les vrais facteurs de motivation ?', 'Salaire et avantages', 'Reconnaissance et accomplissement', 'Securite de l\'emploi', 'Conditions de travail', 'B', 'Pour Herzberg, la reconnaissance et l\'accomplissement sont des facteurs de motivation intrinseques.', 'Quiz Leadership', '0000-00-00 00:00:00', 3),
(11, 'La premiere etape pour resoudre un conflit est :', 'Ignorer le probleme', 'Sanctionner immediatement', 'Identifier la cause racine', 'Faire une reunion generale', 'C', 'Comprendre la cause profonde du conflit est essentiel avant toute intervention.', 'Quiz Leadership', '0000-00-00 00:00:00', 3),
(12, 'Quel pourcentage de la communication est non verbal selon Mehrabian ?', '7%', '38%', '55%', '93%', 'C', 'Selon Mehrabian : 55% corporel, 38% vocal, 7% verbal.', 'Quiz Communication', '0000-00-00 00:00:00', 4),
(13, 'La reformulation en ecoute active permet de :', 'Changer le sujet', 'Verifier la comprehension du message recu', 'Couper la parole', 'Donner son avis', 'B', 'La reformulation montre a l\'interlocuteur qu\'il a bien ete compris.', 'Quiz Communication', '0000-00-00 00:00:00', 4),
(14, 'Le feedback constructif doit etre :', 'General et tardif', 'Specifique, factuel et oriente solution', 'Negatif pour motiver', 'Public pour l\'exemple', 'B', 'Un bon feedback est SMART : Specifique, Mesurable et oriente vers l\'amelioration.', 'Quiz Communication', '0000-00-00 00:00:00', 4),
(15, 'Quel est le role du canal dans le modele de communication ?', 'Encoder le message', 'Transmettre le message de l\'emetteur au recepteur', 'Decoder le message', 'Generer du bruit', 'B', 'Le canal est le support de transmission : email, voix, video, etc.', 'Quiz Communication', '0000-00-00 00:00:00', 4),
(16, 'Quelle annotation marque le point d\'entree d\'une application Spring Boot ?', '@SpringComponent', '@SpringBootApplication', '@EnableAutoConfiguration', '@ComponentScan', 'B', '@SpringBootApplication combine @Configuration, @EnableAutoConfiguration et @ComponentScan.', 'Quiz Spring Boot', '0000-00-00 00:00:00', 5),
(17, 'Quelle annotation definit un endpoint HTTP GET dans Spring Boot ?', '@PostMapping', '@RequestMapping(method=GET)', '@GetMapping', '@HttpGet', 'C', '@GetMapping est l\'annotation raccourcie pour @RequestMapping(method = RequestMethod.GET).', 'Quiz Spring Boot', '0000-00-00 00:00:00', 5),
(18, 'Qu\'est-ce qu\'un JPA Repository ?', 'Un service de cache', 'Une interface pour acceder aux donnees sans ecrire de SQL', 'Un controleur REST', 'Un fichier de configuration', 'B', 'JpaRepository fournit des methodes CRUD prets a l\'emploi via Spring Data.', 'Quiz Spring Boot', '0000-00-00 00:00:00', 5),
(19, 'Dans Spring Security, JWT signifie :', 'Java Web Token', 'JSON Web Token', 'Java Wrapper Type', 'JSON Worker Thread', 'B', 'JWT (JSON Web Token) est un standard de token securise pour l\'authentification stateless.', 'Quiz Spring Boot', '0000-00-00 00:00:00', 5),
(20, 'Quelle annotation permet de simuler un objet dans les tests JUnit ?', '@Spy', '@Mock', '@Inject', '@Fake', 'B', '@Mock de Mockito cree un objet simule pour isoler les tests unitaires.', 'Quiz Spring Boot', '0000-00-00 00:00:00', 5),
(21, 'Quel operateur RxJS transforme chaque valeur emise en un nouvel Observable ?', 'map', 'filter', 'switchMap', 'tap', 'C', 'switchMap projette chaque valeur vers un Observable et annule le precedent.', 'Quiz Angular Avance', '0000-00-00 00:00:00', 6),
(22, 'Dans NgRx, les Effects sont utilises pour :', 'Modifier le state directement', 'Gerer les effets de bord (appels API, etc.)', 'Definir la structure du state', 'Selectionner des donnees du store', 'B', 'Les Effects interceptent les actions et executent des traitements asynchrones comme les appels HTTP.', 'Quiz Angular Avance', '0000-00-00 00:00:00', 6),
(23, 'La strategie OnPush dans Angular optimise :', 'Les animations', 'La detection des changements (Change Detection)', 'Le lazy loading', 'Le routing', 'B', 'OnPush limite la detection de changements aux inputs modifies, ameliorant les performances.', 'Quiz Angular Avance', '0000-00-00 00:00:00', 6),
(24, 'BehaviorSubject vs Subject : quelle est la difference principale ?', 'BehaviorSubject est plus rapide', 'BehaviorSubject emet immediatement la derniere valeur aux nouveaux abonnes', 'Subject supporte plusieurs valeurs', 'Aucune difference', 'B', 'BehaviorSubject conserve et reemet la derniere valeur a tout nouvel abonne, contrairement a Subject.', 'Quiz Angular Avance', '0000-00-00 00:00:00', 6),
(25, 'WBS signifie :', 'Work Budget Schedule', 'Work Breakdown Structure', 'Weekly Business Summary', 'Work Based System', 'B', 'Le WBS decompose le projet en lots de travail hierarchiques et livrables.', 'Quiz PMP', '0000-00-00 00:00:00', 7),
(26, 'Dans Scrum, qui est responsable du Product Backlog ?', 'Scrum Master', 'L\'equipe de developpement', 'Product Owner', 'Le client', 'C', 'Le Product Owner priorise et maintient le backlog selon la valeur metier.', 'Quiz PMP', '0000-00-00 00:00:00', 7),
(27, 'Le diagramme de PERT est utilise pour :', 'Suivre les budgets', 'Estimer la duree et identifier le chemin critique', 'Gerer les ressources humaines', 'Planifier les reunions', 'B', 'PERT modelise les dependances entre taches et calcule le chemin critique.', 'Quiz PMP', '0000-00-00 00:00:00', 7),
(28, 'Quel est le role du Scrum Master ?', 'Gerer le budget du projet', 'Ecrire les user stories', 'Faciliter le processus Scrum et lever les obstacles', 'Valider les livrables', 'C', 'Le Scrum Master est un servant-leader qui protege l\'equipe et facilite l\'adoption de Scrum.', 'Quiz PMP', '0000-00-00 00:00:00', 7),
(29, 'Un sprint en Scrum dure generalement :', '1 jour', '1 semaine', '2 a 4 semaines', '3 mois', 'C', 'Un sprint est une iteration de 1 a 4 semaines, typiquement 2 semaines.', 'Quiz PMP', '0000-00-00 00:00:00', 7),
(30, 'Selon le modele de Tuckman, quelle est la 3eme phase de developpement d\'une equipe ?', 'Forming', 'Storming', 'Norming', 'Performing', 'C', 'Forming -> Storming -> Norming -> Performing -> Adjourning selon Tuckman.', 'Quiz Team Building', '0000-00-00 00:00:00', 12),
(31, 'La confiance au sein d\'une equipe est construite principalement par :', 'Des regles strictes', 'Des interactions regulieres et la transparence', 'La hierarchie', 'La competition interne', 'B', 'La confiance se developpe par la coherence des actions, la transparence et la communication ouverte.', 'Quiz Team Building', '0000-00-00 00:00:00', 12),
(32, 'Qu\'est-ce qu\'une equipe a haute performance ?', 'Une equipe qui travaille de longues heures', 'Une equipe autonome avec des objectifs clairs et une forte cohesion', 'Une equipe competitive', 'Une equipe avec un manager directif', 'B', 'Les equipes hautement performantes combinent autonomie, competences, objectifs partages et confiance mutuelle.', 'Quiz Team Building', '0000-00-00 00:00:00', 12),
(33, 'Quelle bibliothèque est utilisée pour la manipulation de DataFrames en Python ?', 'NumPy', 'Matplotlib', 'Pandas', 'Scikit-learn', 'C', 'Pandas fournit la structure DataFrame idéale pour manipuler des données tabulaires.', 'Quiz Python Data Science', '0000-00-00 00:00:00', 13),
(34, 'Comment filtrer un DataFrame df pour garder les lignes où age > 30 ?', 'df.filter(age > 30)', 'df[df[\"age\"] > 30]', 'df.where(\"age > 30\")', 'df.select(age > 30)', 'B', 'Le filtrage booléen df[condition] est la syntaxe standard Pandas.', 'Quiz Python Data Science', '0000-00-00 00:00:00', 13),
(35, 'Quelle fonction Pandas lit un fichier CSV ?', 'pd.load_csv()', 'pd.read_csv()', 'pd.import_csv()', 'pd.open_csv()', 'B', 'pd.read_csv() est la fonction standard pour charger un fichier CSV.', 'Quiz Python Data Science', '0000-00-00 00:00:00', 13),
(36, 'np.array([1,2,3]) + 10 retourne :', 'Erreur', '[11]', '[11, 12, 13]', '[1, 2, 3, 10]', 'C', 'NumPy applique le broadcasting : chaque élément est additionné à 10.', 'Quiz Python Data Science', '0000-00-00 00:00:00', 13),
(37, 'Quelle méthode supprime les lignes avec des valeurs manquantes ?', 'df.remove_na()', 'df.clean()', 'df.dropna()', 'df.fillna()', 'C', 'df.dropna() supprime les lignes contenant des NaN. df.fillna() les remplace.', 'Quiz Python Data Science', '0000-00-00 00:00:00', 13),
(38, 'Quelle commande Git crée une nouvelle branche ET bascule dessus ?', 'git branch feature', 'git checkout feature', 'git checkout -b feature', 'git switch --create feature', 'C', 'git checkout -b crée et bascule en une seule commande. git switch -c est l\'alternative moderne.', 'Quiz Git & DevOps', '0000-00-00 00:00:00', 14),
(39, 'Que fait git rebase main ?', 'Fusionne main dans la branche courante', 'Rejoue les commits de la branche sur le sommet de main', 'Supprime main', 'Crée une branche main', 'B', 'Rebase déplace la base de la branche courante au sommet de main, créant un historique linéaire.', 'Quiz Git & DevOps', '0000-00-00 00:00:00', 14),
(40, 'Dans GitHub Actions, un workflow est déclenché par :', 'Un cron uniquement', 'Des events (push, pull_request, schedule...)', 'Un merge uniquement', 'Une commande manuelle uniquement', 'B', 'Les workflows peuvent être déclenchés par push, pull_request, schedule, workflow_dispatch et bien d\'autres événements.', 'Quiz Git & DevOps', '0000-00-00 00:00:00', 14),
(41, 'CI/CD signifie :', 'Code Integration / Code Deployment', 'Continuous Integration / Continuous Deployment', 'Complete Integration / Complete Delivery', 'Core Interface / Core Development', 'B', 'CI/CD = Continuous Integration (tests auto à chaque commit) / Continuous Deployment (livraison automatique).', 'Quiz Git & DevOps', '0000-00-00 00:00:00', 14),
(42, 'Combien de valeurs fondamentales contient le Manifeste Agile ?', '3', '4', '6', '12', 'B', 'Le Manifeste Agile (2001) contient 4 valeurs et 12 principes.', 'Quiz Agile & Scrum', '0000-00-00 00:00:00', 15),
(43, 'Qui priorise le Product Backlog dans Scrum ?', 'Scrum Master', 'L\'équipe de développement', 'Product Owner', 'Le manager', 'C', 'Le Product Owner est responsable du backlog et de la priorisation selon la valeur métier.', 'Quiz Agile & Scrum', '0000-00-00 00:00:00', 15),
(44, 'La rétrospective Scrum sert à :', 'Présenter le produit au client', 'S\'améliorer sur les processus et la collaboration', 'Planifier le prochain sprint', 'Démo des fonctionnalités', 'B', 'La rétrospective (fin de sprint) porte sur le \"comment on travaille\" pour s\'améliorer continuellement.', 'Quiz Agile & Scrum', '0000-00-00 00:00:00', 15),
(45, 'Un sprint se termine toujours par :', 'Un rapport PDF', 'Un incrément potentiellement livrable', 'Une présentation PowerPoint', 'Un compte rendu', 'B', 'L\'objectif de chaque sprint est de produire un incrément \"Done\" potentiellement livrable au client.', 'Quiz Agile & Scrum', '0000-00-00 00:00:00', 15),
(46, 'Amazon RDS supporte quel moteur de base de données ?', 'MongoDB uniquement', 'MySQL, PostgreSQL, Oracle, SQL Server', 'DynamoDB uniquement', 'Cassandra', 'B', 'RDS supporte 6 moteurs : MySQL, PostgreSQL, MariaDB, Oracle, SQL Server, et Amazon Aurora.', 'RDS et Bases de donnees managees', '0000-00-00 00:00:00', 2),
(47, 'Quelle est la différence entre RDS et DynamoDB ?', 'Aucune différence', 'RDS est relationnel (SQL), DynamoDB est NoSQL', 'DynamoDB est plus lent', 'RDS ne supporte pas les backups', 'B', 'RDS = bases relationnelles SQL. DynamoDB = base NoSQL clé-valeur/document hautement scalable.', 'RDS et Bases de donnees managees', '0000-00-00 00:00:00', 2),
(48, 'Selon la règle de Mehrabian, quel pourcentage représente le langage verbal dans la communication ?', '55%', '38%', '7%', '93%', 'C', '7% verbal (les mots), 38% vocal (voix, intonation), 55% visuel (langage corporel).', 'Quiz : Prise de parole en public', '0000-00-00 00:00:00', 8),
(49, 'La structure PREP pour répondre à une question signifie :', 'Prepare, Rehearse, Execute, Polish', 'Point, Reason, Example, Point', 'Present, Repeat, Engage, Pause', 'Plan, React, Evaluate, Perform', 'B', 'PREP : Point (votre position) + Reason (la raison) + Example (exemple concret) + Point (conclusion).', 'Quiz : Prise de parole en public', '0000-00-00 00:00:00', 8),
(50, 'Le \"hook\" dans un discours sert à :', 'Conclure le discours', 'Résumer les points clés', 'Accrocher l\'attention dès les premières secondes', 'Gérer les questions', 'C', 'Le hook (accroche) est la première phrase — question, statistique, anecdote — qui capte immédiatement l\'auditoire.', 'Quiz : Prise de parole en public', '0000-00-00 00:00:00', 8),
(51, 'Le trac avant de parler en public est :', 'Un signe de manque de préparation', 'Un problème psychologique', 'Normal et convertible en énergie positive', 'À éviter absolument', 'C', 'Le trac est une réponse physiologique normale. Les meilleurs orateurs le ressentent et le canalisent en énergie.', 'Quiz : Prise de parole en public', '0000-00-00 00:00:00', 8),
(52, 'La matrice d\'Eisenhower classe les tâches selon :', 'Durée et coût', 'Urgence et importance', 'Priorité et ressources', 'Facilité et impact', 'B', 'Eisenhower divise les tâches en 4 quadrants : Urgent+Important, Urgent+Non important, Non urgent+Important, Non urgent+Non important.', 'Quiz : Gestion du temps', '0000-00-00 00:00:00', 10),
(53, 'La règle de Pareto appliquée au travail indique que :', '80% du temps produit 80% des résultats', '20% des tâches génèrent 80% des résultats', '50% du travail donne 100% des résultats', 'Le travail doit être réparti également', 'B', 'Pareto (80/20) : 20% de vos actions génèrent 80% de vos résultats. Identifiez et protégez ces 20% à fort impact.', 'Quiz : Gestion du temps', '0000-00-00 00:00:00', 10),
(54, 'Le multitasking (faire plusieurs choses à la fois) :', 'Double la productivité', 'N\'a aucun impact', 'Réduit la productivité de 40% selon les études', 'Améliore la créativité', 'C', 'Des études (dont celles de l\'APA) montrent que le multitasking réduit la productivité jusqu\'à 40% en raison du coût cognitif du changement de contexte.', 'Quiz : Gestion du temps', '0000-00-00 00:00:00', 10),
(55, 'Le triangle CIA en cybersécurité signifie :', 'Cyber Intelligence Agency', 'Confidentialité, Intégrité, Disponibilité', 'Conformité, Innovation, Audit', 'Cryptage, Isolation, Authentification', 'B', 'CIA = Confidentiality (Confidentialité), Integrity (Intégrité), Availability (Disponibilité) — les 3 piliers de la sécurité de l\'information.', 'Quiz : Cybersécurité Fondamentaux', '0000-00-00 00:00:00', 11),
(56, 'Le phishing est une attaque qui exploite :', 'Les failles techniques des serveurs', 'La psychologie humaine pour obtenir des informations', 'Les vulnérabilités des bases de données', 'Le réseau Wi-Fi', 'B', 'Le phishing (et l\'ingénierie sociale en général) exploite la psychologie humaine : urgence, peur, curiosité, confiance.', 'Quiz : Cybersécurité Fondamentaux', '0000-00-00 00:00:00', 11),
(57, 'Le MFA (Multi-Factor Authentication) réduit le risque de compromission de :', '50%', '75%', '99,9%', '30%', 'C', 'Selon Microsoft, l\'activation du MFA bloque 99,9% des attaques automatisées sur les comptes.', 'Quiz : Cybersécurité Fondamentaux', '0000-00-00 00:00:00', 11),
(58, 'Quelle est la cause principale des incidents de cybersécurité ?', 'Les failles logicielles non patchées', 'Les attaques de force brute', 'L\'erreur humaine', 'Les vulnérabilités hardware', 'C', '95% des incidents de cybersécurité impliquent une erreur humaine (IBM, 2023) : clic sur lien phishing, mot de passe faible, mauvaise configuration.', 'Quiz : Cybersécurité Fondamentaux', '0000-00-00 00:00:00', 11),
(59, 'Un ransomware est :', 'Un antivirus puissant', 'Un outil de sauvegarde automatique', 'Un malware qui chiffre vos données et demande une rançon', 'Un pare-feu avancé', 'C', 'Le ransomware chiffre les fichiers de la victime et exige une rançon (souvent en crypto) pour fournir la clé de déchiffrement.', 'Quiz : Cybersécurité Fondamentaux', '0000-00-00 00:00:00', 11),
(60, 'What is Docker primarily used for?', 'Managing physical servers', 'Containerizing applications and their dependencies', 'Building databases', 'Monitoring network traffic', 'B', 'Easy - Docker packages apps into portable containers.', 'Docker Basics', '2026-04-19 16:23:38', 9),
(61, 'What is the smallest deployable unit in Kubernetes?', 'Container', 'Pod', 'Node', 'Service', 'B', 'Easy - A Pod is the fundamental unit in Kubernetes.', 'Kubernetes Fundamentals', '2026-04-19 16:23:38', 9),
(62, 'Which command builds a Docker image from a Dockerfile?', 'docker run', 'docker build', 'docker pull', 'docker push', 'B', 'Easy - docker build reads the Dockerfile and creates the image.', 'Docker Commands', '2026-04-19 16:23:38', 9),
(63, 'What does the command \"kubectl get pods\" do?', 'Deletes pods', 'Lists all pods in the current namespace', 'Creates a new pod', 'Restarts the cluster', 'B', 'Easy - It displays the status of pods.', 'Kubernetes Commands', '2026-04-19 16:23:38', 9),
(64, 'What is a Docker image?', 'A running container', 'A read-only template used to launch containers', 'A Kubernetes deployment', 'A network policy', 'B', 'Easy - Images are templates for creating containers.', 'Docker', '2026-04-19 16:23:38', 9),
(65, 'What is the purpose of a Dockerfile?', 'To run containers', 'To provide instructions to build a Docker image', 'To store logs', 'To manage Kubernetes resources', 'B', 'Easy - It contains the build steps for an image.', 'Docker', '2026-04-19 16:23:38', 9),
(66, 'What does \"kubectl apply\" do?', 'Deletes resources', 'Creates or updates resources declaratively', 'Only views resources', 'Restarts all pods', 'B', 'Medium - It applies configuration files to the cluster.', 'Kubernetes', '2026-04-19 16:23:38', 9),
(67, 'What is a Kubernetes Service used for?', 'Storing persistent data', 'Providing stable networking and load balancing to Pods', 'Scheduling Pods on nodes', 'Monitoring cluster health', 'B', 'Medium - Services give Pods a stable endpoint.', 'Kubernetes Networking', '2026-04-19 16:23:38', 9),
(68, 'What is a Kubernetes Deployment?', 'A one-time job', 'A way to manage Pods and ReplicaSets declaratively', 'A storage volume', 'A network policy', 'B', 'Medium - Deployments handle scaling, updates, and rollbacks.', 'Kubernetes Advanced', '2026-04-19 16:23:38', 9),
(69, 'How does Docker differ from a traditional virtual machine?', 'Docker uses more resources', 'Docker virtualizes at the OS level and is lighter and faster', 'Docker cannot run Linux applications', 'Docker always requires a hypervisor', 'B', 'Medium - Containers share the host OS kernel.', 'Docker vs VM', '2026-04-19 16:23:38', 9),
(70, 'What is a Kubernetes Namespace used for?', 'Storing secrets only', 'Dividing cluster resources into virtual sub-clusters', 'Running background jobs', 'Exposing applications to the internet', 'B', 'Medium - Namespaces support multi-tenancy and organization.', 'Kubernetes', '2026-04-19 16:23:38', 9),
(71, 'What is Docker Compose mainly used for?', 'Building single images', 'Defining and running multi-container applications', 'Pushing images to registries', 'Monitoring containers', 'B', 'Medium - It simplifies managing multiple services.', 'Docker', '2026-04-19 16:23:38', 9),
(72, 'What command shows detailed information about a Pod?', 'kubectl get pods', 'kubectl describe pod <name>', 'kubectl logs <name>', 'kubectl delete pod <name>', 'B', 'Medium - describe gives rich details.', 'Kubernetes Troubleshooting', '2026-04-19 16:23:38', 9),
(73, 'What is the role of the kubelet?', 'Manages the control plane', 'Runs on each node and ensures containers in Pods are running', 'Stores cluster state', 'Handles API requests', 'B', 'Medium - kubelet is the node agent.', 'Kubernetes Architecture', '2026-04-19 16:23:38', 9),
(74, 'What is a ReplicaSet in Kubernetes?', 'A one-time task', 'Ensures a specified number of Pod replicas are running', 'A load balancer', 'A storage class', 'B', 'Medium - It maintains the desired number of pods.', 'Kubernetes', '2026-04-19 16:23:38', 9),
(75, 'What does a Docker volume provide?', 'Temporary storage inside the container', 'Persistent storage that survives container restarts', 'Network routing', 'Image caching', 'B', 'Medium - Volumes decouple data from the container lifecycle.', 'Docker Storage', '2026-04-19 16:23:38', 9),
(76, 'What is Helm in the context of Kubernetes?', 'A container runtime', 'A package manager for Kubernetes', 'A logging tool', 'A monitoring dashboard', 'B', 'Medium - Helm helps manage complex Kubernetes applications.', 'Kubernetes Tools', '2026-04-19 16:23:38', 9),
(77, 'What is the difference between a Docker container and an image?', 'They are the same', 'An image is a template; a container is a running instance of that image', 'A container is a template; an image is running', 'Images cannot be shared', 'B', 'Medium - Clear distinction between static and running state.', 'Docker', '2026-04-19 16:23:38', 9),
(78, 'What happens when you run \"docker run\" ?', 'It only builds the image', 'It creates and starts a new container from an image', 'It deletes old containers', 'It pushes the image to a registry', 'B', 'Easy - docker run launches containers.', 'Docker Commands', '2026-04-19 16:23:38', 9),
(79, 'What is a ConfigMap in Kubernetes?', 'A secret storage', 'A way to store non-confidential configuration data', 'A deployment template', 'A network rule', 'B', 'Medium - ConfigMaps separate configuration from Pods.', 'Kubernetes Configuration', '2026-04-19 16:23:38', 9),
(80, 'What is the purpose of Kubernetes Ingress?', 'Internal service discovery', 'Managing external access to services, typically HTTP', 'Storing persistent volumes', 'Scheduling nodes', 'B', 'Medium - Ingress provides routing rules.', 'Kubernetes Networking', '2026-04-19 16:23:38', 9),
(81, 'What is a liveness probe in Kubernetes?', 'Checks if a Pod is ready to receive traffic', 'Checks if the container is still alive; restarts if failed', 'Monitors CPU usage', 'Checks storage space', 'B', 'Hard - Liveness probes detect dead containers.', 'Kubernetes Health Checks', '2026-04-19 16:23:38', 9),
(82, 'What does \"docker push\" do?', 'Pulls an image from a registry', 'Pushes a local image to a remote registry', 'Builds an image', 'Runs a container', 'B', 'Easy - It uploads images to Docker Hub or private registries.', 'Docker Registry', '2026-04-19 16:23:38', 9),
(83, 'What is a DaemonSet in Kubernetes?', 'Runs one Pod per node', 'Ensures a copy of a Pod runs on all (or selected) nodes', 'Manages batch jobs', 'Handles stateful applications', 'B', 'Medium - DaemonSets are useful for agents like logging.', 'Kubernetes Controllers', '2026-04-19 16:23:38', 9),
(84, 'What is the control plane in Kubernetes?', 'The worker nodes', 'The management layer responsible for cluster decisions', 'A single Pod', 'A storage backend', 'B', 'Medium - It includes API server, scheduler, controller manager, etc.', 'Kubernetes Architecture', '2026-04-19 16:23:38', 9),
(85, 'What is a StatefulSet used for?', 'Stateless applications', 'Managing stateful applications with stable identities and storage', 'One-time jobs', 'External load balancing', 'B', 'Hard - StatefulSets guarantee ordering and uniqueness.', 'Kubernetes Advanced', '2026-04-19 16:23:38', 9),
(86, 'How can you view logs of a container in a Pod?', 'docker logs', 'kubectl logs <pod-name>', 'kubectl describe pod', 'kubectl get logs', 'B', 'Medium - kubectl logs is the standard way.', 'Kubernetes Troubleshooting', '2026-04-19 16:23:38', 9),
(87, 'What is multi-stage build in Docker?', 'Building multiple images at once', 'Using multiple FROM statements to create a smaller final image', 'Running multiple containers', 'Pushing to multiple registries', 'B', 'Hard - It reduces image size by discarding build tools.', 'Docker Advanced', '2026-04-19 16:23:38', 9),
(88, 'What is a Node in Kubernetes?', 'A Pod', 'A physical or virtual machine that runs Pods', 'A Service', 'A Namespace', 'B', 'Easy - Nodes are the workers in the cluster.', 'Kubernetes Architecture', '2026-04-19 16:23:38', 9),
(89, 'What does \"kubectl scale\" do?', 'Changes the number of replicas in a Deployment or ReplicaSet', 'Scales the cluster nodes', 'Scales storage volumes', 'Scales network bandwidth', 'A', 'Medium - It adjusts the desired replica count.', 'Kubernetes Scaling', '2026-04-19 16:23:38', 9),
(90, 'What is a Secret in Kubernetes?', 'Public configuration', 'A way to store sensitive information like passwords and keys', 'A deployment manifest', 'A logging configuration', 'B', 'Medium - Secrets are base64-encoded and more secure than ConfigMaps.', 'Kubernetes Security', '2026-04-19 16:23:38', 9),
(91, 'What command removes stopped Docker containers?', 'docker rm', 'docker container prune', 'docker rmi', 'docker stop', 'B', 'Easy - prune cleans up unused containers.', 'Docker Maintenance', '2026-04-19 16:23:38', 9),
(92, 'What is a Job in Kubernetes?', 'A long-running service', 'A batch task that runs to completion', 'A cron schedule', 'A persistent volume', 'B', 'Medium - Jobs are for finite tasks.', 'Kubernetes Controllers', '2026-04-19 16:23:38', 9),
(93, 'What is the default restart policy for a Pod?', 'Never', 'Always', 'OnFailure', 'UnlessStopped', 'B', 'Medium - Pods restart on failure by default in most controllers.', 'Kubernetes', '2026-04-19 16:23:38', 9),
(94, 'What does the etcd component store in Kubernetes?', 'Container images', 'All cluster data and state', 'Application logs', 'User passwords', 'B', 'Hard - etcd is the consistent key-value store for cluster state.', 'Kubernetes Architecture', '2026-04-19 16:23:38', 9),
(95, 'What is a rolling update in Kubernetes Deployments?', 'Immediate replacement of all Pods', 'Gradual replacement of old Pods with new ones', 'Deleting and recreating the Deployment', 'Scaling down to zero', 'B', 'Medium - It minimizes downtime during updates.', 'Kubernetes Deployments', '2026-04-19 16:23:38', 9),
(96, 'What is the purpose of Dockerignore file?', 'To ignore certain files when building the image (reduces context size)', 'To ignore running containers', 'To ignore logs', 'To ignore networks', 'A', 'Medium - It speeds up builds by excluding unnecessary files.', 'Docker', '2026-04-19 16:23:38', 9),
(97, 'What is a Horizontal Pod Autoscaler (HPA)?', 'Manually scales nodes', 'Automatically scales the number of Pods based on CPU/memory', 'Scales storage', 'Scales namespaces', 'B', 'Hard - HPA reacts to observed metrics.', 'Kubernetes Autoscaling', '2026-04-19 16:23:38', 9),
(98, 'What command enters a running Docker container?', 'docker run -it', 'docker exec -it <container> /bin/sh', 'docker attach', 'docker start', 'B', 'Medium - exec is the modern way to enter containers.', 'Docker', '2026-04-19 16:23:38', 9),
(99, 'What is a taint in Kubernetes?', 'A label on Pods', 'A mark on nodes that repels Pods unless they tolerate it', 'A storage class', 'A network policy', 'B', 'Hard - Taints control Pod scheduling.', 'Kubernetes Scheduling', '2026-04-19 16:23:38', 9),
(100, 'What is the difference between docker stop and docker kill?', 'stop is graceful; kill is immediate', 'kill is graceful; stop is immediate', 'They do the same', 'stop only works on images', 'A', 'Medium - stop sends SIGTERM first.', 'Docker', '2026-04-19 16:23:38', 9),
(101, 'What is a CronJob in Kubernetes?', 'A one-time job', 'A Job that runs on a repeating schedule', 'A continuous Deployment', 'A Service', 'B', 'Medium - CronJobs are for scheduled tasks.', 'Kubernetes Controllers', '2026-04-19 16:23:38', 9),
(102, 'What does \"docker tag\" allow you to do?', 'Rename or add another tag to an existing image', 'Build a new image', 'Push to registry', 'Run a container', 'A', 'Medium - Useful before pushing to registries.', 'Docker Registry', '2026-04-19 16:23:38', 9),
(103, 'What is a Pod Disruption Budget (PDB)?', 'Limits how many Pods can be disrupted during voluntary disruptions', 'A budget for storage usage', 'A CPU limit', 'A network bandwidth limit', 'A', 'Hard - PDB protects application availability.', 'Kubernetes Advanced', '2026-04-19 16:23:38', 9),
(104, 'What is the role of the Kubernetes Scheduler?', 'Runs containers', 'Assigns Pods to suitable nodes', 'Stores state', 'Handles API requests', 'B', 'Medium - It makes scheduling decisions.', 'Kubernetes Architecture', '2026-04-19 16:23:38', 9),
(105, 'What is a headless Service in Kubernetes?', 'A Service without a cluster IP', 'A Service with load balancing disabled', 'A Service for external traffic only', 'A deprecated Service type', 'A', 'Hard - Headless Services return individual Pod IPs.', 'Kubernetes Networking', '2026-04-19 16:23:38', 9),
(106, 'What does the --rm flag do with docker run?', 'Removes the container automatically when it exits', 'Runs in read-only mode', 'Mounts a volume', 'Runs in detached mode', 'A', 'Medium - Useful for temporary containers.', 'Docker', '2026-04-19 16:23:38', 9),
(107, 'What is a mutating admission webhook in Kubernetes?', 'A validation check', 'A webhook that can modify resources before they are persisted', 'A logging plugin', 'A monitoring tool', 'B', 'Hard - It allows dynamic modification of objects.', 'Kubernetes Security', '2026-04-19 16:23:38', 9),
(108, 'What is the recommended way to expose a Kubernetes application to the internet?', 'Directly via Pod IP', 'Using a LoadBalancer or Ingress Service', 'Using a NodePort only', 'Using a ClusterIP', 'B', 'Medium - Ingress or LoadBalancer is preferred for external access.', 'Kubernetes Networking', '2026-04-19 16:23:38', 9),
(109, 'What is containerd?', 'A Docker CLI tool', 'A lightweight container runtime used by Kubernetes', 'A Kubernetes controller', 'A networking plugin', 'B', 'Medium - It is a common CRI-compliant runtime.', 'Kubernetes Runtime', '2026-04-19 16:23:38', 9),
(111, 'Which AWS service provides object storage with 99.999999999% durability?', 'Amazon EC2', 'Amazon S3', 'Amazon RDS', 'AWS Lambda', 'B', 'Easy - S3 is designed for scalable and durable object storage.', 'Storage Services', '2026-04-19 16:47:50', 2),
(112, 'What does Amazon EC2 stand for?', 'Elastic Container Service', 'Elastic Compute Cloud', 'Elastic Cache', 'Elastic Database', 'B', 'Easy - EC2 provides resizable compute capacity in the cloud.', 'Compute Services', '2026-04-19 16:47:50', 2),
(113, 'Which component of AWS Global Infrastructure represents isolated data centers?', 'Regions', 'Availability Zones', 'Edge Locations', 'Local Zones', 'B', 'Medium - AZs provide high availability and fault tolerance.', 'Global Infrastructure', '2026-04-19 16:47:50', 2),
(114, 'What is the AWS Shared Responsibility Model?', 'AWS handles all security', 'AWS secures the cloud; customer secures data and applications in the cloud', 'Customer handles everything', 'Only applies to on-premises', 'B', 'Medium - Responsibilities vary by service type.', 'Security & Compliance', '2026-04-19 16:47:50', 2),
(115, 'Which service is used for serverless compute?', 'Amazon EC2', 'AWS Lambda', 'Amazon ECS', 'Amazon EKS', 'B', 'Easy - Lambda runs code without managing servers.', 'Serverless', '2026-04-19 16:47:50', 2),
(116, 'What does IAM stand for in AWS?', 'Identity and Access Management', 'Infrastructure Access Model', 'Internal Authentication Module', 'Integrated Account Manager', 'A', 'Easy - IAM controls who can access what.', 'Identity & Access', '2026-04-19 16:47:50', 2),
(117, 'Which AWS service provides managed relational databases?', 'Amazon S3', 'Amazon RDS', 'Amazon DynamoDB', 'Amazon Redshift', 'B', 'Medium - RDS supports MySQL, PostgreSQL, etc.', 'Database Services', '2026-04-19 16:47:50', 2),
(118, 'What is the purpose of Amazon VPC?', 'Object storage', 'Isolated virtual network in the AWS cloud', 'Serverless functions', 'Content delivery', 'B', 'Medium - VPC enables secure networking.', 'Networking', '2026-04-19 16:47:50', 2),
(119, 'Which pillar of the AWS Well-Architected Framework focuses on protecting information and systems?', 'Operational Excellence', 'Security', 'Cost Optimization', 'Sustainability', 'B', 'Medium - Security is a core pillar.', 'Well-Architected Framework', '2026-04-19 16:47:50', 2),
(120, 'What is Amazon CloudFront?', 'A database service', 'A Content Delivery Network (CDN)', 'A compute service', 'A monitoring tool', 'B', 'Medium - CloudFront speeds up content delivery.', 'Networking & Delivery', '2026-04-19 16:47:50', 2),
(121, 'Which service allows you to monitor AWS resources and applications?', 'AWS CloudTrail', 'Amazon CloudWatch', 'AWS Config', 'Amazon GuardDuty', 'B', 'Medium - CloudWatch provides metrics, logs, and alarms.', 'Monitoring', '2026-04-19 16:47:50', 2),
(122, 'What does AWS CloudTrail primarily record?', 'API calls and account activity', 'Resource configuration changes', 'Application performance', 'Billing details', 'A', 'Medium - Useful for governance and compliance.', 'Security & Logging', '2026-04-19 16:47:50', 2),
(123, 'Which pricing model offers significant discounts for committed usage?', 'On-Demand', 'Reserved Instances', 'Spot Instances', 'Savings Plans', 'B', 'Medium - Reserved Instances provide cost savings for predictable workloads.', 'Billing & Pricing', '2026-04-19 16:47:50', 2),
(124, 'What is the benefit of AWS Auto Scaling?', 'Fixed instance count', 'Automatically adjusts capacity based on demand', 'Only works with Lambda', 'Increases costs', 'B', 'Easy - Maintains performance while optimizing costs.', 'Compute', '2026-04-19 16:47:50', 2),
(125, 'Which service is best for NoSQL key-value database?', 'Amazon RDS', 'Amazon DynamoDB', 'Amazon Aurora', 'Amazon Neptune', 'B', 'Medium - DynamoDB offers single-digit millisecond latency.', 'Database Services', '2026-04-19 16:47:50', 2),
(126, 'What is AWS Organizations used for?', 'Single account management', 'Centralized management of multiple AWS accounts', 'Database migration', 'Application deployment', 'B', 'Medium - Enables consolidated billing and policies.', 'Management & Governance', '2026-04-19 16:47:50', 2),
(127, 'Which service helps with data backup and recovery?', 'Amazon S3 Glacier', 'AWS Backup', 'Amazon EBS', 'Amazon EFS', 'B', 'Medium - Centralized backup across AWS services.', 'Storage', '2026-04-19 16:47:50', 2),
(128, 'What does the AWS Free Tier provide?', 'Unlimited free usage forever', 'Free usage of popular services for the first 12 months', 'Only for enterprise customers', 'Free hardware', 'B', 'Easy - Helps new users explore AWS.', 'Billing & Pricing', '2026-04-19 16:47:50', 2),
(129, 'Which service is used for container orchestration managed by AWS?', 'Amazon EC2', 'Amazon ECS or EKS', 'AWS Lambda', 'Amazon Lightsail', 'B', 'Medium - ECS is simpler; EKS is Kubernetes-based.', 'Containers', '2026-04-19 16:47:50', 2),
(130, 'What is the purpose of AWS Trusted Advisor?', 'Only billing analysis', 'Provides best practice recommendations for cost, security, and performance', 'Application deployment', 'Database optimization', 'B', 'Hard - Helps optimize AWS environment.', 'Management Tools', '2026-04-19 16:47:50', 2),
(131, 'Which AWS service detects threats and malicious activity?', 'Amazon Inspector', 'Amazon GuardDuty', 'AWS WAF', 'AWS Shield', 'B', 'Medium - Intelligent threat detection using ML.', 'Security', '2026-04-19 16:47:50', 2),
(132, 'What is Amazon Route 53?', 'A storage service', 'A scalable DNS web service', 'A compute instance', 'A monitoring tool', 'B', 'Medium - Used for domain registration and routing.', 'Networking', '2026-04-19 16:47:50', 2),
(133, 'In the Well-Architected Framework, what does Reliability focus on?', 'Cost reduction', 'Recovering from failures and meeting demand', 'User interface design', 'Marketing strategies', 'B', 'Medium - Includes testing and automation.', 'Well-Architected Framework', '2026-04-19 16:47:50', 2),
(134, 'What is the difference between Amazon EBS and Amazon EFS?', 'EBS is for single instance; EFS is for multiple instances', 'Both are the same', 'EBS is object storage', 'EFS is block storage only', 'A', 'Hard - EBS for EC2; EFS for shared file storage.', 'Storage Services', '2026-04-19 16:47:50', 2),
(135, 'Which service enables infrastructure as code?', 'AWS CloudFormation', 'AWS Lambda', 'Amazon S3', 'Amazon EC2', 'A', 'Medium - Declarative templates for provisioning.', 'Management & Governance', '2026-04-19 16:47:50', 2),
(136, 'What does AWS Artifact provide?', 'Compute resources', 'Compliance reports and agreements', 'Database queries', 'Content delivery', 'B', 'Medium - Helps with audits and compliance.', 'Security & Compliance', '2026-04-19 16:47:50', 2),
(137, 'Which pricing model is best for unpredictable workloads?', 'Reserved Instances', 'On-Demand Instances', 'Spot Instances', 'Dedicated Hosts', 'B', 'Medium - Pay per second/hour without commitment.', 'Billing & Pricing', '2026-04-19 16:47:50', 2),
(138, 'What is the role of AWS Support plans?', 'Only for billing questions', 'Provide technical support levels from Basic to Enterprise', 'Only for new accounts', 'Replace training', 'B', 'Easy - Different response times and features.', 'Support & Billing', '2026-04-19 16:47:50', 2),
(139, 'Amazon SageMaker is primarily used for?', 'Web hosting', 'Building, training, and deploying machine learning models', 'Database management', 'Network security', 'B', 'Medium - End-to-end ML platform.', 'Machine Learning', '2026-04-19 16:47:50', 2),
(140, 'What is AWS Snowball used for?', 'Real-time data streaming', 'Offline data transfer of large amounts of data', 'Serverless compute', 'Monitoring', 'B', 'Medium - Physical device for petabyte-scale migration.', 'Migration', '2026-04-19 16:47:50', 2),
(141, 'Which service protects web applications from common exploits?', 'AWS Shield', 'AWS WAF', 'Amazon GuardDuty', 'AWS IAM', 'B', 'Medium - Web Application Firewall.', 'Security', '2026-04-19 16:47:50', 2),
(142, 'What is the benefit of AWS Regions?', 'Lower latency only', 'Compliance with data residency laws and fault isolation', 'Higher costs', 'Limited services', 'B', 'Medium - Global infrastructure advantage.', 'Global Infrastructure', '2026-04-19 16:47:50', 2),
(143, 'AWS Budgets is used for?', 'Creating EC2 instances', 'Setting custom cost and usage budgets with alerts', 'Database queries', 'Application deployment', 'B', 'Easy - Cost management tool.', 'Billing & Pricing', '2026-04-19 16:47:50', 2),
(144, 'What does Amazon Aurora offer compared to standard RDS?', 'Higher performance and MySQL/PostgreSQL compatibility', 'Only NoSQL support', 'Object storage', 'Serverless compute', 'A', 'Hard - Enterprise-grade relational database.', 'Database Services', '2026-04-19 16:47:50', 2),
(145, 'Which service provides managed Kubernetes?', 'Amazon ECS', 'Amazon EKS', 'AWS Fargate', 'Amazon Lightsail', 'B', 'Medium - Fully managed Kubernetes service.', 'Containers', '2026-04-19 16:47:50', 2),
(146, 'What is the AWS Well-Architected Framework pillar for running workloads efficiently?', 'Security', 'Performance Efficiency', 'Reliability', 'Operational Excellence', 'B', 'Medium - Includes selection of resources and monitoring.', 'Well-Architected Framework', '2026-04-19 16:47:50', 2),
(147, 'AWS Config is used to?', 'Run code', 'Assess, audit, and evaluate resource configurations', 'Store files', 'Deliver content', 'B', 'Hard - Tracks compliance over time.', 'Management & Governance', '2026-04-19 16:47:50', 2),
(148, 'What is the purpose of AWS Direct Connect?', 'Public internet access', 'Dedicated private network connection to AWS', 'Serverless functions', 'Monitoring only', 'B', 'Medium - Reduces latency and increases bandwidth.', 'Networking', '2026-04-19 16:47:50', 2),
(149, 'Which service helps with cost optimization recommendations?', 'AWS Cost Explorer', 'AWS Trusted Advisor', 'Amazon CloudWatch', 'AWS Organizations', 'B', 'Medium - Checks for underutilized resources.', 'Billing & Pricing', '2026-04-19 16:47:50', 2),
(150, 'What is Amazon Rekognition used for?', 'Text translation', 'Image and video analysis using ML', 'Database backup', 'Network routing', 'B', 'Medium - Computer vision service.', 'Machine Learning', '2026-04-19 16:47:50', 2),
(151, 'In AWS, what does high availability mean?', 'System continues operating despite failures', 'Lowest possible cost', 'Maximum security only', 'Single region deployment', 'A', 'Easy - Achieved through multiple AZs.', 'Cloud Concepts', '2026-04-19 16:47:50', 2),
(152, 'What is the primary goal of Spring Boot?', 'To replace the Spring Framework', 'To simplify Spring application development with auto-configuration', 'To only support web applications', 'To remove dependency injection', 'B', 'Easy - Reduces boilerplate and configuration.', 'Spring Boot Basics', '2026-04-19 16:48:10', 5),
(153, 'Which annotation combines @Configuration, @EnableAutoConfiguration, and @ComponentScan?', '@SpringBootTest', '@SpringBootApplication', '@RestController', '@Service', 'B', 'Easy - Entry point for most Spring Boot apps.', 'Core Annotations', '2026-04-19 16:48:10', 5),
(154, 'What does @EnableAutoConfiguration do?', 'Disables all auto-configuration', 'Automatically configures beans based on classpath', 'Only scans components', 'Enables security by default', 'B', 'Medium - Core feature of Spring Boot.', 'Auto-Configuration', '2026-04-19 16:48:10', 5),
(155, 'How do you run a Spring Boot application as a standalone JAR?', 'Deploy to external Tomcat', 'java -jar application.jar', 'mvn spring-boot:run only', 'Requires WAR packaging', 'B', 'Easy - Embedded server support.', 'Deployment', '2026-04-19 16:48:10', 5),
(156, 'Which annotation is used to create a RESTful controller?', '@Controller', '@RestController', '@Service', '@Repository', 'B', 'Easy - Combines @Controller and @ResponseBody.', 'Web Layer', '2026-04-19 16:48:10', 5),
(157, 'What is the default embedded server in Spring Boot?', 'Jetty', 'Tomcat', 'Undertow', 'GlassFish', 'B', 'Medium - Can be changed easily.', 'Web Development', '2026-04-19 16:48:10', 5),
(158, 'How does Spring Boot handle application.properties?', 'Ignored by default', 'Externalized configuration with multiple sources', 'Only for testing', 'Must be in XML format', 'B', 'Medium - Supports YAML too.', 'Configuration', '2026-04-19 16:48:10', 5),
(159, 'What is Spring Boot Actuator used for?', 'Only logging', 'Production-ready features like health and metrics endpoints', 'UI development', 'Database migrations', 'B', 'Medium - Helps monitor and manage applications.', 'Monitoring', '2026-04-19 16:48:10', 5),
(160, 'Which annotation injects dependencies?', '@Inject', '@Autowired', '@Qualifier only', '@Bean', 'B', 'Easy - Core of Spring DI.', 'Dependency Injection', '2026-04-19 16:48:10', 5),
(161, 'What is @SpringBootTest used for?', 'Production deployment', 'Integration testing with full application context', 'Unit testing only', 'Performance testing', 'B', 'Medium - Loads complete Spring context.', 'Testing', '2026-04-19 16:48:10', 5),
(162, 'How can you exclude auto-configuration in Spring Boot?', 'Using @ExcludeAutoConfiguration', 'Using spring.autoconfigure.exclude property or @SpringBootApplication(exclude=...)', 'Deleting the starter', 'Not possible', 'B', 'Hard - Fine control over auto-config.', 'Auto-Configuration', '2026-04-19 16:48:10', 5),
(163, 'What does @Profile annotation allow?', 'Multiple environments (dev, prod, test)', 'Only testing', 'Security profiles', 'Database profiles only', 'A', 'Medium - Conditional bean registration.', 'Configuration', '2026-04-19 16:48:10', 5),
(164, 'Which starter dependency is used for web applications?', 'spring-boot-starter-data-jpa', 'spring-boot-starter-web', 'spring-boot-starter-test', 'spring-boot-starter-actuator', 'B', 'Easy - Includes Tomcat and Spring MVC.', 'Starters', '2026-04-19 16:48:10', 5),
(165, 'What is the purpose of CommandLineRunner?', 'To run code after application context is loaded', 'To define beans', 'To configure security', 'To start the server', 'A', 'Medium - Useful for initialization.', 'Application Lifecycle', '2026-04-19 16:48:10', 5),
(166, 'How does Spring Boot support JPA?', 'Requires manual Hibernate config', 'spring-boot-starter-data-jpa provides auto-configuration', 'Only for MongoDB', 'No support', 'B', 'Medium - Simplifies ORM setup.', 'Data Access', '2026-04-19 16:48:10', 5),
(167, 'What is @Value used for?', 'Injecting property values', 'Creating controllers', 'Defining services', 'Mapping requests', 'A', 'Easy - Property injection.', 'Configuration', '2026-04-19 16:48:10', 5),
(168, 'Which annotation marks a class as a service layer component?', '@Controller', '@Service', '@Repository', '@Component', 'B', 'Easy - Semantic stereotype.', 'Layered Architecture', '2026-04-19 16:48:10', 5),
(169, 'What is Spring Boot DevTools?', 'Production monitoring tool', 'Development utilities like auto-restart', 'Database tool', 'Security module', 'B', 'Medium - Improves developer experience.', 'Development Tools', '2026-04-19 16:48:10', 5),
(170, 'How do you secure a Spring Boot application?', 'Only with @EnableWebSecurity', 'Using spring-boot-starter-security and configurations', 'No built-in support', 'External firewall only', 'B', 'Medium - Integrates with Spring Security.', 'Security', '2026-04-19 16:48:10', 5),
(171, 'What is the role of @ConfigurationProperties?', 'Binding external properties to POJO', 'Defining REST endpoints', 'Creating beans', 'Testing only', 'A', 'Hard - Type-safe configuration.', 'Configuration', '2026-04-19 16:48:10', 5),
(172, 'Which embedded server can replace Tomcat?', 'Only Jetty via starter', 'Jetty or Undertow by excluding Tomcat and adding the desired one', 'GlassFish', 'Not possible', 'B', 'Hard - Flexible server choice.', 'Web Layer', '2026-04-19 16:48:10', 5),
(173, 'What does @RequestMapping do?', 'Handles HTTP requests', 'Defines database mappings', 'Injects properties', 'Creates profiles', 'A', 'Easy - Base for @GetMapping etc.', 'Web Layer', '2026-04-19 16:48:10', 5),
(174, 'How does Spring Boot handle validation?', 'No built-in support', 'Using @Valid and Bean Validation (Hibernate Validator)', 'Only manual checks', 'External library only', 'B', 'Medium - Integrates with Jakarta Validation.', 'Web Layer', '2026-04-19 16:48:10', 5),
(175, 'What is the purpose of @ExceptionHandler?', 'Global exception handling in controllers', 'Defining beans', 'Auto-configuration', 'Testing', 'A', 'Medium - Clean error handling.', 'Web Layer', '2026-04-19 16:48:10', 5),
(176, 'Spring Data JPA simplifies?', 'Only REST development', 'Repository pattern with CRUD operations', 'Security configuration', 'UI rendering', 'B', 'Medium - Reduces boilerplate for data access.', 'Data Access', '2026-04-19 16:48:10', 5),
(177, 'What is @Transactional used for?', 'Declarative transaction management', 'Defining controllers', 'Auto-configuration', 'Testing', 'A', 'Medium - Ensures ACID properties.', 'Data Access', '2026-04-19 16:48:10', 5),
(178, 'How can you customize the banner in Spring Boot?', 'Not possible', 'By placing banner.txt in resources', 'Only via properties', 'Using @Bean', 'B', 'Easy - Fun customization.', 'Application Customization', '2026-04-19 16:48:10', 5),
(179, 'What does spring-boot-starter-test include?', 'Only JUnit', 'JUnit, Mockito, AssertJ, Spring Test', 'Only integration tests', 'No testing support', 'B', 'Medium - Comprehensive testing stack.', 'Testing', '2026-04-19 16:48:10', 5),
(180, 'Which annotation is used for scheduling tasks?', '@Scheduled', '@Async', '@EnableScheduling on config class', 'Both A and C', 'D', 'Hard - Requires enabling.', 'Advanced Features', '2026-04-19 16:48:10', 5),
(181, 'What is the difference between @Component and @Bean?', 'Same thing', '@Component for class scanning; @Bean for method-level in @Configuration', '@Bean is deprecated', 'Only for testing', 'B', 'Hard - Different registration methods.', 'Core Concepts', '2026-04-19 16:48:10', 5);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(182, 'How does Spring Boot support reactive programming?', 'No support', 'Via spring-boot-starter-webflux', 'Only with JPA', 'External library only', 'B', 'Hard - Non-blocking with WebFlux.', 'Advanced', '2026-04-19 16:48:10', 5),
(183, 'What is the purpose of @ConditionalOnProperty?', 'Always loads beans', 'Conditional bean creation based on properties', 'Security only', 'Testing only', 'B', 'Hard - Advanced auto-configuration.', 'Auto-Configuration', '2026-04-19 16:48:10', 5),
(184, 'Spring Boot uses which build tools by default?', 'Only Maven', 'Maven and Gradle support', 'Only Ant', 'No build tool needed', 'B', 'Easy - Official starters for both.', 'Project Setup', '2026-04-19 16:48:10', 5),
(185, 'What does @ControllerAdvice provide?', 'Global exception handling and model attributes', 'Only for one controller', 'Database access', 'Security', 'A', 'Medium - Cross-cutting concerns.', 'Web Layer', '2026-04-19 16:48:10', 5),
(186, 'How do you externalize configuration in production?', 'Hardcode values', 'Using environment variables, config server, or profiles', 'Only application.properties', 'Not recommended', 'B', 'Medium - 12-factor app principles.', 'Configuration', '2026-04-19 16:48:10', 5),
(187, 'What is Spring Boot CLI?', 'Command line tool for Groovy-based rapid prototyping', 'Only for deployment', 'Database tool', 'Security scanner', 'A', 'Medium - Quick development.', 'Tools', '2026-04-19 16:48:10', 5),
(188, 'Which annotation enables async methods?', '@Async', '@EnableAsync on config', 'Both A and B required', 'Only @Scheduled', 'C', 'Hard - Proper setup needed.', 'Advanced Features', '2026-04-19 16:48:10', 5),
(189, 'What is the role of ApplicationContext in Spring Boot?', 'Only for web', 'Central interface for bean management', 'Database connection only', 'Testing only', 'B', 'Medium - Core container.', 'Core Concepts', '2026-04-19 16:48:10', 5),
(190, 'Spring Boot 3+ requires which Java version minimum?', 'Java 8', 'Java 17', 'Java 11', 'Java 21', 'B', 'Medium - Check release notes.', 'Versioning', '2026-04-19 16:48:10', 5),
(191, 'How does Spring Boot integrate with Micrometer?', 'No integration', 'For metrics and observability (Actuator)', 'Only logging', 'Database only', 'B', 'Hard - Prometheus, Grafana friendly.', 'Monitoring', '2026-04-19 16:48:10', 5),
(192, 'What is @RequestBody used for?', 'Binding request parameters', 'Deserializing JSON/XML to object in POST/PUT', 'Returning responses', 'Path variables', 'B', 'Easy - Common in REST.', 'Web Layer', '2026-04-19 16:48:10', 5),
(193, 'Which property disables the banner?', 'spring.main.banner-mode=off', 'spring.banner.enabled=false', 'No such property', 'banner=false', 'A', 'Easy - Simple customization.', 'Application Customization', '2026-04-19 16:48:10', 5),
(194, 'What is the purpose of Health Indicators in Actuator?', 'Only security', 'Custom or built-in health checks (DB, disk, etc.)', 'Logging only', 'Testing', 'B', 'Medium - /actuator/health endpoint.', 'Monitoring', '2026-04-19 16:48:10', 5),
(195, 'How can you create custom Actuator endpoints?', '@Endpoint annotation or WebEndpoint', 'Only properties', 'Not possible', 'External tool only', 'A', 'Hard - Extend monitoring.', 'Monitoring', '2026-04-19 16:48:10', 5),
(196, 'What does @Lazy do?', 'Eager bean initialization', 'Defers bean creation until needed', 'Only for testing', 'Security feature', 'B', 'Medium - Performance optimization.', 'Core Concepts', '2026-04-19 16:48:10', 5),
(197, 'Spring Boot supports which logging frameworks by default?', 'Only Log4j', 'Commons Logging with Logback as default', 'No logging', 'Only SLF4J', 'B', 'Medium - Configurable via properties.', 'Logging', '2026-04-19 16:48:10', 5),
(198, 'What are Standalone Components in Angular?', 'Components that require NgModules', 'Components that can be used without NgModules', 'Deprecated feature', 'Only for services', 'B', 'Easy - Major simplification in modern Angular.', 'Standalone Components', '2026-04-19 16:48:45', 6),
(199, 'What is the main benefit of Signals in Angular?', 'Slower reactivity', 'Fine-grained reactive state management', 'Only for templates', 'Replaces all RxJS', 'B', 'Medium - Better performance than zone.js change detection.', 'Signals', '2026-04-19 16:48:45', 6),
(200, 'How do you bootstrap an Angular application with Standalone Components?', 'Using NgModule only', 'bootstrapApplication() in main.ts', 'Always use AppModule', 'No bootstrap needed', 'B', 'Medium - Modern way without root module.', 'Application Bootstrap', '2026-04-19 16:48:45', 6),
(201, 'What does the inject() function allow?', 'Only in constructors', 'Dependency injection in functions and outside constructors', 'Replaces all services', 'Only for components', 'B', 'Hard - Functional DI in modern Angular.', 'Dependency Injection', '2026-04-19 16:48:45', 6),
(202, 'In Angular, what is a Functional Guard?', 'Class-based only', 'Guard written as a function instead of class', 'Deprecated', 'Only for routing', 'B', 'Medium - Cleaner and tree-shakable.', 'Routing', '2026-04-19 16:48:45', 6),
(203, 'What is the purpose of provideIn: \'root\' in services?', 'Creates new instance per component', 'Provides singleton at root level (tree-shakable)', 'Disables the service', 'Only for lazy modules', 'B', 'Easy - Recommended for most services.', 'Services', '2026-04-19 16:48:45', 6),
(204, 'How does Angular Ivy improve compilation?', 'Slower builds', 'Faster builds, smaller bundles, better debugging', 'Removes AOT', 'Only for production', 'B', 'Medium - Default since Angular 9.', 'Compiler', '2026-04-19 16:48:45', 6),
(205, 'What are Angular Signals primarily used for?', 'HTTP calls', 'Reactive primitive for state (computed, effect, signal)', 'Routing only', 'Styling', 'B', 'Medium - Modern reactivity model.', 'Signals', '2026-04-19 16:48:45', 6),
(206, 'Which directive is used for conditional rendering in templates?', '*ngIf', '*ngFor', '*ngSwitch', 'All of them', 'D', 'Easy - Structural directives.', 'Templates', '2026-04-19 16:48:45', 6),
(207, 'What is lazy loading in Angular routing?', 'Loading all modules at startup', 'Loading feature modules only when navigated to', 'Disabling routing', 'Only for standalone', 'B', 'Easy - Improves initial load time.', 'Routing', '2026-04-19 16:48:45', 6),
(208, 'How do you import a Standalone Component?', 'In NgModule declarations', 'Directly in the imports array of another component', 'Only via router', 'Not possible', 'B', 'Medium - No module needed.', 'Standalone Components', '2026-04-19 16:48:45', 6),
(209, 'What is the difference between @Input() and @Output()?', 'Both for parent to child', '@Input receives data; @Output emits events', 'Both deprecated', 'Only for services', 'B', 'Easy - Component communication.', 'Component Interaction', '2026-04-19 16:48:45', 6),
(210, 'What does trackBy do in *ngFor?', 'Slows down rendering', 'Improves performance by tracking items by unique identifier', 'Only for objects', 'Removes duplicates', 'B', 'Medium - Optimizes list rendering.', 'Performance', '2026-04-19 16:48:45', 6),
(211, 'How do you create a custom pipe in Angular?', '@Pipe decorator', '@Directive', '@Component', '@Injectable', 'A', 'Medium - Transforms data in templates.', 'Pipes', '2026-04-19 16:48:45', 6),
(212, 'What is Content Projection?', 'Routing feature', 'Passing content from parent to child using <ng-content>', 'State management', 'HTTP only', 'B', 'Medium - Reusable components.', 'Components', '2026-04-19 16:48:45', 6),
(213, 'Which lifecycle hook runs after input properties are set?', 'ngOnInit', 'ngOnChanges', 'ngAfterViewInit', 'ngOnDestroy', 'B', 'Medium - Responds to input changes.', 'Lifecycle Hooks', '2026-04-19 16:48:45', 6),
(214, 'What is the recommended way for state management in modern Angular?', 'Only NgRx', 'Signals + Services or NgRx SignalStore', 'Only Redux', 'No state management needed', 'B', 'Hard - Evolving best practices.', 'State Management', '2026-04-19 16:48:45', 6),
(215, 'How do you handle HTTP requests in Angular?', 'Direct XMLHttpRequest', 'HttpClient service', 'Only fetch API', 'No support', 'B', 'Easy - Injectable and observable-based.', 'HTTP', '2026-04-19 16:48:45', 6),
(216, 'What is Angular Universal used for?', 'Mobile apps', 'Server-Side Rendering (SSR)', 'Testing only', 'Styling', 'B', 'Medium - Improves SEO and performance.', 'Advanced Rendering', '2026-04-19 16:48:45', 6),
(217, 'What does the deferrable loading (@defer) block do?', 'Loads content immediately', 'Lazy loads template blocks on trigger or viewport', 'Removes content', 'Only for routing', 'B', 'Hard - New in recent Angular versions.', 'Performance', '2026-04-19 16:48:45', 6),
(218, 'How do you provide environment-specific configuration?', 'Hardcode values', 'Using environment.ts files and build configurations', 'Only via API', 'Not supported', 'B', 'Medium - Standard practice.', 'Configuration', '2026-04-19 16:48:45', 6),
(219, 'What is a Resolver in Angular routing?', 'Only for guards', 'Pre-fetches data before activating route', 'Defines routes', 'Handles errors', 'B', 'Medium - Improves UX.', 'Routing', '2026-04-19 16:48:45', 6),
(220, 'Which change detection strategy improves performance?', 'Default', 'OnPush', 'Always', 'None', 'B', 'Hard - Requires immutable data or signals.', 'Performance', '2026-04-19 16:48:45', 6),
(221, 'What are Functional Interceptors?', 'Class-based only', 'Interceptors written as functions', 'Deprecated', 'Only for HTTP', 'B', 'Hard - Modern HttpClient style.', 'HTTP', '2026-04-19 16:48:45', 6),
(222, 'How do you share data between unrelated components?', 'Only @Input/@Output', 'Using a shared service with Signals or Subject', 'Global variable', 'Not possible', 'B', 'Medium - Common pattern.', 'Component Communication', '2026-04-19 16:48:45', 6),
(223, 'What is the role of provideRouter()?', 'Old routing API', 'Functional routing configuration without modules', 'Only for lazy loading', 'Disables routing', 'B', 'Hard - Modern standalone routing.', 'Routing', '2026-04-19 16:48:45', 6),
(224, 'Angular Signals can be used with?', 'Only templates', 'Components, services, and effects', 'Only routing', 'Only HTTP', 'B', 'Medium - Flexible reactivity.', 'Signals', '2026-04-19 16:48:45', 6),
(225, 'What does effect() do in Signals?', 'Creates computed value', 'Runs side effects when signals change', 'Defines input', 'Handles routing', 'B', 'Hard - For logging, API calls, etc.', 'Signals', '2026-04-19 16:48:45', 6),
(226, 'How do you test Standalone Components?', 'Only with TestBed module', 'Using TestBed with importProvidersFrom or standalone: true', 'No testing support', 'Only e2e', 'B', 'Medium - Updated testing approach.', 'Testing', '2026-04-19 16:48:45', 6),
(227, 'What is the benefit of Tree Shakable providers?', 'Larger bundles', 'Unused services are removed from final bundle', 'Slower runtime', 'Only for components', 'B', 'Hard - Optimization technique.', 'Performance', '2026-04-19 16:48:45', 6),
(228, 'Which directive is used for two-way data binding?', '[(ngModel)]', '*ngIf', '[ngClass]', 'No such directive', 'A', 'Easy - Forms feature.', 'Forms', '2026-04-19 16:48:45', 6),
(229, 'What is Angular\'s recommended approach for forms?', 'Only Template-driven', 'Reactive Forms for complex scenarios', 'No forms support', 'Only third-party', 'B', 'Medium - More scalable.', 'Forms', '2026-04-19 16:48:45', 6),
(230, 'How do you optimize bundle size in Angular?', 'Disable Ivy', 'Lazy loading, standalone, differential loading, and tree shaking', 'Load all at once', 'Remove TypeScript', 'B', 'Hard - Production best practices.', 'Performance', '2026-04-19 16:48:45', 6),
(231, 'What is the new control flow syntax (@if, @for)?', 'Old *ngIf', 'Built-in template syntax replacing structural directives', 'Only for signals', 'Deprecated', 'B', 'Medium - Cleaner templates.', 'Templates', '2026-04-19 16:48:45', 6),
(232, 'How do you handle errors globally in Angular?', 'Only per component', 'Using ErrorHandler or HttpInterceptor', 'No global handling', 'Console only', 'B', 'Medium - Centralized error management.', 'Error Handling', '2026-04-19 16:48:45', 6),
(233, 'What is Zoneless change detection?', 'Default mode', 'Future mode using Signals without Zone.js', 'Only for SSR', 'Not supported', 'B', 'Hard - Performance improvement direction.', 'Change Detection', '2026-04-19 16:48:45', 6),
(234, 'How do you create a custom structural directive?', '@Directive with TemplateRef and ViewContainerRef', '@Component', '@Pipe', '@Injectable', 'A', 'Hard - Advanced directive development.', 'Directives', '2026-04-19 16:48:45', 6),
(235, 'What is the purpose of ViewChild?', 'Access child component or DOM element', 'Only for routing', 'State management', 'HTTP calls', 'A', 'Medium - DOM/querying.', 'Component Interaction', '2026-04-19 16:48:45', 6),
(236, 'Angular supports which rendering strategies?', 'Client-side only', 'CSR, SSR, SSG, and hydration', 'Only SSR', 'No rendering options', 'B', 'Hard - Modern rendering capabilities.', 'Rendering', '2026-04-19 16:48:45', 6),
(237, 'What does computed() create in Signals?', 'Mutable signal', 'Derived read-only value from other signals', 'Side effect', 'Input property', 'B', 'Medium - Reactive derived state.', 'Signals', '2026-04-19 16:48:45', 6),
(238, 'How do you preload lazy-loaded modules?', 'Not possible', 'Using preload strategy in router configuration', 'Always load all', 'Only in development', 'B', 'Medium - Improves navigation speed.', 'Routing', '2026-04-19 16:48:45', 6),
(239, 'What is the role of TransferState in SSR?', 'Only caching', 'Transfer data from server to client to avoid double requests', 'Routing only', 'Styling', 'B', 'Hard - Hydration optimization.', 'SSR', '2026-04-19 16:48:45', 6),
(240, 'Which pipe is used for async data in templates?', 'json', 'async', 'date', 'uppercase', 'B', 'Easy - Handles observables/promises.', 'Pipes', '2026-04-19 16:48:45', 6),
(241, 'What is the best practice for component sizing?', 'Large monolithic components', 'Small, focused, single-responsibility components', 'No best practice', 'Only smart components', 'B', 'Medium - Maintainability.', 'Best Practices', '2026-04-19 16:48:45', 6),
(242, 'How do you internationalize an Angular app?', 'Hardcode strings', 'Using @angular/localize and i18n attributes', 'Only third-party', 'Not supported', 'B', 'Medium - Built-in i18n support.', 'Internationalization', '2026-04-19 16:48:45', 6),
(243, 'What is the purpose of provideAnimations()?', 'Disables animations', 'Enables browser animations in standalone bootstrap', 'Only for routing', 'Styling only', 'B', 'Medium - Modern setup.', 'Animations', '2026-04-19 16:48:45', 6),
(244, 'Angular\'s recommended HTTP testing is done with?', 'Only HttpClientTestingModule', 'HttpClientTestingModule or provideHttpClientTesting()', 'No testing', 'Manual mocks only', 'B', 'Hard - Updated for standalone.', 'Testing', '2026-04-19 16:48:45', 6),
(245, 'What is the benefit of using Signals over RxJS in simple cases?', 'More complex code', 'Simpler mental model and better performance', 'Only for backend', 'No benefit', 'B', 'Hard - When to choose which.', 'Signals vs RxJS', '2026-04-19 16:48:45', 6),
(246, 'What is the main goal of Agile?', 'Strict documentation', 'Delivering value through iterative and incremental development', 'Fixed scope and budget', 'Individual work only', 'B', 'Easy - Agile Manifesto focus.', 'Agile Principles', '2026-04-19 16:49:20', 15),
(247, 'How many values are in the Agile Manifesto?', '4', '12', '5', '10', 'A', 'Easy - Core foundation.', 'Agile Manifesto', '2026-04-19 16:49:20', 15),
(248, 'What are the three pillars of Scrum?', 'Planning, Execution, Review', 'Transparency, Inspection, Adaptation', 'Roles, Events, Artifacts', 'Team, Product, Sprint', 'B', 'Easy - Empirical process control.', 'Scrum Theory', '2026-04-19 16:49:20', 15),
(249, 'Who is responsible for maximizing the value of the product?', 'Scrum Master', 'Product Owner', 'Development Team', 'Stakeholders', 'B', 'Easy - Core accountability.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(250, 'What is the maximum duration of a Sprint?', '1 month', '4 weeks', 'Both A and B', '2 weeks', 'C', 'Easy - Time-boxed.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(251, 'What is the purpose of the Daily Scrum?', 'Status report to manager', 'Inspect progress toward Sprint Goal and adapt plan', 'Detailed technical discussion', 'Product backlog refinement', 'B', 'Medium - 15-minute time-box.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(252, 'Who facilitates the Sprint Retrospective?', 'Product Owner', 'Scrum Master', 'Developers', 'External coach', 'B', 'Medium - Promotes continuous improvement.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(253, 'What is the Definition of Done (DoD)?', 'Optional checklist', 'Shared understanding of what \"Done\" means for an Increment', 'Defined only by Product Owner', 'Fixed for all teams', 'B', 'Medium - Ensures quality.', 'Artifacts', '2026-04-19 16:49:20', 15),
(254, 'What is a Product Backlog?', 'Sprint tasks only', 'Ordered list of everything needed in the product', 'Technical debt list', 'Team velocity report', 'B', 'Easy - Evolving artifact.', 'Artifacts', '2026-04-19 16:49:20', 15),
(255, 'The Scrum Team consists of?', 'Only Developers', 'Product Owner, Scrum Master, and Developers', 'Managers and stakeholders', 'External vendors', 'B', 'Easy - Self-managing cross-functional team.', 'Scrum Team', '2026-04-19 16:49:20', 15),
(256, 'What is the Sprint Goal?', 'Detailed tasks', 'Single objective for the Sprint that provides coherence', 'Product vision', 'Release plan', 'B', 'Medium - Commitment for the Sprint.', 'Artifacts', '2026-04-19 16:49:20', 15),
(257, 'When should the Product Backlog be refined?', 'Only during Sprint Planning', 'Ongoing activity throughout the Sprint', 'Only at the beginning of the project', 'Never', 'B', 'Medium - Collaborative activity.', 'Backlog Management', '2026-04-19 16:49:20', 15),
(258, 'What does the Scrum Master do?', 'Manages the team', 'Serves the team by removing impediments and coaching', 'Prioritizes the backlog', 'Writes code', 'B', 'Medium - Servant leadership.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(259, 'What is an Increment?', 'Only completed Product Backlog items', 'Sum of all completed Product Backlog items at the end of a Sprint', 'Sprint plan', 'Velocity metric', 'B', 'Medium - Must be \"Done\".', 'Artifacts', '2026-04-19 16:49:20', 15),
(260, 'How long is the Sprint Planning event?', 'Up to 8 hours for a one-month Sprint', 'Maximum 4 hours', 'No time-box', '15 minutes', 'A', 'Medium - Time-boxed.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(261, 'What is the primary measure of progress in Agile?', 'Lines of code', 'Working software', 'Hours worked', 'Number of meetings', 'B', 'Easy - Agile principle.', 'Agile Principles', '2026-04-19 16:49:20', 15),
(262, 'Who can cancel a Sprint?', 'Developers', 'Product Owner', 'Scrum Master', 'Stakeholders', 'B', 'Hard - If the Sprint Goal becomes obsolete.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(263, 'What is self-management in Scrum?', 'No rules', 'Team internally decides how to accomplish the work', 'Manager directs daily work', 'Product Owner assigns tasks', 'B', 'Medium - Key to agility.', 'Scrum Team', '2026-04-19 16:49:20', 15),
(264, 'What should the Sprint Retrospective focus on?', 'Individual performance', 'What went well, what could be improved, and action items', 'Product features only', 'Budget review', 'B', 'Medium - Process improvement.', 'Continuous Improvement', '2026-04-19 16:49:20', 15),
(265, 'What is the role of stakeholders in Scrum?', 'Part of the Scrum Team', 'Provide feedback during Sprint Review', 'Define DoD', 'Facilitate Daily Scrum', 'B', 'Medium - Collaboration is key.', 'Stakeholders', '2026-04-19 16:49:20', 15),
(266, 'Velocity is used for?', 'Exact future prediction', 'Forecasting and understanding team capacity over time', 'Individual performance review', 'Budget approval', 'B', 'Medium - Empirical planning aid.', 'Planning', '2026-04-19 16:49:20', 15),
(267, 'What is empiricism?', 'Following a detailed upfront plan', 'Making decisions based on what is known through observation and experimentation', 'Waterfall approach', 'Fixed requirements', 'B', 'Easy - Foundation of Scrum.', 'Scrum Theory', '2026-04-19 16:49:20', 15),
(268, 'The Product Owner is accountable for?', 'The Sprint Backlog', 'Maximizing the value of the product and the work of the Developers', 'Removing impediments', 'Facilitating events', 'B', 'Medium - Value focus.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(269, 'What is a Sprint Backlog?', 'Product vision', 'Set of Product Backlog items selected for the Sprint plus a plan', 'All unfinished work', 'Release plan', 'B', 'Medium - Commitment for the Sprint.', 'Artifacts', '2026-04-19 16:49:20', 15),
(270, 'How often should the Product Owner review the Product Backlog?', 'Only once', 'Continuously', 'Only during Sprint Review', 'Never', 'B', 'Easy - Living artifact.', 'Backlog Management', '2026-04-19 16:49:20', 15),
(271, 'What is the time-box for the Sprint Review?', '15 minutes', '4 hours maximum for a one-month Sprint', '8 hours', 'No limit', 'B', 'Medium - Inspect the Increment.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(272, 'Scrum values include?', 'Commitment, Courage, Focus, Openness, Respect', 'Speed, Cost, Quality', 'Planning, Execution, Delivery', 'Hierarchy, Control, Documentation', 'A', 'Easy - Guide behavior.', 'Scrum Values', '2026-04-19 16:49:20', 15),
(273, 'What is technical debt?', 'Planned feature', 'Work that needs to be done to keep code clean and maintainable', 'Sprint Goal', 'Velocity metric', 'B', 'Medium - Should be managed.', 'Agile Practices', '2026-04-19 16:49:20', 15),
(274, 'In Scrum, the Developers are responsible for?', 'Only coding', 'Creating a \"Done\" Increment every Sprint', 'Prioritizing backlog', 'Facilitating meetings', 'B', 'Medium - Cross-functional and self-managing.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(275, 'What is the main purpose of the Sprint Planning?', 'Detailed task assignment', 'Defining the Sprint Goal and selecting what can be Done', 'Reviewing past Sprints', 'Reporting to management', 'B', 'Medium - Collaborative planning.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(276, 'How does Scrum support adaptation?', 'Through fixed plans', 'Via inspection and adaptation in events and empiricism', 'Monthly reports only', 'Change control board', 'B', 'Medium - Responding to change.', 'Scrum Theory', '2026-04-19 16:49:20', 15),
(277, 'What should not happen during the Daily Scrum?', 'Discussing impediments', 'Detailed problem-solving or design discussions', 'Updating Sprint Backlog', 'Inspecting progress', 'B', 'Medium - Keep it short.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(278, 'What is a \"potentially shippable\" Increment?', 'Partially tested', 'Meets the Definition of Done and could be released', 'Only backend work', 'Planned but not started', 'B', 'Medium - Quality focus.', 'Artifacts', '2026-04-19 16:49:20', 15),
(279, 'The Scrum Master helps the team with?', 'Removing impediments and coaching on Scrum', 'Writing user stories', 'Approving budget', 'Hiring team members', 'A', 'Easy - Servant role.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(280, 'What is Product Goal?', 'Short-term Sprint objective', 'Long-term objective for the product that guides the Product Backlog', 'Team velocity target', 'Release date', 'B', 'Hard - Newer Scrum Guide concept.', 'Artifacts', '2026-04-19 16:49:20', 15),
(281, 'How should estimates be done in Scrum?', 'By managers', 'Collaboratively by the Developers', 'Product Owner alone', 'Using hours only', 'B', 'Medium - Relative sizing common.', 'Planning', '2026-04-19 16:49:20', 15),
(282, 'What is the benefit of time-boxing?', 'Creates pressure only', 'Creates focus and regularity', 'Allows unlimited discussion', 'Reduces quality', 'B', 'Medium - Promotes discipline.', 'Scrum Framework', '2026-04-19 16:49:20', 15),
(283, 'Who owns the Sprint Backlog?', 'Product Owner', 'Developers', 'Scrum Master', 'Stakeholders', 'B', 'Medium - Team manages their work.', 'Artifacts', '2026-04-19 16:49:20', 15),
(284, 'What is the Agile principle about welcoming changing requirements?', 'Even late in development', 'Only at the beginning', 'Never', 'Only if approved', 'A', 'Easy - Harness change for advantage.', 'Agile Manifesto', '2026-04-19 16:49:20', 15),
(285, 'What is a common anti-pattern in Scrum?', 'Daily Scrum as status meeting', 'Self-managing team', 'Frequent inspection', 'Transparent communication', 'A', 'Medium - Avoid turning into reporting.', 'Common Pitfalls', '2026-04-19 16:49:20', 15),
(286, 'How does Scrum promote transparency?', 'Hiding problems', 'Making key artifacts and progress visible to all', 'Private meetings', 'Detailed reports only', 'B', 'Easy - One of the pillars.', 'Scrum Theory', '2026-04-19 16:49:20', 15),
(287, 'What is the main output of the Sprint Retrospective?', 'New features', 'Actionable improvements for the next Sprint', 'Product backlog items', 'Velocity calculation', 'B', 'Medium - Continuous improvement.', 'Continuous Improvement', '2026-04-19 16:49:20', 15),
(288, 'In Agile, business and developers should?', 'Work separately', 'Work together daily throughout the project', 'Meet only at the end', 'Communicate via documents only', 'B', 'Easy - Agile principle.', 'Agile Principles', '2026-04-19 16:49:20', 15),
(289, 'What is the recommended team size in Scrum?', '3-9 Developers', '15+ people', '1-2 people', 'No limit', 'A', 'Medium - Optimal collaboration.', 'Scrum Team', '2026-04-19 16:49:20', 15),
(290, 'What does \"Done\" mean?', 'Coded only', 'Meets Definition of Done including quality criteria', 'Tested by QA only', 'Approved by manager', 'B', 'Medium - Shared understanding.', 'Definition of Done', '2026-04-19 16:49:20', 15),
(291, 'The Product Owner represents?', 'The development team', 'The customer / stakeholders', 'The Scrum Master', 'External vendors', 'B', 'Easy - Voice of the customer.', 'Scrum Roles', '2026-04-19 16:49:20', 15),
(292, 'What is forecasting in Scrum?', 'Exact prediction', 'Using velocity and backlog to forecast possible delivery', 'Only for budget', 'Not part of Scrum', 'B', 'Hard - Empirical approach.', 'Planning', '2026-04-19 16:49:20', 15),
(293, 'Scrum is described as?', 'A prescriptive methodology', 'A lightweight framework', 'A detailed process', 'A project management tool', 'B', 'Medium - Provides structure without too much prescription.', 'Scrum Framework', '2026-04-19 16:49:20', 15),
(294, 'What is the focus of the Sprint Review?', 'Internal team only', 'Inspect the Increment and adapt the Product Backlog with stakeholders', 'Technical deep dive', 'Retrospective', 'B', 'Medium - Collaboration event.', 'Scrum Events', '2026-04-19 16:49:20', 15),
(295, 'What is the main difference between a Python list and a tuple?', 'Lists are immutable; tuples are mutable', 'Tuples are immutable; lists are mutable', 'Both are immutable', 'Both are mutable', 'B', 'Easy - Tuples cannot be changed after creation.', 'Python Basics', '2026-04-19 16:54:34', 13),
(296, 'Which library is primarily used for numerical computing and array operations in Data Science?', 'Pandas', 'NumPy', 'Matplotlib', 'Scikit-learn', 'B', 'Easy - NumPy provides efficient multi-dimensional arrays.', 'NumPy', '2026-04-19 16:54:34', 13),
(297, 'What does Pandas primarily work with?', 'Arrays only', 'Structured tabular data (Series and DataFrame)', 'Images', 'Text files only', 'B', 'Easy - Pandas is built on NumPy for data analysis.', 'Pandas Basics', '2026-04-19 16:54:34', 13),
(298, 'How do you select a column named \"age\" from a Pandas DataFrame df?', 'df[\"age\"]', 'df.age()', 'df.select(\"age\")', 'df.column(\"age\")', 'A', 'Easy - Bracket notation or dot notation for columns.', 'Pandas', '2026-04-19 16:54:34', 13),
(299, 'What is the purpose of df.describe()?', 'To display the first 5 rows', 'To generate summary statistics (mean, std, min, max, etc.)', 'To drop missing values', 'To merge two DataFrames', 'B', 'Medium - Useful for quick exploratory data analysis.', 'Exploratory Data Analysis', '2026-04-19 16:54:34', 13),
(300, 'Which method handles missing values by removing rows?', 'df.fillna()', 'df.dropna()', 'df.replace()', 'df.interpolate()', 'B', 'Medium - Common data cleaning technique.', 'Data Cleaning', '2026-04-19 16:54:34', 13),
(301, 'What is a key advantage of NumPy arrays over Python lists?', 'Slower performance', 'Vectorized operations and better memory efficiency', 'Support for mixed data types', 'Built-in database connectivity', 'B', 'Medium - Enables fast mathematical operations.', 'NumPy', '2026-04-19 16:54:34', 13),
(302, 'How do you create a Pandas Series from a list?', 'pd.Series(data)', 'pd.DataFrame(data)', 'pd.array(data)', 'np.series(data)', 'A', 'Easy - Basic Series creation.', 'Pandas', '2026-04-19 16:54:34', 13),
(303, 'What does groupby() allow you to do in Pandas?', 'Filter rows', 'Split data into groups and apply aggregate functions', 'Join two tables', 'Plot data', 'B', 'Medium - Powerful for aggregation and analysis.', 'Data Manipulation', '2026-04-19 16:54:34', 13),
(304, 'Which library is most commonly used for data visualization in Python Data Science?', 'Seaborn', 'Matplotlib (and Seaborn built on it)', 'Plotly only', 'NumPy', 'B', 'Easy - Foundation for plotting.', 'Data Visualization', '2026-04-19 16:54:34', 13),
(305, 'What is list comprehension used for?', 'Creating new lists based on existing iterables concisely', 'Defining functions', 'Connecting to databases', 'Handling exceptions', 'A', 'Medium - Elegant and efficient way to create lists.', 'Python Basics', '2026-04-19 16:54:34', 13),
(306, 'How do you merge two Pandas DataFrames on a common column?', 'df1.join(df2)', 'pd.merge(df1, df2, on=\"key\")', 'df1 + df2', 'df1.concat(df2)', 'B', 'Medium - Similar to SQL JOIN.', 'Data Wrangling', '2026-04-19 16:54:34', 13),
(307, 'What does the apply() method do in Pandas?', 'Applies a function along an axis of the DataFrame', 'Applies a filter', 'Renames columns', 'Sorts data', 'A', 'Medium - Flexible for custom transformations.', 'Pandas Advanced', '2026-04-19 16:54:34', 13),
(308, 'Which of the following is mutable in Python?', 'Tuple', 'String', 'List', 'Integer', 'C', 'Easy - Lists can be modified after creation.', 'Python Basics', '2026-04-19 16:54:34', 13),
(309, 'What is the difference between loc and iloc in Pandas?', 'loc uses labels; iloc uses integer positions', 'Both use labels', 'iloc is for filtering only', 'loc is deprecated', 'A', 'Hard - Important for precise data selection.', 'Pandas Indexing', '2026-04-19 16:54:34', 13),
(310, 'How do you read a CSV file into a Pandas DataFrame?', 'pd.read_csv(\"file.csv\")', 'pd.load_csv(\"file.csv\")', 'np.read(\"file.csv\")', 'df.from_csv(\"file.csv\")', 'A', 'Easy - Standard data import method.', 'Data Import', '2026-04-19 16:54:34', 13),
(311, 'What is the purpose of Matplotlib\'s pyplot?', 'Data cleaning', 'Creating static, animated, and interactive visualizations', 'Machine learning models', 'Database queries', 'B', 'Medium - Core plotting interface.', 'Visualization', '2026-04-19 16:54:34', 13),
(312, 'Which function from NumPy generates evenly spaced numbers?', 'np.linspace()', 'np.random()', 'np.mean()', 'np.sum()', 'A', 'Medium - Useful for creating ranges.', 'NumPy', '2026-04-19 16:54:34', 13),
(313, 'What does df.corr() compute?', 'Mean values', 'Pairwise correlation of columns', 'Missing value count', 'Data types', 'B', 'Medium - Helps understand relationships between variables.', 'Exploratory Data Analysis', '2026-04-19 16:54:34', 13),
(314, 'How do you handle categorical data in Pandas?', 'Using pd.get_dummies() for one-hot encoding or astype(\"category\")', 'Only with NumPy', 'By dropping the column', 'Not possible', 'A', 'Medium - Common preprocessing step.', 'Data Preprocessing', '2026-04-19 16:54:34', 13),
(315, 'What is a lambda function in Python?', 'A small anonymous function defined with lambda keyword', 'A built-in math function', 'A class method', 'A decorator', 'A', 'Medium - Useful for short operations.', 'Python Functions', '2026-04-19 16:54:34', 13),
(316, 'Which scikit-learn module is used for splitting data into train and test sets?', 'sklearn.model_selection.train_test_split', 'sklearn.preprocessing', 'sklearn.metrics', 'sklearn.cluster', 'A', 'Medium - Essential for model evaluation.', 'Machine Learning Basics', '2026-04-19 16:54:34', 13),
(317, 'What does the head() method return by default?', 'Last 5 rows', 'First 5 rows', 'All rows', 'Summary statistics', 'B', 'Easy - Quick data inspection.', 'Pandas', '2026-04-19 16:54:34', 13),
(318, 'How can you sort a DataFrame by a specific column?', 'df.sort_values(by=\"column\")', 'df.order(\"column\")', 'df.sorted(\"column\")', 'df.sort(\"column\")', 'A', 'Easy - Common data organization.', 'Data Manipulation', '2026-04-19 16:54:34', 13),
(319, 'What is the GIL in Python?', 'Global Interpreter Lock that prevents multiple native threads from executing Python bytecodes simultaneously', 'A graphics library', 'A data loading tool', 'A machine learning algorithm', 'A', 'Hard - Impacts multi-threading performance.', 'Python Internals', '2026-04-19 16:54:34', 13),
(320, 'Which method fills missing values with the mean?', 'df.fillna(df.mean())', 'df.dropna()', 'df.replace()', 'df.interpolate()', 'A', 'Medium - Simple imputation technique.', 'Data Cleaning', '2026-04-19 16:54:34', 13),
(321, 'What is the purpose of Seaborn?', 'Statistical data visualization built on Matplotlib', 'Data storage', 'Model training', 'Web scraping', 'A', 'Medium - Higher-level interface for attractive plots.', 'Visualization', '2026-04-19 16:54:34', 13),
(322, 'How do you reset the index of a Pandas DataFrame after filtering?', 'df.reset_index(drop=True)', 'df.reindex()', 'df.set_index()', 'df.index.reset()', 'A', 'Medium - Cleans up after operations.', 'Pandas', '2026-04-19 16:54:34', 13),
(323, 'What does np.where() do?', 'Returns elements based on a condition', 'Calculates mean', 'Sorts array', 'Reshapes array', 'A', 'Medium - Vectorized conditional selection.', 'NumPy', '2026-04-19 16:54:34', 13),
(324, 'In Data Science, what is feature engineering?', 'Building machine learning models', 'Creating new features from existing data to improve model performance', 'Cleaning data only', 'Visualizing data', 'B', 'Hard - Critical step for better predictions.', 'Feature Engineering', '2026-04-19 16:54:34', 13),
(325, 'What is the output of len() on a DataFrame?', 'Number of columns', 'Number of rows', 'Total elements', 'Number of missing values', 'B', 'Easy - Basic shape inspection.', 'Pandas', '2026-04-19 16:54:34', 13),
(326, 'Which method converts a DataFrame column to datetime?', 'pd.to_datetime()', 'pd.to_date()', 'df.astype(\"datetime\")', 'np.datetime()', 'A', 'Medium - Essential for time series.', 'Data Types', '2026-04-19 16:54:34', 13),
(327, 'What is broadcasting in NumPy?', 'Automatic expansion of arrays with different shapes for arithmetic operations', 'Sending data over network', 'Plotting multiple charts', 'Saving models', 'A', 'Hard - Key to efficient computations.', 'NumPy Advanced', '2026-04-19 16:54:34', 13),
(328, 'How do you drop duplicate rows in Pandas?', 'df.drop_duplicates()', 'df.unique()', 'df.remove_duplicates()', 'df.distinct()', 'A', 'Medium - Data quality step.', 'Data Cleaning', '2026-04-19 16:54:34', 13),
(329, 'What library is commonly used for statistical modeling in Python?', 'Statsmodels', 'Scikit-learn only', 'TensorFlow', 'Keras', 'A', 'Medium - Provides detailed statistical output.', 'Statistical Analysis', '2026-04-19 16:54:34', 13),
(330, 'What does the pivot_table() function do?', 'Reshapes data similar to Excel pivot tables', 'Filters data', 'Merges files', 'Plots histograms', 'A', 'Medium - Summarizes data effectively.', 'Pandas Advanced', '2026-04-19 16:54:34', 13),
(331, 'How do you save a DataFrame to CSV?', 'df.to_csv(\"file.csv\")', 'df.save_csv(\"file.csv\")', 'pd.write_csv(df)', 'np.to_csv(df)', 'A', 'Easy - Standard export method.', 'Data Export', '2026-04-19 16:54:34', 13),
(332, 'What is the purpose of OneHotEncoder in scikit-learn?', 'To convert categorical variables into numerical format', 'To scale features', 'To split data', 'To train models', 'A', 'Medium - Preprocessing for ML algorithms.', 'Preprocessing', '2026-04-19 16:54:34', 13),
(333, 'Which operator is used for matrix multiplication in NumPy?', '*', '@ or np.dot()', '**', '%', 'B', 'Medium - Important for linear algebra.', 'NumPy', '2026-04-19 16:54:34', 13),
(334, 'What happens when you use df.iloc[:, 0:2]?', 'Selects first two columns by position', 'Selects by label', 'Selects rows only', 'Filters values', 'A', 'Medium - Integer-location based indexing.', 'Pandas Indexing', '2026-04-19 16:54:34', 13),
(335, 'How do you create a histogram in Matplotlib?', 'plt.hist(data)', 'plt.plot(data)', 'plt.bar(data)', 'plt.scatter(data)', 'A', 'Easy - Basic visualization of distribution.', 'Visualization', '2026-04-19 16:54:34', 13),
(336, 'What does df.info() show?', 'Summary statistics', 'Data types, non-null counts, and memory usage', 'First 10 rows', 'Correlation matrix', 'B', 'Easy - Quick overview of DataFrame.', 'Pandas', '2026-04-19 16:54:34', 13),
(337, 'What is cross-validation used for in Data Science?', 'Evaluating model performance more reliably', 'Training faster', 'Visualizing data', 'Cleaning text', 'A', 'Hard - Prevents overfitting in evaluation.', 'Model Evaluation', '2026-04-19 16:54:34', 13),
(338, 'Which function from Pandas is used for time-based resampling?', 'df.resample()', 'df.group_by_time()', 'df.time_split()', 'df.period()', 'A', 'Hard - Useful for time series analysis.', 'Time Series', '2026-04-19 16:54:34', 13),
(339, 'What is the role of StandardScaler?', 'To normalize features to mean=0 and std=1', 'To encode categories', 'To split data', 'To plot distributions', 'A', 'Medium - Feature scaling for ML.', 'Preprocessing', '2026-04-19 16:54:34', 13),
(340, 'How do you handle outliers in a dataset?', 'Using IQR method or Z-score with Pandas/NumPy', 'Ignoring them always', 'Only with visualization', 'Not possible in Python', 'A', 'Hard - Common data cleaning challenge.', 'Data Cleaning', '2026-04-19 16:54:34', 13),
(341, 'What does the CIA triad represent in cybersecurity?', 'Confidentiality, Integrity, Availability', 'Control, Identification, Authentication', 'Cost, Impact, Assessment', 'Compliance, Investigation, Audit', 'A', 'Easy - Core principles of information security.', 'Cybersecurity Fundamentals', '2026-04-19 16:54:48', 11),
(342, 'What is the primary goal of a firewall?', 'To encrypt data', 'To monitor and control incoming and outgoing network traffic', 'To store passwords', 'To scan for viruses only', 'B', 'Easy - Network security barrier.', 'Network Security', '2026-04-19 16:54:48', 11),
(343, 'What is phishing?', 'A type of malware that encrypts files', 'A social engineering attack that tricks users into revealing sensitive information', 'A hardware failure', 'A software update', 'B', 'Easy - Common attack vector.', 'Social Engineering', '2026-04-19 16:54:48', 11),
(344, 'What does MFA stand for?', 'Multi-Factor Authentication', 'Manual File Access', 'Managed Firewall Application', 'Main Frame Access', 'A', 'Easy - Adds layers of security.', 'Authentication', '2026-04-19 16:54:48', 11),
(345, 'Which of the following is an example of malware?', 'Firewall', 'Trojan horse', 'VPN', 'Antivirus', 'B', 'Easy - Malicious software.', 'Malware', '2026-04-19 16:54:48', 11),
(346, 'What is encryption used for?', 'To speed up internet', 'To convert data into a coded form to prevent unauthorized access', 'To delete files permanently', 'To monitor user activity', 'B', 'Medium - Protects confidentiality.', 'Cryptography', '2026-04-19 16:54:48', 11),
(347, 'What is a strong password characteristic?', 'Short and simple', 'Long, complex with mix of characters, numbers, and symbols', 'Only numbers', 'Same as username', 'B', 'Easy - Basic password hygiene.', 'Access Control', '2026-04-19 16:54:48', 11),
(348, 'What does a VPN provide?', 'Faster internet speed', 'Secure encrypted connection over public networks', 'Free storage', 'Automatic backups', 'B', 'Medium - Protects privacy and data in transit.', 'Network Security', '2026-04-19 16:54:48', 11),
(349, 'What is ransomware?', 'Malware that steals data', 'Malware that encrypts files and demands payment for decryption', 'A type of firewall', 'A password manager', 'B', 'Medium - Growing threat.', 'Malware Types', '2026-04-19 16:54:48', 11),
(350, 'What is the purpose of an antivirus program?', 'To increase system speed', 'To detect, prevent, and remove malicious software', 'To manage emails', 'To edit documents', 'B', 'Easy - Essential security software.', 'Endpoint Security', '2026-04-19 16:54:48', 11),
(351, 'What is social engineering?', 'Hacking hardware', 'Manipulating people into divulging confidential information', 'Writing secure code', 'Building firewalls', 'B', 'Medium - Human element of security.', 'Social Engineering', '2026-04-19 16:54:48', 11),
(352, 'What does \"zero-day\" vulnerability mean?', 'A vulnerability known for zero days', 'A newly discovered vulnerability with no available patch yet', 'A vulnerability fixed immediately', 'A hardware issue', 'B', 'Hard - High-risk threat.', 'Vulnerabilities', '2026-04-19 16:54:48', 11),
(353, 'What is the best practice for handling suspicious emails?', 'Click all links to check', 'Delete or report without opening attachments', 'Forward to friends', 'Save attachments', 'B', 'Easy - Phishing prevention.', 'Email Security', '2026-04-19 16:54:48', 11),
(354, 'What is the role of a DMZ in network security?', 'Main storage area', 'Demilitarized Zone - buffer network between internal and external networks', 'Password database', 'Backup server', 'B', 'Medium - Adds protection layer.', 'Network Architecture', '2026-04-19 16:54:48', 11),
(355, 'What does HTTPS use for secure communication?', 'HTTP only', 'SSL/TLS encryption', 'Plain text', 'FTP', 'B', 'Medium - Secures web traffic.', 'Web Security', '2026-04-19 16:54:48', 11),
(356, 'What is a brute force attack?', 'Using social tricks', 'Trying all possible password combinations systematically', 'Injecting SQL code', 'Stealing hardware', 'B', 'Medium - Common credential attack.', 'Attacks', '2026-04-19 16:54:48', 11),
(357, 'What is the principle of least privilege?', 'Giving users maximum access', 'Giving users only the minimum access necessary to perform their job', 'No access at all', 'Temporary full access', 'B', 'Medium - Reduces risk.', 'Access Control', '2026-04-19 16:54:48', 11),
(358, 'What is a keylogger?', 'A tool to log keyboard inputs secretly', 'A network scanner', 'An encryption tool', 'A backup software', 'A', 'Hard - Type of spyware.', 'Malware', '2026-04-19 16:54:48', 11),
(359, 'What should you do if you suspect a data breach?', 'Ignore it', 'Report it immediately to the appropriate person or authority', 'Post on social media', 'Try to fix it yourself', 'B', 'Easy - Incident response basics.', 'Incident Response', '2026-04-19 16:54:48', 11),
(360, 'What is two-factor authentication (2FA)?', 'Using two passwords', 'Using something you know and something you have/are', 'Using only biometrics', 'Using only email', 'B', 'Medium - Enhances security.', 'Authentication', '2026-04-19 16:54:48', 11),
(361, 'What does confidentiality mean in the CIA triad?', 'Ensuring data is accurate', 'Ensuring data is accessible', 'Protecting data from unauthorized access', 'Backing up data', 'C', 'Easy - Part of CIA.', 'Security Principles', '2026-04-19 16:54:48', 11),
(362, 'What is patch management?', 'Managing software updates to fix vulnerabilities', 'Buying new hardware', 'Training employees', 'Changing passwords', 'A', 'Medium - Keeps systems secure.', 'Vulnerability Management', '2026-04-19 16:54:48', 11),
(363, 'What is a man-in-the-middle (MitM) attack?', 'Attacking from inside the company', 'Intercepting communication between two parties', 'Deleting all data', 'Sending spam', 'B', 'Hard - Network attack.', 'Attacks', '2026-04-19 16:54:48', 11),
(364, 'What is the purpose of a security policy?', 'To restrict all internet use', 'To define rules and guidelines for protecting information assets', 'To list hardware inventory', 'To schedule meetings', 'B', 'Medium - Organizational foundation.', 'Security Governance', '2026-04-19 16:54:48', 11),
(365, 'What is biometric authentication?', 'Using passwords', 'Using unique physical or behavioral characteristics (fingerprint, face, etc.)', 'Using security questions only', 'Using PIN only', 'B', 'Medium - Modern authentication.', 'Authentication', '2026-04-19 16:54:48', 11),
(366, 'What should you avoid when creating passwords?', 'Using personal information', 'Reusing the same password across sites', 'Both A and B', 'Using long complex passwords', 'C', 'Easy - Common mistakes.', 'Password Security', '2026-04-19 16:54:48', 11),
(367, 'What is DDoS attack?', 'Distributed Denial of Service - flooding a target with traffic to make it unavailable', 'Data deletion attack', 'Password cracking', 'File encryption', 'A', 'Medium - Availability threat.', 'Attacks', '2026-04-19 16:54:48', 11),
(368, 'What does integrity mean in cybersecurity?', 'Protecting data from unauthorized modification', 'Making data available', 'Encrypting data', 'Backing up data', 'A', 'Easy - CIA triad.', 'Security Principles', '2026-04-19 16:54:48', 11),
(369, 'What is the best way to dispose of sensitive documents?', 'Throw in regular trash', 'Shred or use secure deletion methods', 'Recycle without shredding', 'Leave on desk', 'B', 'Easy - Physical security.', 'Physical Security', '2026-04-19 16:54:48', 11),
(370, 'What is SQL injection?', 'Attacking SQL databases by inserting malicious code', 'Injecting viruses via USB', 'Stealing SQL servers', 'Updating SQL software', 'A', 'Hard - Web application vulnerability.', 'Web Security', '2026-04-19 16:54:48', 11),
(371, 'What is the role of an Intrusion Detection System (IDS)?', 'To block all traffic', 'To monitor network traffic for suspicious activity', 'To encrypt files', 'To manage users', 'B', 'Medium - Detection tool.', 'Network Security', '2026-04-19 16:54:48', 11),
(372, 'What is shoulder surfing?', 'Looking over someone\'s shoulder to steal passwords or information', 'Hacking via satellite', 'Remote desktop attack', 'Email phishing', 'A', 'Medium - Physical/social threat.', 'Social Engineering', '2026-04-19 16:54:48', 11),
(373, 'What is data encryption at rest?', 'Encrypting data while it is stored', 'Encrypting data during transmission', 'Encrypting backups only', 'Encrypting emails', 'A', 'Medium - Storage protection.', 'Cryptography', '2026-04-19 16:54:48', 11),
(374, 'What is the purpose of a honeypot?', 'To attract and study attackers', 'To speed up the network', 'To store passwords', 'To backup data', 'A', 'Hard - Security research tool.', 'Threat Intelligence', '2026-04-19 16:54:48', 11),
(375, 'What should employees do with unknown USB drives?', 'Plug them in immediately', 'Avoid using them or report to IT', 'Use them on personal computers only', 'Share with colleagues', 'B', 'Easy - USB threat prevention.', 'Endpoint Security', '2026-04-19 16:54:48', 11),
(376, 'What is availability in the CIA triad?', 'Ensuring systems and data are accessible when needed', 'Keeping data secret', 'Ensuring data accuracy', 'Deleting old data', 'A', 'Easy - CIA principle.', 'Security Principles', '2026-04-19 16:54:48', 11),
(377, 'What is a rootkit?', 'Malware that hides its presence and provides privileged access', 'A backup tool', 'A password manager', 'A firewall', 'A', 'Hard - Advanced malware.', 'Malware', '2026-04-19 16:54:48', 11),
(378, 'What is the benefit of regular security awareness training?', 'Reduces human error and improves security culture', 'Increases internet speed', 'Deletes all data', 'Only for managers', 'A', 'Medium - People are the weakest link.', 'Security Awareness', '2026-04-19 16:54:48', 11),
(379, 'What does a digital signature provide?', 'Authentication, integrity, and non-repudiation', 'Only encryption', 'Faster downloads', 'Storage space', 'A', 'Hard - Cryptographic assurance.', 'Cryptography', '2026-04-19 16:54:48', 11),
(380, 'What is tailgating in physical security?', 'Following someone through a secured door without authorization', 'Sending tail messages', 'Hacking tail servers', 'Logging activity', 'A', 'Medium - Physical access control issue.', 'Physical Security', '2026-04-19 16:54:48', 11),
(381, 'What is the recommended action for software updates?', 'Ignore them', 'Install security patches promptly', 'Install only when forced', 'Uninstall updates', 'B', 'Easy - Reduces vulnerabilities.', 'Patch Management', '2026-04-19 16:54:48', 11),
(382, 'What is spear phishing?', 'Mass email phishing', 'Targeted phishing attack against specific individuals or organizations', 'Hardware phishing', 'Wi-Fi attack', 'B', 'Medium - More sophisticated phishing.', 'Social Engineering', '2026-04-19 16:54:48', 11),
(383, 'What does a proxy server do?', 'Hides user IP and can filter content', 'Stores all passwords', 'Encrypts hard drives', 'Scans for malware only', 'A', 'Medium - Privacy and control tool.', 'Network Security', '2026-04-19 16:54:48', 11),
(384, 'What is the first step in basic incident response?', 'Ignore the incident', 'Identification and containment', 'Immediate public disclosure', 'Delete all logs', 'B', 'Medium - Structured response.', 'Incident Response', '2026-04-19 16:54:48', 11),
(385, 'What is the primary role of a Project Manager?', 'To perform all technical work', 'To lead the team and ensure project objectives are met within constraints', 'To only manage the budget', 'To handle customer complaints only', 'B', 'Easy - Overall responsibility for project success.', 'Project Management Basics', '2026-04-19 16:54:58', 7);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(386, 'Which document formally authorizes a project?', 'Project Charter', 'Project Management Plan', 'Stakeholder Register', 'Risk Register', 'A', 'Easy - Initiating process group.', 'Project Initiation', '2026-04-19 16:54:58', 7),
(387, 'What are the triple constraints (Iron Triangle)?', 'Scope, Time, Cost', 'Quality, Risk, Resources', 'Stakeholders, Communication, Procurement', 'Integration, Scope, Quality', 'A', 'Easy - Classic project constraints.', 'Project Constraints', '2026-04-19 16:54:58', 7),
(388, 'What is the purpose of the Project Management Plan?', 'To list risks only', 'To integrate and coordinate all subsidiary plans', 'To define the project charter', 'To close the project', 'B', 'Medium - Guiding document.', 'Planning', '2026-04-19 16:54:58', 7),
(389, 'In which process group is the majority of project work performed?', 'Initiating', 'Planning', 'Executing', 'Monitoring & Controlling', 'C', 'Easy - Execution phase.', 'Process Groups', '2026-04-19 16:54:58', 7),
(390, 'What is a Work Breakdown Structure (WBS)?', 'Detailed schedule', 'Hierarchical decomposition of the total scope of work', 'Risk list', 'Budget breakdown', 'B', 'Medium - Scope management tool.', 'Scope Management', '2026-04-19 16:54:58', 7),
(391, 'What does the Critical Path represent?', 'Shortest path in the project', 'Longest sequence of dependent activities determining project duration', 'Cheapest activities', 'Riskiest activities', 'B', 'Medium - Schedule management.', 'Schedule Management', '2026-04-19 16:54:58', 7),
(392, 'What is earned value management (EVM) used for?', 'Only tracking costs', 'Measuring project performance against scope, schedule, and cost baselines', 'Identifying stakeholders', 'Closing procurements', 'B', 'Hard - Performance measurement.', 'Cost & Schedule Control', '2026-04-19 16:54:58', 7),
(393, 'Who is primarily responsible for managing stakeholder expectations?', 'Project Sponsor', 'Project Manager', 'Team Members', 'Functional Manager', 'B', 'Medium - Key responsibility.', 'Stakeholder Management', '2026-04-19 16:54:58', 7),
(394, 'What is a risk?', 'Any uncertain event that can affect project objectives positively or negatively', 'Only negative events', 'Only budget overrun', 'Only delay', 'A', 'Easy - Risk definition.', 'Risk Management', '2026-04-19 16:54:58', 7),
(395, 'What is the purpose of the Stakeholder Register?', 'To track schedule', 'To identify and analyze stakeholders', 'To manage quality', 'To control costs', 'B', 'Medium - Stakeholder identification.', 'Stakeholder Management', '2026-04-19 16:54:58', 7),
(396, 'What does RACI chart stand for?', 'Responsible, Accountable, Consulted, Informed', 'Risk, Action, Cost, Impact', 'Resource, Assignment, Control, Inspection', 'Report, Approve, Create, Inform', 'A', 'Medium - Responsibility assignment.', 'Resource Management', '2026-04-19 16:54:58', 7),
(397, 'In Agile, what is a Product Backlog?', 'Sprint tasks only', 'Prioritized list of features and requirements', 'Team velocity report', 'Burndown chart', 'B', 'Medium - Agile artifact.', 'Agile Practices', '2026-04-19 16:54:58', 7),
(398, 'What is the main output of the Develop Project Charter process?', 'Project Management Plan', 'Project Charter', 'Stakeholder Register', 'Risk Register', 'B', 'Easy - Initiating.', 'Initiating', '2026-04-19 16:54:58', 7),
(399, 'What is a baseline in project management?', 'Approved version of a plan used as reference for comparison', 'Initial rough estimate', 'Final project report', 'Risk list', 'A', 'Medium - Performance measurement.', 'Integration Management', '2026-04-19 16:54:58', 7),
(400, 'What does the Perform Integrated Change Control process do?', 'Approves or rejects change requests', 'Creates the WBS', 'Develops the schedule', 'Closes the project', 'A', 'Medium - Change management.', 'Integration Management', '2026-04-19 16:54:58', 7),
(401, 'What is the purpose of a lessons learned register?', 'To document issues during execution', 'To capture knowledge for future projects', 'To track costs only', 'To manage risks', 'B', 'Medium - Continuous improvement.', 'Closing', '2026-04-19 16:54:58', 7),
(402, 'In which knowledge area is the Develop Team process found?', 'Resource Management', 'Scope Management', 'Cost Management', 'Quality Management', 'A', 'Medium - Building team performance.', 'Resource Management', '2026-04-19 16:54:58', 7),
(403, 'What is a fixed-price contract?', 'Seller bears most cost risk', 'Buyer pays actual costs plus fee', 'Time and materials', 'Cost reimbursable only', 'A', 'Medium - Procurement types.', 'Procurement Management', '2026-04-19 16:54:58', 7),
(404, 'What does CPI (Cost Performance Index) greater than 1 indicate?', 'Project is over budget', 'Project is under budget', 'Project is on schedule', 'Project is delayed', 'B', 'Hard - EVM metric.', 'Cost Management', '2026-04-19 16:54:58', 7),
(405, 'What is the role of the Project Sponsor?', 'To provide resources and support; ultimate accountability for project success', 'To perform daily tasks', 'To write code', 'To test deliverables', 'A', 'Easy - Senior leadership role.', 'Project Governance', '2026-04-19 16:54:58', 7),
(406, 'What is progressive elaboration?', 'Planning in detail only at the end', 'Planning becomes more detailed as the project progresses', 'Avoiding planning', 'Fixed detailed plan from start', 'B', 'Medium - Rolling wave planning concept.', 'Planning', '2026-04-19 16:54:58', 7),
(407, 'What is the main purpose of the Sprint Review in Scrum?', 'To inspect the Increment and adapt the Product Backlog', 'To plan the next sprint', 'To retrospective the process', 'To assign tasks', 'A', 'Medium - Agile event.', 'Agile', '2026-04-19 16:54:58', 7),
(408, 'What is a Monte Carlo simulation used for?', 'Risk analysis and schedule/cost forecasting', 'Quality testing', 'Stakeholder mapping', 'Resource leveling', 'A', 'Hard - Quantitative risk analysis.', 'Risk Management', '2026-04-19 16:54:58', 7),
(409, 'What does the Manage Communications process involve?', 'Ensuring timely and appropriate generation and distribution of project information', 'Only sending emails', 'Creating the charter', 'Closing procurements', 'A', 'Medium - Communication management.', 'Communication Management', '2026-04-19 16:54:58', 7),
(410, 'What is the difference between verification and validation?', 'Verification: are we building the product right? Validation: are we building the right product?', 'Both are the same', 'Verification is only for quality', 'Validation is only for scope', 'A', 'Hard - Quality management.', 'Quality Management', '2026-04-19 16:54:58', 7),
(411, 'What is a burndown chart?', 'Shows remaining work in a sprint', 'Shows total project budget', 'Shows stakeholder engagement', 'Shows risks', 'A', 'Medium - Agile tracking tool.', 'Agile', '2026-04-19 16:54:58', 7),
(412, 'What is the output of the Close Project or Phase process?', 'Final product, service, or result transition and project documents updates', 'Project Charter', 'WBS', 'Risk Register', 'A', 'Medium - Closing activities.', 'Closing', '2026-04-19 16:54:58', 7),
(413, 'What does SPI (Schedule Performance Index) measure?', 'Cost efficiency', 'Schedule efficiency', 'Quality level', 'Risk level', 'B', 'Hard - EVM metric.', 'Schedule Management', '2026-04-19 16:54:58', 7),
(414, 'What is a hybrid project management approach?', 'Combining predictive (waterfall) and adaptive (agile) elements', 'Only waterfall', 'Only agile', 'No planning', 'A', 'Medium - Modern practice.', 'Project Management Approaches', '2026-04-19 16:54:58', 7),
(415, 'What is the purpose of quality audits?', 'To determine if project activities comply with organizational processes', 'To create the WBS', 'To approve changes', 'To manage risks', 'A', 'Medium - Quality assurance.', 'Quality Management', '2026-04-19 16:54:58', 7),
(416, 'Who maintains the issue log?', 'Project Manager', 'All team members contribute; Project Manager oversees', 'Sponsor only', 'Customer only', 'B', 'Medium - Issue management.', 'Integration Management', '2026-04-19 16:54:58', 7),
(417, 'What is the main benefit of using a Project Management Office (PMO)?', 'Standardization of project practices and support', 'Only reporting', 'Only budgeting', 'Only hiring', 'A', 'Medium - Organizational support.', 'Project Governance', '2026-04-19 16:54:58', 7),
(418, 'In risk management, what is a risk response strategy for threats?', 'Exploit, Share, Enhance, Accept', 'Avoid, Mitigate, Transfer, Accept', 'Only Accept', 'Only Mitigate', 'B', 'Medium - Negative risk strategies.', 'Risk Management', '2026-04-19 16:54:58', 7),
(419, 'What is the focus of the Direct and Manage Project Work process?', 'Executing the work defined in the project management plan', 'Planning only', 'Closing only', 'Monitoring only', 'A', 'Medium - Execution.', 'Executing', '2026-04-19 16:54:58', 7),
(420, 'What is a responsibility assignment matrix?', 'RACI chart', 'Stakeholder register', 'Risk register', 'Resource calendar', 'A', 'Medium - Clarity of roles.', 'Resource Management', '2026-04-19 16:54:58', 7),
(421, 'What does the term \"gold plating\" mean?', 'Adding extra features not required', 'Delivering exactly what is asked', 'Reducing scope', 'Increasing budget', 'A', 'Hard - Scope creep risk.', 'Scope Management', '2026-04-19 16:54:58', 7),
(422, 'What is the purpose of the Perform Qualitative Risk Analysis?', 'Prioritizing risks based on probability and impact', 'Calculating exact cost of risks', 'Implementing responses', 'Monitoring risks', 'A', 'Medium - Risk prioritization.', 'Risk Management', '2026-04-19 16:54:58', 7),
(423, 'What is a kick-off meeting?', 'Meeting at project start to align stakeholders and team', 'Closing meeting', 'Risk review only', 'Budget approval only', 'A', 'Easy - Initiating/Planning.', 'Project Initiation', '2026-04-19 16:54:58', 7),
(424, 'What does the Control Scope process monitor?', 'Changes to project scope', 'Team performance only', 'Budget only', 'Risks only', 'A', 'Medium - Scope control.', 'Scope Management', '2026-04-19 16:54:58', 7),
(425, 'In Agile, what is velocity?', 'Measure of work completed per sprint', 'Total project duration', 'Budget spent', 'Number of risks', 'A', 'Medium - Agile metric.', 'Agile', '2026-04-19 16:54:58', 7),
(426, 'What is the main output of Identify Risks process?', 'Risk Register', 'Project Charter', 'WBS', 'Schedule baseline', 'A', 'Medium - Risk identification.', 'Risk Management', '2026-04-19 16:54:58', 7),
(427, 'What is configuration management?', 'Controlling changes to project deliverables and documentation', 'Managing team configuration', 'Budget configuration', 'Risk configuration', 'A', 'Hard - Change control system.', 'Integration Management', '2026-04-19 16:54:58', 7),
(428, 'What does the Acquire Resources process involve?', 'Obtaining team members, facilities, equipment, etc.', 'Only hiring', 'Only planning', 'Only closing', 'A', 'Medium - Resource acquisition.', 'Resource Management', '2026-04-19 16:54:58', 7),
(429, 'What is the purpose of the Manage Stakeholder Engagement process?', 'To communicate and work with stakeholders to meet their needs and expectations', 'To identify stakeholders only', 'To close the project', 'To create the charter', 'A', 'Medium - Stakeholder management.', 'Stakeholder Management', '2026-04-19 16:54:58', 7),
(430, 'What is a projectized organizational structure?', 'Team members report directly to the Project Manager with high authority', 'Functional structure', 'Matrix structure only', 'No project manager', 'A', 'Medium - Organizational influence.', 'Project Environment', '2026-04-19 16:54:58', 7),
(431, 'What is the primary purpose of Git?', 'To deploy applications', 'To track changes in source code and enable collaboration', 'To monitor servers', 'To manage databases', 'B', 'Easy - Git is a distributed version control system.', 'Git Basics', '2026-04-19 17:05:40', 14),
(432, 'Which command creates a new Git repository?', 'git init', 'git clone', 'git commit', 'git push', 'A', 'Easy - Initializes a local repository.', 'Git Commands', '2026-04-19 17:05:40', 14),
(433, 'What does \"git clone\" do?', 'Creates a copy of a remote repository locally', 'Commits changes', 'Pushes code to remote', 'Checks status', 'A', 'Easy - Downloads a full copy of the repository.', 'Git Basics', '2026-04-19 17:05:40', 14),
(434, 'What is the staging area in Git?', 'Where committed code is stored', 'Where changes are prepared before committing', 'Remote repository', 'Backup folder', 'B', 'Medium - Also known as the index.', 'Git Workflow', '2026-04-19 17:05:40', 14),
(435, 'Which command records changes to the repository?', 'git add', 'git commit', 'git push', 'git pull', 'B', 'Easy - Creates a commit with a message.', 'Git Commands', '2026-04-19 17:05:40', 14),
(436, 'What does \"git status\" show?', 'Commit history', 'Current state of the working directory and staging area', 'Remote branches', 'Merged changes', 'B', 'Easy - Essential command for checking status.', 'Git Commands', '2026-04-19 17:05:40', 14),
(437, 'What is a branch in Git?', 'A separate line of development', 'A backup of the code', 'A remote server', 'A commit message', 'A', 'Medium - Allows parallel development.', 'Git Branching', '2026-04-19 17:05:40', 14),
(438, 'Which command switches to another branch?', 'git checkout <branch>', 'git merge <branch>', 'git branch <name>', 'git push <branch>', 'A', 'Medium - Or git switch in newer versions.', 'Git Branching', '2026-04-19 17:05:40', 14),
(439, 'What does \"git merge\" do?', 'Creates a new branch', 'Combines changes from one branch into another', 'Deletes a branch', 'Pushes changes', 'B', 'Medium - Integrates code from different branches.', 'Git Branching', '2026-04-19 17:05:40', 14),
(440, 'What is the purpose of a .gitignore file?', 'To ignore specific files and directories from being tracked by Git', 'To store passwords', 'To define commit messages', 'To configure remote repositories', 'A', 'Medium - Keeps repository clean.', 'Git Best Practices', '2026-04-19 17:05:40', 14),
(441, 'What is DevOps?', 'A programming language', 'A set of practices combining software development and IT operations', 'A type of database', 'A cloud provider', 'B', 'Easy - Aims to shorten development lifecycle.', 'DevOps Fundamentals', '2026-04-19 17:05:40', 14),
(442, 'What is Continuous Integration (CI)?', 'Automatically building and testing code changes frequently', 'Manual code review only', 'Deploying to production daily', 'Writing documentation', 'A', 'Medium - Early detection of integration issues.', 'CI/CD', '2026-04-19 17:05:40', 14),
(443, 'What does CI/CD stand for?', 'Continuous Integration / Continuous Deployment', 'Code Inspection / Continuous Delivery', 'Continuous Improvement / Code Delivery', 'Central Integration / Continuous Development', 'A', 'Easy - Core DevOps practices.', 'CI/CD', '2026-04-19 17:05:40', 14),
(444, 'Which tool is commonly used for version control in DevOps?', 'Docker', 'Git', 'Jenkins', 'Kubernetes', 'B', 'Easy - Foundation of collaboration.', 'Version Control', '2026-04-19 17:05:40', 14),
(445, 'What is Infrastructure as Code (IaC)?', 'Writing application code only', 'Managing and provisioning infrastructure through code', 'Manual server configuration', 'Using cloud consoles only', 'B', 'Medium - Enables automation and consistency.', 'Infrastructure as Code', '2026-04-19 17:05:40', 14),
(446, 'What is the purpose of Jenkins?', 'Version control', 'Automation server for building, testing, and deploying', 'Container orchestration', 'Monitoring tool', 'B', 'Medium - Popular CI/CD tool.', 'CI/CD Tools', '2026-04-19 17:05:40', 14),
(447, 'What does \"git pull\" do?', 'Fetches changes from remote and merges them', 'Pushes local changes', 'Creates a new branch', 'Deletes remote branch', 'A', 'Medium - Combines git fetch and git merge.', 'Git Commands', '2026-04-19 17:05:40', 14),
(448, 'What is a pull request?', 'Request to merge changes from one branch to another', 'Request to delete code', 'Request for code review only', 'Request to clone repository', 'A', 'Medium - Common in collaborative workflows.', 'Git Workflow', '2026-04-19 17:05:40', 14),
(449, 'What is Containerization?', 'Running applications in isolated user spaces (containers)', 'Virtualizing entire servers', 'Storing code in cloud', 'Monitoring performance', 'A', 'Medium - Docker is the most popular tool.', 'Containers', '2026-04-19 17:05:40', 14),
(450, 'What is the main benefit of DevOps?', 'Slower releases', 'Faster, more reliable software delivery through automation and collaboration', 'More manual work', 'Higher costs', 'B', 'Medium - Cultural and technical shift.', 'DevOps Benefits', '2026-04-19 17:05:40', 14),
(451, 'What command shows the commit history?', 'git log', 'git status', 'git diff', 'git show', 'A', 'Easy - Useful for reviewing changes.', 'Git Commands', '2026-04-19 17:05:40', 14),
(452, 'What is GitHub used for?', 'Only code editing', 'Hosting Git repositories and enabling collaboration', 'Container deployment', 'Monitoring servers', 'B', 'Easy - Popular Git hosting platform.', 'Git Hosting', '2026-04-19 17:05:40', 14),
(453, 'What does \"git rebase\" do?', 'Merges branches with a merge commit', 'Reapplies commits on top of another base', 'Deletes commits', 'Creates tags', 'B', 'Hard - Creates a linear history.', 'Git Advanced', '2026-04-19 17:05:40', 14),
(454, 'What is Continuous Delivery?', 'Automatically deploying every change to production', 'Automatically building and testing, with manual deployment step', 'Only monitoring', 'Writing tests only', 'B', 'Medium - Extends Continuous Integration.', 'CI/CD', '2026-04-19 17:05:40', 14),
(455, 'What is Docker primarily used for?', 'Version control', 'Packaging applications and dependencies into containers', 'Writing IaC', 'Monitoring', 'B', 'Medium - Enables consistent environments.', 'Containers', '2026-04-19 17:05:40', 14),
(456, 'What command stages all changes?', 'git commit -a', 'git add .', 'git push --all', 'git stash', 'B', 'Easy - Quick way to stage files.', 'Git Commands', '2026-04-19 17:05:40', 14),
(457, 'What is a DevOps pipeline?', 'Sequence of automated steps for building, testing, and deploying code', 'Team meeting schedule', 'Server hardware setup', 'Code review process only', 'A', 'Medium - Automates software delivery.', 'CI/CD', '2026-04-19 17:05:40', 14),
(458, 'What does \"git stash\" do?', 'Permanently deletes changes', 'Temporarily saves uncommitted changes', 'Pushes to remote', 'Merges branches', 'B', 'Medium - Useful when switching contexts.', 'Git Workflow', '2026-04-19 17:05:40', 14),
(459, 'What is Ansible used for?', 'Container orchestration', 'Configuration management and IaC', 'Version control', 'Monitoring', 'B', 'Medium - Agentless automation tool.', 'Infrastructure as Code', '2026-04-19 17:05:40', 14),
(460, 'What is the difference between git merge and git rebase?', 'Merge creates a merge commit; rebase rewrites history', 'They do the same thing', 'Rebase is safer for shared branches', 'Merge is only for local branches', 'A', 'Hard - Important workflow decision.', 'Git Advanced', '2026-04-19 17:05:40', 14),
(461, 'What is Kubernetes mainly used for?', 'Version control', 'Orchestrating and managing containers at scale', 'Building pipelines', 'Monitoring only', 'B', 'Medium - Popular container orchestration platform.', 'Containers', '2026-04-19 17:05:40', 14),
(462, 'What command creates and switches to a new branch?', 'git branch <name>', 'git checkout -b <name>', 'git push -u origin <name>', 'git merge <name>', 'B', 'Medium - Common branching command.', 'Git Branching', '2026-04-19 17:05:40', 14),
(463, 'What is meant by \"Shift Left\" in DevOps?', 'Testing and security earlier in the development process', 'Deploying to production faster', 'Moving servers to the left', 'Reducing team size', 'A', 'Hard - Quality and security focus.', 'DevOps Practices', '2026-04-19 17:05:40', 14),
(464, 'What does \"git diff\" show?', 'Difference between working directory and staging area', 'Commit history', 'Remote branches', 'Staged changes only', 'A', 'Medium - Helps review changes.', 'Git Commands', '2026-04-19 17:05:40', 14),
(465, 'What is Terraform used for?', 'Configuration management', 'Declarative Infrastructure as Code', 'Continuous Integration', 'Monitoring', 'B', 'Medium - Popular IaC tool.', 'Infrastructure as Code', '2026-04-19 17:05:40', 14),
(466, 'What is a feature branch?', 'Main development branch', 'Branch created for developing a new feature', 'Branch for production only', 'Temporary stash branch', 'B', 'Easy - Common Git workflow.', 'Git Workflow', '2026-04-19 17:05:40', 14),
(467, 'What is the purpose of Git tags?', 'To mark specific points in history (usually releases)', 'To ignore files', 'To create branches', 'To delete commits', 'A', 'Medium - Useful for versioning.', 'Git Advanced', '2026-04-19 17:05:40', 14),
(468, 'What does CI/CD pipeline typically include?', 'Build → Test → Deploy', 'Only deployment', 'Only code writing', 'Only monitoring', 'A', 'Medium - Automated software delivery.', 'CI/CD', '2026-04-19 17:05:40', 14),
(469, 'What command undoes the last commit?', 'git reset --soft HEAD~1', 'git revert', 'git checkout', 'git stash', 'A', 'Hard - Be careful with history rewriting.', 'Git Advanced', '2026-04-19 17:05:40', 14),
(470, 'What is observability in DevOps?', 'Monitoring logs, metrics, and traces to understand system behavior', 'Only checking server uptime', 'Writing code faster', 'Managing team', 'A', 'Hard - Modern DevOps practice.', 'Monitoring & Observability', '2026-04-19 17:05:40', 14),
(471, 'What is the main branch often called?', 'develop', 'main or master', 'feature', 'hotfix', 'B', 'Easy - Default branch.', 'Git Branching', '2026-04-19 17:05:40', 14),
(472, 'What does \"git remote -v\" show?', 'Local branches', 'Configured remote repositories', 'Commit history', 'Staged files', 'B', 'Medium - Checks remote connections.', 'Git Remote', '2026-04-19 17:05:40', 14),
(473, 'What is Blue-Green Deployment?', 'Technique to reduce downtime by switching between two environments', 'Deploying only on weekends', 'Using two monitors', 'Testing in production', 'A', 'Hard - Zero-downtime strategy.', 'Deployment Strategies', '2026-04-19 17:05:40', 14),
(474, 'What is the benefit of using Git in a team?', 'Better collaboration, version history, and code review', 'Faster internet', 'Automatic coding', 'No need for backups', 'A', 'Easy - Core collaboration tool.', 'Git Collaboration', '2026-04-19 17:05:40', 14),
(475, 'What is the main goal of public speaking?', 'To entertain only', 'To communicate ideas effectively to an audience', 'To read a script', 'To sell products', 'B', 'Easy - Core purpose.', 'Public Speaking Basics', '2026-04-19 17:05:50', 8),
(476, 'What is the fear of public speaking called?', 'Anxiety', 'Glossophobia', 'Stage fright only', 'Nervousness', 'B', 'Easy - Common term.', 'Overcoming Fear', '2026-04-19 17:05:50', 8),
(477, 'Which is the best way to start a presentation?', 'Apologizing for being nervous', 'Grabbing attention with a strong opening (story, question, or statistic)', 'Reading slides', 'Talking about yourself', 'B', 'Medium - First impression matters.', 'Presentation Structure', '2026-04-19 17:05:50', 8),
(478, 'What does \"body language\" include?', 'Only hand gestures', 'Posture, gestures, facial expressions, and eye contact', 'Only speaking speed', 'Only clothing', 'B', 'Easy - Non-verbal communication.', 'Non-Verbal Communication', '2026-04-19 17:05:50', 8),
(479, 'How can you reduce nervousness before speaking?', 'Avoid preparation', 'Practice, deep breathing, and positive visualization', 'Drink coffee', 'Memorize every word', 'B', 'Medium - Practical techniques.', 'Managing Anxiety', '2026-04-19 17:05:50', 8),
(480, 'What is the recommended structure for a speech?', 'Introduction, Body, Conclusion', 'Conclusion first', 'Body only', 'Random points', 'A', 'Easy - Classic structure.', 'Speech Structure', '2026-04-19 17:05:50', 8),
(481, 'Why is eye contact important?', 'To look confident and connect with the audience', 'To read notes', 'To avoid looking at people', 'To focus on slides', 'A', 'Medium - Builds engagement.', 'Delivery Techniques', '2026-04-19 17:05:50', 8),
(482, 'What should you avoid when using slides?', 'Too much text on each slide', 'Clear visuals and minimal text', 'Relevant images', 'Consistent design', 'A', 'Medium - Common mistake.', 'Visual Aids', '2026-04-19 17:05:50', 8),
(483, 'What is the purpose of storytelling in public speaking?', 'To make the speech longer', 'To make ideas memorable and emotional', 'To fill time', 'To show off vocabulary', 'B', 'Medium - Powerful engagement tool.', 'Engagement Techniques', '2026-04-19 17:05:50', 8),
(484, 'How should you use your voice effectively?', 'Monotone delivery', 'Vary pitch, pace, volume, and pauses', 'Speak very fast', 'Speak very quietly', 'B', 'Medium - Vocal variety.', 'Vocal Delivery', '2026-04-19 17:05:50', 8),
(485, 'What is a call to action?', 'Ending with what you want the audience to do', 'Starting the speech', 'Showing slides', 'Thanking the audience', 'A', 'Medium - Strong closing.', 'Speech Structure', '2026-04-19 17:05:50', 8),
(486, 'How long should you rehearse a presentation?', 'Once', 'Multiple times until confident', 'Never', 'Only the day before', 'B', 'Easy - Practice is key.', 'Preparation', '2026-04-19 17:05:50', 8),
(487, 'What is active listening in the context of Q&A?', 'Interrupting the questioner', 'Paying full attention and responding thoughtfully', 'Changing the subject', 'Giving short yes/no answers', 'B', 'Medium - Handling audience interaction.', 'Q&A Handling', '2026-04-19 17:05:50', 8),
(488, 'What does \"audience analysis\" mean?', 'Ignoring the audience', 'Understanding the audience\'s needs, knowledge, and expectations', 'Counting the number of people', 'Focusing only on slides', 'B', 'Medium - Tailoring your message.', 'Audience Engagement', '2026-04-19 17:05:50', 8),
(489, 'Which posture shows confidence?', 'Slouching', 'Standing tall with open posture', 'Crossing arms tightly', 'Looking at the floor', 'B', 'Easy - Non-verbal cue.', 'Body Language', '2026-04-19 17:05:50', 8),
(490, 'What is the 10-20-30 rule for presentations?', '10 slides, 20 minutes, 30-point font', '10 minutes, 20 slides, 30 seconds per slide', '10 ideas, 20 examples, 30 minutes', '10 audience members minimum', 'A', 'Hard - Guy Kawasaki rule.', 'Visual Aids', '2026-04-19 17:05:50', 8),
(491, 'How can you engage a bored audience?', 'Speak faster', 'Ask questions, use polls, or stories', 'Read from notes', 'Show more slides', 'B', 'Medium - Interaction techniques.', 'Audience Engagement', '2026-04-19 17:05:50', 8),
(492, 'What should you do if you make a mistake during a speech?', 'Apologize repeatedly', 'Acknowledge briefly and continue confidently', 'Stop the speech', 'Blame the audience', 'B', 'Medium - Recovery technique.', 'Handling Mistakes', '2026-04-19 17:05:50', 8),
(493, 'What is the benefit of using pauses?', 'To fill silence', 'To emphasize points and allow audience to absorb information', 'To show nervousness', 'To speed up the speech', 'B', 'Medium - Powerful delivery tool.', 'Vocal Delivery', '2026-04-19 17:05:50', 8),
(494, 'What is extemporaneous speaking?', 'Reading word for word', 'Speaking from notes or outline with natural delivery', 'Memorizing everything', 'Improvising without preparation', 'B', 'Medium - Recommended style.', 'Delivery Styles', '2026-04-19 17:05:50', 8),
(495, 'Why is it important to know your purpose?', 'To make the speech longer', 'To stay focused and deliver a clear message', 'To impress the audience', 'To use difficult words', 'B', 'Easy - Foundation of any speech.', 'Speech Preparation', '2026-04-19 17:05:50', 8),
(496, 'What is a rhetorical question?', 'A question that requires an answer', 'A question asked for effect, not requiring an answer', 'A difficult question', 'A personal question', 'B', 'Medium - Engagement device.', 'Engagement Techniques', '2026-04-19 17:05:50', 8),
(497, 'How should you end a presentation?', 'Abruptly', 'With a strong summary and memorable closing', 'With new information', 'With apologies', 'B', 'Medium - Last impression matters.', 'Closing Techniques', '2026-04-19 17:05:50', 8),
(498, 'What does \"mirroring\" refer to in body language?', 'Copying audience gestures to build rapport', 'Looking in a mirror', 'Using slide animations', 'Speaking loudly', 'A', 'Hard - Advanced non-verbal skill.', 'Body Language', '2026-04-19 17:05:50', 8),
(499, 'What is the ideal speaking rate?', 'Very fast', 'Around 120-150 words per minute', 'Very slow', 'Constant monotone', 'B', 'Medium - Natural pace.', 'Vocal Delivery', '2026-04-19 17:05:50', 8),
(500, 'What should you do with your hands while speaking?', 'Keep them in pockets', 'Use natural, purposeful gestures', 'Hold them behind your back', 'Cross them', 'B', 'Medium - Enhances message.', 'Non-Verbal Communication', '2026-04-19 17:05:50', 8),
(501, 'What is imposter syndrome in public speaking?', 'Feeling like you don\'t deserve to speak despite preparation', 'Being overly confident', 'Speaking too long', 'Using too many slides', 'A', 'Hard - Psychological barrier.', 'Overcoming Fear', '2026-04-19 17:05:50', 8),
(502, 'How can visual aids enhance a speech?', 'By replacing the speaker', 'By supporting and clarifying the message', 'By reading every word', 'By using many animations', 'B', 'Medium - Support, not replace.', 'Visual Aids', '2026-04-19 17:05:50', 8),
(503, 'What is the benefit of recording yourself?', 'To criticize yourself harshly', 'To identify strengths and areas for improvement', 'To avoid practicing', 'To memorize', 'B', 'Medium - Self-evaluation tool.', 'Preparation', '2026-04-19 17:05:50', 8),
(504, 'What should you do during Q&A session?', 'Defend every point aggressively', 'Listen carefully and answer honestly and concisely', 'Avoid questions', 'Change the topic', 'B', 'Medium - Professional interaction.', 'Q&A Handling', '2026-04-19 17:05:50', 8),
(505, 'What is the \"rule of three\" in speaking?', 'Using three main points for better retention', 'Speaking for three hours', 'Using three slides', 'Repeating everything three times', 'A', 'Medium - Classic rhetorical technique.', 'Speech Structure', '2026-04-19 17:05:50', 8),
(506, 'How do you handle a hostile audience member?', 'Argue back', 'Stay calm, acknowledge, and respond professionally', 'Ignore them completely', 'End the speech', 'B', 'Hard - Difficult situation management.', 'Handling Difficult Audiences', '2026-04-19 17:05:50', 8),
(507, 'What is the purpose of a strong opening?', 'To apologize', 'To capture attention immediately', 'To show nervousness', 'To read the agenda', 'B', 'Easy - Hooks the audience.', 'Opening Techniques', '2026-04-19 17:05:50', 8),
(508, 'What does confidence in speaking come from?', 'Natural talent only', 'Preparation, practice, and experience', 'Using big words', 'Wearing expensive clothes', 'B', 'Medium - Developable skill.', 'Building Confidence', '2026-04-19 17:05:50', 8),
(509, 'What should you avoid in slide design?', 'High contrast and readable fonts', 'Too many bullet points and crowded slides', 'Relevant images', 'Simple charts', 'B', 'Medium - Design best practices.', 'Visual Aids', '2026-04-19 17:05:50', 8),
(510, 'What is vocal variety?', 'Speaking in one tone', 'Varying pitch, pace, volume, and tone', 'Speaking only loudly', 'Using accent only', 'B', 'Medium - Keeps audience engaged.', 'Vocal Delivery', '2026-04-19 17:05:50', 8),
(511, 'Why is audience interaction important?', 'To waste time', 'To increase engagement and make the speech memorable', 'To show you are unprepared', 'To avoid eye contact', 'B', 'Medium - Two-way communication.', 'Audience Engagement', '2026-04-19 17:05:50', 8),
(512, 'What is the recommended way to use notes?', 'Read them word for word', 'Use them as prompts, not a script', 'Avoid notes completely', 'Hold them high', 'B', 'Medium - Natural delivery.', 'Delivery Techniques', '2026-04-19 17:05:50', 8),
(513, 'What does \"authenticity\" mean in public speaking?', 'Pretending to be someone else', 'Being genuine and true to yourself', 'Using formal language only', 'Memorizing perfectly', 'B', 'Medium - Builds trust.', 'Personal Style', '2026-04-19 17:05:50', 8),
(514, 'How can you improve pronunciation and clarity?', 'Speak fast', 'Practice articulation and slow down when needed', 'Mumble', 'Use complex jargon', 'B', 'Medium - Clear communication.', 'Vocal Delivery', '2026-04-19 17:05:50', 8),
(515, 'What is the benefit of humor in speeches?', 'To offend the audience', 'To connect with the audience and make points memorable', 'To fill time', 'To avoid serious topics', 'B', 'Medium - When used appropriately.', 'Engagement Techniques', '2026-04-19 17:05:50', 8),
(516, 'What should you do after delivering a speech?', 'Forget about it', 'Reflect on what went well and what to improve', 'Criticize yourself only', 'Avoid feedback', 'B', 'Easy - Continuous improvement.', 'Self-Reflection', '2026-04-19 17:05:50', 8),
(517, 'What is the \"power pose\" technique?', 'Standing confidently for a few minutes before speaking to boost confidence', 'Sitting during the speech', 'Using small gestures', 'Avoiding eye contact', 'A', 'Hard - Amy Cuddy concept.', 'Building Confidence', '2026-04-19 17:05:50', 8),
(518, 'Why is preparation the key to successful public speaking?', 'It reduces anxiety and increases confidence', 'It makes the speech boring', 'It is not necessary', 'It wastes time', 'A', 'Easy - Fundamental truth.', 'Preparation', '2026-04-19 17:05:50', 8),
(519, 'What is the main purpose of communication?', 'To talk a lot', 'To exchange information and understanding between people', 'To win arguments', 'To show intelligence', 'B', 'Easy - Core definition.', 'Communication Basics', '2026-04-19 17:06:04', 4),
(520, 'Which is NOT part of the communication process?', 'Sender', 'Message', 'Receiver', 'Ignoring feedback', 'D', 'Easy - Feedback is essential.', 'Communication Process', '2026-04-19 17:06:04', 4),
(521, 'What is active listening?', 'Pretending to listen while thinking of your reply', 'Fully concentrating, understanding, and responding thoughtfully', 'Only hearing words', 'Interrupting frequently', 'B', 'Medium - Key skill.', 'Active Listening', '2026-04-19 17:06:04', 4),
(522, 'What does \"non-verbal communication\" include?', 'Only spoken words', 'Body language, facial expressions, tone, and gestures', 'Written emails only', 'Phone calls', 'B', 'Easy - Often more powerful than words.', 'Non-Verbal Communication', '2026-04-19 17:06:04', 4),
(523, 'What is empathy in communication?', 'Understanding and sharing the feelings of others', 'Winning every discussion', 'Speaking loudly', 'Using technical jargon', 'A', 'Medium - Builds connection.', 'Emotional Intelligence', '2026-04-19 17:06:04', 4),
(524, 'Why is clarity important in communication?', 'To confuse the receiver', 'To ensure the message is easily understood', 'To use more words', 'To show superiority', 'B', 'Easy - Reduces misunderstandings.', 'Clear Communication', '2026-04-19 17:06:04', 4),
(525, 'What is feedback in communication?', 'Response from the receiver to the sender', 'Only criticism', 'Ignoring the message', 'Changing the topic', 'A', 'Medium - Closes the communication loop.', 'Communication Process', '2026-04-19 17:06:04', 4),
(526, 'How can you improve written communication?', 'Using long complex sentences', 'Being clear, concise, and well-structured', 'Using only emojis', 'Writing without checking', 'B', 'Medium - Professional writing.', 'Written Communication', '2026-04-19 17:06:04', 4),
(527, 'What is assertive communication?', 'Being aggressive', 'Expressing your needs clearly and respectfully', 'Avoiding conflict always', 'Agreeing with everyone', 'B', 'Medium - Balanced style.', 'Communication Styles', '2026-04-19 17:06:04', 4),
(528, 'What should you avoid in effective communication?', 'Using \"I\" statements', 'Using accusatory \"you\" statements', 'Active listening', 'Asking clarifying questions', 'B', 'Medium - Can create defensiveness.', 'Barriers to Communication', '2026-04-19 17:06:04', 4),
(529, 'What is a communication barrier?', 'Anything that prevents clear understanding', 'Using simple language', 'Giving feedback', 'Listening actively', 'A', 'Easy - Common obstacles.', 'Barriers to Communication', '2026-04-19 17:06:04', 4),
(530, 'How does tone of voice affect communication?', 'It has no effect', 'It can completely change the meaning of words', 'It only matters on phone', 'It should always be loud', 'B', 'Medium - Paralinguistics.', 'Non-Verbal Communication', '2026-04-19 17:06:04', 4),
(531, 'What is the difference between hearing and listening?', 'Hearing is physical; listening is mental and involves understanding', 'They are the same', 'Listening is only for meetings', 'Hearing requires more effort', 'A', 'Medium - Important distinction.', 'Active Listening', '2026-04-19 17:06:04', 4),
(532, 'Why is brevity important?', 'To make messages longer', 'To respect the receiver\'s time and improve understanding', 'To show expertise', 'To fill silence', 'B', 'Easy - Keep it short and clear.', 'Clear Communication', '2026-04-19 17:06:04', 4),
(533, 'What are \"I\" statements used for?', 'To express feelings without blaming others', 'To accuse someone', 'To avoid responsibility', 'To dominate conversations', 'A', 'Medium - Reduces conflict.', 'Assertive Communication', '2026-04-19 17:06:04', 4),
(534, 'What is emotional intelligence (EQ)?', 'Only IQ', 'Ability to understand and manage your own and others\' emotions', 'Speaking multiple languages', 'Technical skills only', 'B', 'Medium - Crucial for communication.', 'Emotional Intelligence', '2026-04-19 17:06:04', 4),
(535, 'How can cultural differences affect communication?', 'They have no impact', 'They can lead to misunderstandings if not respected', 'They only affect written communication', 'They make communication easier', 'B', 'Hard - Cross-cultural awareness.', 'Cross-Cultural Communication', '2026-04-19 17:06:04', 4),
(536, 'What is the best way to give constructive feedback?', 'Be vague and general', 'Be specific, balanced, and focused on behavior', 'Only criticize', 'Wait until the person is upset', 'B', 'Medium - Effective feedback model.', 'Giving Feedback', '2026-04-19 17:06:04', 4),
(537, 'What does paraphrasing mean?', 'Repeating exactly', 'Restating the speaker\'s message in your own words to confirm understanding', 'Changing the meaning', 'Interrupting', 'B', 'Medium - Active listening technique.', 'Active Listening', '2026-04-19 17:06:04', 4),
(538, 'Why is silence powerful in communication?', 'It shows disinterest', 'It gives time to think and emphasizes points', 'It should be avoided', 'It means agreement', 'B', 'Hard - Strategic use of pauses.', 'Advanced Communication', '2026-04-19 17:06:04', 4),
(539, 'What is passive communication?', 'Expressing needs clearly', 'Avoiding conflict by not expressing true feelings', 'Being aggressive', 'Using \"I\" statements', 'B', 'Medium - Communication style.', 'Communication Styles', '2026-04-19 17:06:04', 4),
(540, 'How can you resolve conflicts effectively?', 'Avoiding the issue', 'Through open dialogue, active listening, and finding mutual solutions', 'Blaming the other person', 'Ignoring feelings', 'B', 'Medium - Conflict resolution.', 'Conflict Management', '2026-04-19 17:06:04', 4),
(541, 'What is the role of questions in communication?', 'To show you know everything', 'To clarify, engage, and deepen understanding', 'To dominate the conversation', 'To avoid listening', 'B', 'Easy - Powerful tool.', 'Questioning Techniques', '2026-04-19 17:06:04', 4),
(542, 'What should you do when receiving feedback?', 'Defend yourself immediately', 'Listen openly and consider it for improvement', 'Ignore it', 'Argue every point', 'B', 'Medium - Growth mindset.', 'Receiving Feedback', '2026-04-19 17:06:04', 4),
(543, 'What is digital communication etiquette?', 'Using all caps', 'Being professional, clear, and respectful in emails and messages', 'Sending long voice notes', 'Using emojis in every sentence', 'B', 'Medium - Modern workplace skill.', 'Digital Communication', '2026-04-19 17:06:04', 4),
(544, 'What does congruence mean in communication?', 'When verbal and non-verbal messages match', 'Speaking loudly', 'Using difficult words', 'Avoiding eye contact', 'A', 'Hard - Authenticity.', 'Non-Verbal Communication', '2026-04-19 17:06:04', 4),
(545, 'Why is empathy important?', 'To manipulate others', 'To build trust and stronger relationships', 'To win arguments', 'To speak more', 'B', 'Medium - Human connection.', 'Emotional Intelligence', '2026-04-19 17:06:04', 4),
(546, 'What is the difference between aggressive and assertive communication?', 'Aggressive violates others\' rights; assertive respects everyone\'s rights', 'They are the same', 'Assertive is weaker', 'Aggressive is better', 'A', 'Hard - Style comparison.', 'Communication Styles', '2026-04-19 17:06:04', 4),
(547, 'How can you make your message more persuasive?', 'Using only facts', 'Combining logic, emotion, and credibility', 'Speaking fast', 'Repeating the same point', 'B', 'Hard - Persuasion principles.', 'Persuasive Communication', '2026-04-19 17:06:04', 4),
(548, 'What is \"mirroring\" in communication?', 'Copying the other person\'s body language to build rapport', 'Repeating their exact words', 'Changing topic', 'Speaking louder', 'A', 'Hard - Rapport building.', 'Advanced Techniques', '2026-04-19 17:06:04', 4),
(549, 'What should you avoid in emails?', 'Clear subject line', 'Using \"Reply All\" unnecessarily', 'Proofreading', 'Being polite', 'B', 'Medium - Common email mistake.', 'Written Communication', '2026-04-19 17:06:04', 4),
(550, 'What is the Johari Window?', 'Model for understanding self-awareness and communication', 'A type of email', 'A listening technique', 'A conflict style', 'A', 'Hard - Self-awareness tool.', 'Self-Awareness', '2026-04-19 17:06:04', 4),
(551, 'How does listening improve relationships?', 'By making the other person feel valued and understood', 'By allowing you to speak more', 'By avoiding responsibility', 'By winning discussions', 'A', 'Medium - Relationship building.', 'Active Listening', '2026-04-19 17:06:04', 4),
(552, 'What is \"framing\" in communication?', 'Presenting information in a way that influences perception', 'Using picture frames', 'Speaking loudly', 'Avoiding difficult topics', 'A', 'Hard - Advanced technique.', 'Persuasive Communication', '2026-04-19 17:06:04', 4),
(553, 'Why is body language important?', 'It often communicates more than words', 'It is not important', 'Only in meetings', 'Only for actors', 'A', 'Easy - Non-verbal impact.', 'Non-Verbal Communication', '2026-04-19 17:06:04', 4),
(554, 'What is the best way to say \"no\"?', 'Rudely', 'Clearly, politely, and with reasons', 'By avoiding the person', 'By saying yes then not doing it', 'B', 'Medium - Assertiveness.', 'Assertive Communication', '2026-04-19 17:06:04', 4),
(555, 'What does \"open-ended questions\" encourage?', 'Short yes/no answers', 'Detailed responses and deeper conversation', 'Arguments', 'Silence', 'B', 'Medium - Good questioning.', 'Questioning Techniques', '2026-04-19 17:06:04', 4),
(556, 'How can noise affect communication?', 'It improves clarity', 'It creates barriers to understanding', 'It has no effect', 'It makes messages shorter', 'B', 'Easy - Physical barrier.', 'Barriers to Communication', '2026-04-19 17:06:04', 4),
(557, 'What is \"rapport\"?', 'Connection and trust between people', 'Giving orders', 'Competing', 'Avoiding eye contact', 'A', 'Medium - Foundation of good communication.', 'Relationship Building', '2026-04-19 17:06:04', 4),
(558, 'Why should you adapt your communication style?', 'To show you are better', 'To suit the audience and situation', 'To always be formal', 'To use jargon', 'B', 'Medium - Audience-centric approach.', 'Adaptability', '2026-04-19 17:06:04', 4),
(559, 'What is \"confirmation bias\" in communication?', 'Tendency to interpret messages to confirm existing beliefs', 'Listening openly', 'Asking good questions', 'Giving balanced feedback', 'A', 'Hard - Psychological barrier.', 'Barriers to Communication', '2026-04-19 17:06:04', 4),
(560, 'How can you communicate bad news effectively?', 'Bluntly without preparation', 'With empathy, clarity, and support', 'By email only', 'By avoiding the conversation', 'B', 'Hard - Difficult conversations.', 'Difficult Conversations', '2026-04-19 17:06:04', 4),
(561, 'What is the Platinum Rule?', 'Treat others as you would like to be treated', 'Treat others as they would like to be treated', 'Treat others better than yourself', 'Ignore others', 'B', 'Hard - Advanced empathy.', 'Emotional Intelligence', '2026-04-19 17:06:04', 4),
(562, 'What does \"conciseness\" mean?', 'Using many words', 'Being brief and to the point', 'Speaking slowly', 'Using difficult vocabulary', 'B', 'Easy - Key principle.', 'Clear Communication', '2026-04-19 17:06:04', 4),
(563, 'What is the main difference between a manager and a leader?', 'Managers focus on tasks; leaders inspire and influence people', 'Leaders only manage budgets', 'They are the same', 'Managers inspire; leaders control', 'A', 'Easy - Classic distinction.', 'Leadership vs Management', '2026-04-19 17:06:16', 3),
(564, 'What is servant leadership?', 'Serving your own interests first', 'Putting the needs of the team first and helping them grow', 'Giving orders', 'Avoiding responsibility', 'B', 'Medium - Modern leadership style.', 'Leadership Styles', '2026-04-19 17:06:16', 3),
(565, 'Which is a key quality of a good leader?', 'Micromanaging', 'Vision, integrity, and empathy', 'Avoiding decisions', 'Being liked by everyone', 'B', 'Easy - Core leadership traits.', 'Leadership Qualities', '2026-04-19 17:06:16', 3),
(566, 'What does emotional intelligence help leaders with?', 'Only technical skills', 'Understanding and managing emotions of self and others', 'Increasing profits only', 'Avoiding feedback', 'B', 'Medium - Crucial for leadership.', 'Emotional Intelligence', '2026-04-19 17:06:16', 3),
(567, 'What is transformational leadership?', 'Maintaining status quo', 'Inspiring and motivating followers to achieve extraordinary outcomes', 'Strict control', 'Avoiding change', 'B', 'Medium - Change-oriented style.', 'Leadership Styles', '2026-04-19 17:06:16', 3),
(568, 'Why is delegation important?', 'To do everything yourself', 'To empower team members and develop their skills', 'To avoid work', 'To show power', 'B', 'Medium - Key leadership skill.', 'Delegation', '2026-04-19 17:06:16', 3),
(569, 'What is the best way to motivate a team?', 'Only with money', 'Through purpose, recognition, and growth opportunities', 'Fear and pressure', 'Ignoring them', 'B', 'Medium - Intrinsic motivation.', 'Motivation', '2026-04-19 17:06:16', 3),
(570, 'What does \"leading by example\" mean?', 'Telling people what to do', 'Demonstrating the behavior you expect from others', 'Avoiding difficult tasks', 'Only giving orders', 'B', 'Easy - Powerful leadership principle.', 'Leadership Principles', '2026-04-19 17:06:16', 3),
(571, 'What is situational leadership?', 'Using the same style with everyone', 'Adapting your leadership style based on the situation and team readiness', 'Always being strict', 'Never making decisions', 'B', 'Hard - Flexible approach.', 'Leadership Styles', '2026-04-19 17:06:16', 3),
(572, 'Why is trust important in leadership?', 'It is not necessary', 'It builds strong teams and improves performance', 'It slows down work', 'It creates dependency', 'B', 'Medium - Foundation of leadership.', 'Building Trust', '2026-04-19 17:06:16', 3),
(573, 'What should a leader do when facing failure?', 'Blame the team', 'Take responsibility, learn from it, and move forward', 'Hide the failure', 'Quit', 'B', 'Medium - Resilience.', 'Leadership Resilience', '2026-04-19 17:06:16', 3),
(574, 'What is coaching in leadership?', 'Giving orders', 'Helping team members develop skills and reach their potential', 'Doing the work for them', 'Only performance review', 'B', 'Medium - Development tool.', 'Coaching & Mentoring', '2026-04-19 17:06:16', 3),
(575, 'What is the difference between vision and mission?', 'Vision is long-term aspiration; mission is current purpose', 'They are the same', 'Mission is future; vision is today', 'Vision is only for marketing', 'A', 'Hard - Strategic thinking.', 'Strategic Leadership', '2026-04-19 17:06:16', 3),
(576, 'How can a leader handle conflict?', 'Avoid it', 'Address it constructively and fairly', 'Take sides immediately', 'Ignore it', 'B', 'Medium - Conflict resolution.', 'Conflict Management', '2026-04-19 17:06:16', 3),
(577, 'What is authentic leadership?', 'Pretending to be perfect', 'Being genuine, self-aware, and true to your values', 'Copying other leaders', 'Focusing only on results', 'B', 'Medium - Modern concept.', 'Authentic Leadership', '2026-04-19 17:06:16', 3),
(578, 'Why is decision-making important for leaders?', 'To avoid responsibility', 'To guide the team toward goals effectively', 'To make everyone happy', 'To delay work', 'B', 'Easy - Core responsibility.', 'Decision Making', '2026-04-19 17:06:16', 3),
(579, 'What is empowerment?', 'Giving team members authority and autonomy to make decisions', 'Doing all decisions yourself', 'Controlling everything', 'Avoiding delegation', 'A', 'Medium - Builds ownership.', 'Team Development', '2026-04-19 17:06:16', 3),
(580, 'What does \"accountability\" mean in leadership?', 'Blaming others', 'Taking ownership of results and actions', 'Avoiding difficult conversations', 'Only setting goals', 'B', 'Medium - Builds credibility.', 'Leadership Accountability', '2026-04-19 17:06:16', 3),
(581, 'How can leaders foster innovation?', 'Micromanaging', 'Encouraging creativity, risk-taking, and learning from failure', 'Punishing mistakes', 'Maintaining strict processes', 'B', 'Medium - Creating safe environment.', 'Innovative Leadership', '2026-04-19 17:06:16', 3);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(582, 'What is feedback in leadership?', 'Only annual review', 'Regular, constructive input to help people improve', 'Only criticism', 'Avoiding difficult topics', 'B', 'Easy - Growth tool.', 'Giving Feedback', '2026-04-19 17:06:16', 3),
(583, 'What is the role of a leader in change management?', 'Resisting change', 'Guiding and supporting the team through change', 'Ignoring change', 'Forcing change without explanation', 'B', 'Medium - Change leadership.', 'Change Management', '2026-04-19 17:06:16', 3),
(584, 'What is inclusive leadership?', 'Favoring certain people', 'Creating an environment where diverse voices are heard and valued', 'Only hiring similar people', 'Avoiding diversity', 'B', 'Hard - Modern leadership.', 'Inclusive Leadership', '2026-04-19 17:06:16', 3),
(585, 'Why is communication critical for leaders?', 'To control information', 'To align, inspire, and inform the team', 'To show power', 'To avoid meetings', 'B', 'Easy - Essential skill.', 'Leadership Communication', '2026-04-19 17:06:16', 3),
(586, 'What is a growth mindset in leadership?', 'Believing abilities are fixed', 'Believing abilities can be developed through effort and learning', 'Avoiding challenges', 'Fearing failure', 'B', 'Medium - Carol Dweck concept.', 'Mindset', '2026-04-19 17:06:16', 3),
(587, 'How should leaders handle difficult conversations?', 'Avoid them', 'Prepare, stay calm, and focus on facts and solutions', 'Be aggressive', 'Blame the other person', 'B', 'Hard - Critical skill.', 'Difficult Conversations', '2026-04-19 17:06:16', 3),
(588, 'What is strategic thinking?', 'Focusing only on daily tasks', 'Seeing the big picture and planning for the future', 'Avoiding planning', 'Reacting only', 'B', 'Medium - Leadership competency.', 'Strategic Leadership', '2026-04-19 17:06:16', 3),
(589, 'What does \"mentoring\" involve?', 'Doing the work for the mentee', 'Guiding and sharing experience to help someone grow', 'Only giving orders', 'Avoiding responsibility', 'B', 'Medium - Development role.', 'Mentoring', '2026-04-19 17:06:16', 3),
(590, 'Why is resilience important for leaders?', 'To give up easily', 'To recover from setbacks and inspire the team', 'To avoid challenges', 'To blame others', 'B', 'Medium - Leadership strength.', 'Resilience', '2026-04-19 17:06:16', 3),
(591, 'What is ethical leadership?', 'Doing whatever is profitable', 'Leading with integrity, honesty, and moral principles', 'Hiding mistakes', 'Favoring friends', 'B', 'Medium - Values-based leadership.', 'Ethical Leadership', '2026-04-19 17:06:16', 3),
(592, 'How can leaders build high-performing teams?', 'Micromanaging', 'Through clear goals, trust, and collaboration', 'Competition only', 'Avoiding feedback', 'B', 'Medium - Team leadership.', 'Team Leadership', '2026-04-19 17:06:16', 3),
(593, 'What is \"vulnerability\" in leadership?', 'Showing weakness', 'Being open about challenges to build trust', 'Hiding emotions', 'Never admitting mistakes', 'B', 'Hard - Brené Brown concept.', 'Authentic Leadership', '2026-04-19 17:06:16', 3),
(594, 'What should a leader do during a crisis?', 'Panic and blame', 'Stay calm, communicate clearly, and provide direction', 'Hide information', 'Delegate everything', 'B', 'Medium - Crisis leadership.', 'Crisis Management', '2026-04-19 17:06:16', 3),
(595, 'What is the difference between leadership and authority?', 'Authority is given by position; leadership is earned through influence', 'They are identical', 'Leadership is only for managers', 'Authority is better', 'A', 'Hard - Influence vs power.', 'Leadership Fundamentals', '2026-04-19 17:06:16', 3),
(596, 'Why is recognition important?', 'To waste time', 'To motivate and retain talent', 'To show weakness', 'To create competition', 'B', 'Easy - Simple but powerful.', 'Motivation', '2026-04-19 17:06:16', 3),
(597, 'What is \"quiet leadership\"?', 'Speaking loudly', 'Leading through calm influence and deep thinking', 'Avoiding decisions', 'Only following', 'B', 'Hard - Subtle influence.', 'Leadership Styles', '2026-04-19 17:06:16', 3),
(598, 'How can leaders develop future leaders?', 'Keeping all knowledge to themselves', 'Through mentoring, delegation, and opportunities', 'Avoiding promotion', 'Only through training', 'B', 'Medium - Succession planning.', 'Developing Others', '2026-04-19 17:06:16', 3),
(599, 'What is the Pygmalion effect?', 'Low expectations lead to poor performance', 'High expectations can improve performance', 'Ignoring people', 'Punishing mistakes', 'B', 'Hard - Self-fulfilling prophecy.', 'Motivation', '2026-04-19 17:06:16', 3),
(600, 'What does \"adaptability\" mean for leaders?', 'Sticking to old methods', 'Adjusting to new situations and challenges', 'Avoiding change', 'Only following rules', 'B', 'Medium - Key in volatile world.', 'Adaptability', '2026-04-19 17:06:16', 3),
(601, 'Why is self-awareness important?', 'To ignore weaknesses', 'To understand your strengths and blind spots', 'To control others', 'To avoid feedback', 'B', 'Medium - Foundation of growth.', 'Self-Awareness', '2026-04-19 17:06:16', 3),
(602, 'What is \"influencing without authority\"?', 'Using only formal power', 'Persuading others through trust and relationships', 'Giving orders', 'Avoiding responsibility', 'B', 'Hard - Advanced leadership skill.', 'Influence', '2026-04-19 17:06:16', 3),
(603, 'What should leaders focus on for long-term success?', 'Short-term wins only', 'Sustainable growth, people development, and vision', 'Cutting costs only', 'Personal promotion', 'B', 'Medium - Strategic view.', 'Strategic Leadership', '2026-04-19 17:06:16', 3),
(604, 'How can leaders promote diversity and inclusion?', 'Ignoring differences', 'Actively creating equitable opportunities and valuing different perspectives', 'Hiring only similar people', 'Avoiding difficult topics', 'B', 'Medium - Inclusive culture.', 'Inclusive Leadership', '2026-04-19 17:06:16', 3),
(605, 'What is \"360-degree feedback\"?', 'Feedback only from boss', 'Feedback from multiple sources (peers, subordinates, self)', 'Only self-evaluation', 'Only customer feedback', 'B', 'Hard - Comprehensive assessment.', 'Feedback', '2026-04-19 17:06:16', 3),
(606, 'What does \"integrity\" mean in leadership?', 'Doing the right thing even when no one is watching', 'Achieving goals at any cost', 'Hiding mistakes', 'Favoring certain people', 'A', 'Easy - Fundamental value.', 'Ethical Leadership', '2026-04-19 17:06:16', 3),
(607, 'What is the main goal of team building?', 'To compete internally', 'To improve collaboration, trust, and performance', 'To waste time', 'To show individual skills only', 'B', 'Easy - Core objective.', 'Team Building Basics', '2026-04-19 17:06:35', 12),
(608, 'What is a high-performing team?', 'Group of individuals working independently', 'A cohesive group that achieves goals through collaboration', 'Team with strict hierarchy only', 'Team that avoids conflict', 'B', 'Medium - Characteristics.', 'High-Performing Teams', '2026-04-19 17:06:35', 12),
(609, 'Which is a key element of successful teams?', 'Lack of trust', 'Clear roles, goals, and open communication', 'Competition between members', 'Avoiding feedback', 'B', 'Easy - Foundation.', 'Team Dynamics', '2026-04-19 17:06:35', 12),
(610, 'What is psychological safety?', 'Feeling safe to take risks and speak up without fear of negative consequences', 'Physical safety only', 'Avoiding all mistakes', 'Strict rules', 'A', 'Medium - Google study concept.', 'Team Culture', '2026-04-19 17:06:35', 12),
(611, 'Why is trust important in teams?', 'To create competition', 'To enable collaboration and risk-taking', 'To reduce communication', 'To increase micromanagement', 'B', 'Easy - Essential for teamwork.', 'Building Trust', '2026-04-19 17:06:35', 12),
(612, 'What is a team norm?', 'Unwritten rules and expectations for behavior', 'Only official policies', 'Individual goals', 'Competition rules', 'A', 'Medium - Shapes culture.', 'Team Norms', '2026-04-19 17:06:35', 12),
(613, 'What does Tuckman\'s model describe?', 'Stages of team development: Forming, Storming, Norming, Performing, Adjourning', 'Budget planning', 'Individual performance', 'Marketing strategy', 'A', 'Hard - Team development stages.', 'Team Development', '2026-04-19 17:06:35', 12),
(614, 'How can icebreakers help?', 'They waste time', 'They help team members get to know each other and build rapport', 'They create conflict', 'They replace work', 'B', 'Easy - Common team building activity.', 'Icebreakers', '2026-04-19 17:06:35', 12),
(615, 'What is conflict in teams?', 'Always negative', 'Natural and can be constructive if managed well', 'Should be avoided completely', 'Only caused by one person', 'B', 'Medium - Healthy vs unhealthy conflict.', 'Conflict in Teams', '2026-04-19 17:06:35', 12),
(616, 'What is collaboration?', 'Working together toward a common goal', 'Competing against each other', 'Working in isolation', 'Following orders only', 'A', 'Easy - Core of teamwork.', 'Collaboration', '2026-04-19 17:06:35', 12),
(617, 'What role does diversity play in teams?', 'Creates problems only', 'Brings different perspectives and better solutions', 'Should be avoided', 'Slows down decisions', 'B', 'Medium - Strength of teams.', 'Diversity in Teams', '2026-04-19 17:06:35', 12),
(618, 'How can you improve team communication?', 'Reducing meetings', 'Encouraging open, honest, and regular communication', 'Using only email', 'Avoiding difficult topics', 'B', 'Medium - Communication practices.', 'Team Communication', '2026-04-19 17:06:35', 12),
(619, 'What is a team charter?', 'Document outlining goals, roles, and ways of working', 'Budget document', 'Individual contract', 'Marketing plan', 'A', 'Medium - Alignment tool.', 'Team Alignment', '2026-04-19 17:06:35', 12),
(620, 'Why is recognition important in teams?', 'To create jealousy', 'To motivate and reinforce positive behavior', 'To reduce productivity', 'To show weakness', 'B', 'Easy - Simple motivator.', 'Recognition', '2026-04-19 17:06:35', 12),
(621, 'What is \"groupthink\"?', 'Encouraging diverse opinions', 'Tendency to conform and suppress dissenting views', 'Healthy debate', 'Individual thinking', 'B', 'Hard - Dangerous team dynamic.', 'Team Decision Making', '2026-04-19 17:06:35', 12),
(622, 'How can team building activities help remote teams?', 'They have no benefit', 'They build connection and trust despite distance', 'They replace all work', 'They create more problems', 'B', 'Medium - Modern challenge.', 'Remote Team Building', '2026-04-19 17:06:35', 12),
(623, 'What is the role of a team leader?', 'To do all the work', 'To facilitate, support, and guide the team', 'To control everything', 'To avoid responsibility', 'B', 'Medium - Servant approach.', 'Team Leadership', '2026-04-19 17:06:35', 12),
(624, 'What is accountability in teams?', 'Blaming others', 'Taking ownership of commitments and results', 'Avoiding deadlines', 'Working alone', 'B', 'Medium - Shared responsibility.', 'Team Accountability', '2026-04-19 17:06:35', 12),
(625, 'How do you handle free riders in a team?', 'Ignore them', 'Address the issue openly and clarify expectations', 'Punish the whole team', 'Reduce goals', 'B', 'Hard - Common challenge.', 'Team Challenges', '2026-04-19 17:06:35', 12),
(626, 'What is \"synergy\"?', '1+1=2', 'When team performance exceeds the sum of individual efforts', 'Individual competition', 'Working in silos', 'B', 'Medium - Team advantage.', 'Team Performance', '2026-04-19 17:06:35', 12),
(627, 'What should teams celebrate?', 'Only big successes', 'Both big wins and small milestones', 'Nothing', 'Only individual achievements', 'B', 'Easy - Builds morale.', 'Celebrating Success', '2026-04-19 17:06:35', 12),
(628, 'What is the benefit of retrospectives?', 'To blame people', 'To reflect on what went well and how to improve', 'To avoid change', 'To increase workload', 'B', 'Medium - Continuous improvement.', 'Team Retrospectives', '2026-04-19 17:06:35', 12),
(629, 'How can you build psychological safety?', 'Punishing mistakes', 'Encouraging open dialogue and learning from failure', 'Strict hierarchy', 'Avoiding feedback', 'B', 'Hard - Key to innovation.', 'Psychological Safety', '2026-04-19 17:06:35', 12),
(630, 'What is cross-functional collaboration?', 'Working only within your department', 'Teams from different functions working together', 'Avoiding other departments', 'Competition between departments', 'B', 'Medium - Modern organizations.', 'Collaboration', '2026-04-19 17:06:35', 12),
(631, 'Why is clear goal setting important?', 'To create confusion', 'To align efforts and measure progress', 'To reduce motivation', 'To increase workload', 'B', 'Easy - SMART goals concept.', 'Goal Setting', '2026-04-19 17:06:35', 12),
(632, 'What is a \"team contract\"?', 'Legal document only', 'Shared agreement on values, behaviors, and expectations', 'Salary agreement', 'Project timeline only', 'B', 'Medium - Commitment tool.', 'Team Norms', '2026-04-19 17:06:35', 12),
(633, 'How does feedback help teams?', 'Creates conflict only', 'Drives improvement and alignment', 'Should be avoided', 'Only for managers', 'B', 'Medium - Growth mechanism.', 'Feedback in Teams', '2026-04-19 17:06:35', 12),
(634, 'What is \"belonging\" in teams?', 'Feeling included and valued', 'Only attending meetings', 'Competing for position', 'Working remotely only', 'A', 'Medium - Important for engagement.', 'Team Culture', '2026-04-19 17:06:35', 12),
(635, 'What should you do after a team building activity?', 'Forget about it', 'Reflect and apply learnings to daily work', 'Do more activities only', 'Criticize the activity', 'B', 'Medium - Transfer of learning.', 'Follow-up', '2026-04-19 17:06:35', 12),
(636, 'What is the difference between a group and a team?', 'Group is just people together; team has shared goals and interdependence', 'They are the same', 'Team has no leader', 'Group is better', 'A', 'Medium - Important distinction.', 'Team vs Group', '2026-04-19 17:06:35', 12),
(637, 'How can virtual team building be effective?', 'Using only video calls without activities', 'Through interactive online games, workshops, and regular check-ins', 'Avoiding any interaction', 'Only through email', 'B', 'Medium - Remote challenge.', 'Virtual Teams', '2026-04-19 17:06:35', 12),
(638, 'What is \"storming\" stage in Tuckman\'s model?', 'Initial polite phase', 'Conflict and competition phase', 'High performance phase', 'Ending phase', 'B', 'Hard - Team development.', 'Team Development', '2026-04-19 17:06:35', 12),
(639, 'Why is diversity of thought valuable?', 'Creates conflict only', 'Leads to better decisions and innovation', 'Slows down work', 'Should be minimized', 'B', 'Medium - Cognitive diversity.', 'Diversity', '2026-04-19 17:06:35', 12),
(640, 'What is shared leadership?', 'Only one leader', 'Leadership responsibilities distributed among team members', 'Strict hierarchy', 'No leadership', 'B', 'Hard - Modern concept.', 'Shared Leadership', '2026-04-19 17:06:35', 12),
(641, 'How do you measure team success?', 'Only by individual performance', 'By both results and team health (collaboration, satisfaction)', 'Only by speed', 'Only by budget', 'B', 'Medium - Balanced metrics.', 'Team Performance', '2026-04-19 17:06:35', 12),
(642, 'What is \"team cohesion\"?', 'How well team members stick together and support each other', 'Only social events', 'Competition', 'Individual focus', 'A', 'Medium - Group dynamics.', 'Team Cohesion', '2026-04-19 17:06:35', 12),
(643, 'What should leaders do to support team building?', 'Ignore team dynamics', 'Facilitate activities and model positive behaviors', 'Micromanage', 'Avoid difficult conversations', 'B', 'Medium - Leadership role.', 'Leadership in Team Building', '2026-04-19 17:06:35', 12),
(644, 'What is the benefit of debriefing after activities?', 'To waste time', 'To extract lessons and improve future performance', 'To criticize participants', 'To avoid reflection', 'B', 'Medium - Learning transfer.', 'Debriefing', '2026-04-19 17:06:35', 12),
(645, 'How can fun activities improve teams?', 'They distract from work', 'They reduce stress and build relationships', 'They replace all serious work', 'They create division', 'B', 'Easy - Engagement tool.', 'Fun in Team Building', '2026-04-19 17:06:35', 12),
(646, 'What is \"interdependence\" in teams?', 'Everyone works alone', 'Team members rely on each other to succeed', 'Competition', 'Avoiding help', 'B', 'Medium - Team characteristic.', 'Team Dynamics', '2026-04-19 17:06:35', 12),
(647, 'Why is celebrating diversity important?', 'To create division', 'To leverage different strengths and perspectives', 'To slow down decisions', 'To maintain status quo', 'B', 'Medium - Inclusive teams.', 'Diversity', '2026-04-19 17:06:35', 12),
(648, 'What is a common team building challenge?', 'Too much trust', 'Lack of clear goals or roles', 'Too many celebrations', 'Perfect communication always', 'B', 'Medium - Real-world issue.', 'Team Challenges', '2026-04-19 17:06:35', 12),
(649, 'How can teams maintain momentum after team building?', 'Do nothing', 'Integrate learnings into daily routines and follow up regularly', 'Only do more activities', 'Forget everything', 'B', 'Hard - Sustainability.', 'Sustaining Team Building', '2026-04-19 17:06:35', 12),
(650, 'What is the role of empathy in team building?', 'Not important', 'Helps understand and support each other better', 'Creates weakness', 'Should be avoided', 'B', 'Medium - Emotional connection.', 'Empathy in Teams', '2026-04-19 17:06:35', 12),
(651, 'What does \"performing\" stage mean?', 'High productivity and effective collaboration', 'Initial confusion', 'Conflict phase', 'Project ending', 'A', 'Hard - Tuckman\'s model.', 'Team Development', '2026-04-19 17:06:35', 12),
(652, 'What is one outcome of successful team building?', 'Increased trust, collaboration, and overall performance', 'More individual competition', 'Higher turnover', 'Less communication', 'A', 'Easy - Desired result.', 'Benefits of Team Building', '2026-04-19 17:06:35', 12),
(653, 'What is the main goal of time management?', 'To work more hours', 'To use time effectively to achieve goals and reduce stress', 'To avoid all tasks', 'To multitask constantly', 'B', 'Easy - Core purpose.', 'Time Management Basics', '2026-04-19 17:06:44', 10),
(654, 'What is the Eisenhower Matrix?', 'Tool to prioritize tasks based on urgency and importance', 'Calendar app', 'Email tool', 'Budget planner', 'A', 'Medium - Famous prioritization method.', 'Prioritization', '2026-04-19 17:06:44', 10),
(655, 'What does \"procrastination\" mean?', 'Doing tasks immediately', 'Delaying important tasks', 'Planning perfectly', 'Delegating everything', 'B', 'Easy - Common challenge.', 'Overcoming Procrastination', '2026-04-19 17:06:44', 10),
(656, 'Which technique involves working in focused intervals with short breaks?', 'Multitasking', 'Pomodoro Technique', 'Doing everything at once', 'Ignoring deadlines', 'B', 'Easy - Popular method.', 'Focus Techniques', '2026-04-19 17:06:44', 10),
(657, 'What is the 80/20 rule (Pareto Principle)?', '80% of results come from 20% of efforts', 'Work 80 hours per week', 'Spend 80% on planning', '80% of tasks are urgent', 'A', 'Medium - Efficiency principle.', 'Prioritization', '2026-04-19 17:06:44', 10),
(658, 'Why is saying \"no\" important?', 'To offend people', 'To protect your time and focus on priorities', 'To do more work', 'To avoid responsibility', 'B', 'Medium - Boundary setting.', 'Saying No', '2026-04-19 17:06:44', 10),
(659, 'What is a \"to-do list\" best used for?', 'To overwhelm yourself', 'To organize and prioritize daily tasks', 'To replace planning', 'To procrastinate', 'B', 'Easy - Basic tool.', 'Task Management', '2026-04-19 17:06:44', 10),
(660, 'What does \"time blocking\" involve?', 'Doing random tasks', 'Scheduling specific blocks of time for tasks', 'Multitasking', 'Avoiding calendars', 'B', 'Medium - Calendar-based planning.', 'Scheduling', '2026-04-19 17:06:44', 10),
(661, 'How can you reduce distractions?', 'Keep phone notifications on', 'Create a focused environment and limit interruptions', 'Check email constantly', 'Work in noisy places', 'B', 'Medium - Practical strategy.', 'Distraction Management', '2026-04-19 17:06:44', 10),
(662, 'What is \"multitasking\" actually?', 'Doing multiple tasks efficiently', 'Switching between tasks which reduces productivity', 'Saving time', 'Increasing focus', 'B', 'Hard - Myth of productivity.', 'Focus', '2026-04-19 17:06:44', 10),
(663, 'Why is setting goals important for time management?', 'To create pressure', 'To provide direction and motivation', 'To waste time planning', 'To avoid work', 'B', 'Easy - SMART goals.', 'Goal Setting', '2026-04-19 17:06:44', 10),
(664, 'What is the difference between urgent and important tasks?', 'Urgent requires immediate attention; important contributes to long-term goals', 'They are the same', 'Important is always urgent', 'Urgent is never important', 'A', 'Medium - Eisenhower Matrix.', 'Prioritization', '2026-04-19 17:06:44', 10),
(665, 'What is \"batch processing\"?', 'Doing similar tasks together to save time', 'Doing one task at a time', 'Avoiding tasks', 'Delegating everything', 'A', 'Medium - Efficiency technique.', 'Task Management', '2026-04-19 17:06:44', 10),
(666, 'How can delegation help with time management?', 'To avoid all responsibility', 'To free up your time for higher-value tasks', 'To overload others', 'To show you can\'t do it', 'B', 'Medium - Leverage others.', 'Delegation', '2026-04-19 17:06:44', 10),
(667, 'What is the benefit of routines?', 'They make life boring', 'They reduce decision fatigue and improve efficiency', 'They waste time', 'They increase stress', 'B', 'Medium - Habit building.', 'Daily Routines', '2026-04-19 17:06:44', 10),
(668, 'What should you do with your energy levels?', 'Ignore them', 'Schedule demanding tasks during your peak energy times', 'Do hard tasks when tired', 'Work constantly without breaks', 'B', 'Hard - Ultradian rhythms.', 'Energy Management', '2026-04-19 17:06:44', 10),
(669, 'What is \"Parkinson\'s Law\"?', 'Work expands to fill the time available', 'Work shrinks with more time', 'Time is unlimited', 'Planning is useless', 'A', 'Hard - Time perception.', 'Time Perception', '2026-04-19 17:06:44', 10),
(670, 'Why is reviewing your day important?', 'To criticize yourself', 'To learn what worked and improve tomorrow', 'To waste time', 'To avoid planning', 'B', 'Medium - Reflection.', 'Daily Review', '2026-04-19 17:06:44', 10),
(671, 'What is \"single-tasking\"?', 'Doing one task at a time with full focus', 'Switching constantly', 'Avoiding work', 'Multitasking', 'A', 'Medium - Better than multitasking.', 'Focus', '2026-04-19 17:06:44', 10),
(672, 'How can you overcome perfectionism?', 'Aim for \"good enough\" on most tasks', 'Spend unlimited time on every detail', 'Avoid starting', 'Compare to others', 'A', 'Hard - Common barrier.', 'Perfectionism', '2026-04-19 17:06:44', 10),
(673, 'What is a \"priority list\"?', 'Doing everything', 'Ranking tasks by importance', 'Ignoring deadlines', 'Random order', 'B', 'Easy - Basic prioritization.', 'Prioritization', '2026-04-19 17:06:44', 10),
(674, 'What does \"deep work\" mean?', 'Focused, distraction-free work on cognitively demanding tasks', 'Checking email', 'Attending meetings', 'Multitasking', 'A', 'Hard - Cal Newport concept.', 'Deep Work', '2026-04-19 17:06:44', 10),
(675, 'Why is planning your week on Sunday useful?', 'To waste weekend', 'To start the week with clarity and priorities', 'To avoid Monday stress', 'To increase workload', 'B', 'Medium - Weekly planning.', 'Weekly Planning', '2026-04-19 17:06:44', 10),
(676, 'What is \"time waster\"?', 'High-value activity', 'Activity that consumes time without adding value', 'Important meeting', 'Focused work', 'B', 'Easy - Identify and reduce.', 'Time Wasters', '2026-04-19 17:06:44', 10),
(677, 'How can you use the \"2-minute rule\"?', 'If a task takes less than 2 minutes, do it immediately', 'Wait 2 minutes before starting', 'Take 2-minute breaks only', 'Do everything in 2 minutes', 'A', 'Medium - David Allen concept.', 'Task Management', '2026-04-19 17:06:44', 10),
(678, 'What is the benefit of saying no?', 'To do more low-value tasks', 'To protect time for high-priority activities', 'To please everyone', 'To increase stress', 'B', 'Medium - Boundary setting.', 'Boundaries', '2026-04-19 17:06:44', 10),
(679, 'What is \"flow state\"?', 'Complete absorption in an activity with high focus', 'Feeling tired', 'Multitasking', 'Procrastinating', 'A', 'Hard - Optimal experience.', 'Focus', '2026-04-19 17:06:44', 10),
(680, 'How should you handle email efficiently?', 'Check constantly', 'Schedule specific times and process in batches', 'Reply to everything immediately', 'Ignore email', 'B', 'Medium - Inbox management.', 'Email Management', '2026-04-19 17:06:44', 10),
(681, 'What is \"eating the frog\"?', 'Doing the most difficult task first thing in the morning', 'Avoiding hard tasks', 'Eating during work', 'Delegating everything', 'A', 'Medium - Mark Twain concept.', 'Prioritization', '2026-04-19 17:06:44', 10),
(682, 'Why is tracking your time useful?', 'To feel guilty', 'To identify where your time actually goes and improve', 'To waste more time', 'To avoid planning', 'B', 'Medium - Awareness tool.', 'Time Tracking', '2026-04-19 17:06:44', 10),
(683, 'What is the difference between being busy and being productive?', 'Busy means doing many things; productive means achieving important results', 'They are the same', 'Productive is doing less', 'Busy is better', 'A', 'Medium - Key insight.', 'Productivity', '2026-04-19 17:06:44', 10),
(684, 'What is \" Pomodoro \" break for?', 'To check social media', 'To rest and maintain focus for next session', 'To quit working', 'To multitask', 'B', 'Easy - Technique detail.', 'Pomodoro', '2026-04-19 17:06:44', 10),
(685, 'How can you avoid decision fatigue?', 'Make many small decisions', 'Reduce trivial decisions by routines and planning', 'Decide everything at the end', 'Avoid all decisions', 'B', 'Hard - Mental energy.', 'Decision Fatigue', '2026-04-19 17:06:44', 10),
(686, 'What is \"buffer time\"?', 'Extra time scheduled between tasks to handle overruns', 'Wasted time', 'Meeting time only', 'Break time only', 'A', 'Medium - Realistic scheduling.', 'Scheduling', '2026-04-19 17:06:44', 10),
(687, 'Why is sleep important for time management?', 'It wastes time', 'Good sleep improves focus and decision-making', 'You can work 24 hours', 'It reduces productivity', 'B', 'Medium - Energy foundation.', 'Energy Management', '2026-04-19 17:06:44', 10),
(688, 'What is \"task batching\"?', 'Doing one task at a time', 'Grouping similar tasks together', 'Avoiding tasks', 'Delegating only', 'B', 'Medium - Efficiency.', 'Task Management', '2026-04-19 17:06:44', 10),
(689, 'How can you use technology for time management?', 'Only for entertainment', 'With apps like calendars, to-do lists, and focus tools', 'To distract yourself', 'To avoid planning', 'B', 'Medium - Tools support.', 'Tools & Apps', '2026-04-19 17:06:44', 10),
(690, 'What is the \"Zeigarnik effect\"?', 'Forgetting completed tasks', 'Remembering unfinished tasks more', 'Forgetting everything', 'Planning perfectly', 'B', 'Hard - Psychological effect.', 'Psychology of Time', '2026-04-19 17:06:44', 10),
(691, 'What should you do at the end of the day?', 'Continue working', 'Review accomplishments and plan tomorrow', 'Worry about unfinished work', 'Ignore everything', 'B', 'Medium - Daily close.', 'Daily Review', '2026-04-19 17:06:44', 10),
(692, 'How can you manage meetings effectively?', 'Schedule unnecessary meetings', 'Have clear agendas, time limits, and only necessary attendees', 'Make them as long as possible', 'Avoid all meetings', 'B', 'Medium - Meeting culture.', 'Meeting Management', '2026-04-19 17:06:44', 10),
(693, 'What is \"time audit\"?', 'Checking clock', 'Tracking how you spend your time to find improvements', 'Avoiding work', 'Planning only', 'B', 'Medium - Self-awareness.', 'Time Audit', '2026-04-19 17:06:44', 10),
(694, 'Why is rest and recovery important?', 'It is lazy', 'It prevents burnout and sustains long-term productivity', 'You should never rest', 'It wastes time', 'B', 'Medium - Sustainable approach.', 'Work-Life Balance', '2026-04-19 17:06:44', 10),
(695, 'What is the best way to start your day?', 'Checking phone immediately', 'With a morning routine and clear priorities', 'Rushing into emails', 'Sleeping longer', 'B', 'Medium - Morning ritual.', 'Morning Routine', '2026-04-19 17:06:44', 10),
(696, 'How can you handle interruptions?', 'Allow all interruptions', 'Set boundaries and handle them in batches when possible', 'Never take breaks', 'Multitask more', 'B', 'Medium - Practical strategy.', 'Interruptions', '2026-04-19 17:06:44', 10),
(697, 'What is \"outcome-focused\" planning?', 'Focusing on activities only', 'Defining desired results first then planning actions', 'Doing random tasks', 'Avoiding goals', 'B', 'Hard - Effective approach.', 'Goal-Oriented Planning', '2026-04-19 17:06:44', 10),
(698, 'What does \"work-life integration\" mean?', 'Working 24/7', 'Blending work and personal life in a balanced way', 'Separating completely', 'Ignoring personal life', 'B', 'Medium - Modern view.', 'Work-Life Balance', '2026-04-19 17:06:44', 10),
(699, 'What is Redis primarily used for?', 'Relational database', 'In-memory data structure store used for caching and real-time applications', 'File storage system', 'Message queue only', 'B', 'Easy - Redis is an in-memory key-value store.', 'Redis Basics', '2026-04-19 17:12:03', 26),
(700, 'Which data structure in Redis is best for implementing a simple cache?', 'List', 'String', 'Set', 'Hash', 'B', 'Easy - Simple key-value storage.', 'Caching Strategies', '2026-04-19 17:12:03', 26),
(701, 'What does TTL stand for in Redis?', 'Time To Live', 'Time To Load', 'Temporary Task List', 'Total Time Limit', 'A', 'Easy - Controls how long a key exists.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(702, 'Which command sets a key with an expiration time?', 'SET', 'SETEX', 'EXPIRE', 'Both B and C', 'D', 'Medium - Common caching pattern.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(703, 'What is cache invalidation?', 'Adding more data to cache', 'Removing or updating stale data in the cache', 'Increasing cache size', 'Disabling Redis', 'B', 'Medium - Critical for data consistency.', 'Caching Strategies', '2026-04-19 17:12:03', 26),
(704, 'What is the advantage of Redis over traditional databases for caching?', 'Persistent storage only', 'Extremely high speed due to in-memory storage', 'Complex querying', 'ACID compliance by default', 'B', 'Easy - Main benefit of caching.', 'Redis vs Databases', '2026-04-19 17:12:03', 26),
(705, 'Which Redis data type is suitable for storing user sessions?', 'String', 'Hash', 'Sorted Set', 'HyperLogLog', 'B', 'Medium - Field-value pairs.', 'Use Cases', '2026-04-19 17:12:03', 26),
(706, 'What happens when Redis runs out of memory?', 'It automatically deletes all keys', 'It uses eviction policies like LRU or LFU', 'It crashes immediately', 'It switches to disk', 'B', 'Hard - Memory management.', 'Redis Configuration', '2026-04-19 17:12:03', 26),
(707, 'What is cache-aside (lazy loading) pattern?', 'Loading data into cache only when requested', 'Preloading all data at startup', 'Writing directly to database only', 'Using Redis as primary database', 'A', 'Medium - Common caching strategy.', 'Caching Patterns', '2026-04-19 17:12:03', 26),
(708, 'Which command retrieves all keys matching a pattern?', 'GET', 'KEYS', 'SCAN', 'Both B and C (SCAN is preferred)', 'D', 'Hard - Production best practice.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(709, 'What is Write-Through caching?', 'Writing data to cache and database simultaneously', 'Writing only to cache', 'Reading from cache only', 'Deleting cache on write', 'A', 'Medium - Consistency strategy.', 'Caching Strategies', '2026-04-19 17:12:03', 26),
(710, 'What is the purpose of Redis Pub/Sub?', 'Persistent storage', 'Real-time messaging between publishers and subscribers', 'Session management', 'Caching only', 'B', 'Medium - Messaging feature.', 'Redis Features', '2026-04-19 17:12:03', 26),
(711, 'Which eviction policy removes the least recently used keys?', 'allkeys-lru', 'volatile-lru', 'noeviction', 'allkeys-random', 'A', 'Hard - Memory management.', 'Redis Configuration', '2026-04-19 17:12:03', 26),
(712, 'What is Redis Cluster used for?', 'Single instance only', 'Horizontal scaling and high availability', 'Only backup', 'Only monitoring', 'B', 'Medium - Production deployment.', 'Redis Architecture', '2026-04-19 17:12:03', 26),
(713, 'What does the command \"INCR\" do?', 'Decrements a number', 'Increments a number by 1', 'Sets a string value', 'Deletes a key', 'B', 'Easy - Atomic counter.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(714, 'What is cache stampede (thundering herd)?', 'Too many requests hitting the cache at once after expiration', 'Cache being too fast', 'Database failure', 'Redis crash', 'A', 'Hard - Advanced caching problem.', 'Caching Challenges', '2026-04-19 17:12:03', 26),
(715, 'How can you solve cache stampede?', 'Using locks or mutex, or pre-computing expiration', 'Ignoring the problem', 'Disabling cache', 'Using only database', 'A', 'Hard - Mitigation strategy.', 'Advanced Caching', '2026-04-19 17:12:03', 26),
(716, 'What is Redis Sentinel used for?', 'Monitoring and automatic failover for Redis instances', 'Only caching', 'Data visualization', 'Load balancing only', 'A', 'Medium - High availability.', 'Redis High Availability', '2026-04-19 17:12:03', 26),
(717, 'Which data type is ideal for implementing leaderboards?', 'String', 'Sorted Set', 'List', 'Set', 'B', 'Medium - Score-based ordering.', 'Use Cases', '2026-04-19 17:12:03', 26),
(718, 'What is the difference between Redis and Memcached?', 'Redis supports more data structures and persistence', 'Memcached is always better', 'They are identical', 'Redis has no persistence', 'A', 'Medium - Comparison.', 'Redis vs Others', '2026-04-19 17:12:03', 26),
(719, 'What command is used to set multiple keys at once?', 'SET', 'MSET', 'MULTISET', 'SETALL', 'B', 'Medium - Batch operation.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(720, 'What is Read-Through caching?', 'Application reads from cache, if miss, loads from DB and caches it', 'Writing through cache', 'Direct database access only', 'Preloading all data', 'A', 'Medium - Common pattern.', 'Caching Patterns', '2026-04-19 17:12:03', 26),
(721, 'What does \"PERSIST\" command do?', 'Makes a key permanent by removing its TTL', 'Deletes the key', 'Renames the key', 'Moves key to another database', 'A', 'Medium - TTL management.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(722, 'What is Redis Streams used for?', 'Simple caching', 'Log-like data structure for messaging and event sourcing', 'Session storage only', 'Counter only', 'B', 'Hard - Advanced feature.', 'Redis Advanced', '2026-04-19 17:12:03', 26),
(723, 'Why is compression sometimes used with Redis?', 'To reduce memory usage for large values', 'To make Redis slower', 'To increase network traffic', 'To disable persistence', 'A', 'Hard - Optimization technique.', 'Performance Tuning', '2026-04-19 17:12:03', 26),
(724, 'What is the recommended way to invalidate cache on data update?', 'Cache-Aside with proper invalidation or Write-Through', 'Never invalidate', 'Delete entire cache', 'Ignore updates', 'A', 'Medium - Consistency strategy.', 'Caching Strategies', '2026-04-19 17:12:03', 26),
(725, 'What does \"EXPIREAT\" command use?', 'Relative time in seconds', 'Absolute Unix timestamp', 'Milliseconds only', 'String value', 'B', 'Hard - Precise expiration.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(726, 'What is RedisJSON module used for?', 'Storing and querying JSON documents natively', 'Only strings', 'Image storage', 'Video processing', 'A', 'Hard - Modern Redis capability.', 'Redis Modules', '2026-04-19 17:12:03', 26),
(727, 'What is a common anti-pattern in Redis caching?', 'Using Redis as primary database for all data', 'Using it only for hot data', 'Setting appropriate TTLs', 'Monitoring memory usage', 'A', 'Hard - Best practices.', 'Anti-Patterns', '2026-04-19 17:12:03', 26),
(728, 'What command performs atomic operations on multiple keys?', 'MULTI / EXEC', 'PIPELINE', 'WATCH', 'Both A and B', 'D', 'Hard - Transactions and pipelining.', 'Redis Transactions', '2026-04-19 17:12:03', 26),
(729, 'What is \"cache hit ratio\"?', 'Percentage of requests served from cache', 'Number of keys in Redis', 'Memory usage percentage', 'Network latency', 'A', 'Medium - Key performance metric.', 'Monitoring', '2026-04-19 17:12:03', 26),
(730, 'How do you monitor Redis performance?', 'Using INFO command, Redis Exporter, or Redis Insight', 'Only by checking logs', 'Ignoring monitoring', 'Using database tools only', 'A', 'Medium - Observability.', 'Monitoring & Tuning', '2026-04-19 17:12:03', 26),
(731, 'What is RediSearch module for?', 'Full-text search and secondary indexing', 'Simple key-value only', 'Image recognition', 'Video streaming', 'A', 'Hard - Advanced capability.', 'Redis Modules', '2026-04-19 17:12:03', 26),
(732, 'What is the best practice for key naming?', 'Using simple numbers', 'Using meaningful, namespaced keys (e.g., user:123:profile)', 'Using very long random strings', 'Using special characters only', 'B', 'Medium - Maintainability.', 'Best Practices', '2026-04-19 17:12:03', 26),
(733, 'What does \"LRU\" eviction policy stand for?', 'Least Recently Used', 'Last Recently Updated', 'Long Running Usage', 'Least Required Usage', 'A', 'Medium - Memory management.', 'Redis Configuration', '2026-04-19 17:12:03', 26),
(734, 'What is Redis Bitmaps used for?', 'Storing boolean flags efficiently', 'Complex JSON', 'Large text', 'Images', 'A', 'Hard - Space-efficient structure.', 'Redis Data Types', '2026-04-19 17:12:03', 26),
(735, 'What is the purpose of Redis Bloom Filter?', 'To test membership with possible false positives but no false negatives', 'Exact matching only', 'Image storage', 'Full-text search', 'A', 'Hard - Probabilistic data structure.', 'Advanced Redis', '2026-04-19 17:12:03', 26),
(736, 'What is a recommended cache expiration strategy?', 'Short TTL for volatile data, longer for stable data', 'Infinite TTL for everything', 'No expiration', 'Random expiration', 'A', 'Medium - Practical approach.', 'Caching Strategies', '2026-04-19 17:12:03', 26),
(737, 'What command is used for bulk deletion?', 'DEL', 'UNLINK', 'FLUSHALL', 'Both A and B (UNLINK is non-blocking)', 'D', 'Hard - Production safety.', 'Redis Commands', '2026-04-19 17:12:03', 26),
(738, 'What is \"cache penetration\"?', 'Requesting non-existent data that always hits the database', 'Too many cache hits', 'Cache being full', 'Network failure', 'A', 'Hard - Caching problem.', 'Caching Challenges', '2026-04-19 17:12:03', 26),
(739, 'How to mitigate cache penetration?', 'Using Bloom filters or caching negative responses with short TTL', 'Ignoring the requests', 'Disabling cache', 'Using only database', 'A', 'Hard - Advanced solution.', 'Advanced Caching', '2026-04-19 17:12:03', 26),
(740, 'What is RedisGears used for?', 'Serverless functions and data processing inside Redis', 'Only caching', 'Image processing', 'Backup only', 'A', 'Hard - Event-driven processing.', 'Redis Modules', '2026-04-19 17:12:03', 26),
(741, 'What is the difference between RDB and AOF persistence?', 'RDB is snapshot; AOF is append-only log', 'They are the same', 'RDB is slower', 'AOF has no durability', 'A', 'Hard - Persistence options.', 'Redis Persistence', '2026-04-19 17:12:03', 26),
(742, 'What is a good use case for Redis Lists?', 'Queues and stacks (LPUSH/RPOP)', 'Simple counters', 'JSON documents', 'Leaderboards', 'A', 'Medium - Use case.', 'Redis Data Types', '2026-04-19 17:12:03', 26),
(743, 'What does \"MEMORY USAGE\" command show?', 'Memory used by a specific key', 'Total server memory', 'CPU usage', 'Network bandwidth', 'A', 'Medium - Debugging tool.', 'Monitoring', '2026-04-19 17:12:03', 26),
(744, 'What is \"lazy deletion\" in Redis?', 'Background deletion using UNLINK', 'Immediate blocking deletion', 'Never deleting', 'Manual deletion only', 'A', 'Hard - Performance optimization.', 'Redis Internals', '2026-04-19 17:12:03', 26),
(745, 'What is the recommended maximum memory usage for Redis?', '100% of available RAM', 'Leave some headroom (e.g., 70-80% of available RAM)', 'Use swap heavily', 'No limit', 'B', 'Medium - Production practice.', 'Redis Configuration', '2026-04-19 17:12:03', 26),
(746, 'What is RedisAI module for?', 'Running machine learning models inside Redis', 'Simple caching only', 'Text search', 'Backup', 'A', 'Hard - Modern capability.', 'Redis Modules', '2026-04-19 17:12:03', 26),
(747, 'What is Flutter?', 'A backend framework', 'Google\'s open-source UI toolkit for building natively compiled applications', 'A database', 'A web server', 'B', 'Easy - Cross-platform mobile development.', 'Flutter Basics', '2026-04-19 17:12:14', 25),
(748, 'What language is used to write Flutter applications?', 'Java', 'Dart', 'Kotlin', 'Swift', 'B', 'Easy - Official language.', 'Dart Language', '2026-04-19 17:12:14', 25),
(749, 'What is a Widget in Flutter?', 'A small application', 'The basic building block of the UI', 'A database model', 'A backend service', 'B', 'Easy - Everything is a widget.', 'Widgets', '2026-04-19 17:12:14', 25),
(750, 'What is the difference between StatelessWidget and StatefulWidget?', 'Stateless is immutable; Stateful can change over time', 'They are the same', 'Stateful is faster', 'Stateless cannot be used', 'A', 'Medium - Core concept.', 'Widgets', '2026-04-19 17:12:14', 25),
(751, 'What does \"hot reload\" allow you to do?', 'Restart the entire app', 'See code changes instantly without losing state', 'Only change UI', 'Deploy to production', 'B', 'Easy - Major productivity feature.', 'Development Tools', '2026-04-19 17:12:14', 25),
(752, 'Which widget is used to create a scrollable list?', 'Container', 'ListView', 'Column', 'Row', 'B', 'Easy - Common layout.', 'Lists', '2026-04-19 17:12:14', 25),
(753, 'What is the purpose of MaterialApp?', 'To create iOS style apps only', 'To set up Material Design theme and routing', 'To handle backend requests', 'To manage state only', 'B', 'Medium - Root widget.', 'App Structure', '2026-04-19 17:12:14', 25),
(754, 'How do you add padding to a widget?', 'Using Padding widget', 'Using Container with margin', 'Using SizedBox only', 'Using Spacer', 'A', 'Easy - Layout control.', 'Layout', '2026-04-19 17:12:14', 25),
(755, 'What is setState() used for?', 'To update UI in StatefulWidget', 'To navigate to new screen', 'To fetch data', 'To dispose resources', 'A', 'Medium - State management.', 'State Management', '2026-04-19 17:12:14', 25),
(756, 'What is Provider used for in Flutter?', 'Simple and scalable state management', 'Only UI design', 'Backend communication', 'Database only', 'A', 'Medium - Popular state solution.', 'State Management', '2026-04-19 17:12:14', 25),
(757, 'Which widget creates a responsive layout?', 'MediaQuery', 'LayoutBuilder', 'Both A and B', 'Only Row', 'C', 'Hard - Adaptive UI.', 'Responsive Design', '2026-04-19 17:12:14', 25),
(758, 'What is the difference between Navigator.push and Navigator.pushNamed?', 'push is for named routes only', 'pushNamed uses route names defined in MaterialApp', 'They are identical', 'pushNamed is deprecated', 'B', 'Medium - Navigation.', 'Navigation', '2026-04-19 17:12:14', 25),
(759, 'What package is commonly used for HTTP requests?', 'http', 'dio', 'Both are popular', 'flutter_http', 'C', 'Medium - Network calls.', 'Networking', '2026-04-19 17:12:14', 25),
(760, 'What is FutureBuilder used for?', 'Building UI based on asynchronous data', 'Creating animations', 'Managing state', 'Handling gestures', 'A', 'Medium - Async UI.', 'Asynchronous Programming', '2026-04-19 17:12:14', 25),
(761, 'What does const constructor do?', 'Improves performance by allowing widget reuse', 'Makes widget mutable', 'Disables hot reload', 'Increases app size', 'A', 'Hard - Performance optimization.', 'Performance', '2026-04-19 17:12:14', 25),
(762, 'How do you handle platform-specific code?', 'Using Platform class or conditional imports', 'Rewriting entire app', 'Ignoring differences', 'Using only iOS code', 'A', 'Hard - Cross-platform.', 'Platform Channels', '2026-04-19 17:12:14', 25),
(763, 'What is Riverpod?', 'Improved version of Provider for state management', 'A UI component', 'A database', 'An animation library', 'A', 'Hard - Modern state management.', 'State Management', '2026-04-19 17:12:14', 25),
(764, 'What widget is used for infinite scrolling lists?', 'ListView.builder', 'SingleChildScrollView', 'GridView', 'Column', 'A', 'Medium - Performance.', 'Lists', '2026-04-19 17:12:14', 25),
(765, 'What is the purpose of ThemeData?', 'To define consistent styling across the app', 'To handle navigation', 'To manage state', 'To fetch API', 'A', 'Medium - Theming.', 'Styling', '2026-04-19 17:12:14', 25),
(766, 'How do you add dependencies in Flutter?', 'In pubspec.yaml file', 'In main.dart', 'Using terminal only', 'In AndroidManifest', 'A', 'Easy - Package management.', 'Pubspec', '2026-04-19 17:12:14', 25),
(767, 'What is Hero widget used for?', 'Shared element transitions between screens', 'Creating animations', 'Both A and B', 'Database operations', 'C', 'Medium - Beautiful UX.', 'Animations', '2026-04-19 17:12:14', 25),
(768, 'What does \"Sliver\" mean in Flutter?', 'Special scrollable widgets for custom scroll effects', 'Simple containers', 'State management', 'Network calls', 'A', 'Hard - Advanced scrolling.', 'CustomScrollView', '2026-04-19 17:12:14', 25),
(769, 'What is Bloc pattern?', 'Predictable state management using streams', 'Simple setState', 'Only UI', 'Backend only', 'A', 'Hard - Popular architecture.', 'State Management', '2026-04-19 17:12:14', 25),
(770, 'How do you persist data locally?', 'Using shared_preferences or Hive or SQLite', 'Only using API', 'Using global variables', 'Not possible', 'A', 'Medium - Local storage.', 'Local Persistence', '2026-04-19 17:12:14', 25),
(771, 'What is the difference between runApp and main?', 'main is entry point; runApp starts the widget tree', 'They are the same', 'runApp is deprecated', 'main starts UI', 'A', 'Medium - App initialization.', 'App Structure', '2026-04-19 17:12:14', 25),
(772, 'What widget handles gestures?', 'GestureDetector', 'InkWell', 'Both', 'Container', 'C', 'Medium - Interaction.', 'Gestures', '2026-04-19 17:12:14', 25),
(773, 'What is \"InheritedWidget\"?', 'Way to pass data down the widget tree efficiently', 'Simple container', 'Animation widget', 'Network widget', 'A', 'Hard - Foundational concept.', 'State Management', '2026-04-19 17:12:14', 25),
(774, 'How do you create custom widgets?', 'By composing existing widgets or extending Stateless/StatefulWidget', 'Only using Material widgets', 'Using Java code', 'Not possible', 'A', 'Medium - Reusability.', 'Custom Widgets', '2026-04-19 17:12:14', 25),
(775, 'What package is used for beautiful animations?', 'flutter_animate or Rive', 'Only setState', 'Only Hero', 'No package needed', 'A', 'Medium - Animations.', 'Animations', '2026-04-19 17:12:14', 25),
(776, 'What is \"null safety\"?', 'Dart feature that prevents null reference errors at compile time', 'Optional feature', 'Only for web', 'Deprecated', 'A', 'Medium - Modern Dart.', 'Dart Language', '2026-04-19 17:12:14', 25),
(777, 'What does \"Expanded\" widget do?', 'Gives a child widget flexible space in Row/Column/Flex', 'Makes widget smaller', 'Hides widget', 'Adds padding', 'A', 'Medium - Layout.', 'Layout', '2026-04-19 17:12:14', 25),
(778, 'How do you handle different screen sizes?', 'Using MediaQuery, LayoutBuilder, and responsive packages', 'Fixing pixel sizes', 'Ignoring differences', 'Using only iPhone sizes', 'A', 'Hard - Responsive design.', 'Responsive UI', '2026-04-19 17:12:14', 25),
(779, 'What is \"Keys\" used for in Flutter?', 'To preserve state and identify widgets in lists', 'Only for styling', 'For navigation', 'For API calls', 'A', 'Hard - Advanced.', 'Keys', '2026-04-19 17:12:14', 25),
(780, 'What is the recommended architecture for large apps?', 'Clean Architecture, MVVM, or Feature-first', 'All code in main.dart', 'Only setState', 'No architecture', 'A', 'Hard - Scalability.', 'App Architecture', '2026-04-19 17:12:14', 25),
(781, 'What does \"didChangeDependencies\" do?', 'Called when inherited widgets change', 'Called once only', 'Called on every build', 'Never called', 'A', 'Hard - Lifecycle.', 'StatefulWidget Lifecycle', '2026-04-19 17:12:14', 25),
(782, 'What is \"Isolate\" used for?', 'Running code in separate thread to avoid blocking UI', 'Simple function', 'UI rendering', 'Network only', 'A', 'Hard - Performance.', 'Concurrency', '2026-04-19 17:12:14', 25),
(783, 'How do you test Flutter widgets?', 'Using flutter_test package with WidgetTester', 'Only manual testing', 'Using Java tests', 'Not possible', 'A', 'Medium - Testing.', 'Testing', '2026-04-19 17:12:14', 25),
(784, 'What is \"Form\" widget used for?', 'Validating and saving multiple form fields', 'Simple text display', 'Navigation', 'Animation', 'A', 'Medium - Forms.', 'Forms', '2026-04-19 17:12:14', 25),
(785, 'What package is popular for state management in 2025?', 'Riverpod, Bloc, or GetX', 'Only setState', 'Only Provider', 'No state management', 'A', 'Medium - Current trends.', 'State Management', '2026-04-19 17:12:14', 25),
(786, 'What is \"Adaptive\" UI?', 'UI that automatically adapts to Material or Cupertino style', 'Fixed design only', 'Web only', 'Desktop only', 'A', 'Medium - Cross-platform.', 'Platform Adaptation', '2026-04-19 17:12:14', 25),
(787, 'How do you release a Flutter app?', 'Using flutter build apk / ios and app stores', 'Only running on emulator', 'Using web only', 'Not possible', 'A', 'Medium - Deployment.', 'Deployment', '2026-04-19 17:12:14', 25);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(788, 'What is \"go_router\" used for?', 'Declarative and type-safe routing', 'Only named routes', 'No routing', 'Old Navigator only', 'A', 'Hard - Modern navigation.', 'Navigation', '2026-04-19 17:12:14', 25),
(789, 'What does \"const\" keyword improve?', 'Performance and memory usage', 'Code readability only', 'Debugging', 'Hot reload', 'A', 'Medium - Best practice.', 'Performance', '2026-04-19 17:12:14', 25),
(790, 'What is \"Flutter Web\"?', 'Ability to build web applications from same codebase', 'Separate framework', 'Only mobile', 'Deprecated', 'A', 'Medium - Cross-platform.', 'Flutter Web', '2026-04-19 17:12:14', 25),
(791, 'What widget is used for tabbed interfaces?', 'TabBar and TabBarView with DefaultTabController', 'Only Column', 'Only Row', 'No widget needed', 'A', 'Medium - Common UI.', 'Tabbed UI', '2026-04-19 17:12:14', 25),
(792, 'What is the benefit of \"Impeller\" rendering engine?', 'Better performance and consistency across platforms', 'Slower rendering', 'Only for Android', 'Deprecated', 'A', 'Hard - Future of Flutter.', 'Rendering Engine', '2026-04-19 17:12:14', 25),
(793, 'What is the main advantage of using Power BI Desktop over Power BI Service?', 'Real-time collaboration only', 'Advanced data modeling, DAX development, and offline work', 'Only report sharing', 'Automatic refresh only', 'B', 'Easy - Development environment.', 'Power BI Desktop', '2026-04-19 17:12:23', 24),
(794, 'What is a Star Schema in Power BI?', 'A complex many-to-many relationship model', 'A dimensional model with fact tables and dimension tables', 'A flat table structure', 'A snowflake schema only', 'B', 'Medium - Data modeling best practice.', 'Data Modeling', '2026-04-19 17:12:23', 24),
(795, 'Which DAX function calculates the year-to-date total?', 'TOTALYTD', 'SUM', 'CALCULATE', 'Both A and C', 'D', 'Medium - Time intelligence.', 'DAX Time Intelligence', '2026-04-19 17:12:23', 24),
(796, 'What does the CALCULATE function do in DAX?', 'Creates new columns', 'Modifies the filter context of an expression', 'Only sums values', 'Creates relationships', 'B', 'Medium - Most important DAX function.', 'DAX Advanced', '2026-04-19 17:12:23', 24),
(797, 'What is the difference between a calculated column and a measure?', 'Calculated column is computed at refresh time; measure is computed at query time', 'They are identical', 'Measure is for visuals only', 'Calculated column is dynamic', 'A', 'Hard - Performance & behavior.', 'DAX Fundamentals', '2026-04-19 17:12:23', 24),
(798, 'What is Row-Level Security (RLS) used for?', 'Encrypting data', 'Restricting data access based on user roles', 'Improving report performance', 'Creating visuals', 'B', 'Medium - Security feature.', 'Security', '2026-04-19 17:12:23', 24),
(799, 'Which visual is best for showing hierarchical data?', 'Matrix', 'Treemap', 'Both A and B', 'Donut chart', 'C', 'Medium - Visualization choice.', 'Visualizations', '2026-04-19 17:12:23', 24),
(800, 'What is a Composite Model in Power BI?', 'Model using both Import and DirectQuery sources', 'Only Import mode', 'Only DirectQuery mode', 'Live Connection only', 'A', 'Hard - Advanced modeling.', 'Data Modeling', '2026-04-19 17:12:23', 24),
(801, 'What does the \"What If\" parameter allow?', 'Dynamic scenario analysis', 'Static calculations', 'Data import only', 'Report sharing', 'A', 'Medium - Interactive analysis.', 'Advanced Analytics', '2026-04-19 17:12:23', 24),
(802, 'How do you optimize a slow DAX measure?', 'Using variables, avoiding iterators when possible, and checking filter context', 'Adding more columns', 'Using only SUM', 'Ignoring performance', 'A', 'Hard - Performance tuning.', 'DAX Optimization', '2026-04-19 17:12:23', 24),
(803, 'What is Incremental Refresh used for?', 'Refreshing only new or changed data', 'Full refresh every time', 'Manual refresh only', 'Disabling refresh', 'A', 'Medium - Large dataset optimization.', 'Data Refresh', '2026-04-19 17:12:23', 24),
(804, 'What is the purpose of Aggregation tables?', 'To improve query performance on large datasets', 'To store raw data only', 'To create relationships', 'To secure data', 'A', 'Hard - Performance technique.', 'Performance Optimization', '2026-04-19 17:12:23', 24),
(805, 'Which function removes filters from a specific column?', 'ALL', 'REMOVEFILTERS', 'Both A and B', 'FILTER', 'C', 'Hard - Filter context control.', 'DAX Advanced', '2026-04-19 17:12:23', 24),
(806, 'What is a Bridge Table used for?', 'Handling many-to-many relationships', 'Simple one-to-one relationships', 'Time intelligence only', 'Security only', 'A', 'Medium - Relationship modeling.', 'Data Modeling', '2026-04-19 17:12:23', 24),
(807, 'What does the DAX function RANKX do?', 'Ranks values within a table', 'Calculates averages', 'Creates hierarchies', 'Filters data', 'A', 'Medium - Ranking analysis.', 'DAX Functions', '2026-04-19 17:12:23', 24),
(808, 'What is DirectQuery mode?', 'Queries the source database directly without importing data', 'Always imports all data', 'Only for Excel files', 'Deprecated mode', 'A', 'Medium - Connectivity modes.', 'Data Connectivity', '2026-04-19 17:12:23', 24),
(809, 'How do you create a dynamic title in Power BI?', 'Using a measure with SELECTEDVALUE or CONCATENATE', 'Hardcoding the title', 'Using only text box', 'Not possible', 'A', 'Medium - Dynamic reporting.', 'Report Design', '2026-04-19 17:12:23', 24),
(810, 'What is Bookmarks used for?', 'Saving specific report views and filters', 'Sharing reports', 'Scheduling refresh', 'Creating measures', 'A', 'Medium - User experience.', 'Interactivity', '2026-04-19 17:12:23', 24),
(811, 'What is the best practice for date tables?', 'Mark as Date Table and use a proper date dimension', 'Use any date column', 'No need for date table', 'Use multiple date columns', 'A', 'Medium - Time intelligence.', 'Data Modeling', '2026-04-19 17:12:23', 24),
(812, 'What does the USERELATIONSHIP function allow?', 'Using inactive relationships in calculations', 'Creating new relationships', 'Deleting relationships', 'Only active relationships', 'A', 'Hard - Advanced DAX.', 'DAX Relationships', '2026-04-19 17:12:23', 24),
(813, 'What is a KPI visual used for?', 'Showing performance against a target', 'Only displaying text', 'Creating tables', 'Showing trends only', 'A', 'Easy - Business monitoring.', 'Visualizations', '2026-04-19 17:12:23', 24),
(814, 'What is Power BI Dataflows?', 'Reusable data preparation logic in the cloud', 'Only report visuals', 'DAX calculations only', 'Security settings', 'A', 'Medium - ETL in Power BI.', 'Data Preparation', '2026-04-19 17:12:23', 24),
(815, 'How do you handle many-to-many relationships properly?', 'Using a bridge table or limited many-to-many cardinality', 'Direct many-to-many', 'Avoiding them completely', 'Using only one-to-one', 'A', 'Hard - Modeling best practice.', 'Data Modeling', '2026-04-19 17:12:23', 24),
(816, 'What is the purpose of the LOOKUPVALUE function?', 'To retrieve a value from another table without relationship', 'To create relationships', 'To sum values', 'To filter data', 'A', 'Medium - DAX lookup.', 'DAX Functions', '2026-04-19 17:12:23', 24),
(817, 'What is \"Drill-through\" in Power BI?', 'Navigating to a detailed page with context', 'Zooming into visuals', 'Refreshing data', 'Exporting report', 'A', 'Medium - Interactivity.', 'Report Design', '2026-04-19 17:12:23', 24),
(818, 'What does the SUMMARIZE function do?', 'Creates a summary table grouped by columns', 'Adds new columns', 'Filters data', 'Creates measures', 'A', 'Hard - Table manipulation.', 'DAX Advanced', '2026-04-19 17:12:23', 24),
(819, 'What is Auto ML in Power BI?', 'Automated machine learning for predictions', 'Automatic report creation', 'Data refresh only', 'Security configuration', 'A', 'Medium - AI features.', 'Advanced Analytics', '2026-04-19 17:12:23', 24),
(820, 'What is the recommended way to handle currency conversion?', 'Using DAX measures with exchange rate tables', 'Hardcoding rates', 'Ignoring conversion', 'Using only visuals', 'A', 'Hard - Business scenario.', 'DAX Scenarios', '2026-04-19 17:12:23', 24),
(821, 'What is \"Field Parameters\"?', 'Dynamic switching between measures or dimensions in visuals', 'Static parameters only', 'Security parameters', 'Refresh parameters', 'A', 'Medium - Dynamic reporting.', 'Advanced Features', '2026-04-19 17:12:23', 24),
(822, 'What does the TREATAS function allow?', 'Virtual relationships between tables', 'Physical relationships', 'Data deletion', 'Report sharing', 'A', 'Hard - Advanced DAX.', 'DAX Advanced', '2026-04-19 17:12:23', 24),
(823, 'What is the benefit of using variables in DAX?', 'Improved readability and performance', 'Slower calculations', 'More complex code', 'No benefit', 'A', 'Medium - Best practice.', 'DAX Optimization', '2026-04-19 17:12:23', 24),
(824, 'What is Power Query used for in advanced scenarios?', 'Complex data transformation and ETL', 'Only simple cleaning', 'DAX calculations', 'Report design', 'A', 'Medium - Data shaping.', 'Power Query Advanced', '2026-04-19 17:12:23', 24),
(825, 'What is a \"Custom Column\" in Power Query?', 'Adding new columns using M language', 'Creating DAX measures', 'Adding visuals', 'Setting security', 'A', 'Medium - Data transformation.', 'Power Query', '2026-04-19 17:12:23', 24),
(826, 'How do you optimize Power BI report performance?', 'Reducing model size, using aggregations, and proper DAX', 'Adding more visuals', 'Using only DirectQuery', 'Ignoring optimization', 'A', 'Hard - Performance tuning.', 'Performance Optimization', '2026-04-19 17:12:23', 24),
(827, 'What is \"Sensitivity Labels\"?', 'Applying data classification and protection', 'Visual styling', 'Report themes', 'Refresh settings', 'A', 'Medium - Governance.', 'Security & Governance', '2026-04-19 17:12:23', 24),
(828, 'What is the purpose of \"Lineage View\"?', 'Understanding data flow and dependencies', 'Creating visuals', 'Writing DAX', 'Scheduling refresh', 'A', 'Medium - Impact analysis.', 'Governance', '2026-04-19 17:12:23', 24),
(829, 'What is \" paginated reports \"?', 'Pixel-perfect reports for printing or exporting large data', 'Interactive dashboard only', 'Mobile reports', 'Real-time streaming', 'A', 'Hard - Advanced reporting.', 'Paginated Reports', '2026-04-19 17:12:23', 24),
(830, 'What does the GENERATESERIES function create?', 'A table of sequential values', 'Random numbers', 'Filtered data', 'Aggregated data', 'A', 'Medium - DAX utility.', 'DAX Functions', '2026-04-19 17:12:23', 24),
(831, 'What is \"AI visuals\" in Power BI?', 'Visuals that use machine learning for insights', 'Standard bar charts', 'Only tables', 'Security visuals', 'A', 'Medium - AI integration.', 'Advanced Analytics', '2026-04-19 17:12:23', 24),
(832, 'What is the best practice for date intelligence?', 'Using a single marked Date Table with proper relationships', 'Multiple date columns without marking', 'No date table', 'Using text dates', 'A', 'Medium - Best practice.', 'Time Intelligence', '2026-04-19 17:12:23', 24),
(833, 'What is \"Deployment Pipelines\"?', 'Managing development, test, and production workspaces', 'Only data refresh', 'Visual design', 'Security only', 'A', 'Medium - DevOps for Power BI.', 'Deployment', '2026-04-19 17:12:23', 24),
(834, 'What does the SWITCH function replace?', 'Multiple nested IF statements', 'SUMX function', 'CALCULATE', 'FILTER', 'A', 'Medium - Cleaner DAX.', 'DAX Best Practices', '2026-04-19 17:12:23', 24),
(835, 'What is \"Query Folding\"?', 'Pushing transformations back to the source database', 'Loading all data into memory', 'Creating visuals', 'Refreshing reports', 'A', 'Hard - Performance optimization.', 'Power Query', '2026-04-19 17:12:23', 24),
(836, 'What is the role of \"Gateway\" in Power BI?', 'Connecting on-premises data sources to the cloud', 'Creating reports', 'Writing DAX', 'Designing themes', 'A', 'Medium - Data connectivity.', 'Data Gateway', '2026-04-19 17:12:23', 24),
(837, 'What is \"XMLA endpoint\"?', 'Programmatic access to semantic models for advanced management', 'Only for visuals', 'Security setting', 'Refresh option', 'A', 'Hard - Enterprise feature.', 'Enterprise Features', '2026-04-19 17:12:23', 24),
(838, 'What is Power Query primarily used for?', 'Creating charts', 'Data extraction, transformation, and loading (ETL)', 'Writing formulas only', 'Formatting cells', 'B', 'Easy - Data preparation tool.', 'Power Query Basics', '2026-04-19 17:12:33', 23),
(839, 'What is the M language in Power Query?', 'The formula language used for advanced data transformations', 'DAX language', 'VBA code', 'Excel formulas', 'A', 'Medium - Power Query language.', 'Power Query Advanced', '2026-04-19 17:12:33', 23),
(840, 'Which function in Excel allows dynamic arrays?', 'FILTER, SORT, UNIQUE, SEQUENCE', 'VLOOKUP only', 'SUMIF', 'PivotTable', 'A', 'Medium - Modern Excel.', 'Dynamic Arrays', '2026-04-19 17:12:33', 23),
(841, 'What is the difference between Power Query and traditional Excel formulas?', 'Power Query is for data transformation; formulas are for calculations', 'They are the same', 'Power Query is slower', 'Formulas are for ETL', 'A', 'Medium - Tool comparison.', 'Excel vs Power Query', '2026-04-19 17:12:33', 23),
(842, 'How do you create a self-referencing query in Power Query?', 'Using a custom column referencing the previous step', 'Using VBA only', 'Using PivotTable', 'Not possible', 'A', 'Hard - Advanced technique.', 'Power Query Techniques', '2026-04-19 17:12:33', 23),
(843, 'What does the \"Unpivot Columns\" transformation do?', 'Converts columns into rows', 'Pivots data', 'Deletes duplicates', 'Merges files', 'A', 'Medium - Common transformation.', 'Power Query Transformations', '2026-04-19 17:12:33', 23),
(844, 'What is XLOOKUP used for?', 'Advanced lookup replacing VLOOKUP, HLOOKUP, and INDEX/MATCH', 'Only vertical lookup', 'Only horizontal lookup', 'Creating charts', 'A', 'Medium - Modern lookup.', 'Lookup Functions', '2026-04-19 17:12:33', 23),
(845, 'What is a Parameter in Power Query?', 'A dynamic value that can be changed without editing the query', 'A static column', 'A chart title', 'A cell format', 'A', 'Medium - Reusability.', 'Power Query Parameters', '2026-04-19 17:12:33', 23),
(846, 'What does LET function allow?', 'Defining variables inside a formula for better readability', 'Creating tables', 'Importing data', 'Formatting cells', 'A', 'Hard - Formula improvement.', 'Advanced Formulas', '2026-04-19 17:12:33', 23),
(847, 'How do you merge two queries in Power Query?', 'Using Merge Queries (similar to SQL JOIN)', 'Using VLOOKUP only', 'Copy-paste', 'Manual entry', 'A', 'Medium - Data integration.', 'Power Query Joins', '2026-04-19 17:12:33', 23),
(848, 'What is the benefit of using Tables in Excel?', 'Structured references and automatic expansion', 'Slower performance', 'No formulas', 'Limited to 100 rows', 'A', 'Easy - Best practice.', 'Excel Tables', '2026-04-19 17:12:33', 23),
(849, 'What does \"Group By\" in Power Query do?', 'Aggregates data by specified columns', 'Filters rows', 'Sorts data', 'Changes data types', 'A', 'Medium - Aggregation.', 'Power Query Transformations', '2026-04-19 17:12:33', 23),
(850, 'What is INDEX + MATCH combination used for?', 'Flexible lookup in any direction', 'Only left lookup', 'Only right lookup', 'Chart creation', 'A', 'Medium - Classic technique.', 'Lookup Functions', '2026-04-19 17:12:33', 23),
(851, 'What is \"Query Folding\"?', 'Pushing transformations back to the source for better performance', 'Loading all data into Excel', 'Creating visuals', 'Refreshing manually', 'A', 'Hard - Performance.', 'Power Query Optimization', '2026-04-19 17:12:33', 23),
(852, 'How do you create a dynamic drop-down list?', 'Using Data Validation with OFFSET or dynamic arrays', 'Manual entry only', 'Using PivotTable', 'Not possible', 'A', 'Medium - Data validation.', 'Dynamic Lists', '2026-04-19 17:12:33', 23),
(853, 'What is the purpose of Power Pivot?', 'Data modeling with DAX and relationships', 'Simple calculations', 'Chart creation only', 'Text formatting', 'A', 'Medium - Advanced analytics.', 'Power Pivot', '2026-04-19 17:12:33', 23),
(854, 'What does the \"Append Queries\" feature do?', 'Combines multiple queries vertically (union)', 'Joins horizontally', 'Filters data', 'Deletes columns', 'A', 'Medium - Data combination.', 'Power Query', '2026-04-19 17:12:33', 23),
(855, 'What is a calculated column in Power Pivot?', 'A column computed using DAX at model refresh', 'A measure', 'A visual', 'A parameter', 'A', 'Medium - Modeling.', 'Power Pivot', '2026-04-19 17:12:33', 23),
(856, 'How do you handle circular references in Excel?', 'Using iterative calculation or restructuring formulas', 'Ignoring them', 'Deleting all formulas', 'Using only SUM', 'A', 'Hard - Troubleshooting.', 'Formula Errors', '2026-04-19 17:12:33', 23),
(857, 'What is \"Spill\" error in dynamic arrays?', 'When a formula returns multiple values but there is not enough space', 'Syntax error', 'Reference error', 'Value error', 'A', 'Medium - Dynamic arrays.', 'Dynamic Arrays', '2026-04-19 17:12:33', 23),
(858, 'What is the best way to clean messy data?', 'Using Power Query transformations', 'Manual Find & Replace only', 'VBA only', 'Ignoring issues', 'A', 'Easy - Recommended approach.', 'Data Cleaning', '2026-04-19 17:12:33', 23),
(859, 'What does the \"Conditional Column\" in Power Query create?', 'If-then-else logic columns', 'Calculated measures', 'Charts', 'PivotTables', 'A', 'Medium - Logic in PQ.', 'Power Query', '2026-04-19 17:12:33', 23),
(860, 'What is \"DAX\" in the context of Excel?', 'Data Analysis Expressions used in Power Pivot', 'Excel formula language', 'VBA language', 'M language', 'A', 'Medium - Advanced modeling.', 'DAX Basics', '2026-04-19 17:12:33', 23),
(861, 'How do you create a running total in DAX?', 'Using CALCULATE with FILTER and ALL', 'Using SUM only', 'Using AVERAGE', 'Using COUNT', 'A', 'Hard - DAX pattern.', 'DAX Scenarios', '2026-04-19 17:12:33', 23),
(862, 'What is \"Get & Transform\"?', 'Old name for Power Query', 'Charting tool', 'Formatting feature', 'Security setting', 'A', 'Easy - Historical name.', 'Power Query', '2026-04-19 17:12:33', 23),
(863, 'What does the \"Fill Down\" transformation do?', 'Propagates values downward to fill blanks', 'Deletes rows', 'Sorts data', 'Merges columns', 'A', 'Medium - Cleaning.', 'Power Query Transformations', '2026-04-19 17:12:33', 23),
(864, 'What is the benefit of using Named Ranges?', 'Easier formula maintenance and readability', 'Slower performance', 'Limited to 10 cells', 'No benefit', 'A', 'Medium - Best practice.', 'Excel Advanced', '2026-04-19 17:12:33', 23),
(865, 'How do you combine multiple Excel files?', 'Using Power Query \"From Folder\" and combine', 'Manual copy-paste', 'Using VLOOKUP only', 'Not possible', 'A', 'Medium - Automation.', 'Power Query', '2026-04-19 17:12:33', 23),
(866, 'What is \" slicer \" in Excel?', 'Interactive filter for PivotTables and charts', 'Chart type', 'Formula', 'Cell format', 'A', 'Easy - Interactivity.', 'PivotTables', '2026-04-19 17:12:33', 23),
(867, 'What does the \"Transpose\" transformation do?', 'Converts rows to columns and vice versa', 'Deletes data', 'Filters data', 'Groups data', 'A', 'Medium - Reshaping.', 'Power Query', '2026-04-19 17:12:33', 23),
(868, 'What is the purpose of \"Error Handling\" in Power Query?', 'Replacing errors with custom values or removing error rows', 'Ignoring all errors', 'Crashing Excel', 'Deleting queries', 'A', 'Medium - Robust ETL.', 'Power Query Advanced', '2026-04-19 17:12:33', 23),
(869, 'What is \"Power Map\" (3D Map)?', 'Geographic visualization using Bing Maps', 'Simple bar chart', 'PivotTable', 'DAX tool', 'A', 'Medium - Visualization.', 'Advanced Visuals', '2026-04-19 17:12:33', 23),
(870, 'How do you protect sensitive data in Excel?', 'Using sheet protection, file encryption, or sensitivity labels', 'Hiding sheets only', 'Using password on every cell', 'Not possible', 'A', 'Medium - Security.', 'Security', '2026-04-19 17:12:33', 23),
(871, 'What is \"Forecast Sheet\"?', 'Automated time series forecasting', 'Manual chart', 'PivotTable', 'Power Query feature', 'A', 'Medium - Analytics.', 'Forecasting', '2026-04-19 17:12:33', 23),
(872, 'What does the \"Remove Duplicates\" feature do?', 'Deletes duplicate rows based on selected columns', 'Merges duplicates', 'Highlights duplicates', 'Creates new table', 'A', 'Easy - Data cleaning.', 'Data Cleaning', '2026-04-19 17:12:33', 23),
(873, 'What is the advantage of using Power Query over VBA for data cleaning?', 'No coding required and automatic refresh', 'Faster for all cases', 'More flexible always', 'Requires programming knowledge', 'A', 'Medium - Tool comparison.', 'Power Query vs VBA', '2026-04-19 17:12:33', 23),
(874, 'What is \"Dynamic Array Formula\"?', 'Formulas that automatically spill results into multiple cells', 'Single cell formulas only', 'Array formulas with Ctrl+Shift+Enter', 'Deprecated feature', 'A', 'Medium - Modern Excel.', 'Dynamic Arrays', '2026-04-19 17:12:33', 23),
(875, 'How do you create a dependent drop-down list?', 'Using INDIRECT or dynamic arrays with FILTER', 'Manual entry', 'Using VLOOKUP only', 'Not possible', 'A', 'Hard - Advanced validation.', 'Data Validation', '2026-04-19 17:12:33', 23),
(876, 'What is \"Query Parameters\" used for?', 'Making queries dynamic and reusable', 'Static filtering', 'Chart titles', 'Cell formatting', 'A', 'Medium - Reusability.', 'Power Query', '2026-04-19 17:12:33', 23),
(877, 'What does the \"Detect Data Type\" feature do?', 'Automatically suggests appropriate data types', 'Deletes columns', 'Creates relationships', 'Refreshes data', 'A', 'Easy - Automation.', 'Power Query', '2026-04-19 17:12:33', 23),
(878, 'What is the best practice for large Excel files?', 'Using Power Query and Data Model instead of thousands of formulas', 'Using thousands of VLOOKUPs', 'Storing everything in one sheet', 'Avoiding tables', 'A', 'Hard - Performance.', 'Performance Best Practices', '2026-04-19 17:12:33', 23),
(879, 'What is \"Custom Function\" in Power Query?', 'Reusable M code function', 'Excel formula', 'VBA macro', 'Chart type', 'A', 'Hard - Advanced PQ.', 'Power Query Advanced', '2026-04-19 17:12:33', 23),
(880, 'What does \"Promote Headers\" do?', 'Converts first row into column headers', 'Deletes first row', 'Sorts data', 'Filters data', 'A', 'Easy - Common step.', 'Power Query', '2026-04-19 17:12:33', 23),
(881, 'What is the role of \" relationships \" in Power Pivot?', 'Connecting tables for analysis across multiple sources', 'Creating charts', 'Formatting cells', 'Refreshing data', 'A', 'Medium - Modeling.', 'Power Pivot', '2026-04-19 17:12:33', 23),
(882, 'How do you schedule automatic refresh for Power Query?', 'Using Power BI Gateway or scheduled refresh in Excel Online', 'Manual refresh only', 'Using VBA only', 'Not possible', 'A', 'Medium - Automation.', 'Refresh', '2026-04-19 17:12:33', 23),
(883, 'What is \"Flash Fill\"?', 'Automatic pattern recognition and data filling', 'Manual copy', 'Formula feature', 'Chart tool', 'A', 'Easy - Productivity feature.', 'Excel Features', '2026-04-19 17:12:33', 23),
(884, 'What is the best way to analyze large datasets in Excel?', 'Load data into Power Query + Power Pivot Data Model', 'Use regular Excel sheets with millions of rows', 'Use only formulas', 'Avoid analysis', 'A', 'Hard - Scalability.', 'Best Practices', '2026-04-19 17:12:33', 23),
(885, 'What is the main goal of Design Thinking?', 'To implement the first idea quickly', 'To solve complex problems with a human-centered approach', 'To reduce costs only', 'To follow strict processes', 'B', 'Easy - Core philosophy.', 'Design Thinking Basics', '2026-04-19 17:12:56', 22),
(886, 'How many phases are there in the classic Design Thinking process?', '3', '5', '7', '10', 'B', 'Easy - Empathize, Define, Ideate, Prototype, Test.', 'Design Thinking Process', '2026-04-19 17:12:56', 22),
(887, 'What happens in the Empathize phase?', 'Generating many ideas', 'Understanding users through research and observation', 'Building prototypes', 'Testing solutions', 'B', 'Easy - First phase.', 'Empathize', '2026-04-19 17:12:56', 22),
(888, 'What is a Persona in Design Thinking?', 'A fictional character representing a user segment', 'A real customer', 'A technical specification', 'A budget plan', 'A', 'Medium - User representation.', 'User Research', '2026-04-19 17:12:56', 22),
(889, 'What is the purpose of the Define phase?', 'To generate ideas', 'To clearly articulate the problem based on insights', 'To build prototypes', 'To test solutions', 'B', 'Medium - Problem framing.', 'Define', '2026-04-19 17:12:56', 22),
(890, 'What does \"Ideate\" mean?', 'To criticize ideas', 'To generate a large quantity of ideas without judgment', 'To implement the solution', 'To analyze data', 'B', 'Easy - Divergent thinking.', 'Ideation', '2026-04-19 17:12:56', 22),
(891, 'Which technique is commonly used in the Ideate phase?', 'Brainstorming', 'Detailed planning', 'Budget analysis', 'Risk assessment', 'A', 'Medium - Creativity techniques.', 'Ideation Techniques', '2026-04-19 17:12:56', 22),
(892, 'What is a Prototype?', 'A final polished product', 'An early, simplified version of the solution to test ideas', 'A detailed business plan', 'A user interview script', 'B', 'Medium - Making ideas tangible.', 'Prototype', '2026-04-19 17:12:56', 22),
(893, 'What is the goal of the Test phase?', 'To validate assumptions and gather feedback', 'To finalize the product', 'To generate more ideas', 'To define the problem', 'A', 'Medium - Iteration.', 'Testing', '2026-04-19 17:12:56', 22),
(894, 'What is \"iteration\" in Design Thinking?', 'Repeating phases based on feedback to improve the solution', 'Doing everything once', 'Stopping after ideation', 'Implementing without testing', 'A', 'Medium - Core mindset.', 'Iterative Process', '2026-04-19 17:12:56', 22),
(895, 'What is divergent thinking?', 'Narrowing down to one solution', 'Generating many possible ideas', 'Criticizing concepts', 'Following existing processes', 'B', 'Medium - Creativity.', 'Creative Thinking', '2026-04-19 17:12:56', 22),
(896, 'What is convergent thinking?', 'Generating many ideas', 'Evaluating and selecting the best ideas', 'Observing users', 'Building prototypes', 'B', 'Medium - Decision making.', 'Ideation', '2026-04-19 17:12:56', 22),
(897, 'What is an Empathy Map used for?', 'To visualize what users say, think, do, and feel', 'To create financial models', 'To plan project timelines', 'To test prototypes', 'A', 'Medium - User understanding.', 'Empathize', '2026-04-19 17:12:56', 22),
(898, 'What is a \"How Might We\" (HMW) statement?', 'A way to reframe problems into opportunities', 'A technical requirement', 'A budget constraint', 'A risk analysis', 'A', 'Medium - Problem framing.', 'Define', '2026-04-19 17:12:56', 22),
(899, 'Which of the following encourages innovation?', 'Fear of failure', 'Psychological safety and experimentation', 'Strict hierarchical decisions', 'Avoiding risks', 'B', 'Medium - Innovation culture.', 'Innovation Mindset', '2026-04-19 17:12:56', 22),
(900, 'What is \"Design Sprint\"?', 'A 5-day process to solve big problems and test ideas', 'A long development cycle', 'A budgeting meeting', 'A team building activity', 'A', 'Hard - Google Ventures method.', 'Design Sprint', '2026-04-19 17:12:56', 22),
(901, 'What is the difference between Design Thinking and traditional problem solving?', 'Design Thinking is human-centered and iterative', 'They are identical', 'Traditional is more creative', 'Design Thinking avoids user research', 'A', 'Medium - Comparison.', 'Design Thinking vs Traditional', '2026-04-19 17:12:56', 22),
(902, 'What is a Journey Map?', 'A visual representation of the user experience over time', 'A project timeline', 'A financial chart', 'A technical diagram', 'A', 'Medium - User experience.', 'User Research', '2026-04-19 17:12:56', 22),
(903, 'Why is prototyping important?', 'To fail fast, learn quickly, and refine ideas', 'To create the final product immediately', 'To avoid user feedback', 'To spend more time planning', 'A', 'Medium - Learning tool.', 'Prototype', '2026-04-19 17:12:56', 22),
(904, 'What does \"radical collaboration\" mean?', 'Working in silos', 'Bringing together diverse perspectives and disciplines', 'Following one leader only', 'Avoiding conflict', 'B', 'Hard - Team aspect.', 'Collaboration', '2026-04-19 17:12:56', 22),
(905, 'What is the \"Jobs to Be Done\" framework?', 'Understanding what job a product helps the user accomplish', 'Creating job descriptions', 'Budget planning', 'Technical specifications', 'A', 'Hard - Innovation tool.', 'User Insights', '2026-04-19 17:12:56', 22),
(906, 'What is \"Minimum Viable Product\" (MVP)?', 'A version with just enough features to test and gather feedback', 'The complete final product', 'A detailed plan', 'A prototype with all features', 'A', 'Medium - Lean approach.', 'Testing', '2026-04-19 17:12:56', 22),
(907, 'What encourages a culture of innovation?', 'Psychological safety, experimentation, and learning from failure', 'Fear of making mistakes', 'Strict processes without flexibility', 'Avoiding new ideas', 'A', 'Medium - Innovation culture.', 'Innovation Culture', '2026-04-19 17:12:56', 22),
(908, 'What is \"storytelling\" used for in Design Thinking?', 'To communicate insights and ideas effectively', 'To replace research', 'To create budgets', 'To write code', 'A', 'Medium - Communication.', 'Presentation', '2026-04-19 17:12:56', 22),
(909, 'What is the difference between a prototype and a pilot?', 'Prototype is low-fidelity for learning; pilot is higher-fidelity for validation', 'They are the same', 'Pilot is always digital', 'Prototype is the final version', 'A', 'Hard - Validation stages.', 'Testing', '2026-04-19 17:12:56', 22),
(910, 'What is \"abductive reasoning\"?', 'Reasoning from observation to the best possible explanation', 'Deductive reasoning only', 'Inductive reasoning only', 'Avoiding logic', 'A', 'Hard - Creative thinking.', 'Creative Thinking', '2026-04-19 17:12:56', 22),
(911, 'What is the role of a facilitator in Design Thinking workshops?', 'To guide the process, keep energy high, and ensure participation', 'To make all decisions', 'To observe only', 'To present solutions', 'A', 'Medium - Workshop skills.', 'Facilitation', '2026-04-19 17:12:56', 22),
(912, 'What is \"bias\" in user research?', 'Assuming we already know what users want', 'Listening carefully', 'Asking open questions', 'Observing behavior', 'A', 'Medium - Common pitfall.', 'User Research', '2026-04-19 17:12:56', 22),
(913, 'What is \"co-creation\"?', 'Users and stakeholders actively participating in the design process', 'Designers working alone', 'Following competitor solutions', 'Implementing without feedback', 'A', 'Medium - Collaborative approach.', 'Co-Creation', '2026-04-19 17:12:56', 22),
(914, 'What is the final goal of Design Thinking?', 'To create innovative solutions that truly meet user needs', 'To reduce costs only', 'To follow industry standards', 'To implement the first idea', 'A', 'Easy - Overall objective.', 'Design Thinking Goals', '2026-04-19 17:12:56', 22),
(915, 'What is stress according to the transactional model?', 'An external event only', 'The interaction between the person and their environment', 'Always negative', 'Only physical reaction', 'B', 'Easy - Basic definition.', 'Stress Basics', '2026-04-19 17:15:38', 21),
(916, 'What is the difference between eustress and distress?', 'Eustress is positive and motivating; distress is harmful', 'They are the same', 'Eustress is always physical', 'Distress is beneficial', 'A', 'Medium - Stress types.', 'Stress Types', '2026-04-19 17:15:38', 21),
(917, 'Which technique is most effective for immediate stress reduction?', 'Deep breathing and progressive muscle relaxation', 'Overthinking the problem', 'Drinking coffee', 'Avoiding the situation', 'A', 'Easy - Practical tool.', 'Stress Management Techniques', '2026-04-19 17:15:38', 21),
(918, 'What is resilience?', 'The ability to avoid all difficulties', 'The capacity to recover and adapt after adversity', 'Never feeling stressed', 'Ignoring problems', 'B', 'Easy - Core concept.', 'Resilience', '2026-04-19 17:15:38', 21),
(919, 'What is the HPA axis?', 'The body\'s main stress response system (Hypothalamus-Pituitary-Adrenal)', 'A breathing technique', 'A relaxation method', 'A cognitive bias', 'A', 'Medium - Physiology of stress.', 'Stress Physiology', '2026-04-19 17:15:38', 21),
(920, 'Which factor most influences resilience?', 'Genetics only', 'A combination of mindset, social support, and coping strategies', 'Avoiding all stress', 'Working longer hours', 'B', 'Medium - Resilience factors.', 'Building Resilience', '2026-04-19 17:15:38', 21),
(921, 'What is cognitive reframing?', 'Changing the way you interpret a stressful situation', 'Ignoring the situation', 'Suppressing emotions', 'Blaming others', 'A', 'Medium - Cognitive technique.', 'Cognitive Strategies', '2026-04-19 17:15:38', 21),
(922, 'What is mindfulness?', 'Paying attention to the present moment without judgment', 'Planning the future', 'Ruminating on the past', 'Multitasking', 'A', 'Easy - Key practice.', 'Mindfulness', '2026-04-19 17:15:38', 21),
(923, 'What is the \"fight or flight\" response?', 'The body\'s automatic reaction to perceived threat', 'A relaxation response', 'A cognitive process', 'A long-term strategy', 'A', 'Easy - Stress response.', 'Stress Physiology', '2026-04-19 17:15:38', 21),
(924, 'Which habit best builds long-term resilience?', 'Regular physical exercise, sleep, and social connection', 'Working 12 hours a day', 'Avoiding all challenges', 'Constant multitasking', 'A', 'Medium - Lifestyle factors.', 'Resilience Building', '2026-04-19 17:15:38', 21),
(925, 'What is emotional regulation?', 'Suppressing all emotions', 'Recognizing, understanding, and managing emotions effectively', 'Expressing every emotion immediately', 'Ignoring feelings', 'B', 'Medium - Emotional intelligence.', 'Emotional Regulation', '2026-04-19 17:15:38', 21),
(926, 'What is the ABC model in stress management?', 'Activating event, Beliefs, Consequences', 'Avoid, Blame, Complain', 'Action, Behavior, Change', 'Anxiety, Burnout, Crisis', 'A', 'Hard - Cognitive model (Ellis).', 'Cognitive Techniques', '2026-04-19 17:15:38', 21),
(927, 'What is burnout?', 'A state of emotional, physical, and mental exhaustion caused by prolonged stress', 'Normal tiredness', 'Short-term fatigue', 'Motivation boost', 'A', 'Medium - Serious consequence.', 'Burnout Prevention', '2026-04-19 17:15:38', 21),
(928, 'Which practice helps prevent burnout?', 'Setting healthy boundaries and practicing self-care', 'Working harder', 'Ignoring rest', 'Taking on more responsibilities', 'A', 'Medium - Prevention.', 'Burnout Prevention', '2026-04-19 17:15:38', 21),
(929, 'What is the role of social support in resilience?', 'It has no effect', 'It provides emotional and practical help during difficult times', 'It increases stress', 'It should be avoided', 'B', 'Medium - Protective factor.', 'Social Support', '2026-04-19 17:15:38', 21),
(930, 'What is \"post-traumatic growth\"?', 'Positive psychological change after struggling with adversity', 'Permanent damage after trauma', 'Avoiding all stress', 'Immediate recovery without effort', 'A', 'Hard - Advanced resilience concept.', 'Resilience Advanced', '2026-04-19 17:15:38', 21),
(931, 'What breathing technique is effective for acute stress?', '4-7-8 breathing or diaphragmatic breathing', 'Shallow chest breathing', 'Holding breath', 'Rapid breathing', 'A', 'Easy - Immediate tool.', 'Breathing Techniques', '2026-04-19 17:15:38', 21),
(932, 'What is \"gratitude practice\"?', 'Focusing on positive aspects to build resilience', 'Complaining daily', 'Ignoring achievements', 'Comparing with others', 'A', 'Medium - Positive psychology.', 'Positive Psychology', '2026-04-19 17:15:38', 21),
(933, 'What is the difference between stress and anxiety?', 'Stress is usually tied to a specific trigger; anxiety is more persistent worry', 'They are identical', 'Anxiety is always beneficial', 'Stress never affects the body', 'A', 'Medium - Distinction.', 'Stress vs Anxiety', '2026-04-19 17:15:38', 21),
(934, 'What is self-compassion?', 'Treating yourself with kindness during difficult times', 'Self-criticism', 'Ignoring personal needs', 'Blaming yourself', 'A', 'Medium - Resilience skill.', 'Self-Compassion', '2026-04-19 17:15:38', 21),
(935, 'How does chronic stress affect health?', 'It can lead to cardiovascular issues, weakened immune system, and mental health problems', 'It has no long-term effect', 'It only affects mood', 'It improves immunity', 'A', 'Medium - Health impact.', 'Stress and Health', '2026-04-19 17:15:38', 21),
(936, 'What is \"flow state\"?', 'Complete immersion and focus in an activity', 'High stress state', 'Complete relaxation', 'Multitasking', 'A', 'Hard - Optimal experience.', 'Flow and Performance', '2026-04-19 17:15:38', 21),
(937, 'What is the best way to recover from burnout?', 'Rest, professional help if needed, and gradual re-engagement with boundaries', 'Working harder', 'Quitting immediately without plan', 'Ignoring symptoms', 'A', 'Hard - Recovery strategy.', 'Burnout Recovery', '2026-04-19 17:15:38', 21),
(938, 'What is \"assertiveness\" in stress management?', 'Expressing needs and boundaries respectfully', 'Being aggressive', 'Avoiding conflict always', 'Saying yes to everything', 'A', 'Medium - Communication skill.', 'Assertiveness', '2026-04-19 17:15:38', 21),
(939, 'What role does sleep play in stress management?', 'It is essential for emotional regulation and cognitive function', 'It is optional', 'More sleep increases stress', 'Sleep has no effect', 'A', 'Easy - Foundation.', 'Sleep and Stress', '2026-04-19 17:15:38', 21),
(940, 'What is \"rumination\"?', 'Repetitive negative thinking about past events', 'Problem-solving', 'Planning the future', 'Mindful awareness', 'A', 'Medium - Cognitive trap.', 'Cognitive Patterns', '2026-04-19 17:15:38', 21),
(941, 'What is progressive muscle relaxation?', 'Tensing and relaxing muscle groups to reduce physical tension', 'Cardio exercise', 'Meditation only', 'Breathing only', 'A', 'Medium - Relaxation technique.', 'Relaxation Methods', '2026-04-19 17:15:38', 21),
(942, 'What is \"resilience training\"?', 'Structured programs to develop coping skills and mindset', 'Avoiding challenges', 'Working longer hours', 'Ignoring emotions', 'A', 'Medium - Skill development.', 'Resilience Building', '2026-04-19 17:15:38', 21),
(943, 'What is the impact of exercise on stress?', 'It reduces stress hormones and releases endorphins', 'It increases stress', 'It has no effect', 'It only helps physically', 'A', 'Easy - Well-known benefit.', 'Exercise and Stress', '2026-04-19 17:15:38', 21),
(944, 'What is \"acceptance\" in resilience?', 'Acknowledging reality without unnecessary resistance', 'Giving up', 'Fighting every situation', 'Avoiding problems', 'A', 'Hard - Psychological flexibility.', 'Acceptance & Commitment', '2026-04-19 17:15:38', 21),
(945, 'What is the definition of stress in psychology?', 'An external event only', 'A response to a perceived threat or demand', 'Always a negative emotion', 'A permanent state', 'B', 'Easy - Basic understanding.', 'Introduction to Stress', '2026-04-19 17:15:45', 21),
(946, 'What is the difference between acute and chronic stress?', 'Acute is short-term; chronic is long-term and harmful', 'They are the same', 'Acute is always dangerous', 'Chronic is beneficial', 'A', 'Easy - Stress types.', 'Stress Types', '2026-04-19 17:15:45', 21),
(947, 'Which of the following is a symptom of chronic stress?', 'Increased energy', 'Fatigue, irritability, and sleep problems', 'Better concentration', 'Stronger immune system', 'B', 'Easy - Recognition.', 'Stress Symptoms', '2026-04-19 17:15:45', 21),
(948, 'What is resilience?', 'Avoiding all difficulties in life', 'The ability to adapt and recover from adversity', 'Never feeling stressed', 'Working without breaks', 'B', 'Easy - Core concept.', 'Resilience Basics', '2026-04-19 17:15:45', 21),
(949, 'What is the HPA axis?', 'The body’s primary stress response system involving hormones', 'A breathing technique', 'A relaxation method', 'A cognitive model', 'A', 'Medium - Physiology.', 'Stress Physiology', '2026-04-19 17:15:45', 21),
(950, 'Which technique is effective for immediate stress relief?', 'Deep breathing exercises (like 4-7-8 breathing)', 'Overthinking the situation', 'Consuming caffeine', 'Suppressing emotions', 'A', 'Easy - Practical tool.', 'Immediate Stress Management', '2026-04-19 17:15:45', 21),
(951, 'What is cognitive reframing?', 'Changing your interpretation of a stressful event to reduce its impact', 'Ignoring the problem', 'Blaming others', 'Avoiding all challenges', 'A', 'Medium - Cognitive technique.', 'Cognitive Strategies', '2026-04-19 17:15:45', 21),
(952, 'What is mindfulness?', 'Paying attention to the present moment without judgment', 'Worrying about the future', 'Ruminating on the past', 'Multitasking constantly', 'A', 'Easy - Key practice.', 'Mindfulness', '2026-04-19 17:15:45', 21),
(953, 'What is burnout?', 'Temporary tiredness after hard work', 'A state of emotional, physical, and mental exhaustion from prolonged stress', 'High motivation', 'Short-term fatigue', 'B', 'Medium - Serious consequence.', 'Burnout', '2026-04-19 17:15:45', 21),
(954, 'Which factor strongly contributes to resilience?', 'Social support and positive relationships', 'Working longer hours', 'Avoiding all stress', 'Isolating oneself', 'A', 'Medium - Protective factors.', 'Building Resilience', '2026-04-19 17:15:45', 21),
(955, 'What is emotional regulation?', 'Suppressing all emotions', 'Recognizing and managing emotions effectively', 'Expressing every feeling immediately', 'Ignoring emotions completely', 'B', 'Medium - Emotional skill.', 'Emotional Regulation', '2026-04-19 17:15:45', 21),
(956, 'What is the ABC model of stress?', 'Activating event → Beliefs → Consequences', 'Avoid → Blame → Complain', 'Action → Behavior → Change', 'Anxiety → Burnout → Crisis', 'A', 'Hard - Cognitive model.', 'Cognitive Techniques', '2026-04-19 17:15:45', 21),
(957, 'What is progressive muscle relaxation?', 'Tensing and then relaxing different muscle groups to reduce tension', 'Running long distances', 'Watching TV', 'Eating comfort food', 'A', 'Medium - Relaxation technique.', 'Relaxation Methods', '2026-04-19 17:15:45', 21),
(958, 'What is self-compassion?', 'Being kind and understanding toward yourself during difficult times', 'Self-criticism', 'Comparing yourself to others', 'Ignoring personal needs', 'A', 'Medium - Resilience skill.', 'Self-Compassion', '2026-04-19 17:15:45', 21),
(959, 'How does regular physical exercise help with stress?', 'It reduces cortisol and releases endorphins', 'It increases stress hormones', 'It has no effect', 'It only helps physically', 'A', 'Easy - Well-known benefit.', 'Exercise and Stress', '2026-04-19 17:15:45', 21),
(960, 'What is \"flow state\"?', 'Complete absorption in an activity with optimal focus', 'High anxiety state', 'Complete boredom', 'Multitasking', 'A', 'Hard - Positive psychology.', 'Flow and Resilience', '2026-04-19 17:15:45', 21),
(961, 'What is post-traumatic growth?', 'Positive psychological changes after struggling with major life crises', 'Permanent damage after trauma', 'Avoiding all challenges', 'Immediate perfect recovery', 'A', 'Hard - Advanced concept.', 'Resilience Advanced', '2026-04-19 17:15:45', 21),
(962, 'What is the best way to prevent burnout?', 'Setting boundaries, taking regular breaks, and practicing self-care', 'Working longer hours', 'Ignoring fatigue', 'Taking on more responsibilities', 'A', 'Medium - Prevention.', 'Burnout Prevention', '2026-04-19 17:15:45', 21),
(963, 'What is assertiveness?', 'Expressing your needs and feelings respectfully while respecting others', 'Being aggressive', 'Avoiding all conflict', 'Saying yes to everything', 'A', 'Medium - Communication skill.', 'Assertiveness Training', '2026-04-19 17:15:45', 21),
(964, 'What is the role of sleep in stress management?', 'It is essential for emotional regulation and cognitive recovery', 'It is optional when busy', 'More sleep increases stress', 'Sleep has no impact on stress', 'A', 'Easy - Foundation.', 'Sleep and Stress', '2026-04-19 17:15:45', 21),
(965, 'What is rumination?', 'Repetitive negative thinking about past or potential problems', 'Effective problem-solving', 'Planning the future positively', 'Mindful awareness', 'A', 'Medium - Cognitive trap.', 'Cognitive Patterns', '2026-04-19 17:15:45', 21),
(966, 'What is gratitude practice?', 'Regularly focusing on things you are thankful for to build resilience', 'Complaining daily', 'Comparing yourself negatively', 'Ignoring positive events', 'A', 'Medium - Positive psychology.', 'Positive Psychology', '2026-04-19 17:15:45', 21),
(967, 'What is psychological safety?', 'Feeling safe to take risks and express ideas without fear of negative consequences', 'Avoiding all challenges', 'Strict hierarchy', 'Suppressing emotions', 'A', 'Hard - Team & individual resilience.', 'Psychological Safety', '2026-04-19 17:15:45', 21),
(975, 'What is the main difference between coaching and mentoring?', 'Coaching focuses on performance and goals; mentoring is broader guidance', 'They are identical', 'Coaching is only for managers', 'Mentoring is short-term', 'A', 'Easy - Basic distinction.', 'Coaching Basics', '2026-04-19 17:16:04', 20),
(976, 'What is the GROW model in coaching?', 'Goal, Reality, Options, Will', 'Give, Receive, Observe, Work', 'Goal, Review, Order, Win', 'Group, Reality, Options, Win', 'A', 'Medium - Classic coaching model.', 'Coaching Models', '2026-04-19 17:16:04', 20),
(977, 'What is the role of a coach?', 'To give direct advice and solutions', 'To help the coachee find their own solutions through powerful questions', 'To manage the team daily', 'To evaluate performance only', 'B', 'Medium - Coach mindset.', 'Coaching Role', '2026-04-19 17:16:04', 20),
(978, 'What is active listening in coaching?', 'Fully concentrating and understanding what is being said', 'Thinking about your reply while the person speaks', 'Interrupting frequently', 'Giving advice immediately', 'A', 'Easy - Core skill.', 'Active Listening', '2026-04-19 17:16:04', 20),
(979, 'What is psychological safety in team development?', 'Feeling safe to take risks and speak up without fear', 'Strict rules and hierarchy', 'Avoiding all conflict', 'Working in isolation', 'A', 'Medium - Team foundation.', 'Team Development', '2026-04-19 17:16:04', 20),
(980, 'Which leadership style supports team development best?', 'Servant leadership and coaching approach', 'Autocratic command and control', 'Laissez-faire only', 'Transactional only', 'A', 'Medium - Modern leadership.', 'Leadership Styles', '2026-04-19 17:16:04', 20),
(981, 'What is feedback in a coaching context?', 'Constructive, specific, and balanced input to support growth', 'Only criticism', 'Only praise', 'Avoiding difficult conversations', 'A', 'Easy - Growth tool.', 'Giving Feedback', '2026-04-19 17:16:04', 20),
(982, 'What is Tuckman’s model?', 'Forming, Storming, Norming, Performing, Adjourning', 'Planning, Executing, Monitoring', 'Goal, Reality, Options', 'Vision, Mission, Values', 'A', 'Medium - Team stages.', 'Team Development Stages', '2026-04-19 17:16:04', 20),
(983, 'What is delegation in team development?', 'Assigning tasks with responsibility and authority to develop team members', 'Doing everything yourself', 'Micromanaging', 'Avoiding responsibility', 'A', 'Medium - Empowerment.', 'Delegation', '2026-04-19 17:16:04', 20),
(984, 'What is the difference between a goal and an objective?', 'Goals are broad; objectives are specific and measurable', 'They are the same', 'Objectives are long-term', 'Goals are always short-term', 'A', 'Medium - Goal setting.', 'Goal Setting', '2026-04-19 17:16:04', 20),
(985, 'What is Apache Kafka?', 'A traditional relational database', 'A distributed event streaming platform', 'A simple message queue only', 'A web server', 'B', 'Easy - Core definition.', 'Kafka Basics', '2026-04-19 17:16:13', 19),
(986, 'What are the main components of Kafka?', 'Producer, Consumer, Broker, Topic, ZooKeeper/KRaft', 'Only databases', 'Only frontend', 'Only backend servers', 'A', 'Medium - Architecture.', 'Kafka Architecture', '2026-04-19 17:16:13', 19),
(987, 'What is a Topic in Kafka?', 'A category or feed to which records are published', 'A single message', 'A consumer group', 'A broker', 'A', 'Easy - Fundamental concept.', 'Kafka Topics', '2026-04-19 17:16:13', 19),
(988, 'What is a Partition?', 'A way to split a topic for scalability and parallelism', 'A complete topic', 'A consumer only', 'A producer only', 'A', 'Medium - Scalability.', 'Kafka Partitions', '2026-04-19 17:16:13', 19),
(989, 'What does a Producer do?', 'Reads messages from Kafka', 'Writes (publishes) messages to Kafka topics', 'Stores data permanently', 'Monitors the cluster', 'B', 'Easy - Role.', 'Producers', '2026-04-19 17:16:13', 19),
(990, 'What is Consumer Group?', 'A set of consumers that work together to consume a topic', 'A single consumer', 'A producer group', 'A broker group', 'A', 'Medium - Parallel consumption.', 'Consumers', '2026-04-19 17:16:13', 19),
(991, 'What is exactly-once semantics in Kafka?', 'Processing each message exactly one time', 'At-least-once or at-most-once only', 'No guarantee', 'Only for producers', 'A', 'Hard - Delivery semantics.', 'Kafka Reliability', '2026-04-19 17:16:13', 19),
(992, 'What is Kafka Streams?', 'A client library for building stream processing applications', 'A message queue only', 'A database', 'A monitoring tool', 'A', 'Medium - Stream processing.', 'Kafka Streams', '2026-04-19 17:16:13', 19),
(993, 'What is the role of Brokers?', 'They store data and serve producers and consumers', 'They only produce messages', 'They only consume messages', 'They are clients only', 'A', 'Medium - Cluster component.', 'Kafka Brokers', '2026-04-19 17:16:13', 19);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(994, 'What is KRaft?', 'The new consensus protocol replacing ZooKeeper', 'An old monitoring tool', 'A producer library', 'A consumer only', 'A', 'Hard - Modern Kafka.', 'Kafka Architecture', '2026-04-19 17:16:13', 19),
(995, 'What is Infrastructure as Code (IaC)?', 'Manual server configuration', 'Managing infrastructure through code and automation', 'Only using cloud consoles', 'Writing application code only', 'B', 'Easy - Core concept.', 'IaC Basics', '2026-04-19 17:16:20', 18),
(996, 'What is Terraform?', 'An open-source IaC tool by HashiCorp', 'A programming language', 'A monitoring tool', 'A container platform', 'A', 'Easy - Definition.', 'Terraform Basics', '2026-04-19 17:16:20', 18),
(997, 'What is a Terraform provider?', 'A plugin that interacts with external APIs', 'A resource only', 'A module', 'A variable', 'A', 'Medium - Architecture.', 'Providers', '2026-04-19 17:16:20', 18),
(998, 'What does \"terraform init\" do?', 'Initializes the working directory and downloads providers', 'Applies changes', 'Destroys resources', 'Validates configuration', 'A', 'Easy - First command.', 'Terraform Commands', '2026-04-19 17:16:20', 18),
(999, 'What is the purpose of terraform plan?', 'To show what changes will be made before applying', 'To create resources', 'To destroy resources', 'To format code', 'A', 'Easy - Planning.', 'Terraform Workflow', '2026-04-19 17:16:20', 18),
(1000, 'What is a Terraform resource?', 'The main building block representing infrastructure objects', 'A variable', 'A provider', 'A module only', 'A', 'Medium - Core concept.', 'Resources', '2026-04-19 17:16:20', 18),
(1001, 'What is a Terraform module?', 'A reusable package of Terraform configuration', 'A single resource', 'A provider', 'A variable', 'A', 'Medium - Reusability.', 'Modules', '2026-04-19 17:16:20', 18),
(1002, 'What is Terraform state?', 'The file that tracks the current state of managed resources', 'The configuration code', 'The provider plugin', 'The output values', 'A', 'Hard - Important concept.', 'State Management', '2026-04-19 17:16:20', 18),
(1003, 'What command applies changes?', 'terraform apply', 'terraform plan', 'terraform init', 'terraform destroy', 'A', 'Easy - Deployment.', 'Terraform Commands', '2026-04-19 17:16:20', 18),
(1004, 'What is \"terraform destroy\"?', 'Destroys all managed resources', 'Creates resources', 'Validates code', 'Formats code', 'A', 'Medium - Cleanup.', 'Terraform Commands', '2026-04-19 17:16:20', 18),
(1005, 'What is the main advantage of GraphQL over REST?', 'Clients can request exactly the data they need', 'Fixed endpoints only', 'Requires multiple requests', 'Only supports JSON', 'A', 'Easy - Core benefit.', 'GraphQL Basics', '2026-04-19 17:16:27', 17),
(1006, 'What are the three main operation types in GraphQL?', 'Query, Mutation, Subscription', 'GET, POST, PUT', 'Create, Read, Update', 'Read, Write, Delete', 'A', 'Easy - Operations.', 'GraphQL Operations', '2026-04-19 17:16:27', 17),
(1007, 'What is a GraphQL Schema?', 'The contract defining types, queries, and mutations', 'A database table', 'A frontend component', 'A REST endpoint', 'A', 'Medium - Foundation.', 'Schema Design', '2026-04-19 17:16:27', 17),
(1008, 'What is a Resolver?', 'The function that fetches data for a specific field', 'A database query', 'A frontend component', 'A security rule', 'A', 'Medium - Implementation.', 'Resolvers', '2026-04-19 17:16:27', 17),
(1009, 'What is the N+1 problem?', 'Fetching related data causes multiple database queries', 'Too many mutations', 'Too many subscriptions', 'Schema validation error', 'A', 'Hard - Performance issue.', 'Performance', '2026-04-19 17:16:27', 17),
(1010, 'What is Apollo Server?', 'A popular GraphQL server implementation for Node.js', 'A database', 'A frontend library', 'A REST framework', 'A', 'Medium - Server side.', 'GraphQL Servers', '2026-04-19 17:16:27', 17),
(1011, 'What are Subscriptions used for?', 'Real-time updates via WebSocket', 'Only reading data', 'Only writing data', 'Authentication', 'A', 'Medium - Real-time.', 'Subscriptions', '2026-04-19 17:16:27', 17),
(1012, 'What is DataLoader?', 'A utility to batch and cache requests to solve N+1 problem', 'A database driver', 'A frontend library', 'A testing tool', 'A', 'Hard - Optimization.', 'Performance Optimization', '2026-04-19 17:16:27', 17),
(1013, 'What is the @deprecated directive?', 'Marks a field as deprecated with a reason', 'Removes a field', 'Makes a field required', 'Hides a field', 'A', 'Medium - Schema evolution.', 'Schema Best Practices', '2026-04-19 17:16:27', 17),
(1014, 'What is a headless CMS with GraphQL?', 'A content management system exposing data via GraphQL API', 'A traditional CMS', 'A database only', 'A frontend framework', 'A', 'Medium - Modern use case.', 'GraphQL Use Cases', '2026-04-19 17:16:27', 17),
(1015, 'What is the main advantage of GraphQL over traditional REST APIs?', 'Fixed endpoints with predetermined responses', 'Clients can request exactly the data they need in one query', 'Requires multiple HTTP methods for different operations', 'Only supports XML format', 'B', 'Easy - Solves over-fetching and under-fetching problems.', 'GraphQL Basics', '2026-04-19 17:18:54', 17),
(1016, 'In GraphQL, what type of operation is used to read data?', 'Mutation', 'Query', 'Subscription', 'Delete', 'B', 'Easy - Read operations.', 'GraphQL Operations', '2026-04-19 17:18:54', 17),
(1017, 'What is used in GraphQL to modify data on the server?', 'Query', 'Mutation', 'Subscription', 'Fragment', 'B', 'Easy - Write operations.', 'GraphQL Operations', '2026-04-19 17:18:54', 17),
(1018, 'What component executes the logic to fetch data for each field in a GraphQL schema?', 'Schema', 'Resolver', 'Directive', 'Type', 'B', 'Medium - Core implementation piece.', 'Resolvers', '2026-04-19 17:18:54', 17),
(1019, 'What language is used to write GraphQL schemas?', 'SQL', 'GraphQL Schema Definition Language (SDL)', 'JSON Schema', 'YAML', 'B', 'Easy - Schema definition.', 'Schema Design', '2026-04-19 17:18:54', 17),
(1020, 'What problem does DataLoader solve in GraphQL?', 'N+1 query problem by batching and caching requests', 'Authentication issues', 'Subscription management', 'Schema validation', 'A', 'Hard - Performance optimization.', 'Performance', '2026-04-19 17:18:54', 17),
(1021, 'What are Subscriptions in GraphQL used for?', 'Real-time data updates using WebSockets', 'Only reading static data', 'Data modification', 'File uploads', 'A', 'Medium - Real-time features.', 'Subscriptions', '2026-04-19 17:18:54', 17),
(1022, 'What is Apollo Server?', 'A popular GraphQL server implementation for Node.js', 'A database ORM', 'A frontend framework', 'A testing library', 'A', 'Medium - Server-side tool.', 'GraphQL Servers', '2026-04-19 17:18:54', 17),
(1023, 'What does the @deprecated directive do?', 'Marks a field as deprecated with an optional reason', 'Deletes a field permanently', 'Makes a field required', 'Hides a field from clients', 'A', 'Medium - Schema evolution.', 'Schema Best Practices', '2026-04-19 17:18:54', 17),
(1024, 'What is GraphQL Federation?', 'A way to combine multiple GraphQL services into a single unified graph', 'A caching strategy', 'A security protocol', 'A testing framework', 'A', 'Hard - Microservices architecture.', 'Advanced GraphQL', '2026-04-19 17:18:54', 17),
(1025, 'What is the purpose of Fragments in GraphQL?', 'To reuse common field selections across queries', 'To define new types', 'To handle authentication', 'To create mutations', 'A', 'Medium - Query reusability.', 'Queries', '2026-04-19 17:18:54', 17),
(1026, 'How does GraphQL handle authentication?', 'Through context in resolvers or directives', 'Only via HTTP headers', 'Not supported', 'Only cookies', 'A', 'Medium - Security.', 'GraphQL Security', '2026-04-19 17:18:54', 17),
(1027, 'What is the N+1 problem?', 'Multiple database queries triggered by nested data requests', 'Too many mutations', 'Schema validation failure', 'Subscription overload', 'A', 'Hard - Common performance issue.', 'Performance Optimization', '2026-04-19 17:18:54', 17),
(1028, 'What is a headless CMS?', 'A content management system that exposes content via GraphQL API', 'A traditional monolithic CMS', 'A frontend framework', 'A database only', 'A', 'Medium - Modern use case.', 'GraphQL Use Cases', '2026-04-19 17:18:54', 17),
(1029, 'What command is typically used to start an Apollo Server?', 'apollo start', 'node server.js', 'graphql serve', 'npm run dev', 'B', 'Medium - Development.', 'GraphQL Servers', '2026-04-19 17:18:54', 17),
(1030, 'What is the role of the introspection query?', 'To discover the schema of a GraphQL API', 'To execute mutations', 'To delete data', 'To cache responses', 'A', 'Medium - Development tool.', 'Schema Exploration', '2026-04-19 17:18:54', 17),
(1031, 'What is rate limiting in GraphQL?', 'Restricting the number of requests or complexity to prevent abuse', 'Increasing response speed', 'Disabling subscriptions', 'Removing resolvers', 'A', 'Hard - Security & performance.', 'GraphQL Security', '2026-04-19 17:18:54', 17),
(1032, 'What does the @auth directive typically do?', 'Enforces authorization rules on fields or types', 'Defines new types', 'Creates queries', 'Handles caching', 'A', 'Hard - Security.', 'GraphQL Security', '2026-04-19 17:18:54', 17),
(1033, 'What is Relay in the context of GraphQL?', 'A client framework for React that works efficiently with GraphQL', 'A server implementation', 'A database', 'A testing tool', 'A', 'Hard - Client-side.', 'GraphQL Clients', '2026-04-19 17:18:54', 17),
(1034, 'What is cursor-based pagination?', 'Using opaque cursors for efficient pagination instead of offset', 'Using page numbers only', 'Loading all data at once', 'No pagination', 'A', 'Hard - Best practice.', 'Pagination', '2026-04-19 17:18:54', 17),
(1035, 'What is the primary benefit of Infrastructure as Code?', 'Manual configuration is faster', 'Consistent, repeatable, and version-controlled infrastructure', 'Higher costs', 'Slower deployments', 'B', 'Easy - IaC advantage.', 'IaC Fundamentals', '2026-04-19 17:19:01', 18),
(1036, 'What does \"terraform init\" command do?', 'Applies changes to infrastructure', 'Initializes the workspace and downloads providers', 'Destroys all resources', 'Validates configuration syntax', 'B', 'Easy - First step.', 'Terraform Commands', '2026-04-19 17:19:01', 18),
(1037, 'What is a Terraform provider?', 'A plugin that allows Terraform to interact with external APIs', 'A reusable module', 'A variable definition', 'A resource type', 'A', 'Medium - Core component.', 'Providers', '2026-04-19 17:19:01', 18),
(1038, 'What command shows what changes Terraform will make?', 'terraform apply', 'terraform plan', 'terraform destroy', 'terraform validate', 'B', 'Easy - Planning step.', 'Terraform Workflow', '2026-04-19 17:19:01', 18),
(1039, 'What is Terraform state?', 'The record of all resources managed by Terraform', 'The configuration files', 'The provider plugins', 'The output values only', 'A', 'Medium - Critical concept.', 'State Management', '2026-04-19 17:19:01', 18),
(1040, 'What is a Terraform module?', 'A container for multiple resources that can be reused', 'A single resource', 'A provider', 'A variable', 'A', 'Medium - Reusability.', 'Modules', '2026-04-19 17:19:01', 18),
(1041, 'What does \"terraform apply\" do?', 'Creates or updates infrastructure based on configuration', 'Shows planned changes', 'Initializes providers', 'Destroys resources', 'A', 'Easy - Deployment.', 'Terraform Commands', '2026-04-19 17:19:01', 18),
(1042, 'What is the recommended way to store Terraform state in production?', 'Remote backend (S3, Terraform Cloud, etc.)', 'Local state file only', 'In version control', 'In a text document', 'A', 'Medium - Best practice.', 'State Management', '2026-04-19 17:19:01', 18),
(1043, 'What are Terraform variables used for?', 'To parameterize configurations and make them reusable', 'To store secrets permanently', 'To define resources', 'To run commands', 'A', 'Easy - Configuration.', 'Variables', '2026-04-19 17:19:01', 18),
(1044, 'What is \"terraform destroy\"?', 'Safely removes all resources managed by Terraform', 'Applies changes', 'Initializes the project', 'Validates code', 'A', 'Medium - Cleanup.', 'Terraform Commands', '2026-04-19 17:19:01', 18),
(1045, 'What is Apache Kafka mainly used for?', 'Relational data storage', 'Building real-time data pipelines and streaming platforms', 'Simple file storage', 'Web hosting', 'B', 'Easy - Core use case.', 'Kafka Basics', '2026-04-19 17:19:08', 19),
(1046, 'What is a Kafka Topic?', 'A category to which messages are published', 'A single consumer', 'A database table', 'A broker only', 'A', 'Easy - Fundamental concept.', 'Kafka Topics', '2026-04-19 17:19:08', 19),
(1047, 'What is a Kafka Partition?', 'Unit of parallelism and scalability within a topic', 'A complete message', 'A consumer group', 'A producer', 'A', 'Medium - Scalability.', 'Partitions', '2026-04-19 17:19:08', 19),
(1048, 'What does a Kafka Producer do?', 'Publishes messages to topics', 'Consumes messages', 'Stores data long-term', 'Monitors cluster health', 'A', 'Easy - Role.', 'Producers', '2026-04-19 17:19:08', 19),
(1049, 'What is a Consumer Group in Kafka?', 'A group of consumers that coordinate to consume topics in parallel', 'A single consumer', 'A producer group', 'A broker', 'A', 'Medium - Consumption model.', 'Consumers', '2026-04-19 17:19:08', 19),
(1050, 'What is the role of Brokers in Kafka?', 'They store data and handle client requests', 'They only produce messages', 'They only consume messages', 'They manage configuration only', 'A', 'Medium - Cluster component.', 'Kafka Brokers', '2026-04-19 17:19:08', 19),
(1051, 'What is Exactly-Once Semantics?', 'Guarantee that each message is processed exactly one time', 'At-least-once delivery', 'At-most-once delivery', 'No delivery guarantee', 'A', 'Hard - Reliability.', 'Kafka Reliability', '2026-04-19 17:19:08', 19),
(1052, 'What is Kafka Streams?', 'A library for building real-time stream processing applications', 'A message queue only', 'A database connector', 'A monitoring tool', 'A', 'Medium - Stream processing.', 'Kafka Streams', '2026-04-19 17:19:08', 19),
(1053, 'What is KRaft?', 'The new quorum-based consensus protocol replacing ZooKeeper', 'An old monitoring tool', 'A producer library', 'A consumer API', 'A', 'Hard - Modern architecture.', 'Kafka Architecture', '2026-04-19 17:19:08', 19),
(1054, 'What is Schema Registry used for?', 'Managing and enforcing schemas for messages', 'Storing raw data', 'Monitoring brokers', 'Load balancing', 'A', 'Medium - Data governance.', 'Schema Registry', '2026-04-19 17:19:08', 19),
(1055, 'What is the primary goal of coaching?', 'To give direct solutions to problems', 'To help individuals or teams find their own solutions and develop skills', 'To evaluate performance only', 'To manage daily operations', 'B', 'Easy - Coaching mindset.', 'Coaching Fundamentals', '2026-04-19 17:19:15', 20),
(1056, 'What does the GROW coaching model stand for?', 'Goal, Reality, Options, Will', 'Give, Receive, Observe, Work', 'Group, Review, Order, Win', 'Goal, Result, Output, Win', 'A', 'Medium - Classic model.', 'Coaching Models', '2026-04-19 17:19:15', 20),
(1057, 'What is a key skill of an effective coach?', 'Active listening and asking powerful questions', 'Giving advice constantly', 'Making decisions for the team', 'Avoiding difficult conversations', 'A', 'Medium - Core competency.', 'Coaching Skills', '2026-04-19 17:19:15', 20),
(1058, 'What is the difference between coaching and mentoring?', 'Coaching is goal-oriented and short-term; mentoring is long-term guidance', 'They are the same', 'Mentoring is only for managers', 'Coaching is directive', 'A', 'Medium - Comparison.', 'Coaching vs Mentoring', '2026-04-19 17:19:15', 20),
(1059, 'What is psychological safety in team development?', 'The belief that the team is safe for interpersonal risk-taking', 'Strict hierarchy and rules', 'Avoiding all conflict', 'Individual competition', 'A', 'Medium - Team culture.', 'Team Development', '2026-04-19 17:19:15', 20),
(1060, 'What is the primary focus of coaching in a team context?', 'Giving orders to team members', 'Helping individuals unlock their potential and achieve specific goals', 'Evaluating past performance only', 'Managing daily tasks for the team', 'B', 'Easy - Coaching purpose.', 'Coaching Fundamentals', '2026-04-19 17:20:42', 20),
(1061, 'Which coaching model is widely used for structured conversations?', 'SMART model', 'GROW model (Goal, Reality, Options, Will)', 'SWOT analysis', 'KPI tracking', 'B', 'Medium - Popular framework.', 'Coaching Models', '2026-04-19 17:20:42', 20),
(1062, 'What is a powerful questioning technique in coaching?', 'Asking closed yes/no questions', 'Using open-ended questions that encourage reflection', 'Giving direct advice', 'Interrupting frequently', 'B', 'Medium - Core skill.', 'Coaching Skills', '2026-04-19 17:20:42', 20),
(1063, 'What does \"active listening\" involve in coaching?', 'Thinking about your response while the person speaks', 'Fully focusing on the speaker and reflecting back understanding', 'Changing the subject quickly', 'Giving solutions immediately', 'B', 'Easy - Essential skill.', 'Active Listening', '2026-04-19 17:20:42', 20),
(1064, 'What is the difference between directive and non-directive coaching?', 'Directive coaching gives advice; non-directive helps the person find their own solutions', 'They are the same approach', 'Non-directive is faster', 'Directive avoids questions', 'A', 'Medium - Coaching styles.', 'Coaching Approaches', '2026-04-19 17:20:42', 20),
(1065, 'What is delegation in team development?', 'Assigning tasks while giving the necessary authority and responsibility', 'Doing the work yourself to ensure quality', 'Micromanaging every step', 'Avoiding responsibility', 'A', 'Medium - Empowerment tool.', 'Delegation', '2026-04-19 17:20:42', 20),
(1066, 'What is Tuckman’s team development model?', 'Forming, Storming, Norming, Performing, Adjourning', 'Planning, Executing, Monitoring', 'Goal, Reality, Options, Will', 'Vision, Mission, Strategy', 'A', 'Medium - Team stages.', 'Team Development', '2026-04-19 17:20:42', 20),
(1067, 'What is psychological safety essential for?', 'Creating an environment where team members feel safe to take risks and speak up', 'Enforcing strict rules', 'Reducing team size', 'Increasing competition', 'A', 'Medium - Team culture.', 'Team Culture', '2026-04-19 17:20:42', 20),
(1068, 'How should feedback be given in a coaching context?', 'Specific, balanced, timely, and focused on behavior', 'Vague and general', 'Only negative criticism', 'Only positive praise', 'A', 'Medium - Effective feedback.', 'Feedback Techniques', '2026-04-19 17:20:42', 20),
(1069, 'What is the role of emotional intelligence in team coaching?', 'Understanding and managing emotions to improve team dynamics', 'Focusing only on technical skills', 'Avoiding emotions completely', 'Using emotions to control the team', 'A', 'Hard - Leadership skill.', 'Emotional Intelligence', '2026-04-19 17:20:42', 20),
(1070, 'What is motivation theory (Herzberg) mainly about?', 'Hygiene factors and motivators', 'Only financial rewards', 'Strict hierarchy', 'Punishment systems', 'A', 'Hard - Motivation.', 'Motivation Theories', '2026-04-19 17:20:42', 20),
(1071, 'What is conflict resolution in teams?', 'Addressing disagreements constructively to reach better outcomes', 'Avoiding all conflict', 'Winning arguments', 'Blaming individuals', 'A', 'Medium - Team skill.', 'Conflict Management', '2026-04-19 17:20:42', 20),
(1072, 'What does \"servant leadership\" mean?', 'Putting the needs of the team first to help them grow', 'Commanding and controlling the team', 'Avoiding responsibility', 'Focusing only on personal goals', 'A', 'Medium - Leadership style.', 'Leadership Styles', '2026-04-19 17:20:42', 20),
(1073, 'What is performance coaching?', 'Helping team members improve their skills and achieve performance goals', 'Only annual evaluation', 'Giving orders', 'Ignoring underperformance', 'A', 'Medium - Development.', 'Performance Coaching', '2026-04-19 17:20:42', 20),
(1074, 'Why is trust important in team development?', 'It enables open communication, collaboration, and risk-taking', 'It slows down decisions', 'It creates dependency', 'It is not necessary', 'A', 'Easy - Foundation.', 'Building Trust', '2026-04-19 17:20:42', 20),
(1075, 'What is a coaching conversation?', 'A structured dialogue aimed at helping the coachee gain clarity and take action', 'A one-way lecture', 'A performance review only', 'A casual chat', 'A', 'Medium - Practical application.', 'Coaching Conversations', '2026-04-19 17:20:42', 20),
(1076, 'What is the benefit of team building activities?', 'Improving relationships, trust, and collaboration', 'Replacing actual work', 'Increasing competition', 'Reducing communication', 'A', 'Easy - Team development.', 'Team Building', '2026-04-19 17:20:42', 20),
(1077, 'What is \"empowerment\" in coaching?', 'Giving team members autonomy and decision-making power', 'Doing all tasks for them', 'Micromanaging', 'Avoiding delegation', 'A', 'Medium - Development.', 'Empowerment', '2026-04-19 17:20:42', 20),
(1078, 'What should a coach do when facing resistance?', 'Listen actively, explore the resistance, and adjust approach', 'Ignore it and push harder', 'Give up immediately', 'Blame the coachee', 'A', 'Hard - Coaching skill.', 'Handling Resistance', '2026-04-19 17:20:42', 20),
(1079, 'What is the difference between a coach and a manager?', 'A coach facilitates growth; a manager focuses on results and operations', 'They have identical roles', 'A coach gives orders', 'A manager only coaches', 'A', 'Medium - Role clarity.', 'Coach vs Manager', '2026-04-19 17:20:42', 20),
(1080, 'What is \"accountability\" in team coaching?', 'Taking ownership of commitments and results', 'Blaming others for failures', 'Avoiding responsibility', 'Only setting goals', 'A', 'Medium - Team value.', 'Accountability', '2026-04-19 17:20:42', 20),
(1081, 'What is the GROW model\'s \"Options\" stage?', 'Exploring possible solutions and strategies', 'Setting the goal', 'Assessing current reality', 'Committing to action', 'A', 'Medium - Model detail.', 'Coaching Models', '2026-04-19 17:20:42', 20),
(1082, 'How can a leader develop a high-performing team?', 'Through clear goals, trust, feedback, and continuous development', 'By micromanaging', 'By avoiding feedback', 'By working alone', 'A', 'Hard - Team leadership.', 'High-Performing Teams', '2026-04-19 17:20:42', 20),
(1083, 'What is \"reflective practice\" in coaching?', 'Regularly reviewing experiences to learn and improve', 'Repeating the same mistakes', 'Avoiding self-assessment', 'Focusing only on others', 'A', 'Medium - Professional growth.', 'Reflective Practice', '2026-04-19 17:20:42', 20),
(1084, 'What is the importance of contracting in coaching?', 'Establishing clear agreements on goals, roles, and expectations', 'Skipping agreements', 'Making decisions unilaterally', 'Avoiding boundaries', 'A', 'Hard - Professional standard.', 'Coaching Process', '2026-04-19 17:20:42', 20),
(1085, 'What is \"situational leadership\" in team development?', 'Adapting leadership style based on team maturity and task', 'Using one style for all situations', 'Avoiding leadership', 'Only directive style', 'A', 'Hard - Adaptive leadership.', 'Leadership Styles', '2026-04-19 17:20:42', 20),
(1086, 'What is a common coaching pitfall?', 'Asking too many leading questions or giving advice too early', 'Listening actively', 'Using open questions', 'Building trust', 'A', 'Hard - Common mistake.', 'Coaching Pitfalls', '2026-04-19 17:20:42', 20),
(1087, 'What is \"team charter\"?', 'A document defining team purpose, values, roles, and working agreements', 'A budget plan', 'A project timeline only', 'An individual contract', 'A', 'Medium - Team alignment.', 'Team Alignment', '2026-04-19 17:20:42', 20),
(1088, 'What does \"facilitation\" mean in team coaching?', 'Guiding group processes to achieve better outcomes', 'Making all decisions', 'Lecturing the team', 'Avoiding group discussions', 'A', 'Medium - Skill.', 'Facilitation', '2026-04-19 17:20:42', 20),
(1089, 'What is the benefit of regular team retrospectives?', 'Continuous improvement through reflection on what worked and what didn\'t', 'Maintaining status quo', 'Avoiding feedback', 'Increasing workload', 'A', 'Medium - Continuous improvement.', 'Team Retrospectives', '2026-04-19 17:20:42', 20),
(1090, 'What is \"motivational interviewing\" used for in coaching?', 'Helping individuals resolve ambivalence and increase motivation', 'Giving direct orders', 'Criticizing performance', 'Avoiding difficult topics', 'A', 'Hard - Advanced technique.', 'Motivational Interviewing', '2026-04-19 17:20:42', 20),
(1091, 'How can a coach support diversity and inclusion in teams?', 'By creating an inclusive environment where all voices are valued', 'By ignoring differences', 'By favoring certain members', 'By avoiding the topic', 'A', 'Hard - Modern coaching.', 'Diversity & Inclusion', '2026-04-19 17:20:42', 20),
(1092, 'What is the purpose of a remote backend in Terraform?', 'To store state file securely and enable collaboration', 'To run terraform init only', 'To destroy resources', 'To format code', 'A', 'Medium - Best practice.', 'State Management', '2026-04-19 17:20:51', 18),
(1093, 'What does \"terraform workspace\" allow?', 'Managing multiple environments with the same code', 'Deleting resources', 'Creating new providers', 'Formatting configuration', 'A', 'Medium - Environment management.', 'Workspaces', '2026-04-19 17:20:51', 18),
(1094, 'What is a Terraform output?', 'A value exported from the configuration for use elsewhere', 'A resource definition', 'A provider plugin', 'A variable input', 'A', 'Easy - Configuration.', 'Outputs', '2026-04-19 17:20:51', 18),
(1095, 'What command validates Terraform configuration syntax?', 'terraform validate', 'terraform plan', 'terraform apply', 'terraform destroy', 'A', 'Easy - Validation.', 'Terraform Commands', '2026-04-19 17:20:51', 18),
(1096, 'What is \"terraform fmt\"?', 'Formats configuration files to a canonical style', 'Applies changes', 'Destroys resources', 'Initializes providers', 'A', 'Easy - Code formatting.', 'Terraform Commands', '2026-04-19 17:20:51', 18),
(1097, 'What is the recommended way to handle sensitive data in Terraform?', 'Use variables with sensitive = true or secret management tools', 'Hardcode secrets in code', 'Store in local state only', 'Ignore sensitivity', 'A', 'Hard - Security.', 'Security Best Practices', '2026-04-19 17:20:51', 18),
(1098, 'What is a data source in Terraform?', 'A resource that fetches data from an external system', 'A managed resource', 'A module', 'A provider', 'A', 'Medium - Data fetching.', 'Data Sources', '2026-04-19 17:20:51', 18),
(1099, 'What does \"count\" meta-argument do?', 'Creates multiple instances of a resource', 'Deletes resources', 'Formats code', 'Validates configuration', 'A', 'Medium - Resource scaling.', 'Meta Arguments', '2026-04-19 17:20:51', 18),
(1100, 'What is \"for_each\" used for?', 'Creating multiple resource instances using a map or set', 'Looping inside resources', 'Deleting resources', 'Formatting code', 'A', 'Hard - Advanced looping.', 'Meta Arguments', '2026-04-19 17:20:51', 18),
(1101, 'What is Terraform Cloud used for?', 'Remote state storage, collaboration, and CI/CD integration', 'Only local development', 'Writing configuration', 'Destroying resources', 'A', 'Medium - Enterprise feature.', 'Terraform Cloud', '2026-04-19 17:20:51', 18),
(1102, 'What is a Kafka Record?', 'A message containing key, value, timestamp, and headers', 'A complete topic', 'A consumer group', 'A broker only', 'A', 'Easy - Message structure.', 'Kafka Messages', '2026-04-19 17:20:58', 19),
(1103, 'What is rebalancing in Kafka?', 'Reassignment of partitions to consumers when group membership changes', 'Deleting topics', 'Creating producers', 'Formatting messages', 'A', 'Medium - Consumer behavior.', 'Consumers', '2026-04-19 17:20:58', 19),
(1104, 'What is idempotent producer?', 'A producer that avoids duplicate messages', 'A consumer that reads twice', 'A broker that crashes', 'A topic with one partition', 'A', 'Hard - Reliability.', 'Producers', '2026-04-19 17:20:58', 19),
(1105, 'What is Kafka Connect?', 'A framework for connecting Kafka with external systems', 'A stream processing library', 'A monitoring tool', 'A schema registry', 'A', 'Medium - Integration.', 'Kafka Connect', '2026-04-19 17:20:58', 19),
(1106, 'What is the purpose of consumer offset?', 'To track the position of the last consumed message', 'To store message content', 'To define topics', 'To manage brokers', 'A', 'Medium - Consumption tracking.', 'Consumers', '2026-04-19 17:20:58', 19),
(1107, 'What is compaction in Kafka?', 'Removing old messages based on key to keep only the latest value', 'Deleting entire topics', 'Increasing partitions', 'Adding more brokers', 'A', 'Hard - Log cleanup.', 'Kafka Internals', '2026-04-19 17:20:58', 19),
(1108, 'What is the advantage of using Avro with Kafka?', 'Schema evolution and serialization', 'Faster network only', 'No schema needed', 'Text-only messages', 'A', 'Medium - Data format.', 'Schema Management', '2026-04-19 17:20:58', 19),
(1109, 'What is a Kafka MirrorMaker?', 'Tool for replicating data between Kafka clusters', 'A producer library', 'A consumer only', 'A monitoring dashboard', 'A', 'Hard - Cross-cluster.', 'Kafka Replication', '2026-04-19 17:20:58', 19),
(1110, 'What is transaction in Kafka?', 'Atomic write across multiple topics and partitions', 'Simple message sending', 'Consumer reading', 'Broker restart', 'A', 'Hard - Advanced feature.', 'Kafka Transactions', '2026-04-19 17:20:58', 19),
(1111, 'What is the recommended number of partitions for high throughput?', 'Depends on expected load and consumers; usually more than one', 'Always 1', 'Always 1000', 'Never more than 3', 'A', 'Hard - Design decision.', 'Kafka Design', '2026-04-19 17:20:58', 19),
(1112, 'What is GraphQL Resolver chaining?', 'Calling multiple resolvers for nested fields', 'Simple query execution', 'Mutation only', 'Subscription handling', 'A', 'Hard - Execution flow.', 'Resolvers', '2026-04-19 17:21:04', 17),
(1113, 'What is Apollo Client?', 'A comprehensive GraphQL client for frontend applications', 'A server only', 'A database', 'A testing tool', 'A', 'Medium - Client side.', 'GraphQL Clients', '2026-04-19 17:21:04', 17),
(1114, 'What is pagination in GraphQL?', 'Fetching data in smaller chunks using cursors or offsets', 'Loading all data at once', 'No data fetching', 'Only mutations', 'A', 'Medium - Best practice.', 'Pagination', '2026-04-19 17:21:04', 17),
(1115, 'What is GraphQL error handling best practice?', 'Returning errors in the errors field with proper codes', 'Throwing exceptions only', 'Ignoring errors', 'Returning HTML', 'A', 'Hard - Robust APIs.', 'Error Handling', '2026-04-19 17:21:04', 17),
(1116, 'What is \"batching\" in GraphQL?', 'Combining multiple queries into one request', 'Sending separate requests', 'Only subscriptions', 'Deleting data', 'A', 'Medium - Performance.', 'Performance', '2026-04-19 17:21:04', 17),
(1117, 'What is the role of context in GraphQL resolvers?', 'Passing request-specific data like user authentication', 'Defining schema only', 'Handling mutations', 'Creating types', 'A', 'Medium - Execution context.', 'Resolvers', '2026-04-19 17:21:04', 17),
(1118, 'What is GraphQL Playground?', 'An interactive IDE for testing GraphQL queries', 'A production server', 'A database tool', 'A deployment platform', 'A', 'Easy - Development tool.', 'Development Tools', '2026-04-19 17:21:04', 17),
(1119, 'What is \"over-fetching\" in REST vs GraphQL?', 'REST often returns more data than needed; GraphQL allows precise selection', 'GraphQL always over-fetches', 'They are identical', 'REST is always better', 'A', 'Medium - Comparison.', 'GraphQL Advantages', '2026-04-19 17:21:04', 17),
(1120, 'What is a Union type in GraphQL?', 'A type that can be one of several object types', 'A simple scalar', 'An interface only', 'A mutation', 'A', 'Hard - Schema design.', 'Schema Design', '2026-04-19 17:21:04', 17),
(1121, 'What is the purpose of directives in GraphQL?', 'To add metadata or modify execution behavior (@include, @skip, @deprecated)', 'To define new types', 'To handle authentication only', 'To create resolvers', 'A', 'Hard - Advanced schema.', 'Directives', '2026-04-19 17:21:04', 17),
(1122, 'What is the purpose of the \"lifecycle\" block in Terraform?', 'To customize resource behavior during creation, update, or destruction', 'To define variables', 'To format code', 'To initialize providers', 'A', 'Hard - Advanced resource control.', 'Terraform Resources', '2026-04-19 17:22:04', 18),
(1123, 'What does the \"depends_on\" meta-argument do?', 'Explicitly specifies hidden dependencies between resources', 'Creates resources in parallel', 'Deletes resources', 'Formats configuration', 'A', 'Medium - Dependency management.', 'Meta Arguments', '2026-04-19 17:22:04', 18),
(1124, 'What is a Terraform provisioner?', 'A mechanism to execute scripts on a resource after creation or before destruction', 'A data source', 'A module', 'A variable', 'A', 'Hard - Post-creation actions.', 'Provisioners', '2026-04-19 17:22:04', 18),
(1125, 'What is the recommended way to manage Terraform state securely?', 'Use a remote backend with encryption and versioning', 'Store state in Git repository', 'Keep state locally only', 'Ignore state management', 'A', 'Medium - Security best practice.', 'State Management', '2026-04-19 17:22:04', 18),
(1126, 'What is \"terraform taint\"?', 'Marks a resource for recreation on the next apply', 'Deletes a resource', 'Validates configuration', 'Formats code', 'A', 'Hard - Resource management.', 'Terraform Commands', '2026-04-19 17:22:04', 18),
(1127, 'What are dynamic blocks used for in Terraform?', 'To create nested configuration blocks dynamically', 'To delete resources', 'To initialize providers', 'To output values', 'A', 'Hard - Conditional configuration.', 'Dynamic Blocks', '2026-04-19 17:22:04', 18),
(1128, 'What is the difference between \"count\" and \"for_each\"?', 'count uses integers; for_each uses maps or sets for more flexibility', 'They are identical', 'count is deprecated', 'for_each cannot scale', 'A', 'Hard - Resource scaling.', 'Meta Arguments', '2026-04-19 17:22:04', 18),
(1129, 'What is a Terraform workspace used for?', 'Managing multiple environments with the same codebase', 'Storing sensitive data', 'Running terraform plan only', 'Formatting files', 'A', 'Medium - Environment isolation.', 'Workspaces', '2026-04-19 17:22:04', 18),
(1130, 'What does \"terraform refresh\" do?', 'Updates the state file based on real infrastructure without making changes', 'Applies changes', 'Destroys resources', 'Initializes the project', 'A', 'Medium - State synchronization.', 'Terraform Commands', '2026-04-19 17:22:04', 18),
(1131, 'What is the purpose of \"null_resource\"?', 'To run provisioners without creating a real infrastructure resource', 'To define providers', 'To create variables', 'To output values', 'A', 'Hard - Advanced use case.', 'Null Resource', '2026-04-19 17:22:04', 18),
(1132, 'What is \"sentinel\" in Terraform Enterprise?', 'Policy as code to enforce governance rules', 'A monitoring tool', 'A database backend', 'A formatting command', 'A', 'Hard - Enterprise governance.', 'Terraform Enterprise', '2026-04-19 17:22:04', 18),
(1133, 'What is a \"local-exec\" provisioner?', 'Executes commands on the machine running Terraform', 'Runs commands on remote resources', 'Deletes local files', 'Formats code', 'A', 'Medium - Provisioners.', 'Provisioners', '2026-04-19 17:22:04', 18),
(1134, 'What is the best practice for Terraform module design?', 'Keep modules small, focused, and reusable with clear inputs/outputs', 'Put everything in one file', 'Avoid using modules', 'Hardcode all values', 'A', 'Medium - Best practice.', 'Module Design', '2026-04-19 17:22:04', 18),
(1135, 'What does the \"ignore_changes\" lifecycle rule do?', 'Ignores certain attribute changes during plan/apply', 'Forces resource recreation', 'Deletes attributes', 'Formats code', 'A', 'Hard - Lifecycle customization.', 'Lifecycle Rules', '2026-04-19 17:22:04', 18),
(1136, 'What is Terraform Cloud / Terraform Enterprise mainly used for?', 'Remote state, collaboration, VCS integration, and policy enforcement', 'Local development only', 'Writing configuration code', 'Destroying infrastructure', 'A', 'Medium - Collaboration.', 'Terraform Cloud', '2026-04-19 17:22:04', 18),
(1137, 'What command is used to remove a resource from Terraform state?', 'terraform state rm', 'terraform destroy', 'terraform apply', 'terraform init', 'A', 'Hard - State manipulation.', 'State Management', '2026-04-19 17:22:04', 18),
(1138, 'What is \"drift\" in Terraform?', 'Difference between actual infrastructure and Terraform state', 'Code formatting issue', 'Provider version mismatch', 'Variable error', 'A', 'Medium - Reconciliation.', 'State Management', '2026-04-19 17:22:04', 18),
(1139, 'What is the purpose of \"terraform import\"?', 'To bring existing infrastructure under Terraform management', 'To export configuration', 'To destroy resources', 'To format files', 'A', 'Hard - Existing resources.', 'Terraform Commands', '2026-04-19 17:22:04', 18),
(1140, 'What is a \"data source\" vs \"resource\"?', 'Data source reads data; resource manages (creates/updates/deletes) infrastructure', 'They are the same', 'Data source manages resources', 'Resource only reads data', 'A', 'Medium - Key distinction.', 'Data Sources', '2026-04-19 17:22:04', 18),
(1141, 'What is the recommended folder structure for a large Terraform project?', 'Using modules, environments, and separate files for variables and outputs', 'Everything in main.tf', 'No organization', 'Only one file', 'A', 'Hard - Project organization.', 'Project Structure', '2026-04-19 17:22:04', 18),
(1142, 'What is the role of a Kafka Producer?', 'To publish messages to topics', 'To consume messages from topics', 'To store messages permanently', 'To monitor cluster health', 'A', 'Easy - Producer role.', 'Producers', '2026-04-19 17:22:16', 19),
(1143, 'What is Consumer Rebalancing?', 'Redistribution of partitions when consumers join or leave a group', 'Creating new topics', 'Deleting messages', 'Formatting records', 'A', 'Medium - Consumer behavior.', 'Consumers', '2026-04-19 17:22:16', 19),
(1144, 'What is the purpose of message keys in Kafka?', 'To ensure messages with the same key go to the same partition', 'To encrypt messages', 'To delete messages', 'To monitor throughput', 'A', 'Medium - Partitioning.', 'Kafka Messages', '2026-04-19 17:22:16', 19),
(1145, 'What is Kafka Connect used for?', 'Integrating Kafka with external systems using connectors', 'Stream processing only', 'Monitoring brokers', 'Schema validation only', 'A', 'Medium - Integration.', 'Kafka Connect', '2026-04-19 17:22:16', 19),
(1146, 'What does \"log compaction\" do in Kafka?', 'Keeps only the latest message for each key', 'Deletes all messages', 'Increases partition count', 'Adds new brokers', 'A', 'Hard - Log cleanup.', 'Kafka Internals', '2026-04-19 17:22:16', 19),
(1147, 'What is the difference between \"at-least-once\" and \"exactly-once\" delivery?', 'At-least-once may deliver duplicates; exactly-once guarantees single delivery', 'They are identical', 'Exactly-once is less reliable', 'At-least-once is only for consumers', 'A', 'Hard - Semantics.', 'Kafka Reliability', '2026-04-19 17:22:16', 19),
(1148, 'What is Schema Registry?', 'Centralized service for managing Avro/JSON/Protobuf schemas', 'A message broker', 'A consumer group', 'A monitoring dashboard', 'A', 'Medium - Schema management.', 'Schema Registry', '2026-04-19 17:22:16', 19),
(1149, 'What is idempotent producer in Kafka?', 'Ensures messages are not duplicated even if retried', 'Allows duplicate messages', 'Only works for consumers', 'Deletes messages automatically', 'A', 'Hard - Reliability feature.', 'Producers', '2026-04-19 17:22:16', 19),
(1150, 'What is MirrorMaker 2?', 'Tool for replicating data between different Kafka clusters', 'A stream processing library', 'A producer only', 'A consumer API', 'A', 'Medium - Cross-cluster replication.', 'Kafka Replication', '2026-04-19 17:22:16', 19),
(1151, 'What is the recommended way to handle large messages in Kafka?', 'Use compression and consider message size limits', 'Send without limits', 'Split into multiple small topics', 'Avoid large messages completely', 'A', 'Hard - Production tuning.', 'Kafka Best Practices', '2026-04-19 17:22:16', 19),
(1152, 'What is a Kafka Record Header?', 'Metadata attached to a message (key-value pairs)', 'The message key', 'The message value', 'The topic name', 'A', 'Medium - Message structure.', 'Kafka Messages', '2026-04-19 17:22:16', 19),
(1153, 'What does \"acks\" configuration control in producers?', 'Acknowledgment level from brokers for message durability', 'Message compression', 'Partition assignment', 'Consumer group rebalancing', 'A', 'Hard - Producer tuning.', 'Producers', '2026-04-19 17:22:16', 19),
(1154, 'What is the purpose of \"min.insync.replicas\"?', 'Ensures a minimum number of replicas acknowledge writes for durability', 'Controls consumer speed', 'Sets topic retention', 'Manages broker count', 'A', 'Hard - Durability setting.', 'Kafka Configuration', '2026-04-19 17:22:16', 19),
(1155, 'What is Kafka\'s zero-copy optimization?', 'Efficient data transfer without copying between kernel and user space', 'Data encryption', 'Message compression', 'Consumer rebalancing', 'A', 'Hard - Performance.', 'Kafka Internals', '2026-04-19 17:22:16', 19),
(1156, 'What is the best practice for choosing number of partitions?', 'Balance based on throughput, consumers, and future scaling needs', 'Always use 1 partition', 'Use maximum possible partitions', 'Never change after creation', 'A', 'Hard - Design decision.', 'Kafka Design', '2026-04-19 17:22:16', 19),
(1157, 'What is the core principle of Design Thinking?', 'Human-centered problem solving through empathy and iteration', 'Following strict technical specifications', 'Reducing costs only', 'Implementing the first idea quickly', 'A', 'Easy - Fundamental principle.', 'Design Thinking Basics', '2026-04-19 17:22:22', 22),
(1158, 'What happens during the \"Ideate\" phase?', 'Generating a wide range of ideas without judgment', 'Testing the final solution', 'Defining the problem', 'Building detailed prototypes', 'A', 'Easy - Divergent thinking.', 'Ideation Phase', '2026-04-19 17:22:22', 22),
(1159, 'What is a \"prototype\" in Design Thinking?', 'A simple, low-fidelity version created to test ideas quickly', 'The final polished product', 'A detailed business plan', 'A user requirements document', 'A', 'Medium - Experimentation tool.', 'Prototyping', '2026-04-19 17:22:22', 22),
(1160, 'Why is empathy important in Design Thinking?', 'To deeply understand users\' needs, feelings, and pain points', 'To reduce development time', 'To cut project costs', 'To follow competitor solutions', 'A', 'Medium - User focus.', 'Empathy Phase', '2026-04-19 17:22:22', 22),
(1161, 'What is the purpose of the \"Test\" phase?', 'To gather feedback and iterate on prototypes', 'To finalize the product', 'To define the problem statement', 'To generate more ideas', 'A', 'Medium - Validation and iteration.', 'Testing Phase', '2026-04-19 17:22:22', 22),
(1162, 'What is \"Divergent Thinking\"?', 'Generating many different ideas and possibilities', 'Narrowing down to one solution', 'Following existing processes', 'Criticizing ideas early', 'A', 'Medium - Creativity.', 'Creative Thinking', '2026-04-19 17:22:22', 22),
(1163, 'What does a \"Journey Map\" help visualize?', 'The end-to-end user experience and pain points', 'Project budget', 'Technical architecture', 'Team roles', 'A', 'Medium - User experience.', 'User Research', '2026-04-19 17:22:22', 22),
(1164, 'What is \"co-creation\" in Design Thinking?', 'Involving users and stakeholders actively in the design process', 'Designers working alone', 'Implementing without feedback', 'Following industry standards strictly', 'A', 'Hard - Collaborative innovation.', 'Co-Creation', '2026-04-19 17:22:22', 22),
(1165, 'What is the \"How Might We\" statement used for?', 'Reframing problems as opportunities for innovation', 'Defining technical requirements', 'Creating budget plans', 'Scheduling meetings', 'A', 'Medium - Problem framing.', 'Define Phase', '2026-04-19 17:22:22', 22),
(1166, 'What is a key benefit of rapid prototyping?', 'Failing fast and learning quickly with low cost', 'Creating perfect final products immediately', 'Avoiding user feedback', 'Spending more time on planning', 'A', 'Medium - Learning through making.', 'Prototyping', '2026-04-19 17:22:22', 22),
(1167, 'What is the purpose of GraphQL variables?', 'To pass dynamic values into queries and mutations safely', 'To define schema types', 'To handle subscriptions only', 'To format responses', 'A', 'Easy - Query flexibility.', 'GraphQL Queries', '2026-04-19 17:23:03', 17),
(1168, 'What is GraphQL batching?', 'Combining multiple operations into a single HTTP request to reduce overhead', 'Sending one query per field', 'Disabling resolvers', 'Caching all responses', 'A', 'Medium - Performance optimization.', 'GraphQL Performance', '2026-04-19 17:23:03', 17),
(1169, 'What does the @include directive do?', 'Conditionally includes a field based on a boolean variable', 'Excludes a field permanently', 'Marks a field as deprecated', 'Creates a new type', 'A', 'Medium - Query control.', 'Directives', '2026-04-19 17:23:03', 17),
(1170, 'What is the difference between Interface and Union in GraphQL?', 'Interface defines common fields; Union is a collection of unrelated types', 'They are identical', 'Union requires common fields', 'Interface cannot be used in queries', 'A', 'Hard - Schema design.', 'Schema Design', '2026-04-19 17:23:03', 17),
(1171, 'What is Apollo Client cache normalization?', 'Storing data in a flat structure by ID for efficient updates and consistency', 'Storing everything as JSON strings', 'Disabling caching', 'Only caching queries', 'A', 'Hard - Client-side caching.', 'GraphQL Clients', '2026-04-19 17:23:03', 17),
(1172, 'What is a common security risk in GraphQL?', 'Deep nesting or expensive queries leading to denial of service', 'Using too many mutations', 'Not using fragments', 'Disabling subscriptions', 'A', 'Hard - Security concern.', 'GraphQL Security', '2026-04-19 17:23:03', 17),
(1173, 'What is GraphQL Federation?', 'Combining multiple GraphQL services into one unified supergraph', 'Splitting one service into many', 'Only for frontend', 'A caching strategy', 'A', 'Hard - Microservices.', 'Advanced GraphQL', '2026-04-19 17:23:03', 17),
(1174, 'What is the purpose of the \"errors\" field in a GraphQL response?', 'To return partial success with detailed error information', 'To stop execution completely', 'To return only success data', 'To format the response', 'A', 'Medium - Error handling.', 'Error Handling', '2026-04-19 17:23:03', 17),
(1175, 'What is cursor-based pagination preferred for?', 'Infinite scrolling and consistent ordering across pages', 'Fixed page numbers only', 'Loading all data at once', 'No pagination', 'A', 'Medium - Best practice.', 'Pagination', '2026-04-19 17:23:03', 17),
(1176, 'What is the main benefit of using GraphQL Code Generator?', 'Automatically generating TypeScript types and hooks from schema', 'Writing resolvers faster', 'Improving database performance', 'Reducing server load', 'A', 'Medium - Developer experience.', 'Development Tools', '2026-04-19 17:23:03', 17),
(1177, 'What is the \"Prototype\" phase primarily about?', 'Creating low-fidelity versions to test ideas quickly and cheaply', 'Developing the final product', 'Writing detailed specifications', 'Analyzing competitor products', 'A', 'Easy - Experimentation.', 'Prototyping Phase', '2026-04-19 17:23:08', 22),
(1178, 'What does \"radical collaboration\" mean in Design Thinking?', 'Bringing together diverse people with different perspectives to solve problems', 'Working in small isolated teams', 'Following one expert opinion', 'Avoiding user involvement', 'A', 'Medium - Team approach.', 'Collaboration', '2026-04-19 17:23:08', 22),
(1179, 'What is the purpose of a \"Point of View\" statement?', 'To synthesize user insights into a clear problem statement', 'To define technical requirements', 'To create a budget plan', 'To schedule the project', 'A', 'Medium - Define phase.', 'Define Phase', '2026-04-19 17:23:08', 22),
(1180, 'What is \"bias\" in the context of Design Thinking?', 'Assumptions that prevent us from truly understanding users', 'A useful shortcut for decisions', 'A required part of innovation', 'Something to ignore completely', 'A', 'Medium - Common pitfall.', 'User Research', '2026-04-19 17:23:08', 22),
(1181, 'What is the benefit of using \"storytelling\" in Design Thinking?', 'To make insights and ideas more memorable and emotionally engaging', 'To replace actual testing', 'To reduce project time', 'To avoid user research', 'A', 'Medium - Communication tool.', 'Storytelling', '2026-04-19 17:23:08', 22),
(1182, 'What is \"iteration\" and why is it important?', 'Repeatedly refining solutions based on feedback and learning', 'Doing everything perfectly in one attempt', 'Avoiding changes after the first idea', 'Stopping after ideation', 'A', 'Medium - Core mindset.', 'Iterative Process', '2026-04-19 17:23:08', 22),
(1183, 'What is a \"Minimum Viable Prototype\"?', 'The simplest version that allows learning and validation', 'A fully polished final product', 'A detailed business case', 'A competitor analysis report', 'A', 'Medium - Lean innovation.', 'Prototyping', '2026-04-19 17:23:08', 22);
INSERT INTO `formation_final_exam_question` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`, `module_ref`, `created_at`, `formation_id`) VALUES
(1184, 'What does \"empathy\" really mean in Design Thinking?', 'Deeply understanding users’ feelings, needs, and context', 'Feeling sorry for users', 'Designing for yourself', 'Ignoring user feedback', 'A', 'Easy - Human-centered.', 'Empathy', '2026-04-19 17:23:08', 22),
(1185, 'What is the difference between \"Divergent\" and \"Convergent\" thinking?', 'Divergent generates many ideas; Convergent narrows them down to the best', 'They are the same', 'Divergent is for testing only', 'Convergent is for ideation', 'A', 'Hard - Thinking modes.', 'Creative Thinking', '2026-04-19 17:23:08', 22),
(1186, 'What is a key indicator of successful innovation culture?', 'Teams feel safe to experiment, fail, and learn quickly', 'No failures are allowed', 'All ideas must be perfect from the start', 'Innovation is only done by management', 'A', 'Hard - Innovation culture.', 'Innovation Culture', '2026-04-19 17:23:08', 22),
(1187, 'What is the purpose of the \"retention.ms\" configuration in Kafka?', 'It defines how long messages are kept in a topic before being deleted', 'It controls message size limit', 'It sets the number of partitions', 'It manages consumer group rebalancing', 'A', 'Medium - Topic configuration.', 'Kafka Configuration', '2026-04-19 17:23:50', 19),
(1188, 'What is the difference between Kafka and traditional message queues like RabbitMQ?', 'Kafka is designed for high-throughput, durable streaming and replay; traditional queues focus on point-to-point delivery', 'They are identical in all aspects', 'Kafka has no durability', 'Kafka cannot handle real-time data', 'A', 'Hard - Comparison.', 'Kafka vs Traditional Queues', '2026-04-19 17:23:50', 19),
(1189, 'What is \"log compaction\" useful for in Kafka?', 'Keeping only the latest value for each key to save space while maintaining state', 'Deleting all old messages immediately', 'Increasing the number of brokers', 'Reducing partition count', 'A', 'Hard - Log management.', 'Kafka Internals', '2026-04-19 17:23:50', 19),
(1190, 'What does \"enable.idempotence=true\" do for a Kafka producer?', 'Ensures messages are delivered exactly once without duplicates even if retries occur', 'Allows duplicate messages', 'Disables acknowledgments', 'Reduces message size', 'A', 'Hard - Producer reliability.', 'Producers', '2026-04-19 17:23:50', 19),
(1191, 'What is the recommended way to scale Kafka consumers horizontally?', 'Add more consumers to the same consumer group', 'Increase the number of topics', 'Reduce partition count', 'Use only one consumer', 'A', 'Medium - Scaling strategy.', 'Consumers', '2026-04-19 17:23:50', 19),
(1192, 'What is the main benefit of using Aggregations in Power BI?', 'Significantly improves query performance on large datasets by using pre-aggregated tables', 'Increases model size', 'Slows down reports', 'Complicates DAX calculations', 'A', 'Hard - Performance optimization.', 'Performance Optimization', '2026-04-19 17:23:57', 24),
(1193, 'What does the \"TREATAS\" DAX function allow?', 'Virtual relationships between tables without physical relationships', 'Permanent table joins', 'Data deletion', 'Report sharing', 'A', 'Hard - Advanced DAX.', 'DAX Advanced', '2026-04-19 17:23:57', 24),
(1194, 'What is \"Field Parameters\" used for in Power BI?', 'Allowing users to dynamically switch between different measures or dimensions in the same visual', 'Creating static filters', 'Managing row-level security', 'Scheduling data refresh', 'A', 'Medium - Dynamic reporting.', 'Advanced Features', '2026-04-19 17:23:57', 24),
(1195, 'What is the purpose of \"Composite Models\" in Power BI?', 'Combining Import mode and DirectQuery mode in the same semantic model', 'Using only Import mode', 'Using only DirectQuery mode', 'Disabling relationships', 'A', 'Hard - Hybrid modeling.', 'Data Modeling', '2026-04-19 17:23:57', 24),
(1196, 'What is the best practice for handling many-to-many relationships in Power BI?', 'Use a bridge table or limited many-to-many cardinality with caution', 'Create direct many-to-many relationships', 'Avoid relationships completely', 'Use only one-to-one relationships', 'A', 'Hard - Modeling best practice.', 'Data Modeling', '2026-04-19 17:23:57', 24),
(1197, 'What is the purpose of the \"const\" constructor in Flutter widgets?', 'To improve performance by allowing widget reuse and reducing memory usage', 'To make widgets mutable', 'To disable hot reload', 'To increase app size', 'A', 'Medium - Performance optimization.', 'Performance', '2026-04-19 17:24:03', 25),
(1198, 'What is the recommended way to handle platform-specific code in Flutter?', 'Using Platform class or conditional imports with dart:io', 'Rewriting the entire app for each platform', 'Ignoring platform differences', 'Using only Android code', 'A', 'Hard - Cross-platform development.', 'Platform Channels', '2026-04-19 17:24:03', 25),
(1199, 'What does the \"Isolate\" feature provide in Flutter/Dart?', 'Running heavy computations in a separate thread to keep the UI responsive', 'Simple synchronous functions', 'UI rendering optimization', 'Network request handling only', 'A', 'Hard - Concurrency.', 'Concurrency', '2026-04-19 17:24:03', 25),
(1200, 'What is the benefit of using \"Riverpod\" over basic Provider?', 'Better compile-time safety, easier testing, and more flexible dependency injection', 'Slower performance', 'More complex syntax', 'Limited to small apps', 'A', 'Hard - State management.', 'State Management', '2026-04-19 17:24:03', 25),
(1201, 'What is the main benefit of using the GROW coaching model?', 'It provides a structured yet flexible framework for coaching conversations', 'It replaces the need for listening', 'It focuses only on goals', 'It avoids difficult questions', 'A', 'Medium - Coaching framework.', 'Coaching Models', '2026-04-19 17:24:09', 20),
(1202, 'What is \"servant leadership\" in the context of team development?', 'Prioritizing the growth and well-being of team members first', 'Commanding and controlling the team', 'Focusing only on personal success', 'Avoiding responsibility', 'A', 'Medium - Leadership style.', 'Leadership in Coaching', '2026-04-19 17:24:09', 20),
(1203, 'Why is regular feedback important in team coaching?', 'It helps team members grow, align on expectations, and improve performance continuously', 'It should be given only once a year', 'It creates unnecessary conflict', 'It slows down the team', 'A', 'Easy - Team development.', 'Feedback in Teams', '2026-04-19 17:24:09', 20),
(1204, 'What is the main advantage of using Power Query over manual Excel formulas for data cleaning?', 'It provides reproducible, refreshable, and auditable transformations', 'It is slower for large data', 'It requires more manual work', 'It cannot handle multiple files', 'A', 'Medium - Best practice.', 'Power Query vs Formulas', '2026-04-19 17:24:14', 23),
(1205, 'What does \"Query Folding\" mean in Power Query?', 'Pushing transformations back to the source database for better performance', 'Loading all data into memory first', 'Creating manual calculations', 'Disabling automatic refresh', 'A', 'Hard - Performance optimization.', 'Power Query Optimization', '2026-04-19 17:24:14', 23),
(1206, 'What is the benefit of using Excel Tables (ListObjects) in advanced Excel work?', 'Automatic expansion, structured references, and better integration with Power Query', 'Slower performance on large data', 'Limited to 1000 rows', 'No formula support', 'A', 'Medium - Best practice.', 'Excel Tables', '2026-04-19 17:24:14', 23),
(1207, 'What is the key difference between management and leadership?', 'Management focuses on processes and control; leadership focuses on vision, inspiration, and people development', 'They are identical roles', 'Leadership is only about technical skills', 'Management is more important than leadership', 'A', 'Medium - Core distinction.', 'Leadership vs Management', '2026-04-19 17:24:20', 3),
(1208, 'What is \"servant leadership\"?', 'A leadership approach where the leader prioritizes the growth and well-being of team members', 'Commanding and controlling the team', 'Focusing only on personal achievements', 'Avoiding difficult decisions', 'A', 'Medium - Modern leadership style.', 'Leadership Styles', '2026-04-19 17:24:20', 3),
(1209, 'What is the Platinum Rule in communication?', 'Treat others as they would like to be treated', 'Treat others as you would like to be treated', 'Ignore others’ preferences', 'Always be direct without empathy', 'A', 'Medium - Advanced empathy.', 'Communication Principles', '2026-04-19 17:24:26', 4),
(1210, 'What is \"mirroring\" as a communication technique?', 'Subtly matching the other person’s body language or tone to build rapport', 'Copying their exact words', 'Changing the subject', 'Speaking louder than them', 'A', 'Hard - Rapport building.', 'Advanced Communication', '2026-04-19 17:24:26', 4),
(1211, 'What is the \"rule of three\" in public speaking?', 'Structuring content with three main points for better audience retention', 'Speaking for three hours', 'Using three slides only', 'Repeating every point three times', 'A', 'Medium - Rhetorical technique.', 'Speech Structure', '2026-04-19 17:24:33', 8),
(1212, 'What is the benefit of using pauses effectively during a speech?', 'To emphasize key points and give the audience time to absorb information', 'To fill silence with filler words', 'To speed up delivery', 'To show nervousness', 'A', 'Medium - Delivery skill.', 'Vocal Delivery', '2026-04-19 17:24:33', 8),
(1213, 'What is the main benefit of using feature branches in Git?', 'Allows parallel development without affecting the main codebase', 'Makes merging more difficult', 'Slows down collaboration', 'Replaces the need for commits', 'A', 'Medium - Git workflow.', 'Git Branching', '2026-04-19 17:24:40', 14),
(1214, 'What does \"Shift Left\" mean in DevOps?', 'Moving testing, security, and quality checks earlier in the development process', 'Deploying to production faster without checks', 'Reducing team size', 'Moving servers to the left side of the data center', 'A', 'Hard - DevOps principle.', 'DevOps Practices', '2026-04-19 17:24:40', 14),
(1215, 'What is cache penetration and how can it be mitigated?', 'When non-existent keys always hit the database; mitigated by caching negative responses or using Bloom filters', 'When cache is too full', 'When cache is too fast', 'When all requests hit cache', 'A', 'Hard - Caching problem.', 'Caching Challenges', '2026-04-19 17:24:45', 26),
(1216, 'What is the purpose of Redis Bloom Filter?', 'Probabilistic data structure to test membership with low memory usage', 'Exact matching of all keys', 'Storing full documents', 'Replacing regular sets', 'A', 'Hard - Advanced Redis.', 'Redis Advanced', '2026-04-19 17:24:45', 26),
(1217, 'What is the AWS Well-Architected Framework primarily used for?', 'To evaluate and improve the quality of cloud architectures based on best practices', 'To manage billing only', 'To deploy applications automatically', 'To monitor network traffic', 'A', 'Medium - AWS best practice.', 'Well-Architected Framework', '2026-04-19 17:24:51', 2),
(1218, 'What is the Eisenhower Matrix used for?', 'Prioritizing tasks based on urgency and importance', 'Scheduling meetings only', 'Tracking project budget', 'Managing team members', 'A', 'Medium - Prioritization tool.', 'Prioritization', '2026-04-19 17:25:15', 10),
(1219, 'What is the CIA triad in cybersecurity?', 'Confidentiality, Integrity, and Availability - the three core principles of information security', 'Cost, Impact, Assessment', 'Control, Identification, Authentication', 'Compliance, Investigation, Audit', 'A', 'Easy - Fundamental concept.', 'Cybersecurity Fundamentals', '2026-04-19 17:25:22', 11),
(1220, 'What is the main goal of team building activities?', 'To improve trust, collaboration, and overall team performance', 'To replace actual work', 'To increase individual competition', 'To reduce communication', 'A', 'Easy - Team development.', 'Team Building Basics', '2026-04-19 17:25:28', 12);

-- --------------------------------------------------------

--
-- Structure de la table `groupmember`
--

DROP TABLE IF EXISTS `groupmember`;
CREATE TABLE IF NOT EXISTS `groupmember` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupId` int NOT NULL,
  `userId` int NOT NULL,
  `role` varchar(20) DEFAULT 'MEMBER',
  `joinedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member` (`groupId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groupmember`
--

INSERT INTO `groupmember` (`id`, `groupId`, `userId`, `role`, `joinedAt`) VALUES
(12, 7, 11, 'ADMIN', '2026-03-02 23:05:48'),
(13, 7, 6, 'MEMBER', '2026-03-02 23:06:50'),
(14, 7, 3, 'MEMBER', '2026-03-02 23:40:08');

-- --------------------------------------------------------

--
-- Structure de la table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `createdById` int NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `imageUrl` varchar(500) DEFAULT NULL,
  `memberCount` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_groups_creator` (`createdById`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`, `createdById`, `createdAt`, `imageUrl`, `memberCount`) VALUES
(7, 'DEVS', '', 11, '2026-03-02 23:05:48', NULL, 3);

-- --------------------------------------------------------

--
-- Structure de la table `inscriptionformation`
--

DROP TABLE IF EXISTS `inscriptionformation`;
CREATE TABLE IF NOT EXISTS `inscriptionformation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateInscription` date DEFAULT NULL,
  `statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'In Progress',
  `progression` int NOT NULL DEFAULT '0',
  `noteFinale` double DEFAULT '0',
  `session_id` int NOT NULL,
  `employe_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `employe_id` (`employe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inscriptionformation`
--

INSERT INTO `inscriptionformation` (`id`, `dateInscription`, `statut`, `progression`, `noteFinale`, `session_id`, `employe_id`) VALUES
(144, '2025-04-22', 'In Progress', 0, 0, 18, 3),
(147, '2025-06-01', 'In Progress', 0, 0, 22, 3),
(151, '2025-07-01', 'In Progress', 0, 0, 25, 3),
(160, '2026-03-02', 'In Progress', 20, 0, 28, 11),
(161, '2026-03-01', 'In Progress', 0, 0, 23, 3),
(162, '2026-03-01', 'In Progress', 0, 0, 17, 3),
(163, '2026-03-01', 'In Progress', 0, 0, 21, 10),
(164, '2026-03-01', 'In Progress', 0, 0, 18, 10),
(165, '2026-03-01', 'In Progress', 0, 0, 17, 11),
(166, '2026-03-01', 'In Progress', 0, 0, 18, 11),
(167, '2026-02-15', 'In Progress', 0, 0, 19, 13),
(168, '2026-02-20', 'In Progress', 0, 0, 23, 13),
(169, '2026-03-10', 'In Progress', 0, 0, 32, 13),
(170, '2026-02-23', 'In Progress', 0, 0, 27, 16),
(171, '2026-03-01', 'In Progress', 0, 0, 29, 16),
(172, '2026-02-24', 'In Progress', 0, 0, 28, 17),
(173, '2026-03-05', 'In Progress', 0, 0, 35, 17),
(174, '2026-03-02', 'In Progress', 0, 0, 30, 18),
(175, '2026-03-15', 'In Progress', 0, 0, 33, 18),
(176, '2026-02-10', 'In Progress', 0, 85, 21, 15),
(177, '2026-03-20', 'In Progress', 0, 0, 34, 15),
(178, '2026-02-15', 'In Progress', 0, 0, 17, 20),
(179, '2026-03-20', 'In Progress', 0, 0, 34, 20),
(180, '2026-02-20', 'In Progress', 0, 0, 17, 19),
(181, '2026-03-02', 'In Progress', 0, 0, 31, 19),
(182, '2026-03-10', 'In Progress', 0, 0, 32, 14),
(183, '2026-03-01', 'In Progress', 0, 0, 27, 8),
(184, '2026-03-02', 'In Progress', 0, 0, 28, 8),
(185, '2026-03-01', 'In Progress', 0, 0, 21, 22),
(186, '2026-03-05', 'In Progress', 0, 0, 37, 22),
(187, '2026-03-20', 'In Progress', 0, 0, 34, 23),
(188, '2026-03-02', 'In Progress', 0, 0, 34, 6),
(189, '2026-03-02', 'In Progress', 40, 0, 27, 6),
(190, '2026-03-02', 'In Progress', 0, 0, 38, 11),
(191, '2026-03-03', 'Completed', 100, 0, 34, 12),
(192, '2026-03-23', 'In Progress', 0, 0, 41, 11),
(200, '2026-04-05', 'Completed', 100, 0, 28, 12),
(201, '2026-04-07', 'Completed', 100, 0, 27, 12),
(203, '2026-04-19', 'In Progress', 0, 0, 40, 12),
(204, '2026-04-19', 'Completed', 100, 0, 35, 12);

-- --------------------------------------------------------

--
-- Structure de la table `mention`
--

DROP TABLE IF EXISTS `mention`;
CREATE TABLE IF NOT EXISTS `mention` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mentionedUserId` int NOT NULL,
  `authorId` int NOT NULL,
  `publicationId` int DEFAULT NULL,
  `commentaireId` int DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'UNREAD',
  PRIMARY KEY (`id`),
  KEY `IDX_MENTIONED` (`mentionedUserId`),
  KEY `IDX_AUTHOR` (`authorId`),
  KEY `IDX_PUB` (`publicationId`),
  KEY `IDX_COMMENT` (`commentaireId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE IF NOT EXISTS `module` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formation_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type_contenu` enum('video','reading','exercise','quiz') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'video',
  `duree_minutes` int DEFAULT '30',
  `ordre` int DEFAULT '1',
  `contenu_texte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_ressource` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_module_formation` (`formation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module`
--

INSERT INTO `module` (`id`, `formation_id`, `titre`, `description`, `type_contenu`, `duree_minutes`, `ordre`, `contenu_texte`, `url_ressource`) VALUES
(1, 2, 'Introduction', NULL, 'video', 30, 1, 'Le cloud computing est un modele de livraison de services informatiques via Internet. AWS (Amazon Web Services) est la plateforme cloud la plus utilisee au monde avec plus de 200 services.\n\nLes 3 modeles de cloud :\n- IaaS : Infrastructure as a Service (EC2, VPC)\n- PaaS : Platform as a Service (Elastic Beanstalk)\n- SaaS : Software as a Service (WorkMail)\n\nLes avantages du cloud AWS :\n- Paiement a l\'usage\n- Elasticite et scalabilite automatique\n- Haute disponibilite multi-regions\n- Securite de niveau entreprise', 'https://www.youtube.com/watch?v=a9__D53WsUs'),
(2, 2, 'Concepts clés', NULL, 'reading', 20, 2, 'AWS IAM (Identity and Access Management) est le service central de securite AWS.\n\nConcepts cles :\n- Utilisateurs IAM : comptes individuels\n- Groupes : ensemble d\'utilisateurs avec les memes permissions\n- Roles : permissions temporaires pour services/applications\n- Politiques (Policies) : documents JSON definissant les permissions\n\nBonnes pratiques :\n- Ne jamais utiliser le compte root\n- Principe du moindre privilege\n- Activer l\'authentification MFA\n- Faire tourner les cles d\'acces regulierement', 'https://www.youtube.com/watch?v=SXSqhTn2DuE'),
(3, 2, 'Pratique', NULL, 'exercise', 45, 3, NULL, 'https://www.youtube.com/watch?v=TsRBftzZsQo'),
(4, 2, 'Quiz final', NULL, 'quiz', 15, 4, 'Amazon S3 (Simple Storage Service) est un service de stockage objet infiniment scalable.\n\nClasses de stockage :\n- S3 Standard : acces frequent, haute disponibilite\n- S3 Intelligent-Tiering : acces variable, optimise automatiquement\n- S3 Standard-IA : acces peu frequent\n- S3 Glacier : archivage, recuperation en heures\n- S3 Glacier Deep Archive : archivage long terme, le moins cher\n\nFonctionnalites avancees :\n- Versioning : conserver plusieurs versions d\'un objet\n- Lifecycle rules : transition automatique entre classes\n- Replication : copie cross-region\n- Chiffrement AES-256 ou KMS', 'https://www.youtube.com/watch?v=NZElg91l_ms'),
(5, 2, 'Introduction au Cloud et AWS', 'Comprendre les fondamentaux du cloud computing et l\'ecosysteme AWS.', 'reading', 30, 1, 'Le cloud computing est un modele de livraison de services informatiques via Internet. AWS (Amazon Web Services) est la plateforme cloud la plus utilisee au monde avec plus de 200 services.\n\nLes 3 modeles de cloud :\n- IaaS : Infrastructure as a Service (EC2, VPC)\n- PaaS : Platform as a Service (Elastic Beanstalk)\n- SaaS : Software as a Service (WorkMail)\n\nLes avantages du cloud AWS :\n- Paiement a l\'usage\n- Elasticite et scalabilite automatique\n- Haute disponibilite multi-regions\n- Securite de niveau entreprise', 'https://www.youtube.com/watch?v=a9__D53WsUs'),
(6, 2, 'IAM - Gestion des identites', 'Utilisateurs, groupes, roles et politiques IAM.', 'video', 45, 2, 'AWS IAM (Identity and Access Management) est le service central de securite AWS.\n\nConcepts cles :\n- Utilisateurs IAM : comptes individuels\n- Groupes : ensemble d\'utilisateurs avec les memes permissions\n- Roles : permissions temporaires pour services/applications\n- Politiques (Policies) : documents JSON definissant les permissions\n\nBonnes pratiques :\n- Ne jamais utiliser le compte root\n- Principe du moindre privilege\n- Activer l\'authentification MFA\n- Faire tourner les cles d\'acces regulierement', 'https://www.youtube.com/watch?v=SXSqhTn2DuE'),
(7, 2, 'Amazon EC2 et Auto Scaling', 'Instances EC2, types, AMI, groupes Auto Scaling.', 'video', 60, 3, NULL, 'https://www.youtube.com/watch?v=TsRBftzZsQo'),
(8, 2, 'S3 et Stockage AWS', 'Buckets S3, classes de stockage, versioning, lifecycle.', 'reading', 40, 4, 'Amazon S3 (Simple Storage Service) est un service de stockage objet infiniment scalable.\n\nClasses de stockage :\n- S3 Standard : acces frequent, haute disponibilite\n- S3 Intelligent-Tiering : acces variable, optimise automatiquement\n- S3 Standard-IA : acces peu frequent\n- S3 Glacier : archivage, recuperation en heures\n- S3 Glacier Deep Archive : archivage long terme, le moins cher\n\nFonctionnalites avancees :\n- Versioning : conserver plusieurs versions d\'un objet\n- Lifecycle rules : transition automatique entre classes\n- Replication : copie cross-region\n- Chiffrement AES-256 ou KMS', 'https://www.youtube.com/watch?v=NZElg91l_ms'),
(9, 2, 'RDS et Bases de donnees managees', 'Amazon RDS, Aurora, DynamoDB - cas d\'usage et configuration.', 'video', 50, 5, NULL, 'https://www.youtube.com/watch?v=eMzCI7S1P9M'),
(10, 2, 'Quiz de certification AWS', 'Evaluez vos connaissances sur l\'ensemble des services AWS abordes.', 'quiz', 30, 6, NULL, NULL),
(11, 3, 'Les styles de leadership', 'Decouvrez les differents styles : autoritaire, participatif, delegatif.', 'reading', 35, 1, 'Les styles de leadership selon Lewin :\n\n1. AUTORITAIRE (Autocratique)\nLe leader prend seul les decisions. Efficace en situation de crise ou avec des equipes peu experimentees. Risque : demotivation long terme.\n\n2. PARTICIPATIF (Democratique)\nLe leader implique l\'equipe dans les decisions. Favorise l\'engagement et la creativite. Necessite du temps.\n\n3. DELEGATIF (Laisser-faire)\nL\'equipe est totalement autonome. Efficace avec des experts motives. Risque si manque de structure.\n\nSelon Hersey et Blanchard (Leadership situationnel) : le style doit s\'adapter au niveau de maturite de chaque collaborateur.', 'https://www.youtube.com/watch?v=v0MoEs8G5C4'),
(12, 3, 'Communication assertive', 'Techniques pour communiquer avec clarte et impact.', 'video', 40, 2, 'La communication assertive est la capacite a exprimer ses besoins, opinions et limites de maniere claire, directe et respectueuse.\n\n4 styles de communication :\n- PASSIF : evite les conflits, ne dit pas ce qu\'il pense\n- AGRESSIF : impose son point de vue, blesse les autres\n- MANIPULATEUR : obtient ce qu\'il veut de maniere indirecte\n- ASSERTIF : exprime clairement tout en respectant l\'autre\n\nTechniques assertives :\n- Le \"Je\" : \"Je ressens...\" plutot que \"Tu fais...\"\n- Le disque raye : repeter calmement sa position\n- Le sandwich : positif + critique + positif\n- La technique du brouillard : accepter la critique partielle', 'https://www.youtube.com/watch?v=Cr3BD3ZGnEM'),
(13, 3, 'Gestion des conflits', 'Identifier et resoudre les conflits au sein d\'une equipe.', 'exercise', 45, 3, 'EXERCICE PRATIQUE : Gestion des conflits\n\nScenario : Deux membres de votre equipe sont en conflit sur la methode de travail a adopter pour un projet urgent.\n\nEtapes de resolution :\n1. Rencontrer chaque partie separement (ecoute active)\n2. Identifier les besoins reels derriere les positions\n3. Reunir les parties en mediation\n4. Co-construire une solution\n5. Formaliser un accord et assurer le suivi\n\nJeu de role : Pratiquez avec un collegue en alternant les roles de mediateur et de partie en conflit. Duree : 30 minutes.', NULL),
(14, 3, 'Motiver et engager son equipe', 'Leviers de motivation intrinseque et extrinseque.', 'video', 40, 4, NULL, 'https://www.youtube.com/watch?v=fLJsdqxnZb0'),
(15, 3, 'Quiz Leadership', 'Testez vos acquis sur les concepts cles du leadership.', 'quiz', 25, 5, NULL, NULL),
(16, 4, 'Les bases de la communication', 'Emetteur, recepteur, canal, feedback - modele de Shannon.', 'reading', 30, 1, NULL, NULL),
(17, 4, 'Communication non verbale', 'Langage corporel, posture, contact visuel et gestes.', 'video', 35, 2, NULL, NULL),
(18, 4, 'Ecoute active', 'Techniques d\'ecoute et de reformulation pour mieux comprendre.', 'exercise', 40, 3, NULL, NULL),
(19, 4, 'Quiz Communication', 'Evaluation finale des techniques de communication.', 'quiz', 20, 4, NULL, NULL),
(20, 5, 'Introduction a Spring Boot', 'Architecture Spring, auto-configuration, starters et structure de projet.', 'reading', 40, 1, 'Spring Boot est un framework Java qui simplifie la creation d\'applications Spring en elimenant la configuration XML.\n\nPrincipes cles :\n1. Auto-configuration : Spring Boot configure automatiquement les composants selon les dependances detectees\n2. Starters : dependances pre-configurees (spring-boot-starter-web, spring-boot-starter-data-jpa...)\n3. Embedded Server : Tomcat/Jetty integre, pas besoin de deployer un WAR\n4. Production-ready : Actuator pour monitoring\n\nStructure d\'un projet Spring Boot :\n- src/main/java : code source\n- src/main/resources : application.properties, templates\n- src/test/java : tests\n- pom.xml : dependances Maven', 'https://www.youtube.com/watch?v=9SGDpanrc8U'),
(21, 5, 'REST API avec Spring Boot', 'Creation d\'APIs RESTful avec @RestController, @GetMapping, etc.', 'video', 60, 2, NULL, 'https://www.youtube.com/watch?v=OVvHWkkVBKc'),
(22, 5, 'Spring Data JPA', 'ORM, entites JPA, repositories, requetes JPQL et criteres.', 'video', 55, 3, NULL, 'https://www.youtube.com/watch?v=8SGI_XS5OPw'),
(23, 5, 'Securite avec Spring Security', 'Authentification, autorisation, JWT et OAuth2.', 'video', 60, 4, NULL, 'https://www.youtube.com/watch?v=her_7pa0vrg'),
(24, 5, 'Tests unitaires et d\'integration', 'JUnit 5, Mockito, @SpringBootTest et MockMvc.', 'exercise', 50, 5, 'EXERCICE : Tests Spring Boot\n\nObjectif : Ecrire des tests unitaires et d\'integration pour une API REST.\n\nObjectif : Ecrire des tests unitaires et d\'integration pour une API REST.\n\nExercice 1 - Test unitaire avec Mockito :\n@ExtendWith(MockitoExtension.class)\npublic class UserServiceTest {\n    @Mock private UserRepository repo;\n    @InjectMocks private UserService service;\n    \n    @Test\n    void findById_shouldReturnUser() {\n        when(repo.findById(1L)).thenReturn(Optional.of(new User(1L, \"Ahmed\")));\n        User result = service.findById(1L);\n        assertEquals(\"Ahmed\", result.getName());\n    }\n}\n\nExercice 2 - Test d\'integration avec MockMvc :\nTester les endpoints GET /api/users et POST /api/users avec assertion sur le status HTTP et le body JSON.', NULL),
(25, 5, 'Quiz Spring Boot', 'Questions sur l\'ensemble du framework Spring Boot.', 'quiz', 30, 6, NULL, NULL),
(26, 6, 'Rappels Angular et TypeScript', 'Components, modules, services, pipes et decorateurs.', 'reading', 35, 1, 'Angular est un framework frontend basé sur TypeScript maintenu par Google.\n\nCOMPOSANTS ANGULAR :\n@Component({\n  selector: \'app-hero\',\n  template: `<h1>{{hero.name}}</h1>`,\n  styleUrls: [\'./hero.component.scss\']\n})\nexport class HeroComponent {\n  @Input() hero!: Hero;\n  @Output() selected = new EventEmitter<Hero>();\n}\n\nTYPESCRIPT ESSENTIEL :\n// Interfaces\ninterface User { id: number; name: string; email?: string; }\n// Generics\nfunction identity<T>(arg: T): T { return arg; }\n// Decorators\n@Injectable({ providedIn: \'root\' })\n\nSERVICES ET INJECTION DE DÉPENDANCES :\n@Injectable({ providedIn: \'root\' })\nexport class UserService {\n  constructor(private http: HttpClient) {}\n  getUsers(): Observable<User[]> {\n    return this.http.get<User[]>(\'/api/users\');\n  }\n}\n\nROUTING :\nconst routes: Routes = [\n  { path: \'\', component: HomeComponent },\n  { path: \'users/:id\', component: UserDetailComponent },\n  { path: \'**\', redirectTo: \'\' }\n];\n\nFORMULAIRES RÉACTIFS :\nthis.form = this.fb.group({\n  email: [\'\', [Validators.required, Validators.email]],\n  password: [\'\', Validators.minLength(8)]\n});\n\nPIPES COURANTS :\n{{ date | date:\'dd/MM/yyyy\' }}\n{{ price | currency:\'EUR\' }}\n{{ name | uppercase }}\n{{ users | async }}', NULL),
(27, 6, 'RxJS et Programmation reactive', 'Observables, operateurs RxJS, Subject, BehaviorSubject.', 'video', 60, 2, NULL, 'https://www.youtube.com/watch?v=3dHNOWTI7H8'),
(28, 6, 'NgRx - State Management', 'Actions, reducers, effects et selectors avec NgRx Store.', 'video', 70, 3, NULL, 'https://www.youtube.com/watch?v=d3ml_FU55tM'),
(29, 6, 'Optimisation des performances', 'OnPush, lazy loading, trackBy, virtual scrolling.', 'exercise', 45, 4, 'EXERCICE : Optimisation Angular\n\nObjectif : Appliquer les techniques d\'optimisation sur un composant existant.\n\nTache 1 - Implementer OnPush :\n@Component({\n  changeDetection: ChangeDetectionStrategy.OnPush\n})\n\nTache 2 - Ajouter trackBy a une ngFor :\ntrackByFn(index: number, item: any): number {\n  return item.id;\n}\n\nTache 3 - Lazy loading d\'un module :\n{\n  path: \'admin\',\n  loadChildren: () => import(\'./admin/admin.module\').then(m => m.AdminModule)\n}\n\nMesurer l\'amelioration avec Angular DevTools (profiler).', NULL),
(30, 6, 'Quiz Angular Avance', 'Questions approfondies sur Angular et RxJS.', 'quiz', 30, 5, NULL, NULL),
(31, 7, 'Fondamentaux du management de projet', 'Cycle de vie, parties prenantes, charte projet et WBS.', 'reading', 40, 1, NULL, NULL),
(32, 7, 'Planification et estimation', 'Gantt, PERT, gestion des risques et estimation des couts.', 'video', 55, 2, NULL, NULL),
(33, 7, 'Methodes Agile et Scrum', 'Sprints, backlog, ceremonies Scrum et roles.', 'video', 50, 3, NULL, NULL),
(34, 7, 'Exercice : Plan de projet', 'Construisez un plan de projet complet avec jalons et livrables.', 'exercise', 60, 4, NULL, NULL),
(35, 7, 'Quiz PMP', 'Questions de certification Project Management Professional.', 'quiz', 35, 5, NULL, NULL),
(36, 12, 'Dynamiques de groupe', 'Comprendre les phases de developpement d\'une equipe (Tuckman).', 'reading', 30, 1, 'Le modele de Tuckman (1965) decrit 5 phases de developpement d\'une equipe :\n\n1. FORMING (Formation) : Les membres se decouvrent, incertitude sur les roles. Le leader doit donner une direction claire.\n\n2. STORMING (Turbulence) : Conflits et tensions emergent sur les methodes de travail. Phase critique a gerer avec bienveillance.\n\n3. NORMING (Normalisation) : L\'equipe etablit ses regles et processus. La cohesion se construit. Collaboration croissante.\n\n4. PERFORMING (Performance) : L\'equipe est autonome et efficace. Le leader peut deleguer. Objectifs atteints avec fluidite.\n\n5. ADJOURNING (Dissolution) : Fin du projet, dispersion de l\'equipe. Important de valoriser les contributions de chacun.', 'https://www.youtube.com/watch?v=OhSI6oBQmQA'),
(37, 12, 'Confiance et cohesion', 'Activites pour renforcer la confiance et la cooperation.', 'exercise', 45, 2, 'EXERCICE : Activites de team building\n\nActivite 1 - La tour de spaghetti (20 min) :\nEn equipe de 4, construisez la tour la plus haute possible avec 20 spaghettis, 1 metre de ruban adhesif et 1 metre de ficelle. La guimauve doit etre au sommet.\nObjectif : observer les dynamiques de leadership emergent.\n\nActivite 2 - Le cercle de confiance (15 min) :\nUn membre au centre les yeux fermes, se laisse tomber doucement. L\'equipe l\'attrape et le guide. Tourne.\nObjectif : construire la confiance physique et symbolique.\n\nDebriefing : Comment les roles se sont-ils distribues ? Quelles emotions avez-vous ressenties ?', NULL),
(38, 12, 'Resolution collaborative', 'Approches collaboratives pour resoudre les problemes en equipe.', 'video', 40, 3, NULL, 'https://www.youtube.com/watch?v=Lf-H2OvNFH0'),
(39, 12, 'Quiz Team Building', 'Evaluez vos connaissances sur la dynamique d\'equipe.', 'quiz', 20, 4, NULL, NULL),
(40, 13, 'Introduction à Python', 'Variables, types, fonctions, classes et modules Python.', 'reading', 40, 1, 'Python est un langage de programmation polyvalent, lisible et puissant, idéal pour la Data Science.\n\nFondamentaux :\n- Variables et types : int, float, str, list, dict, tuple, set\n- Fonctions : def, *args, **kwargs, lambda\n- Classes et POO : héritage, encapsulation\n- Modules : import, from...import, pip\n\nSpécificités Python pour Data Science :\n- List comprehensions : [x**2 for x in range(10)]\n- Generators : yield\n- Context managers : with open() as f\n\nInstallation recommandée : Anaconda (inclut NumPy, Pandas, Jupyter)', 'https://www.youtube.com/watch?v=rfscVS0vtbw'),
(41, 13, 'NumPy & Pandas', 'Tableaux NumPy et DataFrames Pandas pour l\'analyse de données.', 'video', 60, 2, 'NumPy est la bibliothèque fondamentale pour le calcul scientifique.\n\nNumPy :\n- np.array([1,2,3]) : créer un tableau\n- ndarray.shape, ndarray.dtype\n- Opérations vectorisées (broadcasting)\n- Slicing : arr[1:4, 0:2]\n\nPandas :\n- pd.DataFrame et pd.Series\n- Lecture : pd.read_csv(), pd.read_excel()\n- Filtrage : df[df[\'age\'] > 30]\n- Groupement : df.groupby(\'dept\').mean()\n- Nettoyage : df.dropna(), df.fillna(0)', 'https://www.youtube.com/watch?v=vmEHCJofslg'),
(42, 13, 'Visualisation avec Matplotlib', 'Créer des graphiques professionnels avec Matplotlib et Seaborn.', 'video', 45, 3, NULL, 'https://www.youtube.com/watch?v=UO98lJQ3QGI'),
(43, 13, 'Exercice : Analyse de données RH', 'Analyser un dataset d\'employés avec Pandas.', 'exercise', 50, 4, 'EXERCICE : Analyse d\'un dataset RH\n\nObjectif : Analyser un jeu de données d\'employés.\n\nimport pandas as pd\nimport matplotlib.pyplot as plt\n\n# Charger le dataset\ndf = pd.read_csv(\"employees.csv\")\n\n# Questions à résoudre :\n# 1. Combien d\'employés par département ?\nprint(df.groupby(\"departement\").size())\n\n# 2. Quel est l\'âge moyen par département ?\nprint(df.groupby(\"departement\")[\"age\"].mean())\n\n# 3. Visualiser la répartition des salaires\ndf[\"salaire\"].hist(bins=20)\nplt.title(\"Distribution des salaires\")\nplt.show()\n\n# 4. Trouver les 5 employés les mieux payés\nprint(df.nlargest(5, \"salaire\")[[\"nom\", \"salaire\"]])\n\nSoumettez vos 4 sorties (texte et graphique).', NULL),
(44, 13, 'Quiz Python Data Science', 'Évaluation des connaissances Python et Pandas.', 'quiz', 25, 5, NULL, NULL),
(45, 14, 'Git Fondamentaux', 'init, add, commit, push, pull, clone, status, log.', 'reading', 35, 1, 'Git est un système de contrôle de version distribué créé par Linus Torvalds.\n\nCommandes essentielles :\ngit init                  # initialiser un dépôt\ngit clone <url>           # cloner un dépôt distant\ngit add .                 # stager tous les fichiers\ngit commit -m \"message\"   # créer un commit\ngit push origin main      # pousser vers le remote\ngit pull                  # récupérer et fusionner\ngit log --oneline         # historique condensé\ngit diff                  # voir les modifications\n\nBranches :\ngit branch feature/login  # créer une branche\ngit checkout -b feature   # créer et switcher\ngit merge feature         # fusionner\ngit rebase main           # rebaser', 'https://www.youtube.com/watch?v=RGOj5yH7evk'),
(46, 14, 'Branching & Workflow', 'Stratégies Git : GitFlow, Feature Branch, Pull Requests.', 'video', 40, 2, NULL, 'https://www.youtube.com/watch?v=Uszj_k0DGsg'),
(47, 14, 'CI/CD avec GitHub Actions', 'Créer des pipelines CI/CD automatisés avec GitHub Actions.', 'video', 50, 3, NULL, 'https://www.youtube.com/watch?v=R8_veQiYBjI'),
(48, 14, 'Quiz Git & DevOps', 'Évaluation des concepts Git et CI/CD.', 'quiz', 20, 4, NULL, NULL),
(49, 15, 'Manifeste Agile & Valeurs', 'Les 4 valeurs et 12 principes du Manifeste Agile.', 'reading', 30, 1, 'Le Manifeste Agile (2001) pose 4 valeurs fondamentales :\n\n1. Les individus et leurs interactions > les processus et les outils\n2. Un logiciel fonctionnel > une documentation exhaustive\n3. La collaboration avec le client > la négociation contractuelle\n4. L\'adaptation au changement > le suivi d\'un plan\n\nLes 12 principes incluent :\n- Livraison fréquente de logiciel fonctionnel (toutes les 2-4 semaines)\n- Accueil favorable des changements de besoins\n- Rythme de développement soutenable\n- Excellence technique et conception de qualité\n- Équipes auto-organisées\n- Rétrospectives régulières pour s\'améliorer', 'https://www.youtube.com/watch?v=Z9QbYZh1YXY'),
(50, 15, 'Framework Scrum', 'Rôles, artefacts et cérémonies Scrum.', 'video', 50, 2, NULL, 'https://www.youtube.com/watch?v=9TycLR0TqFA'),
(51, 15, 'Quiz Agile & Scrum', 'Évaluation des concepts Agile et Scrum.', 'quiz', 20, 3, NULL, NULL),
(52, 8, 'Les fondamentaux de la prise de parole', 'Voix, posture, regard et préparation mentale avant de parler en public.', 'reading', 30, 1, 'La prise de parole en public est l\'une des compétences les plus valorisées en entreprise.\n\nLES 3 PILIERS :\n1. LOGOS (le contenu) : structure claire, arguments solides, exemples concrets\n2. ETHOS (la crédibilité) : posture, confiance, expertise perçue\n3. PATHOS (l\'émotion) : connexion avec l\'auditoire, storytelling, enthousiasme\n\nPRÉPARATION MENTALE :\n- La règle des 3P : Préparer, Pratiquer, Performer\n- Technique de visualisation positive (imaginez le succès)\n- Respiration abdominale : 4 temps inspiration, 4 retenue, 6 expiration\n- Power posing : postures d\'ouverture avant de parler\n\nLA VOIX :\n- Volume : projeter sans crier\n- Débit : 130-150 mots/min (adapter aux moments clés)\n- Articulation : ouvrir la bouche, sourire\n- Silences : plus puissants que les mots\n\nLE REGARD :\n- Balayage triangulaire (3 zones)\n- Contact visuel de 2-3 secondes par personne\n- Éviter l\'écran ou les notes', 'https://www.youtube.com/watch?v=K0t-KCSpHL0'),
(53, 8, 'Structurer un discours percutant', 'Plans de discours, hooks d\'accroche et techniques de conclusion.', 'video', 35, 2, 'STRUCTURE EN 3 ACTES :\n\n1. INTRODUCTION (10% du temps)\n- Hook (accroche) : question rhétorique, statistique surprenante, anecdote, citation\n- Annonce du plan : \"Je vais vous parler de X, puis Y, puis Z\"\n- WIIFM (What\'s In It For Me) : pourquoi ça intéresse l\'audience\n\n2. DÉVELOPPEMENT (80%)\n- Maximum 3 points principaux (règle de 3)\n- Pour chaque point : affirmation + argument + exemple + lien\n- Transitions explicites : \"Maintenant que nous avons vu X, parlons de Y\"\n\n3. CONCLUSION (10%)\n- Résumé des points clés\n- Call to action clair\n- Phrase de clôture mémorable\n\nTYPES D\'ACCROCHES :\n- Statistique choc : \"Selon Harvard, 93% de la communication est non-verbale\"\n- Question directe : \"Combien d\'entre vous ont peur de parler en public ?\"\n- Histoire personnelle courte\n- Objet ou démonstration inattendue', 'https://www.youtube.com/watch?v=vlMlY6MLkDg'),
(54, 8, 'Gérer le stress et l\'improvisation', 'Techniques pour garder le contrôle face à l\'imprévu.', 'exercise', 45, 3, 'EXERCICE PRATIQUE : Gestion du stress et improvisation\n\nEXERCICE 1 - Discours improvisé (20 min) :\nChoisissez un mot au hasard parmi : INNOVATION / EQUIPE / CHANGEMENT / CONFIANCE\nPréparez un discours de 2 minutes en 2 minutes et délivrez-le.\nObjectif : travailler la clarté d\'idées sous pression.\n\nEXERCICE 2 - Le miroir (10 min) :\nParlez-vous devant un miroir pendant 3 minutes sur un sujet de votre choix.\nObservez : posture, regard, expressions faciales.\nFilmez-vous si possible (le meilleur feedback).\n\nEXERCICE 3 - Questions difficiles (15 min) :\nUn collègue vous pose des questions auxquelles vous ne vous attendez pas.\nRépondez avec la technique PREP :\n- Point (votre position)\n- Reason (la raison)\n- Example (un exemple)\n- Point (conclusion)\n\nTECHNIQUES ANTI-STRESS :\n- Accepter le trac : \"Le trac prouve que ça compte\"\n- Reframing : \"l\'audience veut que vous réussissiez\"\n- Ancrage : toucher une surface froide, respirer\n- Routines pré-discours personnalisées', NULL),
(55, 8, 'Quiz : Prise de parole en public', 'Évaluez vos connaissances sur les techniques de communication orale.', 'quiz', 20, 4, NULL, NULL),
(56, 10, 'Diagnostiquer ses habitudes de temps', 'Identifier ses voleurs de temps et comprendre sa chronobiologie.', 'reading', 25, 1, 'La gestion du temps commence par une prise de conscience de ses habitudes actuelles.\n\nLES VOLEURS DE TEMPS :\n- Interruptions non planifiées (collègues, emails, notifications)\n- Perfectionnisme excessif\n- Procrastination sur les tâches difficiles\n- Réunions sans ordre du jour\n- Multi-tasking (réduit la productivité de 40%)\n\nCHRONOBIOLOGIE :\n- Pic de productivité cognitive : 9h-12h pour 80% des gens\n- Creux post-déjeuner : 13h-15h (tâches mécaniques)\n- Second pic : 17h-19h\n- Adapter les tâches importantes à son rythme naturel\n\nAUDIT DE SON TEMPS :\nPendant 3 jours, notez TOUTES vos activités par tranches de 30 min.\nCategories : Urgent+Important / Urgent+PasImportant / Pas urgent+Important / Pas urgent+Pas important\n\nRègle du 80/20 (Pareto) :\n20% de vos actions génèrent 80% de vos résultats.\nIdentifiez et protégez ces 20%.', 'https://www.youtube.com/watch?v=oTugjssqOT0'),
(57, 10, 'Méthodes de priorisation', 'Matrice d\'Eisenhower, méthode GTD, Pomodoro et time-blocking.', 'video', 35, 2, NULL, 'https://www.youtube.com/watch?v=W9k0OhJkjQ0'),
(58, 10, 'Quiz : Gestion du temps', 'Testez vos connaissances sur la productivité et les méthodes de priorisation.', 'quiz', 15, 3, NULL, NULL),
(59, 11, 'Introduction à la cybersécurité', 'Panorama des menaces, CIA triad, vocabulaire essentiel.', 'reading', 40, 1, 'La cybersécurité protège les systèmes, réseaux et données contre les attaques numériques.\n\nLE TRIANGLE CIA :\n- Confidentialité : seuls les autorisés accèdent aux données\n- Intégrité : les données ne sont pas altérées\n- Disponibilité : les systèmes sont accessibles quand nécessaire\n\nTYPES DE MENACES :\n1. Malware : Virus, Ransomware, Spyware, Trojan\n2. Phishing : emails frauduleux qui imitent des entités légitimes\n3. MITM (Man in the Middle) : interception des communications\n4. DDoS : saturation d\'un service par des requêtes massives\n5. SQL Injection : injection de code malveillant dans des requêtes\n6. Zero-day : exploitation de vulnérabilités inconnues\n\nACTEURS DE LA MENACE :\n- Script kiddies : débutants utilisant des outils existants\n- Hacktivistes : motivation idéologique\n- Cybercriminels : motivation financière\n- États-nations : espionnage, sabotage\n- Insiders : employés malveillants ou négligents\n\nCHIFFRES CLÉS :\n- 95% des incidents sont dus à une erreur humaine\n- Coût moyen d\'une violation : 4,45M$ (IBM 2023)\n- Délai moyen de détection : 204 jours', 'https://www.youtube.com/watch?v=inWWhr5tnEA'),
(60, 11, 'Sécurité des mots de passe et authentification', 'Bonnes pratiques, gestionnaires de mots de passe, MFA.', 'video', 35, 2, 'RÈGLES DES MOTS DE PASSE FORTS :\n- Minimum 12 caractères\n- Mélange : majuscules + minuscules + chiffres + caractères spéciaux\n- Unique par service\n- Ne jamais réutiliser\n- Phrase de passe : \"MonChatMange3Souris!\" (plus sûr et mémorable)\n\nGESTIONNAIRES DE MOTS DE PASSE :\n- Bitwarden (open source, gratuit)\n- 1Password, Dashlane, KeePass\n- Générateur intégré : mots de passe uniques et aléatoires\n\nAUTHENTIFICATION MULTI-FACTEURS (MFA) :\n- Ce que vous savez : mot de passe\n- Ce que vous avez : smartphone (OTP), clé physique\n- Ce que vous êtes : empreinte digitale, visage\n- MFA réduit le risque de 99,9% (Microsoft)\n\nCOMMUNES ERREURS :\n- \"Password123\" dans le top 10 des mots de passe\n- Post-it avec mots de passe\n- Réutilisation après violation détectée', 'https://www.youtube.com/watch?v=aEmXedgIJH4'),
(61, 11, 'Phishing et ingénierie sociale', 'Reconnaître et déjouer les tentatives de manipulation.', 'reading', 35, 3, 'L\'ingénierie sociale exploite la psychologie humaine plutôt que les failles techniques.\n\nTYPES D\'ATTAQUES SOCIALES :\n1. Phishing : email massif usurpant une marque (banque, Microsoft, DHL)\n2. Spear phishing : email ciblé avec informations personnelles\n3. Vishing : arnaque téléphonique (faux support technique)\n4. Smishing : SMS frauduleux (\"Votre colis est bloqué\")\n5. Pretexting : fausse identité construite (faux prestataire)\n6. Baiting : clé USB piégée laissée dans un parking\n\nSIGNES D\'UN PHISHING :\n- Urgence artificielle (\"Agissez maintenant ou votre compte sera fermé\")\n- URL suspecte : paypal-secure.ru vs paypal.com\n- Fautes d\'orthographe (pas toujours !)\n- Demande d\'informations sensibles par email\n- Expéditeur suspect (vérifier le vrai email)\n\nQUE FAIRE EN CAS DE DOUTE :\n- Ne cliquer sur aucun lien\n- Contacter l\'organisation directement\n- Signaler au service IT\n- Ne jamais ouvrir les pièces jointes inattendues', NULL),
(62, 11, 'Sécurité réseau et HTTPS', 'VPN, pare-feu, chiffrement TLS et réseaux Wi-Fi publics.', 'video', 40, 4, NULL, 'https://www.youtube.com/watch?v=hExRDVZHhig'),
(63, 11, 'Quiz : Cybersécurité Fondamentaux', 'Évaluation complète sur les bases de la cybersécurité.', 'quiz', 30, 5, NULL, NULL),
(64, 19, 'Introduction à Apache Kafka', 'Comprendre les bases de Kafka et son rôle dans les architectures modernes', 'reading', 25, 1, 'Apache Kafka est une plateforme de streaming distribuée utilisée pour gérer des flux de données en temps réel.\r\n\r\nConcepts clés :\r\n- Broker : serveur Kafka qui stocke les données\r\n- Topic : catégorie de messages\r\n- Partition : division d’un topic pour la scalabilité\r\n- Producer : envoie des messages\r\n- Consumer : lit les messages\r\n\r\nKafka est souvent utilisé pour :\r\n- Event-driven architecture\r\n- Pipeline de données\r\n- Streaming en temps réel', NULL),
(65, 19, 'Architecture Kafka expliquée', 'Comprendre le fonctionnement interne de Kafka', 'video', 20, 2, NULL, 'https://www.youtube.com/watch?v=Ch5VhJzaoaI'),
(66, 19, 'Créer Producer et Consumer', 'Mettre en pratique Kafka avec un exemple simple', 'exercise', 40, 3, 'Objectif :\r\n- Installer Kafka\r\n- Créer un topic\r\n- Envoyer et consommer des messages\r\n\r\nÉtapes :\r\n1. Lancer Zookeeper et Kafka\r\n2. Créer un topic\r\n3. Produire un message\r\n4. Lire avec un consumer', NULL),
(67, 9, 'Comprendre Docker', 'Introduction aux conteneurs et à Docker', 'reading', 25, 1, 'Docker permet de créer des conteneurs légers pour exécuter des applications.\r\n\r\nConcepts :\r\n- Image : modèle d’application\r\n- Container : instance d’une image\r\n- Dockerfile : fichier de configuration\r\n\r\nAvantages :\r\n- Portabilité\r\n- Isolation\r\n- Déploiement rapide\r\n\r\nExemple :\r\ndocker run -d -p 80:80 nginx', NULL),
(68, 9, 'Introduction à Kubernetes', 'Orchestration des conteneurs', 'video', 30, 2, NULL, 'https://www.youtube.com/watch?v=X48VuDVv0do'),
(69, 9, 'Déployer une application', 'Utiliser Kubernetes pour déployer une app', 'exercise', 45, 3, 'Objectif :\r\n- Créer un cluster minikube\r\n- Déployer une app\r\n- Exposer via un service\r\n\r\nCommandes :\r\nkubectl create deployment\r\nkubectl expose deployment', NULL),
(70, 17, 'Introduction à GraphQL', 'Comprendre les bases et différences avec REST', 'reading', 20, 1, 'GraphQL est un langage de requête pour API.\r\n\r\nDifférences avec REST :\r\n- Une seule endpoint\r\n- Requêtes personnalisées\r\n- Moins de sur-fetching\r\n\r\nConcepts :\r\n- Query : lecture\r\n- Mutation : modification\r\n- Schema : structure des données\r\n\r\nExemple :\r\nquery {\r\n  user {\r\n    name\r\n    email\r\n  }\r\n}', NULL),
(71, 17, 'Créer une API GraphQL', 'Mise en place avec Node.js', 'video', 25, 2, NULL, 'https://www.youtube.com/watch?v=ed8SzALpx1Q'),
(72, 17, 'Requêtes et mutations', 'Implémenter un CRUD simple', 'exercise', 40, 3, 'Créer :\r\n- Query pour récupérer des données\r\n- Mutation pour ajouter/modifier\r\n\r\nTester avec GraphQL Playground', NULL),
(73, 26, 'Introduction au caching', 'Comprendre les bases du cache', 'reading', 20, 1, 'Le caching permet d’améliorer les performances.\r\n\r\nTypes :\r\n- Cache mémoire (Redis)\r\n- Cache navigateur\r\n\r\nStratégies :\r\n- TTL (Time To Live)\r\n- LRU (Least Recently Used)\r\n\r\nAvantages :\r\n- Réduction du temps de réponse\r\n- Moins de charge serveur', NULL),
(74, 26, 'Utiliser Redis', 'Installation et utilisation', 'video', 20, 2, NULL, 'https://www.youtube.com/watch?v=Hbt56gFj998'),
(75, 26, 'Implémenter un cache', 'Ajouter Redis à une app', 'exercise', 35, 3, 'Objectif :\r\n- Installer Redis\r\n- Connecter backend\r\n- Stocker et lire des données\r\n\r\nTester performance avant/après', NULL),
(76, 18, 'Introduction à Infrastructure as Code', 'Comprendre les concepts de base', 'reading', 25, 1, 'L’Infrastructure as Code (IaC) permet de gérer l’infrastructure via du code.\r\n\r\nAvantages :\r\n- Automatisation\r\n- Versionning\r\n- Reproductibilité\r\n\r\nTerraform utilise des fichiers .tf\r\n\r\nExemple :\r\nresource \"aws_instance\" \"example\" {\r\n  ami = \"ami-123456\"\r\n  instance_type = \"t2.micro\"\r\n}', NULL),
(77, 18, 'Créer une infrastructure', 'Utiliser Terraform', 'video', 30, 2, NULL, 'https://www.youtube.com/watch?v=SLB_c_ayRMo'),
(78, 18, 'Déployer une VM', 'Projet pratique Terraform', 'exercise', 45, 3, 'Objectif :\r\n- Installer Terraform\r\n- Créer fichier main.tf\r\n- Exécuter terraform init / apply', NULL),
(79, 20, 'Fondamentaux du coaching', 'Comprendre le rôle du coach et les bases', 'reading', 25, 1, 'Le coaching consiste à accompagner une personne ou une équipe pour atteindre ses objectifs.\r\n\r\nPrincipes clés :\r\n- Écoute active\r\n- Questionnement ouvert\r\n- Non-jugement\r\n\r\nRôle du coach :\r\n- Faciliter la réflexion\r\n- Encourager l’autonomie\r\n- Développer les compétences\r\n\r\nOutils :\r\n- Feedback constructif\r\n- Reformulation\r\n- Objectifs SMART', NULL),
(80, 20, 'Techniques de feedback efficace', 'Améliorer la communication en équipe', 'video', 20, 2, NULL, 'https://www.youtube.com/watch?v=2c7fP8g5sXo'),
(81, 20, 'Mise en situation de coaching', 'Simulation pratique', 'exercise', 40, 3, 'Exercice :\r\n- Simuler un entretien avec un collaborateur\r\n- Identifier un problème\r\n- Appliquer écoute active et feedback\r\n\r\nObjectif :\r\nAméliorer la posture de coach', NULL),
(82, 22, 'Introduction au Design Thinking', 'Comprendre la méthodologie', 'reading', 25, 1, 'Le Design Thinking est une approche centrée utilisateur.\r\n\r\nÉtapes :\r\n1. Empathie : comprendre les besoins utilisateurs\r\n2. Définition : formuler le problème\r\n3. Idéation : générer des idées\r\n4. Prototype : créer une solution\r\n5. Test : valider auprès des utilisateurs\r\n\r\nObjectif :\r\nCréer des solutions innovantes adaptées aux besoins réels.', NULL),
(83, 22, 'Étude de cas Design Thinking', 'Application réelle', 'video', 20, 2, NULL, 'https://www.youtube.com/watch?v=_r0VX-aU_T8'),
(84, 22, 'Atelier d’idéation', 'Exercice pratique d’innovation', 'exercise', 45, 3, 'Exercice :\r\n- Choisir un problème utilisateur\r\n- Générer 10 idées\r\n- Sélectionner les meilleures\r\n\r\nMéthodes :\r\n- Brainstorming\r\n- Mind mapping', NULL),
(85, 23, 'Fonctions avancées Excel', 'Maîtriser les formules complexes', 'reading', 30, 1, 'Fonctions essentielles :\r\n- RECHERCHEX : recherche avancée\r\n- INDEX + EQUIV : alternative puissante\r\n- SI.CONDITIONS : logique avancée\r\n\r\nBonnes pratiques :\r\n- Structurer les données\r\n- Éviter les doublons\r\n- Utiliser les tableaux dynamiques', NULL),
(86, 23, 'Introduction à Power Query', 'Transformation de données', 'video', 25, 2, NULL, 'https://www.youtube.com/watch?v=6lBqYInBldk'),
(87, 23, 'Nettoyage de données', 'Exercice pratique', 'exercise', 40, 3, 'Objectif :\r\n- Importer un fichier\r\n- Nettoyer les données\r\n- Créer un modèle propre\r\n\r\nUtiliser Power Query', NULL),
(88, 25, 'Introduction à Flutter', 'Découvrir le framework', 'reading', 25, 1, 'Flutter est un framework UI développé par Google.\r\n\r\nCaractéristiques :\r\n- Utilise Dart\r\n- Cross-platform (iOS, Android)\r\n- UI réactive\r\n\r\nConcepts :\r\n- Widgets\r\n- Stateful vs Stateless\r\n- Hot reload', NULL),
(89, 25, 'Créer une application Flutter', 'Premiers pas', 'video', 30, 2, NULL, 'https://www.youtube.com/watch?v=fq4N0hgOWzU'),
(90, 25, 'Interface utilisateur Flutter', 'Construire une UI simple', 'exercise', 45, 3, 'Exercice :\r\n- Créer une app\r\n- Ajouter navigation\r\n- Créer plusieurs écrans', NULL),
(91, 21, 'Comprendre le stress', 'Identifier les causes et effets', 'reading', 20, 1, 'Le stress est une réaction naturelle face à une pression.\r\n\r\nTypes :\r\n- Stress aigu\r\n- Stress chronique\r\n\r\nImpacts :\r\n- Fatigue\r\n- Baisse de concentration\r\n\r\nSolutions :\r\n- Organisation\r\n- Respiration\r\n- Activité physique', NULL),
(92, 21, 'Techniques de relaxation', 'Apprendre à se détendre', 'video', 20, 2, NULL, 'https://www.youtube.com/watch?v=hnpQrMqDoqE'),
(93, 21, 'Routine anti-stress', 'Exercice pratique', 'exercise', 35, 3, 'Créer une routine quotidienne :\r\n- Respiration 5 min\r\n- Pause active\r\n- Planification de la journée', NULL),
(94, 24, 'Modélisation de données', 'Comprendre les relations et DAX', 'reading', 30, 1, 'Power BI permet d’analyser des données efficacement.\r\n\r\nConcepts :\r\n- Relations entre tables\r\n- Mesures DAX\r\n- Modèle en étoile\r\n\r\nExemple DAX :\r\nSUM(Sales[Amount])\r\n\r\nBonnes pratiques :\r\n- Normaliser les données\r\n- Optimiser les relations', NULL),
(95, 24, 'Créer un dashboard', 'Visualisation de données', 'video', 25, 2, NULL, 'https://www.youtube.com/watch?v=AGrl-H87pRU'),
(96, 24, 'Projet Power BI', 'Créer un tableau de bord complet', 'exercise', 45, 3, 'Objectif :\r\n- Importer données\r\n- Créer visuels\r\n- Ajouter filtres interactifs', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `module_highlights`
--

DROP TABLE IF EXISTS `module_highlights`;
CREATE TABLE IF NOT EXISTS `module_highlights` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `inscription_id` int NOT NULL,
  `text_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'First 50 chars of highlighted text',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hex color code',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_highlight` (`module_id`,`inscription_id`,`text_key`),
  KEY `idx_module_inscription` (`module_id`,`inscription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `module_notes`
--

DROP TABLE IF EXISTS `module_notes`;
CREATE TABLE IF NOT EXISTS `module_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `inscription_id` int NOT NULL,
  `note_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'note' COMMENT 'note | summary | explain | quiz',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_module_inscription` (`module_id`,`inscription_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module_notes`
--

INSERT INTO `module_notes` (`id`, `module_id`, `inscription_id`, `note_text`, `type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Ceci est une note importante sur le contenu du module.', 'note', '2026-02-25 13:51:53', '2026-02-25 13:51:53'),
(2, 31, 134, 'aucun contenu', 'note', '2026-02-25 21:48:32', '2026-02-25 21:48:31'),
(3, 12, 141, 'a', 'note', '2026-02-25 22:29:37', '2026-02-25 22:29:36'),
(4, 11, 141, 'AUTORITAIRE', 'note', '2026-02-28 22:13:44', '2026-02-28 22:13:43'),
(5, 5, 189, 'Voici les points clés du cours sur l\'introduction au Cloud et AWS :\n\n* Le cloud computing est un modèle de livraison de services informatiques via Internet 🌐\n* AWS est la plateforme cloud la plus utilisée au monde avec plus de 200 services 📈\n* Les 3 modèles de cloud :\n + IaaS : Infrastructure as a Service (EC2, VPC) 🖥️\n + PaaS : Platform as a Service (Elastic Beanstalk) 📦\n + SaaS : Software as a Service (WorkMail) 📧\n* Les avantages du cloud AWS :\n + Paiement à l\'usage 💸\n + Élasticité et scalabilité automatique 🔍\n + Haute disponibilité multi-régions 🌍\n + Sécurité de niveau entreprise 🔒', 'note', '2026-03-02 19:55:58', '2026-03-02 19:55:57'),
(6, 26, 160, 'Voici un résumé clair et structuré du cours en français, avec des points clés et des emoji, dans la limite de 200 mots :\n\n* **Introduction à Angular** 🌐 : framework frontend basé sur TypeScript maintenu par Google.\n* **Composants Angular** 📦 : exemple de composant avec @Component, @Input, @Output et template.\n* **TypeScript essentiel** 📚 : interfaces, generics et decorators.\n* **Services et injection de dépendances** 💉 : création d\'un service avec @Injectable et injection de dépendances.\n* **Routing** 🗺️ : configuration des routes avec const routes et redirection.\n* **Formulaires réactifs** 📝 : création d\'un formulaire avec this.fb.group et validation.\n* **Pipes courants** 📊 : utilisation de pipes pour formater les données, tels que date, currency, uppercase et async.', 'note', '2026-03-02 23:45:03', '2026-03-02 23:45:03');

-- --------------------------------------------------------

--
-- Structure de la table `module_progression`
--

DROP TABLE IF EXISTS `module_progression`;
CREATE TABLE IF NOT EXISTS `module_progression` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employe_id` int NOT NULL,
  `module_id` int NOT NULL,
  `statut` enum('not_started','in_progress','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `score_quiz` int DEFAULT NULL,
  `date_completion` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_mod` (`employe_id`,`module_id`),
  KEY `idx_progression_employe` (`employe_id`),
  KEY `idx_progression_module` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module_progression`
--

INSERT INTO `module_progression` (`id`, `employe_id`, `module_id`, `statut`, `score_quiz`, `date_completion`) VALUES
(33, 13, 20, 'completed', NULL, '2026-02-16'),
(34, 13, 21, 'completed', NULL, '2026-02-17'),
(35, 13, 22, 'in_progress', NULL, NULL),
(36, 17, 26, 'completed', NULL, '2026-02-25'),
(37, 17, 27, 'completed', NULL, '2026-02-26'),
(38, 17, 28, 'completed', NULL, '2026-02-27'),
(39, 17, 29, 'in_progress', NULL, NULL),
(40, 16, 5, 'completed', NULL, '2026-02-24'),
(41, 16, 6, 'completed', NULL, '2026-02-25'),
(42, 16, 7, 'completed', NULL, '2026-02-26'),
(43, 16, 8, 'in_progress', NULL, NULL),
(44, 15, 31, 'completed', NULL, '2026-02-12'),
(45, 15, 32, 'completed', NULL, '2026-02-13'),
(46, 15, 33, 'completed', NULL, '2026-02-14'),
(47, 15, 34, 'completed', NULL, '2026-02-15'),
(48, 15, 35, 'completed', 85, '2026-02-16'),
(49, 3, 49, 'completed', NULL, '2026-03-02'),
(50, 6, 1, 'completed', NULL, '2026-03-02'),
(51, 6, 5, 'completed', NULL, '2026-03-02'),
(52, 11, 26, 'completed', NULL, '2026-03-03'),
(53, 6, 2, 'completed', NULL, '2026-03-03'),
(54, 6, 6, 'completed', NULL, '2026-03-03'),
(55, 12, 49, 'completed', NULL, '2026-03-03'),
(57, 12, 50, 'completed', 0, '2026-04-11'),
(63, 12, 1, 'completed', 0, '2026-04-12'),
(64, 12, 5, 'completed', 0, '2026-04-12'),
(65, 12, 2, 'completed', 0, '2026-04-13'),
(66, 12, 6, 'completed', 0, '2026-04-13'),
(67, 12, 3, 'completed', 0, '2026-04-13'),
(68, 12, 7, 'completed', 0, '2026-04-13'),
(69, 12, 4, 'completed', 100, '2026-04-13'),
(70, 12, 8, 'completed', 0, '2026-04-13'),
(71, 12, 9, 'completed', 0, '2026-04-13'),
(72, 12, 10, 'completed', 80, '2026-04-13'),
(73, 12, 26, 'completed', 0, '2026-04-13'),
(74, 12, 27, 'completed', 0, '2026-04-13'),
(75, 12, 28, 'completed', 0, '2026-04-13'),
(76, 12, 29, 'completed', 0, '2026-04-19'),
(77, 12, 20, 'completed', 0, '2026-04-19'),
(78, 12, 21, 'completed', 0, '2026-04-19'),
(79, 12, 22, 'completed', 0, '2026-04-19'),
(80, 12, 23, 'completed', 0, '2026-04-19'),
(81, 12, 24, 'completed', 80, '2026-04-19'),
(82, 12, 51, 'completed', 100, '2026-04-19'),
(84, 12, 30, 'completed', 75, '2026-04-19'),
(85, 12, 25, 'completed', 80, '2026-04-20');

-- --------------------------------------------------------

--
-- Structure de la table `module_section`
--

DROP TABLE IF EXISTS `module_section`;
CREATE TABLE IF NOT EXISTS `module_section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordre` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_section_module` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module_section`
--

INSERT INTO `module_section` (`id`, `module_id`, `titre`, `description`, `ordre`) VALUES
(1, 21, 'Introduction à Spring Boot', 'Concepts fondamentaux et setup', 1),
(2, 21, 'Architecture et configuration', 'Auto-config, starters, properties', 2),
(3, 21, 'Exercice pratique', 'Créer ton premier projet Spring Boot', 3);

-- --------------------------------------------------------

--
-- Structure de la table `module_sous_section`
--

DROP TABLE IF EXISTS `module_sous_section`;
CREATE TABLE IF NOT EXISTS `module_sous_section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_contenu` enum('text','video','pdf','image','exercice','quiz_embed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `contenu_texte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_ressource` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree_minutes` int DEFAULT '5',
  `ordre` int DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_soussection_sec` (`section_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `module_sous_section`
--

INSERT INTO `module_sous_section` (`id`, `section_id`, `titre`, `type_contenu`, `contenu_texte`, `url_ressource`, `duree_minutes`, `ordre`) VALUES
(1, 1, 'Qu\'est-ce que Spring Boot ?', 'text', 'Spring Boot simplifie la création d\'applications Spring. Il élimine la configuration XML et propose des conventions intelligentes...', NULL, 5, 1),
(2, 1, 'Vidéo de présentation', 'video', NULL, 'https://www.youtube.com/watch?v=9SGDpanrc8U', 12, 2),
(3, 1, 'Les starters expliqués', 'text', 'Les starters sont des dépendances pré-configurées. Exemple : spring-boot-starter-web inclut Tomcat + Spring MVC + Jackson...', NULL, 5, 3),
(4, 2, 'Auto-configuration en détail', 'text', 'Spring Boot scanne le classpath et configure automatiquement les beans nécessaires via @ConditionalOnClass...', NULL, 8, 1),
(5, 2, 'application.properties / yaml', 'text', 'Le fichier application.properties centralise toute la config : port serveur, datasource, logging...', NULL, 5, 2),
(6, 3, 'Créer un projet depuis Spring Initializr', 'exercice', 'OBJECTIF : Créer une API REST simple avec Spring Boot.\n\n1. Allez sur https://start.spring.io\n2. Choisissez : Maven, Java 17, Spring Web, Spring Data JPA, H2\n3. Créez un contrôleur GET /api/hello qui retourne \"Hello Humania\"\n4. Testez avec Postman\n\nSoumettez le code de votre contrôleur ci-dessous.', NULL, 20, 1);

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  `userId` int NOT NULL,
  `seen` tinyint(1) DEFAULT '0',
  `dateViewAt` datetime DEFAULT NULL,
  `relatedUserId` int DEFAULT NULL,
  `relatedPublicationId` int DEFAULT NULL,
  `relatedCommentaireId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `relatedUserId` (`relatedUserId`),
  KEY `relatedPublicationId` (`relatedPublicationId`),
  KEY `relatedCommentaireId` (`relatedCommentaireId`),
  KEY `idx_notif_user` (`userId`),
  KEY `idx_notif_seen` (`seen`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id`, `titre`, `message`, `type`, `dateCreation`, `userId`, `seen`, `dateViewAt`, `relatedUserId`, `relatedPublicationId`, `relatedCommentaireId`) VALUES
(105, 'Ajout à un groupe', 'Vous avez été ajouté au groupe \"DEVS\".', 'GROUP', '2026-03-02 23:06:50', 6, 1, '2026-03-02 23:34:21', 7, NULL, NULL),
(106, 'Ajout à un groupe', 'Vous avez été ajouté au groupe \"DEVS\".', 'GROUP', '2026-03-02 23:40:08', 3, 1, '2026-04-07 09:40:33', 7, NULL, NULL),
(107, 'Demande de suivi', 'Sara Mejri souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 01:46:40', 6, 0, NULL, 9, NULL, NULL),
(108, 'Demande de suivi', 'Elwess Nossaf souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 01:48:01', 9, 0, NULL, 6, NULL, NULL),
(109, 'Demande acceptée', 'Elwess Nossaf a accepté votre demande de suivi.', 'FOLLOW_ACCEPTED', '2026-03-03 01:48:05', 9, 0, NULL, 6, NULL, NULL),
(110, 'Demande acceptée', 'Sara Mejri a accepté votre demande de suivi.', 'FOLLOW_ACCEPTED', '2026-03-03 01:48:32', 6, 0, NULL, 9, NULL, NULL),
(111, 'Nouveau like', 'Youssef Haddad a aimé votre publication.', 'LIKE', '2026-03-03 01:51:36', 9, 0, NULL, 10, 78, NULL),
(112, 'Demande de suivi', 'Sara Mejri souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 10:01:45', 3, 1, '2026-04-07 09:40:33', 9, NULL, NULL),
(113, 'Demande de suivi', 'Sara Mejri souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 10:01:46', 5, 0, NULL, 9, NULL, NULL),
(114, 'Demande de suivi', 'Sara Mejri souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 10:01:48', 10, 0, NULL, 9, NULL, NULL),
(115, 'Demande de suivi', 'Sara Mejri souhaite vous suivre.', 'FOLLOW_REQUEST', '2026-03-03 10:01:48', 11, 1, '2026-03-03 22:57:25', 9, NULL, NULL),
(116, '👤 Quelqu\'un vous suit', 'Vous avez un nouveau follower', 'FOLLOW', '2026-05-01 10:36:36', 11, 1, '2026-05-01 10:36:46', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `offboarding`
--

DROP TABLE IF EXISTS `offboarding`;
CREATE TABLE IF NOT EXISTS `offboarding` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `status` enum('pending','in_progress','completed','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `post_published` tinyint(1) DEFAULT '0',
  `can_archive` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departure_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offboarding_task`
--

DROP TABLE IF EXISTS `offboarding_task`;
CREATE TABLE IF NOT EXISTS `offboarding_task` (
  `id` int NOT NULL AUTO_INCREMENT,
  `offboarding_id` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_done` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `offboarding_id` (`offboarding_id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `offboarding_task`
--

INSERT INTO `offboarding_task` (`id`, `offboarding_id`, `label`, `is_done`, `created_at`) VALUES
(1, 1, 'Lettre de démission / notification officielle reçue', 0, '2026-04-30 15:57:27'),
(2, 1, 'Entretien de départ planifié', 0, '2026-04-30 15:57:27'),
(3, 1, 'Plan de transfert de connaissances', 0, '2026-04-30 15:57:27'),
(4, 1, 'Entretien de départ effectué', 0, '2026-04-30 15:57:27'),
(5, 1, 'Transmission des responsabilités au remplaçant', 0, '2026-04-30 15:57:27'),
(6, 1, 'Archivage et transfert des fichiers de travail', 0, '2026-04-30 15:57:27'),
(7, 1, 'Restitution du matériel informatique', 0, '2026-04-30 15:57:27'),
(8, 1, 'Restitution du badge d\'accès', 0, '2026-04-30 15:57:27'),
(9, 1, 'Révocation de tous les accès informatiques', 0, '2026-04-30 15:57:27'),
(10, 1, 'Désactivation du compte email', 0, '2026-04-30 15:57:27'),
(11, 1, 'Clôture de l\'affiliation mutuelle', 0, '2026-04-30 15:57:27'),
(12, 1, 'Solde de tout compte préparé', 0, '2026-04-30 15:57:27'),
(13, 1, 'Certificat de travail rédigé', 0, '2026-04-30 15:57:27'),
(14, 1, 'Lettre de recommandation (si demandée)', 0, '2026-04-30 15:57:27'),
(52, 2, 'Désactivation du compte email', 0, '2026-05-01 10:43:48'),
(51, 2, 'Révocation de tous les accès informatiques', 0, '2026-05-01 10:43:48'),
(50, 2, 'Restitution du badge d\'accès', 0, '2026-05-01 10:43:48'),
(49, 2, 'Restitution du matériel informatique', 0, '2026-05-01 10:43:48'),
(48, 2, 'Archivage et transfert des fichiers de travail', 0, '2026-05-01 10:43:48'),
(47, 2, 'Transmission des responsabilités au remplaçant', 0, '2026-05-01 10:43:48'),
(46, 2, 'Entretien de départ effectué', 0, '2026-05-01 10:43:48'),
(45, 2, 'Plan de transfert de connaissances', 0, '2026-05-01 10:43:48'),
(44, 2, 'Entretien de départ planifié', 0, '2026-05-01 10:43:48'),
(43, 2, 'Lettre de démission / notification officielle reçue', 0, '2026-05-01 10:43:48'),
(66, 3, 'Désactivation du compte email', 1, '2026-05-01 11:03:13'),
(65, 3, 'Révocation de tous les accès informatiques', 1, '2026-05-01 11:03:13'),
(64, 3, 'Restitution du badge d\'accès', 1, '2026-05-01 11:03:13'),
(63, 3, 'Restitution du matériel informatique', 1, '2026-05-01 11:03:13'),
(62, 3, 'Archivage et transfert des fichiers de travail', 1, '2026-05-01 11:03:13'),
(61, 3, 'Transmission des responsabilités au remplaçant', 1, '2026-05-01 11:03:13'),
(60, 3, 'Entretien de départ effectué', 1, '2026-05-01 11:03:13'),
(59, 3, 'Plan de transfert de connaissances', 1, '2026-05-01 11:03:13'),
(58, 3, 'Entretien de départ planifié', 1, '2026-05-01 11:03:13'),
(57, 3, 'Lettre de démission / notification officielle reçue', 1, '2026-05-01 11:03:13'),
(53, 2, 'Clôture de l\'affiliation mutuelle', 0, '2026-05-01 10:43:48'),
(54, 2, 'Solde de tout compte préparé', 0, '2026-05-01 10:43:48'),
(55, 2, 'Certificat de travail rédigé', 0, '2026-05-01 10:43:48'),
(56, 2, 'Lettre de recommandation (si demandée)', 0, '2026-05-01 10:43:48'),
(67, 3, 'Clôture de l\'affiliation mutuelle', 1, '2026-05-01 11:03:13'),
(68, 3, 'Solde de tout compte préparé', 1, '2026-05-01 11:03:13'),
(69, 3, 'Certificat de travail rédigé', 1, '2026-05-01 11:03:13'),
(70, 3, 'Lettre de recommandation (si demandée)', 1, '2026-05-01 11:03:13'),
(71, 4, 'Lettre de démission / notification officielle reçue', 0, '2026-05-01 11:14:28'),
(72, 4, 'Entretien de départ planifié', 0, '2026-05-01 11:14:28'),
(73, 4, 'Plan de transfert de connaissances', 0, '2026-05-01 11:14:28'),
(74, 4, 'Entretien de départ effectué', 0, '2026-05-01 11:14:28'),
(75, 4, 'Transmission des responsabilités au remplaçant', 0, '2026-05-01 11:14:28'),
(76, 4, 'Archivage et transfert des fichiers de travail', 0, '2026-05-01 11:14:28'),
(77, 4, 'Restitution du matériel informatique', 0, '2026-05-01 11:14:28'),
(78, 4, 'Restitution du badge d\'accès', 0, '2026-05-01 11:14:28'),
(79, 4, 'Révocation de tous les accès informatiques', 0, '2026-05-01 11:14:28'),
(80, 4, 'Désactivation du compte email', 0, '2026-05-01 11:14:28'),
(81, 4, 'Clôture de l\'affiliation mutuelle', 0, '2026-05-01 11:14:28'),
(82, 4, 'Solde de tout compte préparé', 0, '2026-05-01 11:14:28'),
(83, 4, 'Certificat de travail rédigé', 0, '2026-05-01 11:14:28'),
(84, 4, 'Lettre de recommandation (si demandée)', 0, '2026-05-01 11:14:28'),
(85, 5, 'Lettre de démission / notification officielle reçue', 0, '2026-05-02 12:02:29'),
(86, 5, 'Respect du délai de préavis légal', 0, '2026-05-02 12:02:29'),
(87, 5, 'Entretien de départ planifié', 0, '2026-05-02 12:02:29'),
(88, 5, 'Plan de transfert de connaissances', 0, '2026-05-02 12:02:29'),
(89, 5, 'Entretien de départ effectué', 0, '2026-05-02 12:02:29'),
(90, 5, 'Transmission des responsabilités au remplaçant', 0, '2026-05-02 12:02:29'),
(91, 5, 'Archivage et transfert des fichiers de travail', 0, '2026-05-02 12:02:29'),
(92, 5, 'Restitution du matériel informatique', 0, '2026-05-02 12:02:29'),
(93, 5, 'Restitution du badge d\'accès', 0, '2026-05-02 12:02:29'),
(94, 5, 'Révocation de tous les accès informatiques', 0, '2026-05-02 12:02:29'),
(95, 5, 'Désactivation du compte email', 0, '2026-05-02 12:02:29'),
(96, 5, 'Clôture de l\'affiliation mutuelle', 0, '2026-05-02 12:02:29'),
(97, 5, 'Solde de tout compte préparé', 0, '2026-05-02 12:02:29'),
(98, 5, 'Certificat de travail rédigé', 0, '2026-05-02 12:02:29'),
(99, 5, 'Lettre de recommandation (si demandée)', 0, '2026-05-02 12:02:29'),
(100, 5, 'Remise des documents légaux obligatoires', 0, '2026-05-02 12:02:29'),
(101, 5, 'Contact avec l\'accompagnement emploi', 0, '2026-05-02 12:02:29');

-- --------------------------------------------------------

--
-- Structure de la table `onboarding`
--

DROP TABLE IF EXISTS `onboarding`;
CREATE TABLE IF NOT EXISTS `onboarding` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `status` enum('pending','in_progress','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `post_published` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `departement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `onboarding`
--

INSERT INTO `onboarding` (`id`, `utilisateur_id`, `status`, `started_at`, `completed_at`, `post_published`, `created_at`, `departement`, `arrival_date`) VALUES
(5, 14, 'in_progress', '2026-05-04 13:22:08', NULL, 1, '2026-05-04 14:22:09', 'Marketing', '2026-05-04 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `onboarding_task`
--

DROP TABLE IF EXISTS `onboarding_task`;
CREATE TABLE IF NOT EXISTS `onboarding_task` (
  `id` int NOT NULL AUTO_INCREMENT,
  `onboarding_id` int NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_done` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `onboarding_id` (`onboarding_id`)
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `onboarding_task`
--

INSERT INTO `onboarding_task` (`id`, `onboarding_id`, `label`, `is_done`, `created_at`) VALUES
(40, 1, 'Installation environnement dev', 0, '2026-05-01 10:43:25'),
(39, 1, 'Accès aux outils internes (intranet…)', 0, '2026-05-01 10:43:25'),
(38, 1, 'Affiliation mutuelle / prévoyance', 0, '2026-05-01 10:43:25'),
(37, 1, 'Accès GitHub / GitLab', 0, '2026-05-01 10:43:25'),
(36, 1, 'Présentation à l\'équipe', 0, '2026-05-01 10:43:25'),
(35, 1, 'Remise du livret d\'accueil', 0, '2026-05-01 10:43:25'),
(34, 1, 'Configuration du poste de travail', 0, '2026-05-01 10:43:25'),
(33, 1, 'Création du compte email professionnel', 0, '2026-05-01 10:43:25'),
(32, 1, 'Création du badge d\'accès', 0, '2026-05-01 10:43:25'),
(31, 1, 'Signature du contrat de travail', 0, '2026-05-01 10:43:25'),
(55, 2, 'Installation environnement dev', 0, '2026-05-01 11:03:44'),
(54, 2, 'Accès aux outils internes (intranet…)', 0, '2026-05-01 11:03:44'),
(53, 2, 'Affiliation mutuelle / prévoyance', 0, '2026-05-01 11:03:44'),
(52, 2, 'Accès GitHub / GitLab', 0, '2026-05-01 11:03:44'),
(51, 2, 'Présentation à l\'équipe', 0, '2026-05-01 11:03:44'),
(50, 2, 'Remise du livret d\'accueil', 0, '2026-05-01 11:03:44'),
(49, 2, 'Configuration du poste de travail', 0, '2026-05-01 11:03:44'),
(48, 2, 'Création du compte email professionnel', 0, '2026-05-01 11:03:44'),
(47, 2, 'Création du badge d\'accès', 0, '2026-05-01 11:03:44'),
(46, 2, 'Signature du contrat de travail', 0, '2026-05-01 11:03:44'),
(41, 1, 'Visite des locaux et règles de sécurité', 0, '2026-05-01 10:43:25'),
(42, 1, 'Lecture de la doc technique', 0, '2026-05-01 10:43:25'),
(43, 1, 'Accès aux serveurs de staging', 0, '2026-05-01 10:43:25'),
(44, 1, 'Pair-programming avec le mentor', 0, '2026-05-01 10:43:25'),
(45, 1, 'Entretien d\'intégration J+30', 0, '2026-05-01 10:43:25'),
(56, 2, 'Visite des locaux et règles de sécurité', 0, '2026-05-01 11:03:44'),
(57, 2, 'Lecture de la doc technique', 0, '2026-05-01 11:03:44'),
(58, 2, 'Accès aux serveurs de staging', 0, '2026-05-01 11:03:44'),
(59, 2, 'Pair-programming avec le mentor', 0, '2026-05-01 11:03:44'),
(60, 2, 'Entretien d\'intégration J+30', 0, '2026-05-01 11:03:44'),
(61, 3, 'Signature du contrat de travail', 0, '2026-05-01 11:28:45'),
(62, 3, 'Création du badge d\'accès', 0, '2026-05-01 11:28:45'),
(63, 3, 'Création du compte email professionnel', 0, '2026-05-01 11:28:45'),
(64, 3, 'Configuration du poste de travail', 0, '2026-05-01 11:28:45'),
(65, 3, 'Remise du livret d\'accueil', 0, '2026-05-01 11:28:45'),
(66, 3, 'Présentation à l\'équipe', 0, '2026-05-01 11:28:45'),
(67, 3, 'Accès GitHub / GitLab', 0, '2026-05-01 11:28:45'),
(68, 3, 'Affiliation mutuelle / prévoyance', 0, '2026-05-01 11:28:45'),
(69, 3, 'Accès aux outils internes (intranet…)', 0, '2026-05-01 11:28:45'),
(70, 3, 'Installation environnement dev', 0, '2026-05-01 11:28:45'),
(71, 3, 'Visite des locaux et règles de sécurité', 0, '2026-05-01 11:28:45'),
(72, 3, 'Lecture de la doc technique', 0, '2026-05-01 11:28:45'),
(73, 3, 'Accès aux serveurs de staging', 0, '2026-05-01 11:28:45'),
(74, 3, 'Pair-programming avec le mentor', 0, '2026-05-01 11:28:45'),
(75, 3, 'Entretien d\'intégration J+30', 0, '2026-05-01 11:28:45'),
(76, 4, 'Signature du contrat de travail', 0, '2026-05-02 12:01:56'),
(77, 4, 'Création du badge d\'accès', 0, '2026-05-02 12:01:56'),
(78, 4, 'Création du compte email professionnel', 0, '2026-05-02 12:01:56'),
(79, 4, 'Configuration du poste de travail', 0, '2026-05-02 12:01:56'),
(80, 4, 'Remise du livret d\'accueil', 0, '2026-05-02 12:01:56'),
(81, 4, 'Présentation à l\'équipe', 0, '2026-05-02 12:01:56'),
(82, 4, 'Accès GitHub / GitLab', 0, '2026-05-02 12:01:56'),
(83, 4, 'Affiliation mutuelle / prévoyance', 0, '2026-05-02 12:01:56'),
(84, 4, 'Accès aux outils internes (intranet…)', 0, '2026-05-02 12:01:56'),
(85, 4, 'Installation environnement dev', 0, '2026-05-02 12:01:56'),
(86, 4, 'Visite des locaux et règles de sécurité', 0, '2026-05-02 12:01:56'),
(87, 4, 'Lecture de la doc technique', 0, '2026-05-02 12:01:56'),
(88, 4, 'Accès aux serveurs de staging', 0, '2026-05-02 12:01:56'),
(89, 4, 'Pair-programming avec le mentor', 0, '2026-05-02 12:01:56'),
(90, 4, 'Entretien d\'intégration J+30', 0, '2026-05-02 12:01:56'),
(91, 5, 'Signature du contrat de travail', 0, '2026-05-04 13:22:08'),
(92, 5, 'Création du badge d\'accès', 0, '2026-05-04 13:22:08'),
(93, 5, 'Création du compte email professionnel', 0, '2026-05-04 13:22:08'),
(94, 5, 'Configuration du poste de travail', 0, '2026-05-04 13:22:08'),
(95, 5, 'Remise du livret d\'accueil', 0, '2026-05-04 13:22:08'),
(96, 5, 'Présentation à l\'équipe', 0, '2026-05-04 13:22:08'),
(97, 5, 'Accès Canva / Adobe Suite', 0, '2026-05-04 13:22:08'),
(98, 5, 'Affiliation mutuelle / prévoyance', 0, '2026-05-04 13:22:08'),
(99, 5, 'Accès aux outils internes (intranet…)', 0, '2026-05-04 13:22:08'),
(100, 5, 'Accès réseaux sociaux d\'entreprise', 0, '2026-05-04 13:22:08'),
(101, 5, 'Visite des locaux et règles de sécurité', 0, '2026-05-04 13:22:08'),
(102, 5, 'Formation Google Analytics', 0, '2026-05-04 13:22:08'),
(103, 5, 'Calendrier éditorial partagé', 0, '2026-05-04 13:22:08'),
(104, 5, 'Entretien d\'intégration J+30', 0, '2026-05-04 13:22:08');

-- --------------------------------------------------------

--
-- Structure de la table `participation_evenement`
--

DROP TABLE IF EXISTS `participation_evenement`;
CREATE TABLE IF NOT EXISTS `participation_evenement` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `idEvenement` int UNSIGNED NOT NULL,
  `idEmploye` int UNSIGNED NOT NULL,
  `dateParticipation` date NOT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `creeLe` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_participation_evenement` (`idEvenement`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pdi`
--

DROP TABLE IF EXISTS `pdi`;
CREATE TABLE IF NOT EXISTS `pdi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `annee` int NOT NULL,
  `progressionGlobale` int DEFAULT NULL,
  `dateCreation` date DEFAULT NULL,
  `statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employe_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pdi_utilisateur` (`employe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pdi`
--

INSERT INTO `pdi` (`id`, `annee`, `progressionGlobale`, `dateCreation`, `statut`, `employe_id`) VALUES
(11, 2026, 50, '2026-03-03', 'Active', 12);

-- --------------------------------------------------------

--
-- Structure de la table `pipeline_etape`
--

DROP TABLE IF EXISTS `pipeline_etape`;
CREATE TABLE IF NOT EXISTS `pipeline_etape` (
  `id` int NOT NULL AUTO_INCREMENT,
  `poste_externe_id` int DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_etape` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duree_moyenne` int DEFAULT NULL,
  `action_automatique` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `obligatoire` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pipeline_poste` (`poste_externe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `poll`
--

DROP TABLE IF EXISTS `poll`;
CREATE TABLE IF NOT EXISTS `poll` (
  `id` int NOT NULL AUTO_INCREMENT,
  `publicationId` int DEFAULT NULL,
  `question` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `closedAt` datetime DEFAULT NULL,
  `totalVotes` int DEFAULT '0',
  `expiresAt` datetime DEFAULT NULL,
  `isAnonymous` tinyint(1) DEFAULT '0',
  `allowMultiple` tinyint(1) DEFAULT '0',
  `createdById` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `publicationId` (`publicationId`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `poll_option`
--

DROP TABLE IF EXISTS `poll_option`;
CREATE TABLE IF NOT EXISTS `poll_option` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pollId` int NOT NULL,
  `optionText` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voteCount` int DEFAULT '0',
  `optionOrder` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pollId` (`pollId`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `poll_option`
--

INSERT INTO `poll_option` (`id`, `pollId`, `optionText`, `voteCount`, `optionOrder`) VALUES
(1, 2, 'mardi', 1, 1),
(2, 2, 'mercredi', 1, 2),
(3, 3, 'vbvbvbv', 0, 1),
(4, 3, 'bvbvb', 0, 2),
(5, 4, 'rereeer', 0, 1),
(6, 4, 'rerere', 1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `poll_vote`
--

DROP TABLE IF EXISTS `poll_vote`;
CREATE TABLE IF NOT EXISTS `poll_vote` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pollId` int NOT NULL,
  `optionId` int NOT NULL,
  `userId` int NOT NULL,
  `votedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`pollId`,`optionId`,`userId`),
  KEY `optionId` (`optionId`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `poll_vote`
--

INSERT INTO `poll_vote` (`id`, `pollId`, `optionId`, `userId`, `votedAt`) VALUES
(7, 2, 2, 1, '2026-02-28 23:45:19'),
(8, 2, 1, 2, '2026-03-01 06:32:57'),
(15, 4, 6, 1, '2026-03-01 13:18:16');

-- --------------------------------------------------------

--
-- Structure de la table `poste_externe`
--

DROP TABLE IF EXISTS `poste_externe`;
CREATE TABLE IF NOT EXISTS `poste_externe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type_contrat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire` double DEFAULT NULL,
  `competences_requises` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `experience_requise` int DEFAULT NULL,
  `niveau_etude_requis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `date_cloture` date DEFAULT NULL,
  `nombre_employe` int DEFAULT NULL,
  `priorite` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `poste_externe`
--

INSERT INTO `poste_externe` (`id`, `titre`, `description`, `type_contrat`, `salaire`, `competences_requises`, `experience_requise`, `niveau_etude_requis`, `statut`, `date_publication`, `date_cloture`, `nombre_employe`, `priorite`) VALUES
(4, 'Développeur Full-Stack Java/Angular', 'Développement et maintenance d\'applications web pour nos clients grands comptes. Travail en équipe Agile/Scrum.', 'CDI', 55000, 'Java, Spring Boot, Angular, SQL, Git', 3, 'Bac+5', 'Ouvert', '2025-01-10', '2025-04-30', 2, 1),
(5, 'Développeur Frontend React', 'Création d\'interfaces utilisateurs modernes et performantes. Collaboration avec les équipes UX/UI.', 'CDI', 48000, 'React, TypeScript, CSS3, Figma', 2, 'Bac+3', 'Ouvert', '2025-01-15', '2025-05-15', 1, 2),
(6, 'Ingénieur DevOps', 'Mise en place et maintenance des pipelines CI/CD. Gestion de l\'infrastructure cloud AWS/Azure.', 'CDI', 62000, 'Docker, Kubernetes, Jenkins, AWS, Terraform', 5, 'Bac+5', 'Ouvert', '2025-02-01', '2025-06-01', 1, 1),
(7, 'Data Analyst', 'Analyse des données métier, création de dashboards et rapports pour les équipes décisionnelles.', 'CDD', 42000, 'Python, SQL, Power BI, Tableau', 2, 'Bac+4', 'Ouvert', '2025-02-10', '2025-07-31', 1, 2),
(8, 'Stagiaire Développeur Mobile Flutter', 'Développement d\'une application mobile cross-platform iOS/Android. Encadrement par un développeur senior.', 'Stagiaire', 800, 'Flutter, Dart, Firebase, Git', 0, 'Bac+3 en cours', 'Ouvert', '2025-03-01', '2025-09-01', 2, 3),
(9, 'Chef de Projet IT', 'Pilotage de projets de transformation digitale. Gestion d\'équipes pluridisciplinaires et suivi budgétaire.', 'CDI', 70000, 'Gestion de projet, Agile, Scrum, MS Project, JIRA', 7, 'Bac+5', 'Ouvert', '2025-01-05', '2025-03-31', 1, 1),
(10, 'Administrateur Système Linux', 'Administration et sécurisation des serveurs Linux. Support N2/N3 pour les équipes internes.', 'CDD', 44000, 'Linux, Bash, Ansible, Nagios, VMware', 4, 'Bac+3', 'Fermé', '2024-11-01', '2025-01-31', 1, 2),
(11, 'UX/UI Designer', 'Conception de parcours utilisateurs et prototypage d\'interfaces pour nos produits SaaS.', 'CDI', 46000, 'Figma, Adobe XD, Sketch, CSS, User Research', 3, 'Bac+3', 'Ouvert', '2025-02-20', '2025-06-30', 1, 2),
(12, 'Technicien Support Informatique', 'Assistance aux utilisateurs internes. Installation, configuration et dépannage du parc informatique.', 'CDD', 32000, 'Windows, Active Directory, Helpdesk, Réseaux', 1, 'Bac+2', 'Fermé', '2024-10-01', '2024-12-31', 2, 3),
(13, 'Architecte Cloud AWS', 'Conception et déploiement d\'architectures cloud scalables. Accompagnement des équipes de développement.', 'CDI', 85000, 'AWS, Architecture microservices, Terraform, Python', 10, 'Bac+5', 'Ouvert', '2025-03-01', '2025-08-31', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `poste_interne`
--

DROP TABLE IF EXISTS `poste_interne`;
CREATE TABLE IF NOT EXISTS `poste_interne` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_poste` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remuneration` double NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `poste_interne`
--

INSERT INTO `poste_interne` (`id`, `type_poste`, `remuneration`, `date_debut`, `date_fin`) VALUES
(1, 'Renfort', 1230, '2026-02-13', '2026-02-21'),
(2, 'Renfort', 1000, '2026-02-13', '2026-02-28'),
(3, 'Mission externe', 3000, '2026-03-10', '2026-04-22'),
(4, 'Mission interne', 1234, '2026-02-06', '2027-02-26'),
(5, 'Mission externe', 22322, '2026-03-11', '2026-03-21'),
(6, 'Mission externe', 4000, '2026-03-10', '2026-03-27'),
(7, 'Mission interne', 4500, '2025-01-15', '2025-04-15'),
(8, 'Mission externe', 5200, '2025-02-01', '2025-07-31'),
(9, 'Renfort', 3800, '2025-01-20', '2025-03-20'),
(10, 'Mission interne', 4200, '2025-03-01', '2025-06-30'),
(11, 'Mission externe', 6000, '2025-04-01', '2025-09-30'),
(12, 'Renfort', 3500, '2025-02-10', '2025-04-10'),
(13, 'Mission interne', 4800, '2025-01-01', '2025-12-31'),
(14, 'Mission externe', 5500, '2025-05-01', '2025-10-31'),
(15, 'Renfort', 3200, '2025-03-15', '2025-05-15'),
(16, 'Mission interne', 4100, '2025-06-01', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `publication`
--

DROP TABLE IF EXISTS `publication`;
CREATE TABLE IF NOT EXISTS `publication` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `authorId` int DEFAULT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `statut` enum('ACTIF','SUPPRIME','ARCHIVE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIF',
  `imageUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombreCommentaires` int DEFAULT '0',
  `nombreReactions` int DEFAULT '0',
  `sharedFromId` int DEFAULT NULL,
  `shareMessage` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `groupId` int DEFAULT NULL,
  `visibility` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'PUBLIC',
  `gifUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generic',
  `content` text COLLATE utf8mb4_unicode_ci,
  `related_employee_id` int DEFAULT NULL,
  `related_event_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pub_author` (`authorId`),
  KEY `idx_pub_date` (`dateCreation`),
  KEY `idx_pub_statut` (`statut`),
  KEY `idx_pub_shared` (`sharedFromId`),
  KEY `idx_publication_group` (`groupId`),
  KEY `idx_publication_related_employee` (`related_employee_id`),
  KEY `idx_publication_related_event` (`related_event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `publication`
--

INSERT INTO `publication` (`id`, `contenu`, `authorId`, `dateCreation`, `dateModification`, `statut`, `imageUrl`, `nombreCommentaires`, `nombreReactions`, `sharedFromId`, `shareMessage`, `groupId`, `visibility`, `gifUrl`, `type`, `content`, `related_employee_id`, `related_event_id`) VALUES
(69, '\"Bonjour à tous ! Nous sommes ravis de partager avec vous les dernières actualités et mises à jour de notre équipe RH. Suivez-nous pour découvrir nos conseils experts, nos offres d\'emploi et nos initiatives pour favoriser un environnement de travail dynamique et inclusif !\"', 11, '2026-03-02 21:35:08', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 1, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', '\"Bonjour à tous ! Nous sommes ravis de partager avec vous les dernières actualités et mises à jour de notre équipe RH. Suivez-nous pour découvrir nos conseils experts, nos offres d\'emploi et nos initiatives pour favoriser un environnement de travail dynamique et inclusif !\"', NULL, NULL),
(70, 'Bienvenue à Humania 🌟, votre nouvelle communauté professionnelle où vous pourrez vous connecter avec des collègues passionnés et engagés 🤝. Nous sommes ravis de vous avoir parmi nous et nous nous réjouissons de voir vos contributions et vos idées innovantes 📈. Rejoignez nos discussions en cours et participez activement à la vie de notre communauté 🗣️. Ensemble, nous allons créer un espace de partage et de croissance 🌱. N\'hésitez pas à partager vos expériences et vos connaissances avec nous 💬.', 11, '2026-03-02 22:51:58', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 1, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Bienvenue à Humania 🌟, votre nouvelle communauté professionnelle où vous pourrez vous connecter avec des collègues passionnés et engagés 🤝. Nous sommes ravis de vous avoir parmi nous et nous nous réjouissons de voir vos contributions et vos idées innovantes 📈. Rejoignez nos discussions en cours et participez activement à la vie de notre communauté 🗣️. Ensemble, nous allons créer un espace de partage et de croissance 🌱. N\'hésitez pas à partager vos expériences et vos connaissances avec nous 💬.', NULL, NULL),
(71, 'Bienvenue à Humania 🌟, votre nouvelle communauté professionnelle où vous pourrez vous connecter avec des collègues passionnés et engagés 🤝. Nous sommes ravis de vous avoir parmi nous et nous nous réjouissons de voir vos contributions et vos idées innovantes 📈. Rejoignez nos discussions en cours et participez activement à la vie de notre communauté 🗣️. Ensemble, nous allons créer un espace de partage et de croissance 🌱. N\'hésitez pas à partager vos expériences et vos connaissances avec nous 💬.', 11, '2026-03-02 22:59:47', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, 70, 'WELCOME', NULL, 'PUBLIC', NULL, 'generic', 'Bienvenue à Humania 🌟, votre nouvelle communauté professionnelle où vous pourrez vous connecter avec des collègues passionnés et engagés 🤝. Nous sommes ravis de vous avoir parmi nous et nous nous réjouissons de voir vos contributions et vos idées innovantes 📈. Rejoignez nos discussions en cours et participez activement à la vie de notre communauté 🗣️. Ensemble, nous allons créer un espace de partage et de croissance 🌱. N\'hésitez pas à partager vos expériences et vos connaissances avec nous 💬.', NULL, NULL),
(72, '👋 Bienvenue à Elwess Nossaf qui vient de rejoindre le groupe ! Souhaitons-lui la bienvenue 🎉', 11, '2026-03-02 23:06:50', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, 7, 'GROUP', NULL, 'generic', '👋 Bienvenue à Elwess Nossaf qui vient de rejoindre le groupe ! Souhaitons-lui la bienvenue 🎉', NULL, NULL),
(73, '\"Bonjour à tous ! Bienvenue dans notre communauté professionnelle ! Nous sommes ravis de partager nos connaissances, nos expériences et nos meilleures pratiques en ressources humaines avec vous. Rejoignez-nous pour découvrir les dernières tendances et évolutions dans le domaine RH et pour échanger avec des professionnels passionnés !\"', 11, '2026-03-02 23:07:13', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, 7, 'GROUP', NULL, 'generic', '\"Bonjour à tous ! Bienvenue dans notre communauté professionnelle ! Nous sommes ravis de partager nos connaissances, nos expériences et nos meilleures pratiques en ressources humaines avec vous. Rejoignez-nous pour découvrir les dernières tendances et évolutions dans le domaine RH et pour échanger avec des professionnels passionnés !\"', NULL, NULL),
(74, 'Renforcez l\'esprit d\'équipe et stimulez la collaboration avec nos activités de Team Building ! Créez des liens solides entre vos collègues, favorisez la communication et l\'innovation, et améliorez globalement la performance de votre équipe. Rejoignez-nous pour découvrir les bienfaits d\'un travail d\'équipe efficace et durable ! #TeamBuilding #EspritDEquipe #Collaboration #DéveloppementDesÉquipes', 6, '2026-03-02 23:28:26', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Renforcez l\'esprit d\'équipe et stimulez la collaboration avec nos activités de Team Building ! Créez des liens solides entre vos collègues, favorisez la communication et l\'innovation, et améliorez globalement la performance de votre équipe. Rejoignez-nous pour découvrir les bienfaits d\'un travail d\'équipe efficace et durable ! #TeamBuilding #EspritDEquipe #Collaboration #DéveloppementDesÉquipes', NULL, NULL),
(75, '👋 Bienvenue à Amine Khelifa qui vient de rejoindre le groupe ! Souhaitons-lui la bienvenue 🎉', 11, '2026-03-02 23:40:08', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, 7, 'GROUP', NULL, 'generic', '👋 Bienvenue à Amine Khelifa qui vient de rejoindre le groupe ! Souhaitons-lui la bienvenue 🎉', NULL, NULL),
(77, 'Bonjour à tous, bienvenue dans notre communauté professionnelle ! Nous sommes ravis de vous avoir parmi nous et nous sommes impatients de partager des idées, des expériences et des connaissances pour favoriser notre croissance et notre succès collectifs. Rejoignez-nous pour discuter, échanger et apprendre ensemble ! #Équipe #Collaboration #DéveloppementProfessionnel', 6, '2026-03-03 01:32:29', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, 7, 'GROUP', NULL, 'generic', 'Bonjour à tous, bienvenue dans notre communauté professionnelle ! Nous sommes ravis de vous avoir parmi nous et nous sommes impatients de partager des idées, des expériences et des connaissances pour favoriser notre croissance et notre succès collectifs. Rejoignez-nous pour discuter, échanger et apprendre ensemble ! #Équipe #Collaboration #DéveloppementProfessionnel', NULL, NULL),
(78, 'Je suis ravie de rejoindre l\'équipe ! Je suis une nouvelle collaboratrice enthousiaste et motivée, prête à apporter ma contribution et à apprendre de mes collègues. Je suis impatiente de découvrir les opportunités et les défis qui m\'attendent dans ce nouveau chapitre de ma carrière. #NouvelleCollaboratrice #Équipe #DéveloppementProfessionnel', 9, '2026-03-03 01:45:49', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 2, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Je suis ravie de rejoindre l\'équipe ! Je suis une nouvelle collaboratrice enthousiaste et motivée, prête à apporter ma contribution et à apprendre de mes collègues. Je suis impatiente de découvrir les opportunités et les défis qui m\'attendent dans ce nouveau chapitre de ma carrière. #NouvelleCollaboratrice #Équipe #DéveloppementProfessionnel', NULL, NULL),
(79, 'Bienvenue à Humania, l\'entreprise où l\'innovation et la passion se rencontrent 💡! Nous sommes ravis de vous accueillir dans notre équipe dynamique et dédiée 🤝. Chez Humania, nous mettons l\'accent sur le développement personnel et professionnel de nos collaborateurs, pour qu\'ils puissent atteindre leur plein potentiel 🚀. Rejoignez-nous pour découvrir un environnement de travail stimulant et collaboratif, où chaque jour est une nouvelle opportunité de grandir et de réussir 🌟. Nous sommes impatients de voir l\'impact que vous allez avoir sur notre équipe et sur notre entreprise 💪!', 12, '2026-03-03 04:20:05', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Bienvenue à Humania, l\'entreprise où l\'innovation et la passion se rencontrent 💡! Nous sommes ravis de vous accueillir dans notre équipe dynamique et dédiée 🤝. Chez Humania, nous mettons l\'accent sur le développement personnel et professionnel de nos collaborateurs, pour qu\'ils puissent atteindre leur plein potentiel 🚀. Rejoignez-nous pour découvrir un environnement de travail stimulant et collaboratif, où chaque jour est une nouvelle opportunité de grandir et de réussir 🌟. Nous sommes impatients de voir l\'impact que vous allez avoir sur notre équipe et sur notre entreprise 💪!', NULL, NULL),
(80, 'Qui est prêt à se défouler sur le court de padel ce week-end 🎾👊 ? Nous organisons un match amical et nous aimerions vous voir là-bas ! 🤩 N\'hésitez pas à vous inscrire et à nous dire si vous avez besoin de partenaires ou de raquettes 🎯. Ce sera l\'occasion parfaite de passer du temps ensemble en dehors du bureau et de créer des souvenirs inoubliables 😄. Venez nombreux et prêts à vous amuser ! 💪', 12, '2026-03-03 08:42:42', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Qui est prêt à se défouler sur le court de padel ce week-end 🎾👊 ? Nous organisons un match amical et nous aimerions vous voir là-bas ! 🤩 N\'hésitez pas à vous inscrire et à nous dire si vous avez besoin de partenaires ou de raquettes 🎯. Ce sera l\'occasion parfaite de passer du temps ensemble en dehors du bureau et de créer des souvenirs inoubliables 😄. Venez nombreux et prêts à vous amuser ! 💪', NULL, NULL),
(81, 'Je suis ravie de rejoindre l\'équipe ! Je suis une nouvelle collaboratrice enthousiaste et motivée, prête à apporter ma contribution et à apprendre de mes collègues. Je suis impatiente de découvrir les opportunités et les défis qui m\'attendent dans ce nouveau chapitre de ma carrière. #NouvelleCollaboratrice #Équipe #DéveloppementProfessionnel', 9, '2026-03-03 10:01:25', '2026-04-30 12:27:46', 'ACTIF', NULL, 1, 0, 78, 'fuhhhh', NULL, 'PUBLIC', NULL, 'generic', 'Je suis ravie de rejoindre l\'équipe ! Je suis une nouvelle collaboratrice enthousiaste et motivée, prête à apporter ma contribution et à apprendre de mes collègues. Je suis impatiente de découvrir les opportunités et les défis qui m\'attendent dans ce nouveau chapitre de ma carrière. #NouvelleCollaboratrice #Équipe #DéveloppementProfessionnel', NULL, NULL),
(82, 'Salut tout le monde ! 🤗 L\'intelligence artificielle (IA) est de plus en plus présente dans notre vie quotidienne et dans notre travail 💻. Mais savez-vous comment l\'IA peut nous aider à améliorer nos processus et à augmenter notre productivité ? 🤔 Nous allons explorer ensemble les possibilités offertes par l\'IA et découvrir comment nous pouvons l\'utiliser pour nous simplifier la vie 💡. Partagez vos idées et vos expériences avec l\'IA en commentaire ci-dessous ! 💬', 9, '2026-03-03 10:03:39', '2026-04-30 12:27:46', 'SUPPRIME', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Salut tout le monde ! 🤗 L\'intelligence artificielle (IA) est de plus en plus présente dans notre vie quotidienne et dans notre travail 💻. Mais savez-vous comment l\'IA peut nous aider à améliorer nos processus et à augmenter notre productivité ? 🤔 Nous allons explorer ensemble les possibilités offertes par l\'IA et découvrir comment nous pouvons l\'utiliser pour nous simplifier la vie 💡. Partagez vos idées et vos expériences avec l\'IA en commentaire ci-dessous ! 💬', NULL, NULL),
(83, 'Le football est de retour en 2025 🏟️🔥 ! Les équipes se préparent pour une nouvelle saison passionnante, avec de nouveaux joueurs talentueux et des stratégies innovantes 🤔. Qui sera le champion cette année ? 🏆 Les fans sont déjà excités et les matchs s\'annoncent intenses 📺💥. Restez à l\'affût des dernières nouvelles et des résultats sur notre page pour suivre l\'actualité du football 2025 📱👍 !', 9, '2026-03-03 10:05:16', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 1, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Le football est de retour en 2025 🏟️🔥 ! Les équipes se préparent pour une nouvelle saison passionnante, avec de nouveaux joueurs talentueux et des stratégies innovantes 🤔. Qui sera le champion cette année ? 🏆 Les fans sont déjà excités et les matchs s\'annoncent intenses 📺💥. Restez à l\'affût des dernières nouvelles et des résultats sur notre page pour suivre l\'actualité du football 2025 📱👍 !', NULL, NULL),
(84, 'Découvrez les dernières innovations en matière de systèmes d\'exploitation mobiles avec Android 📱💻 ! Les équipes de développement travaillent sans relâche pour améliorer la sécurité et la performance de ce système 🚀. Nous sommes ravis de voir comment ces avancées peuvent être mises à profit pour améliorer nos processus internes et notre communication avec les clients 📈. Rejoignez-nous pour explorer les possibilités offertes par Android et découvrir comment nous pouvons les intégrer dans notre stratégie digitale 🤝. Ensemble, nous pouvons créer un avenir plus connecté et plus efficace 💡 !', 9, '2026-03-03 10:05:54', '2026-04-30 12:27:46', 'ACTIF', NULL, 1, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'Découvrez les dernières innovations en matière de systèmes d\'exploitation mobiles avec Android 📱💻 ! Les équipes de développement travaillent sans relâche pour améliorer la sécurité et la performance de ce système 🚀. Nous sommes ravis de voir comment ces avancées peuvent être mises à profit pour améliorer nos processus internes et notre communication avec les clients 📈. Rejoignez-nous pour explorer les possibilités offertes par Android et découvrir comment nous pouvons les intégrer dans notre stratégie digitale 🤝. Ensemble, nous pouvons créer un avenir plus connecté et plus efficace 💡 !', NULL, NULL),
(85, 'aaaaa', 3, '2026-04-06 17:52:11', '2026-04-30 12:27:46', 'SUPPRIME', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'aaaaa', NULL, NULL),
(86, 'bonjour', 3, '2026-04-07 09:35:05', '2026-04-30 12:27:46', 'ACTIF', NULL, 1, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'generic', 'bonjour', NULL, NULL),
(87, '', 3, '2026-04-07 09:41:04', '2026-04-30 12:27:46', 'ACTIF', NULL, 0, 0, 86, NULL, NULL, 'PUBLIC', NULL, 'generic', '', NULL, NULL),
(90, '🎉 Bienvenue à Omar Mansouri !\n\nNous sommes ravis d\'accueillir Omar Mansouri qui rejoint l\'équipe Ingénierie en tant que Chef de projet à compter du 01/05/2026.\n\nToute l\'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n#Onboarding #NouveauCollaborateur #Bienvenue', NULL, '2026-05-01 11:09:27', '2026-05-01 12:10:18', 'ACTIF', NULL, 0, 1, NULL, NULL, NULL, 'PUBLIC', NULL, 'onboarding', NULL, NULL, NULL),
(91, '👋 Karim Ben Ali (IT — Développeur Backend) quitte nos équipes le 31/05/2026 suite à une démission.\nNous lui souhaitons le meilleur pour la suite. Merci pour tout ! 🙏\n\n#Humania #Offboarding #Démission', NULL, '2026-05-01 11:14:30', '2026-05-01 12:17:13', 'ACTIF', NULL, 1, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'offboarding', NULL, NULL, NULL),
(92, '🎉 Bienvenue à Leila Ben Youssef !\n\nNous sommes ravis d\'accueillir Leila Ben Youssef qui rejoint l\'équipe Ingénierie en tant que Product Owner à compter du 01/05/2026.\n\nToute l\'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n#Onboarding #NouveauCollaborateur #Bienvenue', NULL, '2026-05-01 11:28:55', NULL, 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'onboarding', NULL, NULL, NULL),
(93, '🎉 Bienvenue à Sami Haddad !\n\nNous sommes ravis d\'accueillir Sami Haddad qui rejoint l\'équipe Ingénierie en tant que Développeur Frontend à compter du 02/05/2026.\n\nToute l\'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n#Onboarding #NouveauCollaborateur #Bienvenue', NULL, '2026-05-02 12:02:04', NULL, 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'onboarding', NULL, NULL, NULL),
(94, '👋 Amine Zahraoui (Cybersecurity — Ingénieur Sécurité) quitte nos équipes le 01/06/2026 suite à une licenciement.\nNous lui souhaitons le meilleur pour la suite. Merci pour tout ! 🙏\n\n#Humania #Offboarding #Licenciement', NULL, '2026-05-02 12:02:37', NULL, 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'offboarding', NULL, NULL, NULL),
(98, '🎉 Bienvenue à Yasmine Trabelsi !\n\nNous sommes ravis d\'accueillir Yasmine Trabelsi qui rejoint l\'équipe Marketing en tant que Data Analyst à compter du 04/05/2026.\n\nToute l\'équipe Humania lui souhaite une excellente intégration et une belle aventure parmi nous ! 🚀\n\n#Onboarding #NouveauCollaborateur #Bienvenue', NULL, '2026-05-04 13:22:13', NULL, 'ACTIF', NULL, 0, 0, NULL, NULL, NULL, 'PUBLIC', NULL, 'onboarding', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `quiz_question`
--

DROP TABLE IF EXISTS `quiz_question`;
CREATE TABLE IF NOT EXISTS `quiz_question` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_d` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonne_reponse` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `explication` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_quiz_module` (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `quiz_question`
--

INSERT INTO `quiz_question` (`id`, `module_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `bonne_reponse`, `explication`) VALUES
(5, 4, 'Quelle est la bonne pratique ?', 'Option A', 'Option B (correcte)', 'Option C', 'Option D', 'B', NULL),
(6, 4, 'Qu\'est-ce que ce concept ?', 'Vrai', 'Faux', NULL, NULL, 'A', NULL),
(7, 10, 'Quel service AWS gere les identites et les acces ?', 'Amazon S3', 'AWS IAM', 'Amazon EC2', 'Amazon RDS', 'B', 'IAM gere utilisateurs, groupes, roles et politiques d\'acces.'),
(8, 10, 'Quelle classe de stockage S3 est la moins chere pour des archives ?', 'S3 Standard', 'S3 Intelligent-Tiering', 'S3 Glacier', 'S3 Standard-IA', 'C', 'S3 Glacier est optimise pour l\'archivage long terme a tres faible cout.'),
(9, 10, 'Qu\'est-ce qu\'une AMI dans AWS ?', 'Un service de messagerie', 'Une image machine Amazon pour lancer des instances EC2', 'Un outil de monitoring', 'Un service de base de donnees', 'B', 'Amazon Machine Image est un modele pre-configure pour lancer des instances EC2.'),
(10, 10, 'Quel service permet une base de donnees MySQL entierement managee ?', 'Amazon DynamoDB', 'Amazon Redshift', 'Amazon RDS', 'Amazon ElastiCache', 'C', 'Amazon RDS supporte MySQL, PostgreSQL, MariaDB, Oracle et SQL Server.'),
(11, 10, 'A quoi sert l\'Auto Scaling Group ?', 'Sauvegarder automatiquement les donnees', 'Ajuster automatiquement le nombre d\'instances EC2', 'Chiffrer les donnees S3', 'Creer des VPCs automatiquement', 'B', 'L\'Auto Scaling ajuste la capacite en fonction de la demande pour optimiser couts et performances.'),
(14, 15, 'Quel style de leadership laisse le maximum d\'autonomie a l\'equipe ?', 'Autoritaire', 'Participatif', 'Delegatif', 'Transactionnel', 'C', 'Le style delegatif offre une totale autonomie aux membres competents et autonomes.'),
(15, 15, 'La communication assertive consiste a :', 'Imposer son point de vue', 'Exprimer ses besoins clairement tout en respectant autrui', 'Eviter les conflits a tout prix', 'Etre toujours d\'accord', 'B', 'L\'assertivite equilibre affirmation de soi et respect de l\'autre.'),
(16, 15, 'Selon Herzberg, quels sont les vrais facteurs de motivation ?', 'Salaire et avantages', 'Reconnaissance et accomplissement', 'Securite de l\'emploi', 'Conditions de travail', 'B', 'Pour Herzberg, la reconnaissance et l\'accomplissement sont des facteurs de motivation intrinseques.'),
(17, 15, 'La premiere etape pour resoudre un conflit est :', 'Ignorer le probleme', 'Sanctionner immediatement', 'Identifier la cause racine', 'Faire une reunion generale', 'C', 'Comprendre la cause profonde du conflit est essentiel avant toute intervention.'),
(21, 19, 'Quel pourcentage de la communication est non verbal selon Mehrabian ?', '7%', '38%', '55%', '93%', 'C', 'Selon Mehrabian : 55% corporel, 38% vocal, 7% verbal.'),
(22, 19, 'La reformulation en ecoute active permet de :', 'Changer le sujet', 'Verifier la comprehension du message recu', 'Couper la parole', 'Donner son avis', 'B', 'La reformulation montre a l\'interlocuteur qu\'il a bien ete compris.'),
(23, 19, 'Le feedback constructif doit etre :', 'General et tardif', 'Specifique, factuel et oriente solution', 'Negatif pour motiver', 'Public pour l\'exemple', 'B', 'Un bon feedback est SMART : Specifique, Mesurable et oriente vers l\'amelioration.'),
(24, 19, 'Quel est le role du canal dans le modele de communication ?', 'Encoder le message', 'Transmettre le message de l\'emetteur au recepteur', 'Decoder le message', 'Generer du bruit', 'B', 'Le canal est le support de transmission : email, voix, video, etc.'),
(28, 25, 'Quelle annotation marque le point d\'entree d\'une application Spring Boot ?', '@SpringComponent', '@SpringBootApplication', '@EnableAutoConfiguration', '@ComponentScan', 'B', '@SpringBootApplication combine @Configuration, @EnableAutoConfiguration et @ComponentScan.'),
(29, 25, 'Quelle annotation definit un endpoint HTTP GET dans Spring Boot ?', '@PostMapping', '@RequestMapping(method=GET)', '@GetMapping', '@HttpGet', 'C', '@GetMapping est l\'annotation raccourcie pour @RequestMapping(method = RequestMethod.GET).'),
(30, 25, 'Qu\'est-ce qu\'un JPA Repository ?', 'Un service de cache', 'Une interface pour acceder aux donnees sans ecrire de SQL', 'Un controleur REST', 'Un fichier de configuration', 'B', 'JpaRepository fournit des methodes CRUD prets a l\'emploi via Spring Data.'),
(31, 25, 'Dans Spring Security, JWT signifie :', 'Java Web Token', 'JSON Web Token', 'Java Wrapper Type', 'JSON Worker Thread', 'B', 'JWT (JSON Web Token) est un standard de token securise pour l\'authentification stateless.'),
(32, 25, 'Quelle annotation permet de simuler un objet dans les tests JUnit ?', '@Spy', '@Mock', '@Inject', '@Fake', 'B', '@Mock de Mockito cree un objet simule pour isoler les tests unitaires.'),
(35, 30, 'Quel operateur RxJS transforme chaque valeur emise en un nouvel Observable ?', 'map', 'filter', 'switchMap', 'tap', 'C', 'switchMap projette chaque valeur vers un Observable et annule le precedent.'),
(36, 30, 'Dans NgRx, les Effects sont utilises pour :', 'Modifier le state directement', 'Gerer les effets de bord (appels API, etc.)', 'Definir la structure du state', 'Selectionner des donnees du store', 'B', 'Les Effects interceptent les actions et executent des traitements asynchrones comme les appels HTTP.'),
(37, 30, 'La strategie OnPush dans Angular optimise :', 'Les animations', 'La detection des changements (Change Detection)', 'Le lazy loading', 'Le routing', 'B', 'OnPush limite la detection de changements aux inputs modifies, ameliorant les performances.'),
(38, 30, 'BehaviorSubject vs Subject : quelle est la difference principale ?', 'BehaviorSubject est plus rapide', 'BehaviorSubject emet immediatement la derniere valeur aux nouveaux abonnes', 'Subject supporte plusieurs valeurs', 'Aucune difference', 'B', 'BehaviorSubject conserve et reemet la derniere valeur a tout nouvel abonne, contrairement a Subject.'),
(42, 35, 'WBS signifie :', 'Work Budget Schedule', 'Work Breakdown Structure', 'Weekly Business Summary', 'Work Based System', 'B', 'Le WBS decompose le projet en lots de travail hierarchiques et livrables.'),
(43, 35, 'Dans Scrum, qui est responsable du Product Backlog ?', 'Scrum Master', 'L\'equipe de developpement', 'Product Owner', 'Le client', 'C', 'Le Product Owner priorise et maintient le backlog selon la valeur metier.'),
(44, 35, 'Le diagramme de PERT est utilise pour :', 'Suivre les budgets', 'Estimer la duree et identifier le chemin critique', 'Gerer les ressources humaines', 'Planifier les reunions', 'B', 'PERT modelise les dependances entre taches et calcule le chemin critique.'),
(45, 35, 'Quel est le role du Scrum Master ?', 'Gerer le budget du projet', 'Ecrire les user stories', 'Faciliter le processus Scrum et lever les obstacles', 'Valider les livrables', 'C', 'Le Scrum Master est un servant-leader qui protege l\'equipe et facilite l\'adoption de Scrum.'),
(46, 35, 'Un sprint en Scrum dure generalement :', '1 jour', '1 semaine', '2 a 4 semaines', '3 mois', 'C', 'Un sprint est une iteration de 1 a 4 semaines, typiquement 2 semaines.'),
(49, 39, 'Selon le modele de Tuckman, quelle est la 3eme phase de developpement d\'une equipe ?', 'Forming', 'Storming', 'Norming', 'Performing', 'C', 'Forming -> Storming -> Norming -> Performing -> Adjourning selon Tuckman.'),
(50, 39, 'La confiance au sein d\'une equipe est construite principalement par :', 'Des regles strictes', 'Des interactions regulieres et la transparence', 'La hierarchie', 'La competition interne', 'B', 'La confiance se developpe par la coherence des actions, la transparence et la communication ouverte.'),
(51, 39, 'Qu\'est-ce qu\'une equipe a haute performance ?', 'Une equipe qui travaille de longues heures', 'Une equipe autonome avec des objectifs clairs et une forte cohesion', 'Une equipe competitive', 'Une equipe avec un manager directif', 'B', 'Les equipes hautement performantes combinent autonomie, competences, objectifs partages et confiance mutuelle.'),
(52, 44, 'Quelle bibliothèque est utilisée pour la manipulation de DataFrames en Python ?', 'NumPy', 'Matplotlib', 'Pandas', 'Scikit-learn', 'C', 'Pandas fournit la structure DataFrame idéale pour manipuler des données tabulaires.'),
(53, 44, 'Comment filtrer un DataFrame df pour garder les lignes où age > 30 ?', 'df.filter(age > 30)', 'df[df[\"age\"] > 30]', 'df.where(\"age > 30\")', 'df.select(age > 30)', 'B', 'Le filtrage booléen df[condition] est la syntaxe standard Pandas.'),
(54, 44, 'Quelle fonction Pandas lit un fichier CSV ?', 'pd.load_csv()', 'pd.read_csv()', 'pd.import_csv()', 'pd.open_csv()', 'B', 'pd.read_csv() est la fonction standard pour charger un fichier CSV.'),
(55, 44, 'np.array([1,2,3]) + 10 retourne :', 'Erreur', '[11]', '[11, 12, 13]', '[1, 2, 3, 10]', 'C', 'NumPy applique le broadcasting : chaque élément est additionné à 10.'),
(56, 44, 'Quelle méthode supprime les lignes avec des valeurs manquantes ?', 'df.remove_na()', 'df.clean()', 'df.dropna()', 'df.fillna()', 'C', 'df.dropna() supprime les lignes contenant des NaN. df.fillna() les remplace.'),
(57, 48, 'Quelle commande Git crée une nouvelle branche ET bascule dessus ?', 'git branch feature', 'git checkout feature', 'git checkout -b feature', 'git switch --create feature', 'C', 'git checkout -b crée et bascule en une seule commande. git switch -c est l\'alternative moderne.'),
(58, 48, 'Que fait git rebase main ?', 'Fusionne main dans la branche courante', 'Rejoue les commits de la branche sur le sommet de main', 'Supprime main', 'Crée une branche main', 'B', 'Rebase déplace la base de la branche courante au sommet de main, créant un historique linéaire.'),
(59, 48, 'Dans GitHub Actions, un workflow est déclenché par :', 'Un cron uniquement', 'Des events (push, pull_request, schedule...)', 'Un merge uniquement', 'Une commande manuelle uniquement', 'B', 'Les workflows peuvent être déclenchés par push, pull_request, schedule, workflow_dispatch et bien d\'autres événements.'),
(60, 48, 'CI/CD signifie :', 'Code Integration / Code Deployment', 'Continuous Integration / Continuous Deployment', 'Complete Integration / Complete Delivery', 'Core Interface / Core Development', 'B', 'CI/CD = Continuous Integration (tests auto à chaque commit) / Continuous Deployment (livraison automatique).'),
(61, 51, 'Combien de valeurs fondamentales contient le Manifeste Agile ?', '3', '4', '6', '12', 'B', 'Le Manifeste Agile (2001) contient 4 valeurs et 12 principes.'),
(62, 51, 'Qui priorise le Product Backlog dans Scrum ?', 'Scrum Master', 'L\'équipe de développement', 'Product Owner', 'Le manager', 'C', 'Le Product Owner est responsable du backlog et de la priorisation selon la valeur métier.'),
(63, 51, 'La rétrospective Scrum sert à :', 'Présenter le produit au client', 'S\'améliorer sur les processus et la collaboration', 'Planifier le prochain sprint', 'Démo des fonctionnalités', 'B', 'La rétrospective (fin de sprint) porte sur le \"comment on travaille\" pour s\'améliorer continuellement.'),
(64, 51, 'Un sprint se termine toujours par :', 'Un rapport PDF', 'Un incrément potentiellement livrable', 'Une présentation PowerPoint', 'Un compte rendu', 'B', 'L\'objectif de chaque sprint est de produire un incrément \"Done\" potentiellement livrable au client.'),
(65, 9, 'Amazon RDS supporte quel moteur de base de données ?', 'MongoDB uniquement', 'MySQL, PostgreSQL, Oracle, SQL Server', 'DynamoDB uniquement', 'Cassandra', 'B', 'RDS supporte 6 moteurs : MySQL, PostgreSQL, MariaDB, Oracle, SQL Server, et Amazon Aurora.'),
(66, 9, 'Quelle est la différence entre RDS et DynamoDB ?', 'Aucune différence', 'RDS est relationnel (SQL), DynamoDB est NoSQL', 'DynamoDB est plus lent', 'RDS ne supporte pas les backups', 'B', 'RDS = bases relationnelles SQL. DynamoDB = base NoSQL clé-valeur/document hautement scalable.'),
(67, 55, 'Selon la règle de Mehrabian, quel pourcentage représente le langage verbal dans la communication ?', '55%', '38%', '7%', '93%', 'C', '7% verbal (les mots), 38% vocal (voix, intonation), 55% visuel (langage corporel).'),
(68, 55, 'La structure PREP pour répondre à une question signifie :', 'Prepare, Rehearse, Execute, Polish', 'Point, Reason, Example, Point', 'Present, Repeat, Engage, Pause', 'Plan, React, Evaluate, Perform', 'B', 'PREP : Point (votre position) + Reason (la raison) + Example (exemple concret) + Point (conclusion).'),
(69, 55, 'Le \"hook\" dans un discours sert à :', 'Conclure le discours', 'Résumer les points clés', 'Accrocher l\'attention dès les premières secondes', 'Gérer les questions', 'C', 'Le hook (accroche) est la première phrase — question, statistique, anecdote — qui capte immédiatement l\'auditoire.'),
(70, 55, 'Le trac avant de parler en public est :', 'Un signe de manque de préparation', 'Un problème psychologique', 'Normal et convertible en énergie positive', 'À éviter absolument', 'C', 'Le trac est une réponse physiologique normale. Les meilleurs orateurs le ressentent et le canalisent en énergie.'),
(71, 58, 'La matrice d\'Eisenhower classe les tâches selon :', 'Durée et coût', 'Urgence et importance', 'Priorité et ressources', 'Facilité et impact', 'B', 'Eisenhower divise les tâches en 4 quadrants : Urgent+Important, Urgent+Non important, Non urgent+Important, Non urgent+Non important.'),
(72, 58, 'La règle de Pareto appliquée au travail indique que :', '80% du temps produit 80% des résultats', '20% des tâches génèrent 80% des résultats', '50% du travail donne 100% des résultats', 'Le travail doit être réparti également', 'B', 'Pareto (80/20) : 20% de vos actions génèrent 80% de vos résultats. Identifiez et protégez ces 20% à fort impact.'),
(73, 58, 'Le multitasking (faire plusieurs choses à la fois) :', 'Double la productivité', 'N\'a aucun impact', 'Réduit la productivité de 40% selon les études', 'Améliore la créativité', 'C', 'Des études (dont celles de l\'APA) montrent que le multitasking réduit la productivité jusqu\'à 40% en raison du coût cognitif du changement de contexte.'),
(74, 63, 'Le triangle CIA en cybersécurité signifie :', 'Cyber Intelligence Agency', 'Confidentialité, Intégrité, Disponibilité', 'Conformité, Innovation, Audit', 'Cryptage, Isolation, Authentification', 'B', 'CIA = Confidentiality (Confidentialité), Integrity (Intégrité), Availability (Disponibilité) — les 3 piliers de la sécurité de l\'information.'),
(75, 63, 'Le phishing est une attaque qui exploite :', 'Les failles techniques des serveurs', 'La psychologie humaine pour obtenir des informations', 'Les vulnérabilités des bases de données', 'Le réseau Wi-Fi', 'B', 'Le phishing (et l\'ingénierie sociale en général) exploite la psychologie humaine : urgence, peur, curiosité, confiance.'),
(76, 63, 'Le MFA (Multi-Factor Authentication) réduit le risque de compromission de :', '50%', '75%', '99,9%', '30%', 'C', 'Selon Microsoft, l\'activation du MFA bloque 99,9% des attaques automatisées sur les comptes.'),
(77, 63, 'Quelle est la cause principale des incidents de cybersécurité ?', 'Les failles logicielles non patchées', 'Les attaques de force brute', 'L\'erreur humaine', 'Les vulnérabilités hardware', 'C', '95% des incidents de cybersécurité impliquent une erreur humaine (IBM, 2023) : clic sur lien phishing, mot de passe faible, mauvaise configuration.'),
(78, 63, 'Un ransomware est :', 'Un antivirus puissant', 'Un outil de sauvegarde automatique', 'Un malware qui chiffre vos données et demande une rançon', 'Un pare-feu avancé', 'C', 'Le ransomware chiffre les fichiers de la victime et exige une rançon (souvent en crypto) pour fournir la clé de déchiffrement.');

-- --------------------------------------------------------

--
-- Structure de la table `reaction`
--

DROP TABLE IF EXISTS `reaction`;
CREATE TABLE IF NOT EXISTS `reaction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('LIKE','LOVE','LAUGH','WOW','SAD','ANGRY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'LIKE',
  `userId` int NOT NULL,
  `publicationId` int DEFAULT NULL,
  `commentaireId` int DEFAULT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction_pub` (`userId`,`publicationId`),
  UNIQUE KEY `unique_reaction_comment` (`userId`,`commentaireId`),
  KEY `idx_reaction_user` (`userId`),
  KEY `idx_reaction_pub` (`publicationId`),
  KEY `idx_reaction_comment` (`commentaireId`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reaction`
--

INSERT INTO `reaction` (`id`, `type`, `userId`, `publicationId`, `commentaireId`, `dateCreation`) VALUES
(90, 'LOVE', 11, 69, NULL, '2026-03-02 22:52:02'),
(92, 'LIKE', 11, 70, NULL, '2026-03-02 22:52:09'),
(93, 'LIKE', 10, 78, NULL, '2026-03-03 01:51:36'),
(94, 'LOVE', 9, 78, NULL, '2026-03-03 09:57:35'),
(96, 'LIKE', 9, NULL, 48, '2026-03-03 10:09:37'),
(97, 'LIKE', 3, 83, NULL, '2026-04-06 17:50:56'),
(100, 'LOVE', 11, 90, NULL, '2026-05-01 11:10:18');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `chair_id` int DEFAULT NULL,
  `reservation_date` date DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chair_id` (`chair_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservation_espaces`
--

DROP TABLE IF EXISTS `reservation_espaces`;
CREATE TABLE IF NOT EXISTS `reservation_espaces` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `idEspace` int UNSIGNED NOT NULL,
  `idEmploye` int UNSIGNED NOT NULL,
  `dateReservation` date NOT NULL,
  `dateHeureDebut` datetime NOT NULL,
  `dateHeureFin` datetime NOT NULL,
  `objectif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statut` tinyint(1) DEFAULT '1',
  `creeLe` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reservation_space` (`idEspace`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservation_espaces`
--

INSERT INTO `reservation_espaces` (`id`, `idEspace`, `idEmploye`, `dateReservation`, `dateHeureDebut`, `dateHeureFin`, `objectif`, `statut`, `creeLe`) VALUES
(2, 28, 12, '2026-03-03', '2026-03-03 08:00:00', '2026-03-03 09:00:00', 'sdfghj,k;,nbvc', 1, '2026-03-03 00:00:00'),
(3, 28, 12, '2026-03-03', '2026-03-03 09:00:00', '2026-03-03 10:00:00', 'jh', 1, '2026-03-03 00:00:00'),
(4, 29, 11, '2026-04-28', '2026-04-28 08:00:00', '2026-04-28 09:00:00', 'mahmoud', 1, '2026-04-28 20:03:03'),
(5, 28, 11, '2026-04-28', '2026-04-28 08:00:00', '2026-04-28 09:00:00', 'bena', 1, '2026-04-28 20:03:15'),
(6, 28, 11, '2026-05-02', '2026-05-02 15:00:00', '2026-05-02 16:00:00', 'lamma', 1, '2026-05-02 15:34:04'),
(7, 28, 3, '2026-05-02', '2026-05-02 12:00:00', '2026-05-02 13:00:00', 'aa', 1, '2026-05-02 15:34:39');

-- --------------------------------------------------------

--
-- Structure de la table `resultatevaluation`
--

DROP TABLE IF EXISTS `resultatevaluation`;
CREATE TABLE IF NOT EXISTS `resultatevaluation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note` double DEFAULT NULL,
  `datePassage` date DEFAULT NULL,
  `statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commentaire` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evaluation_id` int NOT NULL,
  `employe_id` int DEFAULT NULL,
  `score_pct` int DEFAULT NULL,
  `niveau_delta` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_resultat_evaluation` (`evaluation_id`),
  KEY `fk_resultat_utilisateur` (`employe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `resultatevaluation`
--

INSERT INTO `resultatevaluation` (`id`, `note`, `datePassage`, `statut`, `commentaire`, `evaluation_id`, `employe_id`, `score_pct`, `niveau_delta`) VALUES
(6, 100, '2026-03-03', 'passed', NULL, 7, 6, 100, 0),
(18, NULL, '2026-04-18', 'passed', NULL, 22, 11, 100, 1),
(19, NULL, '2026-04-18', 'passed', NULL, 18, 11, 100, 1);

-- --------------------------------------------------------

--
-- Structure de la table `reunion`
--

DROP TABLE IF EXISTS `reunion`;
CREATE TABLE IF NOT EXISTS `reunion` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `dateHeureDebut` datetime NOT NULL,
  `dateHeureFin` datetime NOT NULL,
  `idSalle` int UNSIGNED DEFAULT NULL,
  `nomOrganisateur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `emailOrganisateur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `participants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `statut` tinyint(1) DEFAULT '1',
  `enLigne` tinyint(1) NOT NULL DEFAULT '0',
  `creeLe` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `zoom_meeting_id` bigint DEFAULT '0',
  `zoom_join_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zoom_start_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zoom_password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reunion_room` (`idSalle`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reunion`
--

INSERT INTO `reunion` (`id`, `titre`, `description`, `dateHeureDebut`, `dateHeureFin`, `idSalle`, `nomOrganisateur`, `emailOrganisateur`, `participants`, `statut`, `enLigne`, `creeLe`, `zoom_meeting_id`, `zoom_join_url`, `zoom_start_url`, `zoom_password`) VALUES
(39, 'reunion', 'ija ya bnin', '2026-06-01 09:00:00', '2026-06-01 11:00:00', NULL, 'imcherif', 'imen.cherif@humania.tn', 'akhelifa099@gmail.com', 1, 1, '2026-04-28 21:00:30', 78968034622, 'https://us04web.zoom.us/j/78968034622?pwd=bFMWap5mqaK2VxlGaDYC8eEL9bF6tz.1', 'https://us04web.zoom.us/s/78968034622?zak=eyJ0eXAiOiJKV1QiLCJzdiI6IjAwMDAwMiIsInptX3NrbSI6InptX28ybSIsImFsZyI6IkhTMjU2In0.eyJpc3MiOiJ3ZWIiLCJjbHQiOjAsIm1udW0iOiI3ODk2ODAzNDYyMiIsImF1ZCI6ImNsaWVudHNtIiwidWlkIjoicVN3WTRPcjhSSWFwXzlGOGZySzc3USIsInppZCI6ImNjNjU0NjdiMDc1YjQxYTY5YzAzZjEwMjFmMzlhYTU5Iiwic2siOiIyMjgwNzQxOTU1OTgwMDk3NzUwIiwic3R5IjoxLCJ3Y2QiOiJ1czA0IiwiZXhwIjoxNzc3NDE3MjMwLCJpYXQiOjE3Nzc0MTAwMzAsImFpZCI6InpmbExpQVl2U3M2UDVRZTdYZXY2UmciLCJjaWQiOiIifQ.TtMhWjjFNVn-gjQf3SRw1oOzIfqLZvaftT_SIx2NMKo', 'ZU3yUq'),
(40, 'aa', 's', '2026-05-03 18:06:00', '2026-05-03 21:06:00', 28, 'AminKhelifa', 'Amine.Khelifa3@humania.tn', 'akhelifa088@gmail.com', 1, 0, '2026-05-02 15:06:31', 0, '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `savedpost`
--

DROP TABLE IF EXISTS `savedpost`;
CREATE TABLE IF NOT EXISTS `savedpost` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `publicationId` int NOT NULL,
  `savedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_saved` (`userId`,`publicationId`),
  KEY `idx_saved_user` (`userId`),
  KEY `idx_saved_pub` (`publicationId`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `savedpost`
--

INSERT INTO `savedpost` (`id`, `userId`, `publicationId`, `savedAt`) VALUES
(17, 11, 69, '2026-03-02 21:37:09'),
(18, 3, 83, '2026-04-06 17:51:13'),
(19, 3, 86, '2026-04-07 09:41:36'),
(20, 12, 87, '2026-04-20 17:41:52');

-- --------------------------------------------------------

--
-- Structure de la table `sessionformation`
--

DROP TABLE IF EXISTS `sessionformation`;
CREATE TABLE IF NOT EXISTS `sessionformation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `lieu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formation_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_session_formation` (`formation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessionformation`
--

INSERT INTO `sessionformation` (`id`, `dateDebut`, `dateFin`, `lieu`, `statut`, `formation_id`) VALUES
(2, '2025-04-10', '2025-04-17', 'salle 102', 'Planned', 2),
(3, '2025-05-01', '2025-05-03', 'salle 103', 'Open', 3),
(4, '2025-06-15', '2025-06-17', 'salle 202', 'Open', 4),
(16, '2025-01-05', '2025-01-12', 'salle 102', 'Active', 2),
(17, '2025-02-01', '2025-02-04', 'salle 203', 'Active', 3),
(18, '2025-03-05', '2025-03-06', 'salle 302', 'Active', 4),
(19, '2025-03-15', '2025-03-21', 'salle 103', 'Active', 5),
(20, '2025-04-01', '2025-04-05', 'salle 100', 'Active', 6),
(21, '2025-04-20', '2025-04-28', 'salle 301', 'Active', 7),
(22, '2025-05-05', '2025-05-06', 'salle 300', 'Active', 8),
(23, '2025-06-01', '2025-06-07', 'salle 200', 'Active', 9),
(24, '2025-06-15', '2025-06-16', 'salle 401', 'Active', 10),
(25, '2025-07-01', '2025-07-04', 'salle 402', 'Active', 11),
(26, '2025-07-20', '2025-07-21', 'salle 404', 'Active', 12),
(27, '2026-02-23', '2026-03-25', 'En ligne', 'Planifiée', 2),
(28, '2026-02-24', '2026-03-26', 'En ligne', 'Planifiée', 6),
(29, '2026-03-01', '2026-03-31', 'En ligne', 'Planifiée', 11),
(30, '2026-03-02', '2026-04-01', 'En ligne', 'Planifiée', 9),
(31, '2026-03-02', '2026-04-01', 'En ligne', 'Planifiée', 4),
(32, '2026-03-10', '2026-03-14', 'En ligne', 'Planifiée', 13),
(33, '2026-03-15', '2026-03-17', 'salle 201', 'Planifiée', 14),
(34, '2026-03-20', '2026-03-21', 'salle 301', 'Planifiée', 15),
(35, '2026-04-01', '2026-04-08', 'En ligne', 'Planifiée', 5),
(36, '2026-04-10', '2026-04-12', 'salle 102', 'Planifiée', 3),
(37, '2026-04-15', '2026-04-16', 'salle 103', 'Planifiée', 4),
(38, '2026-04-20', '2026-04-27', 'En ligne', 'Planifiée', 7),
(39, '2026-04-28', '2026-04-29', 'salle 300', 'Planifiée', 8),
(40, '2026-05-05', '2026-05-11', 'En ligne', 'Planifiée', 9),
(41, '2026-05-12', '2026-05-12', 'En ligne', 'Planifiée', 10),
(42, '2026-05-15', '2026-05-18', 'En ligne', 'Planifiée', 11),
(43, '2026-05-20', '2026-05-21', 'salle 404', 'Planifiée', 12),
(44, '2025-09-01', '2025-09-05', 'salle 101', 'Active', 17),
(45, '2025-09-10', '2025-09-15', 'salle 201', 'Active', 18),
(46, '2025-10-01', '2025-10-05', 'En ligne', 'Active', 19),
(47, '2025-10-10', '2025-10-13', 'salle 302', 'Active', 20),
(48, '2025-11-05', '2025-11-06', 'salle 103', 'Active', 21),
(49, '2025-11-15', '2025-11-17', 'salle 202', 'Active', 22),
(50, '2025-12-01', '2025-12-02', 'En ligne', 'Active', 23),
(51, '2025-12-10', '2025-12-12', 'En ligne', 'Active', 24),
(52, '2026-04-05', '2026-04-09', 'En ligne', 'Planifiée', 17),
(53, '2026-04-10', '2026-04-15', 'En ligne', 'Planifiée', 18),
(54, '2026-04-20', '2026-04-24', 'En ligne', 'Planifiée', 19),
(55, '2026-05-03', '2026-05-06', 'salle 302', 'Planifiée', 20),
(56, '2026-05-10', '2026-05-11', 'salle 103', 'Planifiée', 21),
(57, '2026-05-17', '2026-05-19', 'salle 202', 'Planifiée', 22),
(58, '2026-05-25', '2026-05-26', 'En ligne', 'Planifiée', 23),
(59, '2026-06-01', '2026-06-03', 'En ligne', 'Planifiée', 24),
(60, '2026-06-08', '2026-06-12', 'En ligne', 'Planifiée', 25),
(61, '2026-06-15', '2026-06-17', 'En ligne', 'Planifiée', 26),
(62, '2026-07-01', '2026-07-05', 'salle 100', 'Open', 17),
(63, '2026-07-07', '2026-07-11', 'salle 200', 'Open', 22),
(64, '2026-07-14', '2026-07-15', 'salle 301', 'Open', 21),
(65, '2026-07-20', '2026-07-24', 'En ligne', 'Open', 25);

-- --------------------------------------------------------

--
-- Structure de la table `share`
--

DROP TABLE IF EXISTS `share`;
CREATE TABLE IF NOT EXISTS `share` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sharedAt` datetime NOT NULL,
  `sharedMessage` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shareCount` int NOT NULL,
  `userId` int DEFAULT NULL,
  `publicationId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EF069D5A64B64DCC` (`userId`),
  KEY `IDX_EF069D5A796DA8A` (`publicationId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sous_section_progression`
--

DROP TABLE IF EXISTS `sous_section_progression`;
CREATE TABLE IF NOT EXISTS `sous_section_progression` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employe_id` int NOT NULL,
  `sous_section_id` int NOT NULL,
  `statut` enum('not_started','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'not_started',
  `date_completion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_ss` (`employe_id`,`sous_section_id`),
  KEY `sous_section_id` (`sous_section_id`),
  KEY `idx_ssprog_emp` (`employe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_absence`
--

DROP TABLE IF EXISTS `type_absence`;
CREATE TABLE IF NOT EXISTS `type_absence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `type_absence`
--

INSERT INTO `type_absence` (`id`, `libelle`, `description`) VALUES
(1, 'Absence justifiée', 'Absence avec justificatif'),
(2, 'Absence injustifiée', 'Absence sans justificatif'),
(3, 'Absence médicale', 'Absence pour raison médicale');

-- --------------------------------------------------------

--
-- Structure de la table `type_conge`
--

DROP TABLE IF EXISTS `type_conge`;
CREATE TABLE IF NOT EXISTS `type_conge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `type_conge`
--

INSERT INTO `type_conge` (`id`, `libelle`, `description`) VALUES
(1, 'Congé modifié', 'Description modifiée'),
(2, 'Congé maladie', 'Congé pour raison médicale'),
(3, 'Congé maternité', 'Congé de maternité'),
(5, 'Congé exceptionnel', 'Pour événement familial'),
(8, 'Congé paternité', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `userprofile`
--

DROP TABLE IF EXISTS `userprofile`;
CREATE TABLE IF NOT EXISTS `userprofile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userId` int NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `avatarUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coverUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jobTitle` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `followersCount` int DEFAULT '0',
  `followingCount` int DEFAULT '0',
  `postsCount` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `userprofile`
--

INSERT INTO `userprofile` (`id`, `userId`, `bio`, `avatarUrl`, `coverUrl`, `location`, `website`, `company`, `jobTitle`, `followersCount`, `followingCount`, `postsCount`) VALUES
(6, 6, '', 'file:///C:/Users/Imen/.humania/avatars/4gUce0XfUdc.jpg', NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(7, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(8, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(9, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0),
(10, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `avatarUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isActive` tinyint(1) DEFAULT '1',
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EMPLOYEE',
  `lastSeen` datetime DEFAULT NULL,
  `isOnline` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `firstName`, `lastName`, `bio`, `avatarUrl`, `createdAt`, `updatedAt`, `isActive`, `role`, `lastSeen`, `isOnline`) VALUES
(3, 'Amine Khelifa', 'Amine Khelifa3@humania.com', '55977266575c007c6ee6bc137bad80cc9deacdb6c85ccc21ab4bd5ab683c99f6', 'Amine', 'Khelifa', 'Manager IT', NULL, '2026-03-01 13:26:20', '2026-03-01 13:26:20', 1, 'MANAGER', NULL, 0),
(5, 'ohaaha', 'ohaaha5@humania.com', '481c02e69e4a5210ea5fe61277b6f4b47c9c074a418ee3efe5a2f8225c58b87d', 'Oha', 'Aha', 'Développeur Junior', NULL, '2026-03-01 14:03:22', '2026-03-01 14:03:22', 1, 'EMPLOYEE', NULL, 0),
(6, 'elwessnossaf', 'elwessnossaf6@humania.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Elwess', 'Nossaf', 'Data Engineer', NULL, '2026-03-01 14:22:14', '2026-03-01 14:22:14', 1, 'EMPLOYEE', NULL, 0),
(7, 'imen', 'aminezaaraoui95@gmail.com', 'e81162805228457fc6bd6028654bc6c83af51b00a453bea3285738dd4e6f8ea1', 'Imen', 'Zaaraoui', 'Stagiaire RH', NULL, '2026-03-01 14:30:59', '2026-03-01 14:30:59', 0, 'EMPLOYEE', NULL, 0),
(8, 'agharbi', 'amine.gharbi@humania.tn', '9b8769a4a742959a2d0298c36fb70623f2dfacda8436237df08d8dfd5b37374c', 'Amine', 'Gharbi', 'Développeur Full Stack', NULL, '2026-03-01 23:15:30', '2026-03-01 23:15:30', 1, 'EMPLOYEE', NULL, 0),
(9, 'smejri', 'sara.mejri@humania.tn', '9b8769a4a742959a2d0298c36fb70623f2dfacda8436237df08d8dfd5b37374c', 'Sara', 'Mejri', 'Business Analyst Junior', NULL, '2026-03-01 23:15:30', '2026-03-01 23:15:30', 1, 'EMPLOYEE', NULL, 0),
(10, 'yhaddad', 'youssef.haddad@humania.tn', '9b8769a4a742959a2d0298c36fb70623f2dfacda8436237df08d8dfd5b37374c', 'Youssef', 'Haddad', 'Manager Projets', NULL, '2026-03-01 23:15:30', '2026-03-01 23:15:30', 1, 'MANAGER', NULL, 0),
(11, 'imcherif', 'imen.cherif@humania.tn', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Imen', 'Cherif', 'Manager RH & Data', NULL, '2026-03-01 23:15:30', '2026-03-01 23:15:30', 1, 'MANAGER', '2026-03-02 21:27:42', 1),
(12, 'admin', 'admin@humania.com', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'System', 'Admin', 'Administrateur Système', '', '2026-03-01 23:20:54', '2026-03-01 23:20:54', 1, 'ADMIN', NULL, 0),
(13, 'kbenali', 'karim.benali@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Karim', 'Ben Ali', 'Développeur Backend', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(14, 'ytrabelsi', 'yasmine.trabelsi@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Yasmine', 'Trabelsi', 'Data Analyst', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(15, 'omansouri', 'omar.mansouri@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Omar', 'Mansouri', 'Chef de projet', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(16, 'nelamrani', 'nadia.elamrani@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Nadia', 'El Amrani', 'Ingénieur QA', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(17, 'shaddad', 'sami.haddad@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Sami', 'Haddad', 'Développeur Frontend', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(18, 'rkhelifi', 'rania.khelifi@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Rania', 'Khelifi', 'DevOps Engineer', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(19, 'halami', 'hassan.alami@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Hassan', 'Alami', 'Responsable RH', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(20, 'lbenyoussef', 'leila.benyoussef@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Leila', 'Ben Youssef', 'Product Owner', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(21, 'azahraoui', 'amine.zahraoui@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Amine', 'Zahraoui', 'Ingénieur Sécurité', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(22, 'sidrissi', 'salma.idrissi@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Salma', 'Idrissi', 'Business Analyst', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(23, 'ychikhi', 'youssef.chikhi@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Youssef', 'Chikhi', 'Scrum Master', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(24, 'fnoor', 'fatima.noor@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Fatima', 'Noor', 'Support Technique', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(25, 'aboussaid', 'adel.boussaid@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Adel', 'Boussaid', 'Stagiaire', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(26, 'mchaoui', 'meryem.chaoui@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Meryem', 'Chaoui', 'Stagiaire', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(27, 'bnajjar', 'bilal.najjar@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Bilal', 'Najjar', 'Stagiaire', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(28, 'smahfoudh', 'sara.mahfoudh@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Sara', 'Mahfoudh', 'Consultant', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0),
(29, 'tbensalah', 'tarek.bensalah@humania.tn', '$2a$10$defaultHashHumania2025xxxxx', 'Tarek', 'Ben Salah', 'Chef de projet', NULL, '2026-03-02 02:27:18', '2026-03-02 02:27:18', 1, 'EMPLOYEE', NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `numtel` varchar(20) DEFAULT NULL,
  `pdp` varchar(255) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('ADMIN','EMPLOYE','MANAGER','RH','FORMATEUR') DEFAULT NULL,
  `statut` enum('Archive','Actif','Inactif','bloque','suspendu') NOT NULL DEFAULT 'Actif',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `donnees_faciales` blob,
  `mfa_enabled` tinyint(1) DEFAULT '0',
  `mfa_secret` varchar(255) DEFAULT NULL,
  `posteActuel` varchar(150) DEFAULT NULL,
  `manager_id` int DEFAULT NULL,
  `matricule` varchar(20) DEFAULT NULL,
  `dateEmbauche` date DEFAULT NULL,
  `departement` varchar(100) DEFAULT NULL,
  `fullName` varchar(255) GENERATED ALWAYS AS (concat(ifnull(`prenom`,_utf8mb4''),_utf8mb4' ',ifnull(`nom`,_utf8mb4''))) STORED,
  `is_online` tinyint(1) DEFAULT '0',
  `last_seen` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_manager` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `username`, `numtel`, `pdp`, `mot_de_passe`, `role`, `statut`, `date_creation`, `donnees_faciales`, `mfa_enabled`, `mfa_secret`, `posteActuel`, `manager_id`, `matricule`, `dateEmbauche`, `departement`, `is_online`, `last_seen`) VALUES
(3, 'Khelifa', 'Amine', 'Amine.Khelifa3@humania.tn', 'AminKhelifa', '00110012', '', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'RH', 'Actif', '2026-03-01 13:26:20', NULL, 0, NULL, 'Manager IT', NULL, 'MGR001', '2019-06-01', 'IT', 1, '2026-05-02 14:34:23'),
(6, 'Oueslati', 'Insaf', 'insafoueslati@humania.tn', 'InOueslati', '', '', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'EMPLOYE', 'Actif', '2026-03-01 14:22:14', NULL, 0, NULL, 'Data Engineer', 11, 'EMP019', '2025-02-01', 'Data', 0, '2026-03-03 07:52:05'),
(7, 'Zaaraoui', 'amineee', 'aminezaaraoui95@gmail.tn', 'aminezaa', NULL, NULL, 'e81162805228457fc6bd6028654bc6c83af51b00a453bea3285738dd4e6f8ea1', 'EMPLOYE', 'Actif', '2026-03-01 14:30:59', NULL, 0, NULL, 'Stagiaire RH', 11, 'EMP020', '2025-03-01', 'HR', 0, NULL),
(8, 'Gharbi', 'Amine', 'amine.gharbi@humania.tn', 'agharbi', '99887766', NULL, '9b8769a4a742959a2d0298c36fb70623f2dfacda8436237df08d8dfd5b37374c', 'EMPLOYE', 'Actif', '2026-03-01 23:15:30', NULL, 0, NULL, 'Développeur Full Stack', 3, 'EMP021', '2024-09-01', 'IT', 0, NULL),
(9, 'Mejri', 'Sara', 'sara.mejri@humania.tn', 'smejri', '44556677', NULL, '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'EMPLOYE', 'Actif', '2026-03-01 23:15:30', NULL, 0, NULL, 'Business Analyst Junior', 10, 'EMP022', '2024-10-15', 'Business', 1, '2026-04-25 14:33:34'),
(10, 'Haddad', 'Youssef', 'youssef.haddad@humania.tn', 'yhaddad', '33445566', NULL, '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'MANAGER', 'Actif', '2026-03-01 23:15:30', NULL, 0, NULL, 'Manager Projets', NULL, 'MGR002', '2018-03-15', 'Management', 0, '2026-03-03 01:02:51'),
(11, 'Cherif', 'Imen', 'imen.cherif@humania.tn', 'imcherif', '53123456', 'Screenshot-2024-12-12-192903-69f60061c1a4d.png', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'ADMIN', 'Actif', '2026-03-01 23:15:30', NULL, 0, NULL, 'Manager RH & Data', NULL, 'MGR003', '2019-01-10', 'HR', 1, '2026-05-04 12:20:09'),
(12, 'Admin', 'System', 'admin@humania.tn', 'admin', '00000000', '', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'ADMIN', 'Actif', '2026-03-01 23:20:54', 0x0000010000000001000000052bb03cbc39bc90553b68d9a139b4678a39a0dbe639fb55ba3b8349f7398fbc5e3a182005399f99d6396f72e439addda139c8effa3abb64dc3b72cdb139b8e6493a3eed2639c4f4033a295f233943746f3a25312a39bd291839b2a2b13960f21c39bf02683a9582e53b4b6dc53bb27d9d3b3a25de3a2343423a3642e239c0c7433c5407ad3c27d0533abb8cbd3aa0c56e3aab598b3adedf723ac1b77c3aac0a793af6c8483b376e6c3c1fe3fb3b29c6163c2a4ca53c91af0b3b2682dc3b47114e3b2c5d263cbf247c3cca0a763bc7ccbb3bfdbb223be90fd93bcbf4653d19e17f3d56dfed3c1f29bf3c0db4003d9423293c4db6ab3c7ede6e3c7a9cc33dc3d7d93c7590573d0b8e4c3d2818123c91ce9d3d4334023c4f3f493cc4c5113c6318323c547d1e3cb9d2403c1804fa3c821b063c1b5c1e3c6f94663beb99cc3c0e66bb3bd70cd73c3589e33bbac7fe3c3d8d413c357c883c4c9d5b3bcc5bf23c0c76023c19ffa13ba913a33c3d5d283c7c35723c0b0e4d3bee1b453c6123d53beb0a8a3c3cbced3c9940f93c2e37963c04c97d3c447fa73c3735023c9eff863c84d7b63c3db43d3c84cd7b3cc5c7e83cbd9c203cd715903cfeea5c3d0501a03d08072d3d0ef49b3cdc5f253ca854893c98e28b3c9536323c794dd93c5872393c4ad9173c644f163c2acc963c1f34e83c0d597b3c2c789a3c0afec03c1a5f343c146ce53bd6610f3c1748a03bfdb6673c048f553c0636623bd039fc3c20e94e3bff25cd3bc8ccd33c0220993c078bd43c0b78b93c092bc63c1188873c43b9a93c4cf5c03c9d828b3cba6dc43d48d24c3d80e7903d93e18f3dae1b353d9a12f83dae82b83d9b3afa3d8640683d6912c53d5a31ad3d1e4f3f3d0297e03cb7376e3ca826ab3c590d3f3c55d8bd3c1e74323c219f123c0f61be3c09ecf03bd1c30d3bcd8c973ba63c423ba692373ba88d2c3baec4243b93ba7c3b9870cb3b841c3e3b83c5cc3b8298c63b61e4373ba843ba3b9203513bb875433b98e1bd3b9a86fa3b8d04363b7a2b6d3ba3c00c3b88d81f3bab067e3b8d852e3bb4bb9d3ba7b3f23bc8fdf43bd4a5fc3be45d113c0e1b023c5018573c91da233cc800393d02a7fb3d141e0c3d234d073d2b73be3d337b983d6506b63d6b92033d7865d03d74474e3d75483b3d8062c83d87f2063d86c9343d8441363d81432e3d883cfc3d81a0983d906af43d9d179c3d9108553d89eaf43d8ac68f3d8e97933d8fb5443dad7aab3db360193d9c1d903d9104853d75756f3d881a433db9a8933d621afd3d31c7c53d2a1a5f3d2a74443d57cbac3e6f97963f1cb4c83cf47ba03cf9bf9f3cd6d1953cb2a2af3cb9dd0e3cbdf6763cce85b13cccf60c3cae85f53cae0edd3cb347613cca913f3cb35edc3cadaa083cc4d1ee3cb38cd33cbeb6c73cebd2463cf2aed23f800000, 0, NULL, 'Administrateur Système', NULL, 'ADM001', '2020-01-01', 'IT', 1, '2026-04-25 15:56:10'),
(13, 'Ben Ali', 'Karim', 'karim.benali@humania.tn', 'kbenali', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Développeur Backend', 3, 'EMP001', '2021-03-15', 'IT', 0, NULL),
(14, 'Trabelsi', 'Yasmine', 'yasmine.trabelsi@humania.tn', 'ytrabelsi', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Data Analyst', 11, 'EMP002', '2022-07-01', 'Data', 0, NULL),
(15, 'Mansouri', 'Omar', 'omar.mansouri@humania.tn', 'omansouri', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Chef de projet', 10, 'EMP003', '2020-01-10', 'Management', 0, NULL),
(16, 'El Amrani', 'Nadia', 'nadia.elamrani@humania.tn', 'nelamrani', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Ingénieur QA', 3, 'EMP004', '2023-02-20', 'IT', 0, NULL),
(17, 'Haddad', 'Sami', 'sami.haddad@humania.tn', 'shaddad', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Développeur Frontend', 3, 'EMP005', '2022-05-10', 'IT', 0, NULL),
(18, 'Khelifi', 'Rania', 'rania.khelifi@humania.tn', 'rkhelifi', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'DevOps Engineer', 3, 'EMP006', '2021-11-18', 'IT', 0, NULL),
(19, 'Alami', 'Hassan', 'hassan.alami@humania.tn', 'halami', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Responsable RH', 11, 'EMP007', '2019-09-01', 'HR', 0, NULL),
(20, 'Ben Youssef', 'Leila', 'leila.benyoussef@humania.tn', 'lbenyoussef', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Product Owner', 10, 'EMP008', '2020-06-12', 'Management', 0, NULL),
(21, 'Zahraoui', 'Amine', 'amine.zahraoui@humania.tn', 'azahraoui', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Ingénieur Sécurité', 3, 'EMP009', '2023-01-05', 'Cybersecurity', 0, NULL),
(22, 'Idrissi', 'Salma', 'salma.idrissi@humania.tn', 'sidrissi', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Business Analyst', 10, 'EMP010', '2021-04-22', 'Business', 0, NULL),
(23, 'Chikhi', 'Youssef', 'youssef.chikhi@humania.tn', 'ychikhi', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Scrum Master', 10, 'EMP011', '2020-08-30', 'Management', 0, NULL),
(24, 'Noor', 'Fatima', 'fatima.noor@humania.tn', 'fnoor', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Support Technique', 3, 'EMP012', '2024-02-15', 'IT', 0, NULL),
(25, 'Boussaid', 'Adel', 'adel.boussaid@humania.tn', 'aboussaid', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Stagiaire', 3, 'EMP013', '2022-09-10', 'IT', 0, NULL),
(26, 'Chaoui', 'Meryem', 'meryem.chaoui@humania.tn', 'mchaoui', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Stagiaire', 11, 'EMP014', '2023-03-18', 'Data', 0, NULL),
(27, 'Najjar', 'Bilal', 'bilal.najjar@humania.tn', 'bnajjar', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Stagiaire', 3, 'EMP015', '2021-12-01', 'Cybersecurity', 0, NULL),
(28, 'Mahfoudh', 'Sara', 'sara.mahfoudh@humania.tn', 'smahfoudh', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Consultant', 10, 'EMP016', '2024-01-08', 'Business', 0, NULL),
(29, 'Ben Salah', 'Tarek', 'tarek.bensalah@humania.tn', 'tbensalah', NULL, NULL, '$2a$10$defaultHashHumania2025xxxxx', 'EMPLOYE', 'Actif', '2026-03-02 02:27:18', NULL, 0, NULL, 'Chef de projet', 10, 'EMP017', '2020-11-11', 'Management', 0, NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `actionpdi`
--
ALTER TABLE `actionpdi`
  ADD CONSTRAINT `FK_653CF3185200282E` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_653CF31897CD944` FOREIGN KEY (`pdi_id`) REFERENCES `pdi` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`publicationId`) REFERENCES `publication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `competence`
--
ALTER TABLE `competence`
  ADD CONSTRAINT `competence_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categoriecompetence` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `competenceemploye`
--
ALTER TABLE `competenceemploye`
  ADD CONSTRAINT `fk_ce_competence` FOREIGN KEY (`competence_id`) REFERENCES `competence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ce_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `evaluationformation`
--
ALTER TABLE `evaluationformation`
  ADD CONSTRAINT `evaluationformation_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessionformation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `evaluation_question`
--
ALTER TABLE `evaluation_question`
  ADD CONSTRAINT `evaluation_question_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluationformation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `exercice_soumission`
--
ALTER TABLE `exercice_soumission`
  ADD CONSTRAINT `exercice_soumission_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exercice_soumission_ibfk_3` FOREIGN KEY (`sous_section_id`) REFERENCES `module_sous_section` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exsoum_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`followerId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`followingId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formation`
--
ALTER TABLE `formation`
  ADD CONSTRAINT `formation_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categorieformation` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `groupmember`
--
ALTER TABLE `groupmember`
  ADD CONSTRAINT `groupmember_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupmember_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `fk_groups_creator` FOREIGN KEY (`createdById`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscriptionformation`
--
ALTER TABLE `inscriptionformation`
  ADD CONSTRAINT `fk_insc_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscriptionformation_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessionformation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `mention`
--
ALTER TABLE `mention`
  ADD CONSTRAINT `mention_ibfk_1` FOREIGN KEY (`mentionedUserId`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mention_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mention_ibfk_3` FOREIGN KEY (`publicationId`) REFERENCES `publication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mention_ibfk_4` FOREIGN KEY (`commentaireId`) REFERENCES `commentaire` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `module_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `module_highlights`
--
ALTER TABLE `module_highlights`
  ADD CONSTRAINT `module_highlights_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `module_notes`
--
ALTER TABLE `module_notes`
  ADD CONSTRAINT `module_notes_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `module_progression`
--
ALTER TABLE `module_progression`
  ADD CONSTRAINT `fk_modprog_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `module_progression_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `module_section`
--
ALTER TABLE `module_section`
  ADD CONSTRAINT `module_section_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `module_sous_section`
--
ALTER TABLE `module_sous_section`
  ADD CONSTRAINT `module_sous_section_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `module_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`relatedUserId`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notification_ibfk_3` FOREIGN KEY (`relatedPublicationId`) REFERENCES `publication` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notification_ibfk_4` FOREIGN KEY (`relatedCommentaireId`) REFERENCES `commentaire` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `participation_evenement`
--
ALTER TABLE `participation_evenement`
  ADD CONSTRAINT `fk_participation_evenement` FOREIGN KEY (`idEvenement`) REFERENCES `evenement` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pdi`
--
ALTER TABLE `pdi`
  ADD CONSTRAINT `fk_pdi_employe` FOREIGN KEY (`employe_id`) REFERENCES `employe_competence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pdi_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `poll`
--
ALTER TABLE `poll`
  ADD CONSTRAINT `poll_ibfk_1` FOREIGN KEY (`publicationId`) REFERENCES `publication` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `fk_publication_group` FOREIGN KEY (`groupId`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `publication_ibfk_1` FOREIGN KEY (`authorId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `publication_ibfk_2` FOREIGN KEY (`sharedFromId`) REFERENCES `publication` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD CONSTRAINT `quiz_question_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `module` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reaction`
--
ALTER TABLE `reaction`
  ADD CONSTRAINT `reaction_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reaction_ibfk_2` FOREIGN KEY (`publicationId`) REFERENCES `publication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reaction_ibfk_3` FOREIGN KEY (`commentaireId`) REFERENCES `commentaire` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservation_espaces`
--
ALTER TABLE `reservation_espaces`
  ADD CONSTRAINT `fk_reservation_space` FOREIGN KEY (`idEspace`) REFERENCES `espaces` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `resultatevaluation`
--
ALTER TABLE `resultatevaluation`
  ADD CONSTRAINT `fk_resultat_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resultatEvaluation_employe` FOREIGN KEY (`employe_id`) REFERENCES `employe_competence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `resultatevaluation_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluationformation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reunion`
--
ALTER TABLE `reunion`
  ADD CONSTRAINT `fk_reunion_room` FOREIGN KEY (`idSalle`) REFERENCES `espaces` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `savedpost`
--
ALTER TABLE `savedpost`
  ADD CONSTRAINT `savedpost_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `savedpost_ibfk_2` FOREIGN KEY (`publicationId`) REFERENCES `publication` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sessionformation`
--
ALTER TABLE `sessionformation`
  ADD CONSTRAINT `sessionformation_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `sous_section_progression`
--
ALTER TABLE `sous_section_progression`
  ADD CONSTRAINT `fk_ssprog_utilisateur` FOREIGN KEY (`employe_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sous_section_progression_ibfk_1` FOREIGN KEY (`employe_id`) REFERENCES `employe_competence` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sous_section_progression_ibfk_2` FOREIGN KEY (`sous_section_id`) REFERENCES `module_sous_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `userprofile`
--
ALTER TABLE `userprofile`
  ADD CONSTRAINT `userprofile_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_manager` FOREIGN KEY (`manager_id`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
