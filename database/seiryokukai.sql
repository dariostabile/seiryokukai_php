-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Creato il: Mag 13, 2026 alle 16:25
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
-- Struttura della tabella `applicazioni`
--

CREATE TABLE `applicazioni` (
  `idapplicazione` int NOT NULL,
  `idgruppo_applicazioni` int NOT NULL,
  `applicazione` varchar(45) DEFAULT NULL,
  `url_applicazione` varchar(255) DEFAULT NULL,
  `descrizione_applicazione` varchar(255) DEFAULT NULL,
  `ordine_applicazione` int DEFAULT NULL,
  `icona_applicazione` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `applicazioni`
--

INSERT INTO `applicazioni` (`idapplicazione`, `idgruppo_applicazioni`, `applicazione`, `url_applicazione`, `descrizione_applicazione`, `ordine_applicazione`, `icona_applicazione`) VALUES
(1, 1, 'utenti', 'utenti', 'gestione utenti', 10, 'fas fa-users'),
(2, 3, 'atleti', 'atleti', 'gestione atleti', 10, 'fa-solid fa-people-group'),
(3, 1, 'sedi', 'sedi', 'gestione sedi', 10, 'fa-solid fa-house-flag'),
(4, 1, 'tipi documento', 'tipi_documento', 'gestione tipi documento', 10, 'fa-solid fa-id-card-clip');

-- --------------------------------------------------------

--
-- Struttura della tabella `applicazioni_atleta`
--

CREATE TABLE `applicazioni_atleta` (
  `idapplicazione` int NOT NULL,
  `idgruppo_applicazioni` int NOT NULL,
  `applicazione` varchar(45) DEFAULT NULL,
  `url_applicazione` varchar(255) DEFAULT NULL,
  `descrizione_applicazione` varchar(255) DEFAULT NULL,
  `ordine_applicazione` int DEFAULT NULL,
  `icona_applicazione` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `atleti`
--

CREATE TABLE `atleti` (
  `idatleta` int NOT NULL,
  `titolo` varchar(45) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `cognome` varchar(255) DEFAULT NULL,
  `codice_fiscale` varchar(16) DEFAULT NULL,
  `data_nascita` date DEFAULT NULL,
  `citta_nascita` varchar(255) DEFAULT NULL,
  `provincia_nascita` varchar(45) DEFAULT NULL,
  `stato_nascita` varchar(255) DEFAULT NULL,
  `indirizzo_residenza` varchar(255) DEFAULT NULL,
  `citta_residenza` varchar(255) DEFAULT NULL,
  `provincia_residenza` varchar(45) DEFAULT NULL,
  `cap_residenza` varchar(45) DEFAULT NULL,
  `stato_residenza` varchar(255) DEFAULT NULL,
  `sesso` varchar(1) DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `telefono_1` varchar(255) DEFAULT NULL,
  `telefono_2` varchar(255) DEFAULT NULL,
  `email_1` varchar(255) DEFAULT NULL,
  `email_2` varchar(255) DEFAULT NULL,
  `pec` varchar(255) DEFAULT NULL,
  `piva` varchar(45) DEFAULT NULL,
  `codice_univoco_fatturazione` varchar(7) DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT '1',
  `cancellato` tinyint(1) DEFAULT '0',
  `data_creazione_account` datetime DEFAULT NULL,
  `data_scadenza_account` datetime DEFAULT NULL,
  `data_cambio_password` datetime DEFAULT NULL,
  `immagine_atleta` varchar(255) DEFAULT NULL,
  `note_atleta` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `atleti_has_applicazioni_atleta`
--

CREATE TABLE `atleti_has_applicazioni_atleta` (
  `idatleta` int NOT NULL,
  `idapplicazione` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `comuni_italiani`
--

CREATE TABLE `comuni_italiani` (
  `nazione` varchar(45) DEFAULT NULL,
  `comune` varchar(45) NOT NULL DEFAULT '',
  `regione` varchar(45) DEFAULT NULL,
  `provincia` varchar(2) NOT NULL DEFAULT '',
  `cap` varchar(5) DEFAULT NULL,
  `prefisso` varchar(10) DEFAULT NULL,
  `codicenazionale` varchar(4) DEFAULT NULL,
  `codiceregionale` varchar(5) DEFAULT NULL,
  `codiceistat` varchar(8) DEFAULT NULL,
  `codiceusl` varchar(5) DEFAULT NULL,
  `codasp` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `corsi`
--

CREATE TABLE `corsi` (
  `idcorso` int NOT NULL,
  `idsede` int NOT NULL,
  `iddisciplina` int NOT NULL,
  `idutente` int NOT NULL,
  `nome_corso` varchar(45) DEFAULT NULL,
  `descrizione_corso` longtext,
  `data_inizio_corso` date DEFAULT NULL,
  `data_fine_corso` varchar(45) DEFAULT NULL,
  `quota_mensile_corso` decimal(6,2) DEFAULT NULL,
  `orari` varchar(255) DEFAULT NULL,
  `lun` tinyint DEFAULT '0',
  `mar` tinyint DEFAULT '0',
  `merc` tinyint DEFAULT '0',
  `giov` tinyint DEFAULT '0',
  `ven` tinyint DEFAULT '0',
  `sab` tinyint DEFAULT '0',
  `dom` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `discipline`
--

CREATE TABLE `discipline` (
  `iddisciplina` int NOT NULL,
  `disciplina` varchar(255) DEFAULT NULL,
  `note_disciplina` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `documenti`
--

CREATE TABLE `documenti` (
  `iddocumento` int NOT NULL,
  `idatleta` int NOT NULL,
  `idtipo_documento` int NOT NULL,
  `descrizione_documento` varchar(45) DEFAULT NULL,
  `data_documento` date DEFAULT NULL,
  `data_scadenza` date DEFAULT NULL,
  `url_documento` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi_applicazioni`
--

CREATE TABLE `gruppi_applicazioni` (
  `idgruppo_applicazioni` int NOT NULL,
  `gruppo_applicazioni` varchar(45) NOT NULL,
  `icona_gruppo` varchar(45) DEFAULT NULL,
  `ordine_gruppo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `gruppi_applicazioni`
--

INSERT INTO `gruppi_applicazioni` (`idgruppo_applicazioni`, `gruppo_applicazioni`, `icona_gruppo`, `ordine_gruppo`) VALUES
(1, 'Gestione', 'fas fa-sliders-h', 30),
(2, 'Tabelle', 'fa-solid fa-table-list', 40),
(3, 'Planning', 'fa-solid fa-table-list', 10),
(4, 'Amministrazione', 'fas fa-file-invoice-dollar', 20);

-- --------------------------------------------------------

--
-- Struttura della tabella `gruppi_applicazioni_atleta`
--

CREATE TABLE `gruppi_applicazioni_atleta` (
  `idgruppo_applicazioni` int NOT NULL,
  `gruppo_applicazioni` varchar(45) NOT NULL,
  `icona_gruppo` varchar(45) DEFAULT NULL,
  `ordine_gruppo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `impostazioni`
--

CREATE TABLE `impostazioni` (
  `idimpostazione` int NOT NULL,
  `impostazione` varchar(45) DEFAULT NULL,
  `valore` varchar(45) DEFAULT NULL,
  `descrizione_impostazione` varchar(255) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL,
  `visibile` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `impostazioni`
--

INSERT INTO `impostazioni` (`idimpostazione`, `impostazione`, `valore`, `descrizione_impostazione`, `tipo`, `visibile`) VALUES
(1, 'gg_scadenza_password_atleti', '90', 'gg scadenza password atleti', 'n', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `letture_notifiche`
--

CREATE TABLE `letture_notifiche` (
  `idlettura_notifiche` int NOT NULL,
  `idnotifica` int NOT NULL,
  `idutente` int NOT NULL,
  `data_lettura` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_cancellazione` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs`
--

CREATE TABLE `logs` (
  `idlog` int NOT NULL,
  `idutente` int NOT NULL,
  `data_log` datetime DEFAULT NULL,
  `log` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agente` longtext,
  `dettagli_log` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `logs`
--

INSERT INTO `logs` (`idlog`, `idutente`, `data_log`, `log`, `ip`, `agente`, `dettagli_log`) VALUES
(1, 0, '2024-08-09 12:18:26', 'logout_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(2, 0, '2024-08-09 13:48:13', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(3, 0, '2024-08-09 13:48:27', 'update_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0', '[{\"idutente\":1,\"query\":\"SQL: [678] \\n                        UPDATE utenti SET \\n                            titolo=:titolo,\\n                            cognome=:cognome,\\n                            nome=:nome,\\n                            username=:username,\\n                            telefono1=:telefono1,\\n                            telefono2=:telefono2,\\n                            email1=:email1,\\n                            email2=:email2,\\n                            attivo=:attivo,\\n                            superadmin=:superadmin,\\n                            data_scadenza_account=:data_scadenza_account,\\n                            note_utente=:note_utente\\n                     WHERE idutente=:idutente\\nSent SQL: [664] \\n                        UPDATE utenti SET \\n                            titolo=\'Ing.\',\\n                            cognome=\'Stabile\',\\n                            nome=\'Dario\',\\n                            username=\'dario.stabile\',\\n                            telefono1=\'3291650348\',\\n                            telefono2=\'\',\\n                            email1=\'dario.stabile@gmail.com\',\\n                            email2=\'\',\\n                            attivo=\'1\',\\n                            superadmin=\'0\',\\n                            data_scadenza_account=\'2034-08-01 23:59:59\',\\n                            note_utente=\'\'\\n                     WHERE idutente=\'1\'\\nParams:  13\\nKey: Name: [7] :titolo\\nparamno=-1\\nname=[7] \\\":titolo\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [8] :cognome\\nparamno=-1\\nname=[8] \\\":cognome\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [5] :nome\\nparamno=-1\\nname=[5] \\\":nome\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [9] :username\\nparamno=-1\\nname=[9] \\\":username\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [10] :telefono1\\nparamno=-1\\nname=[10] \\\":telefono1\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [10] :telefono2\\nparamno=-1\\nname=[10] \\\":telefono2\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [7] :email1\\nparamno=-1\\nname=[7] \\\":email1\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [7] :email2\\nparamno=-1\\nname=[7] \\\":email2\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [7] :attivo\\nparamno=-1\\nname=[7] \\\":attivo\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [11] :superadmin\\nparamno=-1\\nname=[11] \\\":superadmin\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [22] :data_scadenza_account\\nparamno=-1\\nname=[22] \\\":data_scadenza_account\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [12] :note_utente\\nparamno=-1\\nname=[12] \\\":note_utente\\\"\\nis_param=1\\nparam_type=2\\nKey: Name: [9] :idutente\\nparamno=-1\\nname=[9] \\\":idutente\\\"\\nis_param=1\\nparam_type=2\\n\"}]'),
(4, 0, '2024-08-09 14:36:52', 'logout_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:128.0) Gecko/20100101 Firefox/128.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(5, 0, '2024-09-02 17:37:00', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:129.0) Gecko/20100101 Firefox/129.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(6, 0, '2024-09-02 17:37:22', 'logout_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:129.0) Gecko/20100101 Firefox/129.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(7, 0, '2024-09-03 18:19:37', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:129.0) Gecko/20100101 Firefox/129.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(8, 0, '2024-09-03 18:49:38', 'logout_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:129.0) Gecko/20100101 Firefox/129.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(9, 1, '2025-08-11 09:28:01', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:141.0) Gecko/20100101 Firefox/141.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(10, 1, '2025-08-28 14:30:32', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:142.0) Gecko/20100101 Firefox/142.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(11, 1, '2025-08-28 14:57:05', 'login_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:142.0) Gecko/20100101 Firefox/142.0', '[{\"idutente\":1,\"query\":\"\"}]'),
(12, 1, '2025-08-28 14:58:54', 'logout_utente', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:142.0) Gecko/20100101 Firefox/142.0', '[{\"idutente\":1,\"query\":\"\"}]');

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_atleta`
--

CREATE TABLE `logs_atleta` (
  `idlog` int NOT NULL,
  `idatleta` int DEFAULT NULL,
  `data_log` datetime DEFAULT NULL,
  `log` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agente` longtext,
  `dettagli_log` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `nazioni`
--

CREATE TABLE `nazioni` (
  `idnazione` int NOT NULL,
  `codice_UIC` int DEFAULT NULL,
  `codice_ISO` varchar(2) DEFAULT NULL,
  `nome_nazione` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `idnotifica` int NOT NULL,
  `data_notifica` datetime NOT NULL,
  `idtipo_notifica` int NOT NULL,
  `priorita_notifica` int DEFAULT NULL,
  `idmittente` int NOT NULL,
  `titolo_notifica` varchar(255) NOT NULL,
  `testo_notifica` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche_has_destinatari`
--

CREATE TABLE `notifiche_has_destinatari` (
  `idnotifica_destinatari` int NOT NULL,
  `idnotifica` int NOT NULL,
  `idutente` int DEFAULT NULL,
  `idprofilo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `pagamenti`
--

CREATE TABLE `pagamenti` (
  `idpagamento` int NOT NULL,
  `idiscrizione` int NOT NULL,
  `idatleta` int NOT NULL COMMENT 'Inserito in ridondanza per velocizzare la ricerca per atleta',
  `data_pagamento` date DEFAULT NULL,
  `quota_pagamento` decimal(6,2) DEFAULT NULL,
  `note_pagamento` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `presenze`
--

CREATE TABLE `presenze` (
  `idiscrizione` int NOT NULL,
  `idcorso` int NOT NULL,
  `data_corso` date DEFAULT NULL,
  `data_rilevazione` datetime DEFAULT NULL,
  `presente` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `profili`
--

CREATE TABLE `profili` (
  `idprofilo` int NOT NULL,
  `profilo` varchar(45) NOT NULL,
  `ordine_profilo` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `profili`
--

INSERT INTO `profili` (`idprofilo`, `profilo`, `ordine_profilo`) VALUES
(1, 'Amministratore', 1),
(2, 'Istruttore', 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `qualifiche`
--

CREATE TABLE `qualifiche` (
  `idqualifica` int NOT NULL,
  `idatleta` int NOT NULL,
  `iddisciplina` int NOT NULL,
  `qualifica` varchar(45) DEFAULT NULL,
  `data_conseguimento` date DEFAULT NULL,
  `note_qualifica` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struttura della tabella `sedi`
--

CREATE TABLE `sedi` (
  `idsede` int NOT NULL,
  `sede` varchar(45) DEFAULT NULL,
  `codice_sede` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `sedi`
--

INSERT INTO `sedi` (`idsede`, `sede`, `codice_sede`) VALUES
(1, 'Monreale', 'MONREALE');

-- --------------------------------------------------------

--
-- Struttura della tabella `tipi_documento`
--

CREATE TABLE `tipi_documento` (
  `idtipo_documento` int NOT NULL,
  `tipo_documento` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `tipi_documento`
--

INSERT INTO `tipi_documento` (`idtipo_documento`, `tipo_documento`) VALUES
(1, 'altro'),
(2, 'carta identità'),
(3, 'patente'),
(4, 'codice fiscale'),
(5, 'certificato medico non agonistico'),
(6, 'certificato medico agonistico');

-- --------------------------------------------------------

--
-- Struttura della tabella `tipi_notifica`
--

CREATE TABLE `tipi_notifica` (
  `idtipo_notifica` int NOT NULL,
  `tipo_notifica` varchar(45) DEFAULT NULL,
  `priorita_tipo_notifica` varchar(45) DEFAULT NULL,
  `colore_tipo_notifica` varchar(6) DEFAULT NULL,
  `icona_tipo_notifica` varchar(255) DEFAULT NULL,
  `colore_testo_tipo_notifica` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `tipi_notifica`
--

INSERT INTO `tipi_notifica` (`idtipo_notifica`, `tipo_notifica`, `priorita_tipo_notifica`, `colore_tipo_notifica`, `icona_tipo_notifica`, `colore_testo_tipo_notifica`) VALUES
(1, 'Allarmi', '30', 'd9534f', 'fa fa-bell', 'd9534f'),
(2, 'Warning', '20', 'f0ad4e', 'fa fa-exclamation-circle', 'f0ad4e'),
(3, 'Avvisi', '10', '5cb85c', 'fa fa-info-circle', '5cb85c');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `idutente` int NOT NULL,
  `titolo` varchar(45) DEFAULT NULL,
  `cognome` varchar(45) DEFAULT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `telefono1` varchar(45) DEFAULT NULL,
  `telefono2` varchar(45) DEFAULT NULL,
  `email1` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `attivo` tinyint DEFAULT '1',
  `cancellato` tinyint DEFAULT '0',
  `superadmin` tinyint DEFAULT '0',
  `data_creazione_account` datetime DEFAULT NULL,
  `data_scadenza_account` datetime DEFAULT NULL,
  `data_cambio_password` datetime DEFAULT NULL,
  `immagine_utente` varchar(255) DEFAULT NULL,
  `note_utente` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`idutente`, `titolo`, `cognome`, `nome`, `username`, `password`, `telefono1`, `telefono2`, `email1`, `email2`, `attivo`, `cancellato`, `superadmin`, `data_creazione_account`, `data_scadenza_account`, `data_cambio_password`, `immagine_utente`, `note_utente`) VALUES
(1, 'Ing.', 'Stabile', 'Dario', 'dario.stabile', 'c63288911e9f9a3702eb90efb460706716ba4a54e359ebd5e61c025e2bd889bc', '3291650348', '', 'dario.stabile@gmail.com', '', 1, 0, 0, '2024-08-01 10:24:26', '2034-08-01 23:59:59', '2024-08-01 10:24:26', 'public/utenti/1/1.jpg', ''),
(2, 'Sig.', 'Sciabbica', 'Maurizio', 'maurizio.sciabbica', 'e9f754fa2f7ebd2b7fb0c260ade90a3d5debb144ac5cbfe0db45bcc87bf02996', '3297295585', '', '', '', 1, 0, 0, '2024-08-09 09:52:34', '2025-08-09 23:59:59', '2024-08-09 09:52:34', 'public/utenti/2/2.png', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti_has_applicazioni`
--

CREATE TABLE `utenti_has_applicazioni` (
  `idutente` int NOT NULL,
  `idapplicazione` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `utenti_has_applicazioni`
--

INSERT INTO `utenti_has_applicazioni` (`idutente`, `idapplicazione`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti_has_profili`
--

CREATE TABLE `utenti_has_profili` (
  `idutente` int NOT NULL,
  `idprofilo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `utenti_has_profili`
--

INSERT INTO `utenti_has_profili` (`idutente`, `idprofilo`) VALUES
(1, 1),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti_has_sedi`
--

CREATE TABLE `utenti_has_sedi` (
  `idutente` int NOT NULL,
  `idsede` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dump dei dati per la tabella `utenti_has_sedi`
--

INSERT INTO `utenti_has_sedi` (`idutente`, `idsede`) VALUES
(1, 1),
(2, 1);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `applicazioni`
--
ALTER TABLE `applicazioni`
  ADD PRIMARY KEY (`idapplicazione`),
  ADD KEY `fk_applicazioni_gruppi_applicazioni1_idx` (`idgruppo_applicazioni`);

--
-- Indici per le tabelle `applicazioni_atleta`
--
ALTER TABLE `applicazioni_atleta`
  ADD PRIMARY KEY (`idapplicazione`),
  ADD KEY `fk_applicazioni_atleta_gruppi_applicazioni_atleta1_idx` (`idgruppo_applicazioni`);

--
-- Indici per le tabelle `atleti`
--
ALTER TABLE `atleti`
  ADD PRIMARY KEY (`idatleta`),
  ADD UNIQUE KEY `codice_fiscale` (`codice_fiscale`) USING BTREE;

--
-- Indici per le tabelle `atleti_has_applicazioni_atleta`
--
ALTER TABLE `atleti_has_applicazioni_atleta`
  ADD PRIMARY KEY (`idatleta`,`idapplicazione`),
  ADD KEY `fk_atleti_has_applicazioni_atleta_applicazioni_atleta1_idx` (`idapplicazione`),
  ADD KEY `fk_atleti_has_applicazioni_atleta_atleti1_idx` (`idatleta`);

--
-- Indici per le tabelle `comuni_italiani`
--
ALTER TABLE `comuni_italiani`
  ADD PRIMARY KEY (`comune`,`provincia`),
  ADD KEY `Nazione` (`nazione`),
  ADD KEY `Comune` (`comune`),
  ADD KEY `Regione` (`regione`),
  ADD KEY `Provincia` (`provincia`),
  ADD KEY `Cap` (`cap`),
  ADD KEY `CodiceNazionale` (`codicenazionale`),
  ADD KEY `CodiceRegionale` (`codiceregionale`),
  ADD KEY `CodiceISTAT` (`codiceistat`),
  ADD KEY `CodiceUSL` (`codiceusl`),
  ADD KEY `CodASP` (`codasp`),
  ADD KEY `Prefisso` (`prefisso`);

--
-- Indici per le tabelle `corsi`
--
ALTER TABLE `corsi`
  ADD PRIMARY KEY (`idcorso`),
  ADD KEY `fk_corsi_utenti1_idx` (`idutente`),
  ADD KEY `fk_corsi_discipline1_idx` (`iddisciplina`),
  ADD KEY `fk_corsi_sedi1_idx` (`idsede`);

--
-- Indici per le tabelle `discipline`
--
ALTER TABLE `discipline`
  ADD PRIMARY KEY (`iddisciplina`);

--
-- Indici per le tabelle `documenti`
--
ALTER TABLE `documenti`
  ADD PRIMARY KEY (`iddocumento`),
  ADD KEY `fk_documenti_tipi_documento1_idx` (`idtipo_documento`),
  ADD KEY `fk_documenti_atleti1_idx` (`idatleta`);

--
-- Indici per le tabelle `gruppi_applicazioni`
--
ALTER TABLE `gruppi_applicazioni`
  ADD PRIMARY KEY (`idgruppo_applicazioni`);

--
-- Indici per le tabelle `gruppi_applicazioni_atleta`
--
ALTER TABLE `gruppi_applicazioni_atleta`
  ADD PRIMARY KEY (`idgruppo_applicazioni`);

--
-- Indici per le tabelle `impostazioni`
--
ALTER TABLE `impostazioni`
  ADD PRIMARY KEY (`idimpostazione`);

--
-- Indici per le tabelle `letture_notifiche`
--
ALTER TABLE `letture_notifiche`
  ADD PRIMARY KEY (`idlettura_notifiche`),
  ADD KEY `fk_letture_notifiche_notifiche1_idx` (`idnotifica`),
  ADD KEY `fk_letture_notifiche_utenti1_idx` (`idutente`);

--
-- Indici per le tabelle `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `fk_logs_utenti1_idx` (`idutente`);

--
-- Indici per le tabelle `logs_atleta`
--
ALTER TABLE `logs_atleta`
  ADD PRIMARY KEY (`idlog`),
  ADD KEY `fk_logs_atleta_atleti1_idx` (`idatleta`);

--
-- Indici per le tabelle `nazioni`
--
ALTER TABLE `nazioni`
  ADD PRIMARY KEY (`idnazione`),
  ADD KEY `codice_UIC` (`codice_UIC`),
  ADD KEY `codice_ISO` (`codice_ISO`),
  ADD KEY `nome_nazione` (`nome_nazione`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`idnotifica`),
  ADD KEY `fk_notifiche_tipi_notifica1_idx` (`idtipo_notifica`),
  ADD KEY `fk_notifiche_utenti1_idx` (`idmittente`);

--
-- Indici per le tabelle `notifiche_has_destinatari`
--
ALTER TABLE `notifiche_has_destinatari`
  ADD PRIMARY KEY (`idnotifica_destinatari`),
  ADD KEY `fk_notifiche_has_destinatari_notifiche1_idx` (`idnotifica`),
  ADD KEY `fk_notifiche_has_destinatari_utenti1_idx` (`idutente`);

--
-- Indici per le tabelle `pagamenti`
--
ALTER TABLE `pagamenti`
  ADD PRIMARY KEY (`idpagamento`,`idiscrizione`),
  ADD KEY `fk_pagamenti_atleti1_idx` (`idatleta`),
  ADD KEY `fk_pagamenti_iscrizioni1_idx` (`idiscrizione`);

--
-- Indici per le tabelle `presenze`
--
ALTER TABLE `presenze`
  ADD KEY `fk_primary` (`data_corso`),
  ADD KEY `fk_presenze_iscrizioni_has_corsi1_idx` (`idiscrizione`,`idcorso`);

--
-- Indici per le tabelle `profili`
--
ALTER TABLE `profili`
  ADD PRIMARY KEY (`idprofilo`);

--
-- Indici per le tabelle `qualifiche`
--
ALTER TABLE `qualifiche`
  ADD PRIMARY KEY (`idqualifica`),
  ADD KEY `fk_qualifiche_atleti1_idx` (`idatleta`),
  ADD KEY `fk_qualifiche_discipline1_idx` (`iddisciplina`);

--
-- Indici per le tabelle `sedi`
--
ALTER TABLE `sedi`
  ADD PRIMARY KEY (`idsede`);

--
-- Indici per le tabelle `tipi_documento`
--
ALTER TABLE `tipi_documento`
  ADD PRIMARY KEY (`idtipo_documento`);

--
-- Indici per le tabelle `tipi_notifica`
--
ALTER TABLE `tipi_notifica`
  ADD PRIMARY KEY (`idtipo_notifica`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`idutente`);

--
-- Indici per le tabelle `utenti_has_applicazioni`
--
ALTER TABLE `utenti_has_applicazioni`
  ADD PRIMARY KEY (`idutente`,`idapplicazione`),
  ADD KEY `fk_utenti_has_applicazioni_applicazioni1_idx` (`idapplicazione`),
  ADD KEY `fk_utenti_has_applicazioni_utenti1_idx` (`idutente`);

--
-- Indici per le tabelle `utenti_has_profili`
--
ALTER TABLE `utenti_has_profili`
  ADD PRIMARY KEY (`idutente`,`idprofilo`),
  ADD KEY `fk_utenti_has_profili_profili1_idx` (`idprofilo`),
  ADD KEY `fk_utenti_has_profili_utenti1_idx` (`idutente`);

--
-- Indici per le tabelle `utenti_has_sedi`
--
ALTER TABLE `utenti_has_sedi`
  ADD PRIMARY KEY (`idutente`,`idsede`),
  ADD KEY `fk_utenti_has_sedi_sedi1_idx` (`idsede`),
  ADD KEY `fk_utenti_has_sedi_utenti1_idx` (`idutente`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `applicazioni`
--
ALTER TABLE `applicazioni`
  MODIFY `idapplicazione` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `applicazioni_atleta`
--
ALTER TABLE `applicazioni_atleta`
  MODIFY `idapplicazione` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `atleti`
--
ALTER TABLE `atleti`
  MODIFY `idatleta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `corsi`
--
ALTER TABLE `corsi`
  MODIFY `idcorso` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `discipline`
--
ALTER TABLE `discipline`
  MODIFY `iddisciplina` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `documenti`
--
ALTER TABLE `documenti`
  MODIFY `iddocumento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `gruppi_applicazioni`
--
ALTER TABLE `gruppi_applicazioni`
  MODIFY `idgruppo_applicazioni` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `gruppi_applicazioni_atleta`
--
ALTER TABLE `gruppi_applicazioni_atleta`
  MODIFY `idgruppo_applicazioni` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `impostazioni`
--
ALTER TABLE `impostazioni`
  MODIFY `idimpostazione` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `letture_notifiche`
--
ALTER TABLE `letture_notifiche`
  MODIFY `idlettura_notifiche` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `logs`
--
ALTER TABLE `logs`
  MODIFY `idlog` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `logs_atleta`
--
ALTER TABLE `logs_atleta`
  MODIFY `idlog` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `nazioni`
--
ALTER TABLE `nazioni`
  MODIFY `idnazione` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `idnotifica` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche_has_destinatari`
--
ALTER TABLE `notifiche_has_destinatari`
  MODIFY `idnotifica_destinatari` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pagamenti`
--
ALTER TABLE `pagamenti`
  MODIFY `idpagamento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `profili`
--
ALTER TABLE `profili`
  MODIFY `idprofilo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `qualifiche`
--
ALTER TABLE `qualifiche`
  MODIFY `idqualifica` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `sedi`
--
ALTER TABLE `sedi`
  MODIFY `idsede` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `tipi_documento`
--
ALTER TABLE `tipi_documento`
  MODIFY `idtipo_documento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `tipi_notifica`
--
ALTER TABLE `tipi_notifica`
  MODIFY `idtipo_notifica` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `idutente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `applicazioni`
--
ALTER TABLE `applicazioni`
  ADD CONSTRAINT `fk_applicazioni_gruppi_applicazioni1` FOREIGN KEY (`idgruppo_applicazioni`) REFERENCES `gruppi_applicazioni` (`idgruppo_applicazioni`);

--
-- Limiti per la tabella `applicazioni_atleta`
--
ALTER TABLE `applicazioni_atleta`
  ADD CONSTRAINT `fk_applicazioni_atleta_gruppi_applicazioni_atleta1` FOREIGN KEY (`idgruppo_applicazioni`) REFERENCES `gruppi_applicazioni_atleta` (`idgruppo_applicazioni`);

--
-- Limiti per la tabella `atleti_has_applicazioni_atleta`
--
ALTER TABLE `atleti_has_applicazioni_atleta`
  ADD CONSTRAINT `fk_atleti_has_applicazioni_atleta_applicazioni_atleta1` FOREIGN KEY (`idapplicazione`) REFERENCES `applicazioni_atleta` (`idapplicazione`),
  ADD CONSTRAINT `fk_atleti_has_applicazioni_atleta_atleti1` FOREIGN KEY (`idatleta`) REFERENCES `atleti` (`idatleta`);

--
-- Limiti per la tabella `corsi`
--
ALTER TABLE `corsi`
  ADD CONSTRAINT `fk_corsi_discipline1` FOREIGN KEY (`iddisciplina`) REFERENCES `discipline` (`iddisciplina`),
  ADD CONSTRAINT `fk_corsi_sedi1` FOREIGN KEY (`idsede`) REFERENCES `sedi` (`idsede`),
  ADD CONSTRAINT `fk_corsi_utenti1` FOREIGN KEY (`idutente`) REFERENCES `utenti` (`idutente`);

--
-- Limiti per la tabella `documenti`
--
ALTER TABLE `documenti`
  ADD CONSTRAINT `fk_documenti_atleti1` FOREIGN KEY (`idatleta`) REFERENCES `atleti` (`idatleta`),
  ADD CONSTRAINT `fk_documenti_tipi_documento1` FOREIGN KEY (`idtipo_documento`) REFERENCES `tipi_documento` (`idtipo_documento`);

--
-- Limiti per la tabella `letture_notifiche`
--
ALTER TABLE `letture_notifiche`
  ADD CONSTRAINT `fk_letture_notifiche_notifiche1` FOREIGN KEY (`idnotifica`) REFERENCES `notifiche` (`idnotifica`),
  ADD CONSTRAINT `fk_letture_notifiche_utenti1` FOREIGN KEY (`idutente`) REFERENCES `utenti` (`idutente`);

--
-- Limiti per la tabella `logs_atleta`
--
ALTER TABLE `logs_atleta`
  ADD CONSTRAINT `fk_logs_atleta_atleti1` FOREIGN KEY (`idatleta`) REFERENCES `atleti` (`idatleta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
