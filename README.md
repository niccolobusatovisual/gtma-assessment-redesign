# GoToMarket Assessment — redesign UX/UI

Banco di prova per il redesign dell'assessment di Studio Guzzetti / Promexa.
**Non si tocca il file in produzione.** Si lavora qui, si mostra la preview, e solo
le parti approvate vengono poi riportate sull'HTML live.

> ## ⚠️ REGOLA FISSA — MOBILE-FIRST
> L'assessment gira soprattutto su **mobile**. Ogni modifica DEVE essere
> estremamente responsive. **Prima** di iniziare qualunque intervento si verifica
> che l'approccio sia responsive (type fluida con `clamp()`, unità relative, griglie
> che collassano a 1 colonna, tap target ≥ 48px, zero scroll orizzontale); **dopo**
> si controlla il rendering a ~390px e a desktop prima di fare push.

## File

| File / cartella | Cosa è |
|------|--------|
| `index.html` | **Copia di lavoro** — grafica/CSS/JS. Dal 2026-09-10 è anche il template che `server/index.php` inietta di domande e valori presi dal database prima di servirlo |
| `original-2026-09-03.html` | Snapshot esatto del file live al 2026-09-03 — baseline "prima", da non modificare |
| `db/` | Schema MySQL + script per ricrearlo in locale (vedi sotto) |
| `server/` | Applicazione PHP: legge il questionario dal database e salva le compilazioni |
| `NOTES.md` | Mappa tecnica completa — **leggere prima di riprendere il lavoro** |
| `CHANGELOG.md` | Registro delle modifiche, schermata per schermata, per la review con l'agenzia |

Snapshot pristino identico anche fuori dal repo: `../_live-snapshot-2026-09-03.html`.

## Preview

- **Online (solo grafica/CSS, senza backend):** ogni push su `main` aggiorna
  il banco di prova (~1 min) →
  https://niccolobusatovisual.github.io/gtma-assessment-redesign/?c=GMA-2026
- **Locale, solo `index.html` (senza backend):** `python3 -m http.server 8080`
  dentro questa cartella, poi apri `http://localhost:8080/index.html?c=GMA-2026`
- **Locale, con database:** `db/setup_locale.sh` (richiede PHP + MySQL, già
  installati via Homebrew) ricrea il database, poi `server/crea_cartella.sh
  <cartella> prova` prepara una cartella completa da servire con
  `php -S 127.0.0.1:8090 -t <cartella>`. Dettagli in `NOTES.md`.
- **Sottodominio di prova (vero backend):** `provareport2r.keymove.it` —
  caricato via FileZilla dalla cartella generata da `crea_cartella.sh`.

Il parametro `?c=GMA-2026` sblocca la prima schermata (gate col codice
d'accesso) solo sulla versione senza database — con `server/index.php` in
mezzo, il campo `ACCESS_CODE` è gestito dal server, non dall'URL.

## Cosa il redesign non tocca da solo

`QUESTIONS`/`DIMS`/benchmark/target sono passati dal codice al database
(2026-09-10, con l'accordo del collega consulente): restano dati che non si
modificano a mano nel redesign, ma non sono più territorio esclusivo suo.
Il redesign lavora su **markup + CSS + funzioni di render delle schermate**.

## Nota

Il vecchio salvataggio su `gma_api.php` è stato sostituito dal backend in
`server/` (vedi `NOTES.md`). Aprendo `index.html` da solo (preview GitHub
Pages o `http.server` locale) il salvataggio è disattivato di conseguenza:
tutto il resto del flusso gira comunque lato client, offline.
