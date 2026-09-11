-- Tabelle DATI: compilazioni dei clienti e documenti generati.
-- report2r e provareport2r inseriscono e aggiornano; l'admin legge e cancella.
-- Per scelta non si salvano le domande né le risposte date: restano i dati
-- dell'onboarding e i risultati, copiati al momento della compilazione.

SET NAMES utf8mb4;

CREATE TABLE compilazioni (
  id                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ambiente              ENUM('online','prova') NOT NULL,
  versione_id           INT UNSIGNED NOT NULL,
  creata_il             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  compilatore_nome      VARCHAR(120) NOT NULL,
  compilatore_email     VARCHAR(254) NOT NULL,
  azienda_nome          VARCHAR(160) NOT NULL,
  ruolo                 VARCHAR(120) NOT NULL,
  settore               VARCHAR(120) NOT NULL,
  fatturato             VARCHAR(40) NULL,
  dipendenti            VARCHAR(40) NULL,
  clienti_anno          VARCHAR(40) NULL,
  quota_b2b             TINYINT UNSIGNED NOT NULL,
  segmento              ENUM('b2b','b2c') NOT NULL,
  obiettivo_ordine      SMALLINT UNSIGNED NULL,
  obiettivo_testo       VARCHAR(160) NULL,
  canali                JSON NULL,
  descrizione           TEXT NULL,
  punteggio             DECIMAL(6,5) NOT NULL,
  esito                 ENUM('positivo','in sviluppo','critico') NOT NULL,
  consenso_il           DATETIME NOT NULL,
  informativa_versione  VARCHAR(60) NULL,
  pdf_richiesto_il      DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_compilazioni_elenco (ambiente, creata_il),
  KEY ix_compilazioni_email (compilatore_email),
  CONSTRAINT ck_compilazioni_quota CHECK (quota_b2b <= 100),
  CONSTRAINT ck_compilazioni_punteggio CHECK (punteggio BETWEEN 0 AND 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE risultati_sezione (
  compilazione_id  INT UNSIGNED NOT NULL,
  sezione_chiave   VARCHAR(40) NOT NULL,
  sezione_nome     VARCHAR(120) NOT NULL,
  ordine           SMALLINT UNSIGNED NOT NULL,
  punteggio        DECIMAL(6,5) NOT NULL,
  benchmark        DECIMAL(4,3) NOT NULL,
  target           DECIMAL(4,3) NOT NULL,
  PRIMARY KEY (compilazione_id, sezione_chiave),
  CONSTRAINT fk_risultati_compilazione FOREIGN KEY (compilazione_id) REFERENCES compilazioni (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documenti (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  compilazione_id  INT UNSIGNED NOT NULL,
  tipo             ENUM('cliente','interno','presentazione') NOT NULL,
  -- relativo alla cartella archivio, che sta fuori dalla root web
  percorso         VARCHAR(255) NOT NULL,
  dimensione_byte  INT UNSIGNED NOT NULL,
  creato_il        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_documenti_tipo (compilazione_id, tipo),
  CONSTRAINT fk_documenti_compilazione FOREIGN KEY (compilazione_id) REFERENCES compilazioni (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE link_download (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  compilazione_id     INT UNSIGNED NOT NULL,
  -- SHA-256 del codice nel link: chi legge il database non ottiene link funzionanti
  token_hash          CHAR(64) NOT NULL,
  scade_il            DATETIME NOT NULL,
  creato_il           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_download_il  DATETIME NULL,
  n_download          INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_link_token (token_hash),
  KEY ix_link_compilazione (compilazione_id),
  CONSTRAINT fk_link_compilazione FOREIGN KEY (compilazione_id) REFERENCES compilazioni (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
