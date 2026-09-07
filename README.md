# GoToMarket Assessment — redesign UX/UI

Banco di prova per il redesign dell'assessment di Studio Guzzetti / Promexa.
**Non si tocca il file in produzione.** Si lavora qui, si mostra la preview, e solo
le parti approvate vengono poi riportate sull'HTML live.

## File

| File | Cosa è |
|------|--------|
| `index.html` | **Copia di lavoro** — è quella che il redesign modifica e che viene pubblicata online |
| `original-2026-09-03.html` | Snapshot esatto del file live al 2026-09-03 — baseline "prima", da non modificare |
| `NOTES.md` | Mappa tecnica del file originale |
| `CHANGELOG.md` | Registro delle modifiche, schermata per schermata, per la review con l'agenzia |

Snapshot pristino identico anche fuori dal repo: `../_live-snapshot-2026-09-03.html`.

## Preview

- **Online:** ogni push su `main` aggiorna il banco di prova (~1 min) →
  https://niccolobusatovisual.github.io/gtma-assessment-redesign/?c=GMA-2026
- **Locale:** `python3 -m http.server 8080` dentro questa cartella, poi apri
  `http://localhost:8080/index.html?c=GMA-2026`

Il parametro `?c=GMA-2026` sblocca la prima schermata (gate col codice d'accesso).

## Cosa NON si tocca

`QUESTIONS`, `DIMS`, matematica benchmark/target, branching per segmento B2B/B2C:
è il dominio del collega consulente. Il redesign lavora su **markup + CSS + funzioni
di render delle schermate**.

## Nota

Il salvataggio su `gma_api.php` non funziona dal banco di prova (CSP `connect-src 'self'`
sul server originale). Irrilevante per la preview: tutto il flusso gira lato client.
