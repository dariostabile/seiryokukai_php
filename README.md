# seiryokukai_php

Gestionale client/server PHP con template grafico admin responsive.

## Stack

- PHP 8+
- Sessioni PHP per autenticazione
- API JSON lato server (`public/api`)
- Template UI con Bootstrap + Font Awesome + CSS custom

## Avvio locale (MAMP)

1. Punta Apache alla cartella `seiryokukai_php/public`.
2. Copia `config/.env.example` in `config/.env`.
3. (Opzionale) aggiungi credenziali custom in `config/.env`:

APP_USER=admin
APP_PASS=admin123

4. Apri nel browser:

http://localhost/seiryokukai_php/public/index.php?page=login

## Credenziali demo

- Username: `admin`
- Password: `admin123`

## Struttura

- `public/index.php`: front controller pagine
- `public/api`: endpoint API
- `src/lib`: logica auth + dati
- `src/views`: viste template
- `storage/clients.json`: archivio clienti demo
