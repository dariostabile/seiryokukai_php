-- Crea la tabella iscrizioni se manca nello schema aggiornato.
-- Necessaria per il flusso iscrizioni/pagamenti basato su idiscrizione.

CREATE TABLE IF NOT EXISTS `iscrizioni` (
  `idiscrizione` int NOT NULL AUTO_INCREMENT,
  `idatleta` int NOT NULL,
  `data_inizio_iscrizione` date DEFAULT NULL,
  `data_fine_iscrizione` date DEFAULT NULL,
  `totale_iscrizione` decimal(6,2) DEFAULT NULL,
  `stato_iscrizione` varchar(45) DEFAULT NULL,
  `note_iscrizione` longtext,
  PRIMARY KEY (`idiscrizione`),
  KEY `fk_iscrizioni_atleti1_idx` (`idatleta`),
  CONSTRAINT `fk_iscrizioni_atleti1`
    FOREIGN KEY (`idatleta`) REFERENCES `atleti` (`idatleta`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;