-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 25 avr. 2026 à 19:36
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `blue_horizon_hotel`
--

-- --------------------------------------------------------

--
-- Structure de la table `chambre`
--

CREATE TABLE `chambre` (
  `id_chambre` int(11) NOT NULL,
  `nom_chambre` varchar(100) NOT NULL,
  `prix_chambre` decimal(7,2) NOT NULL,
  `type_chambre` varchar(100) NOT NULL,
  `capacite_chambre` int(11) NOT NULL,
  `description_chambre` text DEFAULT NULL,
  `disponibilite_chambre` tinyint(1) NOT NULL DEFAULT 1,
  `image_chambre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chambre`
--

INSERT INTO `chambre` (`id_chambre`, `nom_chambre`, `prix_chambre`, `type_chambre`, `capacite_chambre`, `description_chambre`, `disponibilite_chambre`, `image_chambre`) VALUES
(1, 'Chambre deluxe', 459.00, 'Chambre Simple', 1, 'Chambre cosy avec vue sur la ville.', 1, 'images/simple.jpg'),
(2, 'Suite Balcon', 579.00, 'Suite Double', 2, 'Grande chambre avec lit double et balcon.', 1, 'images/balcon.jpg'),
(3, 'Suite Junior', 1089.00, 'Suite Junior', 2, 'Suite élégante avec coin salon.', 1, 'images/junior.jpg'),
(4, 'Suite Présidentielle', 1590.00, 'Suite Présidentielle', 4, 'Notre suite la plus luxueuse, vue panoramique.', 1, 'images/presidentielle.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id_reservation` int(11) NOT NULL,
  `id_chambre` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `nb_personne` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id_reservation`, `id_chambre`, `nom`, `prenom`, `email`, `date_arrivee`, `date_depart`, `nb_personne`, `created_at`) VALUES
(17, 1, 'odent', 'antonin', 'antoninodent@outlook.fr', '2026-04-20', '2026-04-26', 2, '2026-04-20 19:48:40'),
(18, 3, 'odent', 'antonin', 'antoninodent@outlook.fr', '2026-11-03', '2026-11-11', 2, '2026-04-24 18:55:37'),
(19, 4, 'ode', 'antonin', 'antoninodent@outlook.fr', '2026-11-12', '2026-11-23', 2, '2026-04-24 19:12:43'),
(20, 4, 'odent', 'antonin', 'antoninodent@outlook.fr', '2026-11-23', '2026-11-27', 2, '2026-04-24 19:43:11'),
(21, 1, 'odent', 'antoin', 'antoninodent@gmail.com', '2026-06-25', '2026-07-30', 2, '2026-04-24 19:48:39'),
(22, 2, 'ode', 'antoin', 'antoninodent@gmail.com', '2026-07-25', '2026-08-30', 2, '2026-04-25 17:22:27');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chambre`
--
ALTER TABLE `chambre`
  ADD PRIMARY KEY (`id_chambre`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `id_chambre` (`id_chambre`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chambre`
--
ALTER TABLE `chambre`
  MODIFY `id_chambre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id_reservation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_chambre`) REFERENCES `chambre` (`id_chambre`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

