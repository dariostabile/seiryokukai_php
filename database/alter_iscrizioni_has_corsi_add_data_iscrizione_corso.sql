-- Aggiunge la colonna data_iscrizione_corso alla tabella iscrizioni_has_corsi.
-- Script idempotente: non fallisce se la colonna esiste gia.

SET @schema_name := DATABASE();

SET @table_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'iscrizioni_has_corsi'
);

SET @column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'iscrizioni_has_corsi'
      AND COLUMN_NAME = 'data_iscrizione_corso'
);

SET @sql_stmt := IF(
    @table_exists = 0,
    'SELECT ''Tabella iscrizioni_has_corsi assente: applica prima lo schema di base.'' AS info',
    IF(
        @column_exists = 0,
        'ALTER TABLE `iscrizioni_has_corsi` ADD COLUMN `data_iscrizione_corso` DATE NULL DEFAULT NULL AFTER `idcorso`',
        'SELECT ''Colonna data_iscrizione_corso gia presente.'' AS info'
    )
);

PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
