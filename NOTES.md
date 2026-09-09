# GoToMarket Assessment — note tecniche

> **⚠️ REGOLA FISSA — MOBILE-FIRST.** L'assessment gira soprattutto su mobile.
> Ogni modifica va verificata responsive PRIMA di iniziare e ricontrollata a ~390px
> e desktop prima del push. Type fluida (`clamp()`), unità relative, griglie a 1
> colonna sotto ~560px, tap target ≥ 48px, niente scroll orizzontale.

## Provenienza
- URL live: https://gtma.studioguzzetti.it/?c=GMA-2026
- `original-2026-09-03.html` = copia esatta scaricata il 2026-09-07 (header `last-modified` del server: 2026-09-03). Baseline "prima", da non modificare. Copia identica anche in `../_live-snapshot-2026-09-03.html`.
- `index.html` = copia di lavoro del redesign (quella pubblicata sul banco di prova).
- È un file **statico servito da nginx**: quello che scarichi È il sorgente completo. Nessun build, nessun framework.

## Com'è fatto (un solo file statico, ~430 KB)
| Blocco | Contenuto |
|--------|-----------|
| `<script>` #1 | Chart.js 4.4.0 incollato inline (CDN bloccato dalla CSP del server) |
| `<script>` #2 | GSAP **3.13.0** core incollato inline, stesso motivo (agg. 2026-09-09, era 3.12.5). Aggiornare ri-scaricando da `https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js` |
| `<script>` #2b · #2c | **DrawSVGPlugin 3.13.0** (logo che si disegna) + **MorphSVGPlugin 3.13.0** (tendina a curva), inline subito dopo il core. Dal 2025 sono gratuiti (licenza standard, no Club). Stessi mirror: `…/npm/gsap@3.13.0/dist/DrawSVGPlugin.min.js` e `MorphSVGPlugin.min.js` |
| `<style>` | tutto il CSS inline |
| markup `<body>` | le schermate. Logo Keymove: SVG inline `viewBox="0 0 1122.53 305.71"`, 7 path, presente 2 volte (header `#kmLogo` + loader `#loaderKm`) |
| `<script>` #3 | logica applicativa in JS vanilla: helper GSAP (`gfrom/gto/gcount`, fallback se GSAP assente), `QUESTIONS`, `DIMS`, scoring, branching per segmento, render schermate (onboarding con passo `review` reso come "carta d'identità" azienda `.idc` in `onbRenderReview`, rivelazione progressiva domande `syncQuestionReveal`, risultati a 2 colonne), admin |

## Flusso schermate (single-page, JS mostra/nasconde i `.screen`)
1. `s_locked` — gate con codice d'accesso (`?c=...`)
2. `s0` — welcome / hero
3. `s1` — Sezione A: dati compilatore + azienda (nome, email, ruolo, settore, fatturato, dipendenti, split B2B/B2C, canali, obiettivo)
4. `sectionScreens` — schermate domande generate per dimensione; **cambiano in base allo split B2B/B2C**
5. `s_results` — radar Chart.js + card punteggio per dimensione vs benchmark/target + sintesi + Stampa/PDF + Condividi (`mailto:`)

Nascosto dietro chiave: pannello ⚙️ Setup (config benchmark), tab Database, editor domande.

## Backend
- `gma_api.php` stessa origine. `POST` con header `X-GMA-Key: GMA_WRITE_2026` salva ogni assessment; `GET`/`DELETE` con chiave admin per il pannello Database.
- La CSP del server è `connect-src 'self'` → da una copia su altro dominio/locale il salvataggio non parte. **Irrilevante per una preview UX/UI**: tutto il flusso (welcome → sezione A → domande → risultati + grafico) gira lato client e funziona offline.

## Accesso al pannello Setup (interno)
Nessun pulsante visibile ai clienti. Due ingressi nascosti (poi comunque protetti dal modal password `KeyMoveMMP`):
1. **URL con `?setup`** — es. `https://gtma.studioguzzetti.it/?c=GMA-2026&setup`
2. **5 click rapidi sul logo Keymove** in alto a sinistra (entro ~1,8s)

