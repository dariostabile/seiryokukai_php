-- Esegue inserimenti idempotenti per rendere visibile il modulo Corsi nel menu.

INSERT INTO applicazioni (
  idgruppo_applicazioni,
  applicazione,
  url_applicazione,
  descrizione_applicazione,
  ordine_applicazione,
  icona_applicazione
)
SELECT
  1,
  'discipline',
  'disciplina',
  'gestione discipline',
  20,
  'fa-solid fa-medal'
WHERE NOT EXISTS (
  SELECT 1
  FROM applicazioni
  WHERE url_applicazione = 'disciplina'
);

INSERT INTO applicazioni (
  idgruppo_applicazioni,
  applicazione,
  url_applicazione,
  descrizione_applicazione,
  ordine_applicazione,
  icona_applicazione
)
SELECT
  3,
  'corsi',
  'corsi',
  'gestione corsi',
  20,
  'fa-solid fa-dumbbell'
WHERE NOT EXISTS (
  SELECT 1
  FROM applicazioni
  WHERE url_applicazione = 'corsi'
);

INSERT INTO utenti_has_applicazioni (idutente, idapplicazione)
SELECT
  uha.idutente,
  a.idapplicazione
FROM utenti_has_applicazioni uha
INNER JOIN applicazioni a ON a.url_applicazione = 'disciplina'
WHERE uha.idapplicazione = 1
  AND NOT EXISTS (
    SELECT 1
    FROM utenti_has_applicazioni x
    WHERE x.idutente = uha.idutente
      AND x.idapplicazione = a.idapplicazione
  );

INSERT INTO utenti_has_applicazioni (idutente, idapplicazione)
SELECT
  uha.idutente,
  a.idapplicazione
FROM utenti_has_applicazioni uha
INNER JOIN applicazioni a ON a.url_applicazione = 'corsi'
WHERE uha.idapplicazione = 1
  AND NOT EXISTS (
    SELECT 1
    FROM utenti_has_applicazioni x
    WHERE x.idutente = uha.idutente
      AND x.idapplicazione = a.idapplicazione
  );
