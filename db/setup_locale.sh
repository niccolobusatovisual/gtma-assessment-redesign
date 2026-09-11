#!/usr/bin/env bash
# Crea in locale il database e gli utenti MySQL come sull'hosting: un solo database con tutte le tabelle.
# Uso: ./setup_locale.sh           prima installazione
#      ./setup_locale.sh --reset   cancella e ricrea il database locale
set -euo pipefail
cd "$(dirname "$0")"

MYSQL="${MYSQL:-$(brew --prefix mysql@8.4)/bin/mysql}"
ENV_FILE=.env.locale

if [[ ! -f $ENV_FILE ]]; then
  pw() { LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 24; }
  cat > "$ENV_FILE" <<EOF
DB_NOME=km2r
UTENTE_ADMIN=km2r_u_admin
PASSWORD_ADMIN=$(pw)
UTENTE_ONLINE=km2r_u_online
PASSWORD_ONLINE=$(pw)
UTENTE_PROVA=km2r_u_prova
PASSWORD_PROVA=$(pw)
EOF
  chmod 600 "$ENV_FILE"
fi
source "$ENV_FILE"

root() { "$MYSQL" -uroot "$@"; }

if [[ ${1:-} == --reset ]]; then
  root -e "DROP DATABASE IF EXISTS $DB_NOME;"
fi
root -e "CREATE DATABASE $DB_NOME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

node estrai_seed.js
for f in 01_configurazione.sql 04_seed_configurazione.sql 02_dati.sql 03_admin.sql; do
  root "$DB_NOME" < "$f"
done

# in un solo database i privilegi valgono per tutte le tabelle: che il sito pubblico
# non modifichi il questionario e non legga gli account admin lo garantisce il codice PHP
root <<SQL
CREATE USER IF NOT EXISTS '$UTENTE_ADMIN'@'localhost' IDENTIFIED BY '$PASSWORD_ADMIN';
CREATE USER IF NOT EXISTS '$UTENTE_ONLINE'@'localhost' IDENTIFIED BY '$PASSWORD_ONLINE';
CREATE USER IF NOT EXISTS '$UTENTE_PROVA'@'localhost' IDENTIFIED BY '$PASSWORD_PROVA';
GRANT SELECT, INSERT, UPDATE, DELETE ON $DB_NOME.* TO '$UTENTE_ADMIN'@'localhost';
GRANT SELECT, INSERT, UPDATE ON $DB_NOME.* TO '$UTENTE_ONLINE'@'localhost', '$UTENTE_PROVA'@'localhost';
SQL

echo "Database locale pronto. Credenziali in db/$ENV_FILE"
