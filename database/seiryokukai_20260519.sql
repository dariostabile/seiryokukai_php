-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Creato il: Mag 19, 2026 alle 15:37
-- Versione del server: 8.0.44
-- Versione PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seiryokukai`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `corsi`
--

CREATE TABLE `corsi` (
  `idcorso` int NOT NULL,
  `idsede` int NOT NULL,
  `iddisciplina` int NOT NULL,
  `idutente` int NOT NULL,
  `attivo` tinyint DEFAULT '1',
  `nome_corso` varchar(45) DEFAULT NULL,
  `descrizione_corso` longtext,
  `data_inizio_corso` date DEFAULT NULL,
  `data_fine_corso` date DEFAULT NULL,
  `quota_mensile_corso` decimal(6,2) DEFAULT NULL,
  `orari` varchar(255) DEFAULT NULL,
  `lun_inizio` time DEFAULT NULL,
  `lun_fine` time DEFAULT NULL,
  `mar_inizio` time DEFAULT NULL,
  `mar_fine` time DEFAULT NULL,
  `mer_inizio` time DEFAULT NULL,
  `mer_fine` time DEFAULT NULL,
  `gio_inizio` time DEFAULT NULL,
  `gio_fine` time DEFAULT NULL,
  `ven_inizio` time DEFAULT NULL,
  `ven_fine` time DEFAULT NULL,
  `sab_inizio` time DEFAULT NULL,
  `sab_fine` time DEFAULT NULL,
  `dom_inizio` time DEFAULT NULL,
  `dom_fine` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `corsi`
--

INSERT INTO `corsi` (`idcorso`, `idsede`, `iddisciplina`, `idutente`, `attivo`, `nome_corso`, `descrizione_corso`, `data_inizio_corso`, `data_fine_corso`, `quota_mensile_corso`, `orari`, `lun_inizio`, `lun_fine`, `mar_inizio`, `mar_fine`, `mer_inizio`, `mer_fine`, `gio_inizio`, `gio_fine`, `ven_inizio`, `ven_fine`, `sab_inizio`, `sab_fine`, `dom_inizio`, `dom_fine`) VALUES
(1, 1, 1, 2, 1, 'Karate', 'Corso di Karate adulti', '2025-09-15', '2026-06-30', 40.00, NULL, NULL, NULL, '19:00:00', '20:30:00', NULL, NULL, '19:00:00', '20:30:00', '19:00:00', '20:30:00', NULL, NULL, NULL, NULL);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `corsi`
--
ALTER TABLE `corsi`
  ADD PRIMARY KEY (`idcorso`),
  ADD KEY `fk_corsi_utenti1_idx` (`idutente`),
  ADD KEY `fk_corsi_discipline1_idx` (`iddisciplina`),
  ADD KEY `fk_corsi_sedi1_idx` (`idsede`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `corsi`
--
ALTER TABLE `corsi`
  MODIFY `idcorso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `corsi`
--
ALTER TABLE `corsi`
  ADD CONSTRAINT `fk_corsi_discipline1` FOREIGN KEY (`iddisciplina`) REFERENCES `discipline` (`iddisciplina`),
  ADD CONSTRAINT `fk_corsi_sedi1` FOREIGN KEY (`idsede`) REFERENCES `sedi` (`idsede`),
  ADD CONSTRAINT `fk_corsi_utenti1` FOREIGN KEY (`idutente`) REFERENCES `utenti` (`idutente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
