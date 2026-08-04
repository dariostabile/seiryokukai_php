-- Aggiunge le colonne `attivo` e `immagine_corso` alla tabella `corsi`
-- da eseguire solo se si parte dal dump seiryokukai.sql (non dal dump 20260521)

ALTER TABLE `corsi`
  ADD COLUMN IF NOT EXISTS `attivo` tinyint DEFAULT '1' AFTER `idutente`,
  ADD COLUMN IF NOT EXISTS `immagine_corso` varchar(255) DEFAULT NULL AFTER `dom_fine`;
