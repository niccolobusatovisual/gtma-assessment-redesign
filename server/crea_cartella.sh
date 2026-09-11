#!/usr/bin/env bash
# Prepara la cartella da caricare sul sottodominio con FileZilla.
# Non tocca app/config.php: contiene le credenziali del database e non sta nel repository.
# Uso: ./crea_cartella.sh <cartella di destinazione> <prova|online>
set -euo pipefail
cd "$(dirname "$0")"

DEST=${1:?Indica la cartella di destinazione}
AMBIENTE=${2:?Indica ambiente prova oppure online}
if [[ $AMBIENTE != prova && $AMBIENTE != online ]]; then
  echo "Ambiente non valido: $AMBIENTE (usa prova oppure online)" >&2
  exit 1
fi

mkdir -p "$DEST/app" "$DEST/api"
cp index.php "$DEST/"
cp api/compilazione.php "$DEST/api/"
cp app/db.php app/questionario.php app/.htaccess "$DEST/app/"
cp ../index.html "$DEST/app/pagina.html"
if [[ $AMBIENTE == prova ]]; then
  cp robots-prova.txt "$DEST/robots.txt"
else
  rm -f "$DEST/robots.txt"
fi

if [[ -f $DEST/app/config.php ]]; then
  echo "Cartella pronta: $DEST"
else
  echo "Cartella creata, ma manca $DEST/app/config.php: copia app/config.esempio.php e inserisci i dati del database." >&2
fi