## Note da segnalare
- La **write key `GMA_WRITE_2026` è in chiaro** nel JS lato client, insieme a un pannello admin nello stesso file. Chiunque può leggerla e scrivere sull'API. Bassa gravità ma reale — da girare al referente.
- La password Setup (`KeyMoveMMP`) è solo offuscata in base64 nel client: non è una vera protezione.
- La copia è uno snapshot: se il collega continua a modificare il file live, questa versione va riallineata (basta ri-scaricare l'URL).

## GSAP: core + plugin DrawSVG / MorphSVG (già installati inline)

Dal **GSAP 3.13 (2025)** tutti i plugin sono **gratis** (Webflow ha comprato
GreenSock): DrawSVG e MorphSVG si usano anche nei lavori clienti, senza account,
senza "Club GSAP". Il CodePen ufficiale mostra ancora il badge "PRO" e il login:
è etichettatura vecchia, non un paywall.

**Stato: fatto (2026-09-09).** In `<head>` ci sono tre `<script>` inline:
core `gsap.min.js` 3.13.0, poi `DrawSVGPlugin.min.js`, poi `MorphSVGPlugin.min.js`
(tutti 3.13.0, presi da `https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/`).
Per aggiornarli: riscarica i tre file dalla stessa cartella e sostituisci i tre
blocchi (stesso ordine: core → DrawSVG → MorphSVG).

Cablatura in `index.html` (tutto con fallback: se un domani i plugin non
caricano, `HAS_DRAWSVG` / `HAS_MORPHSVG` tornano `false` e gli effetti degradano):
- `registerPlugin` + flag `HAS_DRAWSVG` / `HAS_MORPHSVG` nel blocco helper GSAP.
- `animateLogoDraw()` — logo `#kmLogo` (7 path pieni) che prende un contorno,
  si disegna (`drawSVG`), poi compare il fill. Chiamato in init.
- `wipeTransition(cb)` + overlay `#wipe` + `startAssessment()` — tendina rossa a
  curva (MorphSVG fra 4 forme; fallback: GSAP core interpola il `d`). Il bottone
  "Inizia l'assessment" della welcome chiama `startAssessment()`.
  Riutilizzabile: `wipeTransition(function(){ /* cambia schermata */ })`.

## Da fare prima della consegna

### 1. Archiviazione dei dati — MySQL (deciso: da valutare/implementare)
Keymove usa il MySQL di WordPress. **È fattibile**, ma la raccomandazione è di
*non* passare da WordPress:

- **Strada consigliata — endpoint PHP dedicato.** Una tabella `km_autovalutazioni`
  nello stesso MySQL, accanto alle `wp_*`, scritta da un `api.php` posato accanto
  all'HTML sul sottodominio. È lo stesso modello del `gma_api.php` attuale, quindi
  lato client cambia solo l'URL. Nessuna dipendenza da WordPress, stessa origine
  (niente CORS), e lì dentro andrà anche la generazione/invio del PDF.
- Alternativa: rotta REST via plugin WordPress custom (`/wp-json/keymove/v1/...`),
  ha senso **solo** se i risultati devono vivere nella bacheca di WP. Più costosa.
- Sconsigliata: plugin form tipo Gravity Forms / WPForms — non sono pensati per
  ricevere un payload strutturato da un'app esterna.

**Da non sbagliare:**
- La **write key non può stare nel JS** (oggi `GMA_WRITE_2026` è in chiaro).
  Servono controllo di origine + rate limit, meglio un token monouso dal server.
- **Salvare anche il consenso** (`f_consenso`) con data e ora: senza, non c'è prova
  di averlo raccolto.
- Policy di cancellazione coerente con l'informativa (bozza attuale: 24 mesi).
- Fare lo schema **quando il flusso è approvato**: finché cambiano campi e domande,
  cambia anche la tabella.

### 2. Endpoint PDF
`requestPdf()` è un segnaposto: manca il lato server che genera il PDF della
valutazione e lo spedisce all'indirizzo del compilatore.

### 3. Migrazione
Tutto finirà su un **sottodominio Keymove** e il repo GitHub andrà eliminato.

## Da rimuovere prima del rilascio

- **Barra DEV** (`?dev` nell'URL): `initDev()`, `devGo()`, `devFill()`, markup
  `#devbar`, CSS `.devbar/.dev-*`. Serve solo alla review interna.
- **Testi provvisori** di privacy e termini (`LEGAL` in JS) — da far validare.
- **`requestPdf()`** è un segnaposto: manca l'endpoint che genera il PDF e lo invia.

## Cosa NON toccare nel redesign
`QUESTIONS`, `DIMS`, matematica benchmark/target, branching per segmento — è il dominio del collega consulente. Il redesign lavora su markup + CSS + funzioni di render delle schermate.
