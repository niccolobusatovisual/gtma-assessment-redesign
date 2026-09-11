-- Tabelle del QUESTIONARIO: sezioni, domande, risposte, benchmark e target.
-- Le modifica solo l'admin; report2r e provareport2r le leggono.
-- Regole che il database non può esprimere (da 3 a 8 sezioni, almeno 2 risposte
-- per domanda, almeno un valore > 0) le controlla l'admin prima di pubblicare.

SET NAMES utf8mb4;

CREATE TABLE versioni (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  stato          ENUM('bozza','pubblicata','archiviata') NOT NULL,
  -- al massimo una bozza e una pubblicata: un indice UNIQUE ignora i NULL
  stato_attivo   VARCHAR(10) GENERATED ALWAYS AS (CASE WHEN stato IN ('bozza','pubblicata') THEN stato END) STORED,
  basata_su_id   INT UNSIGNED NULL,
  nota           VARCHAR(255) NULL,
  creata_il      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creata_da      INT UNSIGNED NULL,
  pubblicata_il  DATETIME NULL,
  pubblicata_da  INT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_versioni_stato_attivo (stato_attivo),
  CONSTRAINT fk_versioni_base FOREIGN KEY (basata_su_id) REFERENCES versioni (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE impostazioni (
  versione_id      INT UNSIGNED NOT NULL,
  soglia_positivo  DECIMAL(4,3) NOT NULL,
  soglia_sviluppo  DECIMAL(4,3) NOT NULL,
  PRIMARY KEY (versione_id),
  CONSTRAINT fk_impostazioni_versione FOREIGN KEY (versione_id) REFERENCES versioni (id) ON DELETE CASCADE,
  CONSTRAINT ck_impostazioni_soglie CHECK (soglia_sviluppo >= 0 AND soglia_positivo <= 1 AND soglia_sviluppo < soglia_positivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE obiettivi (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  versione_id  INT UNSIGNED NOT NULL,
  testo        VARCHAR(160) NOT NULL,
  ordine       SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY ix_obiettivi_versione (versione_id, ordine),
  CONSTRAINT fk_obiettivi_versione FOREIGN KEY (versione_id) REFERENCES versioni (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sezioni (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  versione_id     INT UNSIGNED NOT NULL,
  -- resta uguale tra una versione e l'altra: collega la stessa sezione nello storico e nei report
  chiave          VARCHAR(40) NOT NULL,
  nome            VARCHAR(120) NOT NULL,
  nome_breve      VARCHAR(24) NOT NULL,
  descrizione     VARCHAR(400) NOT NULL DEFAULT '',
  -- posizione nella tavolozza fissa definita nel codice: l'admin non sceglie colori
  colore_indice   TINYINT UNSIGNED NOT NULL,
  ordine          SMALLINT UNSIGNED NOT NULL,
  benchmark_b2b   DECIMAL(4,3) NOT NULL,
  benchmark_b2c   DECIMAL(4,3) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sezioni_chiave (versione_id, chiave),
  KEY ix_sezioni_ordine (versione_id, ordine),
  CONSTRAINT fk_sezioni_versione FOREIGN KEY (versione_id) REFERENCES versioni (id) ON DELETE CASCADE,
  CONSTRAINT ck_sezioni_benchmark CHECK (benchmark_b2b BETWEEN 0 AND 1 AND benchmark_b2c BETWEEN 0 AND 1),
  CONSTRAINT ck_sezioni_colore CHECK (colore_indice < 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE target_sezione (
  sezione_id    INT UNSIGNED NOT NULL,
  obiettivo_id  INT UNSIGNED NOT NULL,
  segmento      ENUM('b2b','b2c') NOT NULL,
  valore        DECIMAL(4,3) NOT NULL,
  PRIMARY KEY (sezione_id, obiettivo_id, segmento),
  CONSTRAINT fk_target_sezione FOREIGN KEY (sezione_id) REFERENCES sezioni (id) ON DELETE CASCADE,
  CONSTRAINT fk_target_obiettivo FOREIGN KEY (obiettivo_id) REFERENCES obiettivi (id) ON DELETE CASCADE,
  CONSTRAINT ck_target_valore CHECK (valore BETWEEN 0 AND 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE domande (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sezione_id  INT UNSIGNED NOT NULL,
  segmento    ENUM('b2b','b2c') NOT NULL,
  -- resta uguale tra le versioni, anche se la domanda cambia sezione
  uid         CHAR(36) NOT NULL,
  testo       VARCHAR(500) NOT NULL,
  ordine      SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY ix_domande_sezione (sezione_id, segmento, ordine),
  KEY ix_domande_uid (uid),
  CONSTRAINT fk_domande_sezione FOREIGN KEY (sezione_id) REFERENCES sezioni (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE risposte (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  domanda_id  INT UNSIGNED NOT NULL,
  uid         CHAR(36) NOT NULL,
  testo       VARCHAR(300) NOT NULL,
  valore      DECIMAL(8,3) NOT NULL,
  ordine      SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY ix_risposte_domanda (domanda_id, ordine),
  CONSTRAINT fk_risposte_domanda FOREIGN KEY (domanda_id) REFERENCES domande (id) ON DELETE CASCADE,
  CONSTRAINT ck_risposte_valore CHECK (valore >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
