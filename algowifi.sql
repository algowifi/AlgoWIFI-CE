-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Lug 28, 2022 alle 17:35
-- Versione del server: 5.7.38-0ubuntu0.18.04.1
-- Versione PHP: 7.2.34-32+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `algowifi`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `Campaign`
--

CREATE TABLE `Campaign` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` varchar(5000) NOT NULL,
  `imageUrl` varchar(500) NOT NULL,
  `landingUrl` varchar(500) NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT '0',
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `Hotspot`
--

CREATE TABLE `Hotspot` (
  `id` int(11) NOT NULL,
  `location` varchar(300) NOT NULL,
  `note` varchar(5000) NOT NULL,
  `validator` varchar(300) NOT NULL DEFAULT '',
  `networkName` varchar(300) NOT NULL,
  `nft` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `Hotspot_Campaign`
--

CREATE TABLE `Hotspot_Campaign` (
  `id` int(11) NOT NULL,
  `hotspotId` int(11) NOT NULL,
  `campaignId` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `note` varchar(2000) NOT NULL,
  `password` varchar(100) NOT NULL,
  `algorandAddress` varchar(200) NOT NULL,
  `address` varchar(200) NOT NULL,
  `nft` varchar(200) NOT NULL,
  `isEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `isLocation` tinyint(1) NOT NULL DEFAULT '0',
  `isPublisher` tinyint(1) NOT NULL DEFAULT '0',
  `isHotspotter` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Campaign`
--
ALTER TABLE `Campaign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Rimozione a cascata delle campagne se rimuovo un utente` (`userId`);

--
-- Indici per le tabelle `Hotspot`
--
ALTER TABLE `Hotspot`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `Hotspot_Campaign`
--
ALTER TABLE `Hotspot_Campaign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Rimozione a cascata se rimuovo una campagna` (`campaignId`);

--
-- Indici per le tabelle `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `algorandAddress` (`algorandAddress`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Campaign`
--
ALTER TABLE `Campaign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `Hotspot`
--
ALTER TABLE `Hotspot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `Hotspot_Campaign`
--
ALTER TABLE `Hotspot_Campaign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Campaign`
--
ALTER TABLE `Campaign`
  ADD CONSTRAINT `Rimozione a cascata delle campagne se rimuovo un utente` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Limiti per la tabella `Hotspot_Campaign`
--
ALTER TABLE `Hotspot_Campaign`
  ADD CONSTRAINT `Rimozione a cascata se rimuovo una campagna` FOREIGN KEY (`campaignId`) REFERENCES `Campaign` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
