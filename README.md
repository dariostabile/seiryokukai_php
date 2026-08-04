# seiryokukai_php

Gestionale client/server PHP con template grafico admin responsive.

## Stack

- PHP 8+
- MySQL (MAMP)
- Sessioni PHP per autenticazione
- API JSON lato server (`public/api`)
- Template UI con Bootstrap + Font Awesome + CSS custom

## Avvio locale (MAMP)

1. Punta Apache alla cartella `seiryokukai_php/public`.
2. Copia `config/.env.example` in `config/.env`.
3. Crea il database importando `database/seiryokukai_20260521.sql`.
4. Verifica credenziali DB in `config/.env` (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).
5. Apri nel browser:

http://localhost/seiryokukai_php/public/index.php?page=login

## Login

- Usa un utente presente nella tabella `utenti` del dump importato.
- La verifica password supporta hash SHA-256 esadecimale (come nel dump) e bcrypt.

## Struttura

- `public/index.php`: front controller pagine
- `public/api`: endpoint API
- `src/lib`: logica auth su `utenti` + dati anagrafica su `atleti`
- `src/views`: viste template
- `database/seiryokukai_20260521.sql`: dump completo aggiornato di riferimento
