-- Tabelle ADMIN: account e storico delle modifiche.
-- Le usa solo l'admin: sito pubblico e prova non devono mai leggere gli hash delle password.

SET NAMES utf8mb4;

CREATE TABLE utenti (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email              VARCHAR(254) NOT NULL,
  nome               VARCHAR(120) NOT NULL,
  password_hash      VARCHAR(255) NOT NULL,
  attivo             TINYINT(1) NOT NULL DEFAULT 1,
  creato_il          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creato_da          INT UNSIGNED NULL,
  ultimo_accesso_il  DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_utenti_email (email),
  CONSTRAINT fk_utenti_creato_da FOREIGN KEY (creato_da) REFERENCES utenti (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reset_password (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  utente_id   INT UNSIGNED NOT NULL,
  token_hash  CHAR(64) NOT NULL,
  scade_il    DATETIME NOT NULL,
  usato_il    DATETIME NULL,
  creato_il   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reset_token (token_hash),
  CONSTRAINT fk_reset_utente FOREIGN KEY (utente_id) REFERENCES utenti (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tentativi_accesso (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email      VARCHAR(254) NOT NULL,
  -- INET6_ATON(): stesso formato per IPv4 e IPv6
  ip         VARBINARY(16) NOT NULL,
  riuscito   TINYINT(1) NOT NULL,
  creato_il  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_tentativi_email (email, creato_il),
  KEY ix_tentativi_ip (ip, creato_il)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE storico (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  utente_id    INT UNSIGNED NULL,
  azione       VARCHAR(40) NOT NULL,
  versione_id  INT UNSIGNED NULL,
  -- valori prima e dopo la modifica
  dettaglio    JSON NULL,
  creato_il    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_storico_data (creato_il),
  KEY ix_storico_versione (versione_id),
  CONSTRAINT fk_storico_utente FOREIGN KEY (utente_id) REFERENCES utenti (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
