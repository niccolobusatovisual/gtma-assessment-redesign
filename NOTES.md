# Autovalutazione 2R (ex GoToMarket Assessment) — note tecniche

> **Stato al 2026-09-10, prima di un `/clear` in chat.** Da qui in poi il file
> riflette lo stato vero: backend PHP+MySQL scritto e testato in locale,
> caricato su `provareport2r`, verifica finale nel browser non completata (vedi
> "Backend PHP + MySQL" più sotto). Chi riprende da qui legga prima quella
> sezione e "Da fare prima della consegna" in fondo.

> **⚠️ REGOLA FISSA — MOBILE-FIRST.** L'assessment gira soprattutto su mobile.
> Ogni modifica va verificata responsive PRIMA di iniziare e ricontrollata a ~390px
> e desktop prima del push. Type fluida (`clamp()`), unità relative, griglie a 1
> colonna sotto ~560px, tap target ≥ 48px, niente scroll orizzontale.

## Provenienza
- URL live originale: https://gtma.studioguzzetti.it/?c=GMA-2026 (ferma al
  2026-09-03, non più il punto di riferimento — vedi sotto).
- `original-2026-09-03.html` = copia esatta scaricata il 2026-09-07 (header `last-modified` del server: 2026-09-03). Baseline "prima", da non modificare. Copia identica anche in `../_live-snapshot-2026-09-03.html`.
- `index.html` = copia di lavoro del redesign. Fino al 2026-09-09 era anche
  il file pubblicato tale e quale sul banco di prova GitHub Pages; dal
  2026-09-10 **non è più il file servito direttamente**: `server/index.php`
  lo usa come template (`app/pagina.html`) e ci inietta domande/valori dal
  database prima di mandarlo al browser. Il preview GitHub Pages continua a
  mostrare `index.html` grezzo, con dati scritti a mano — utile per un
  colpo d'occhio veloce su grafica/CSS, non per collaudare il backend.
- Fino al 2026-09-09 era un file **statico** senza dipendenze server; da
  2026-09-10 richiede PHP + MySQL per funzionare per intero (vedi "Backend
  PHP + MySQL" più sotto). Nessun build, nessun framework: resta HTML + CSS
  + JS scritti a mano.

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

## Backend PHP + MySQL (2026-09-10) — sostituisce `gma_api.php`

Architettura decisa con Michele: tre sottodomini Keymove, un solo database
MySQL (`mobilica_prvadm` per la prova).
- `provareport2r.keymove.it` — banco di prova, protetto da password (da
  attivare sull'hosting, non ancora fatto), non indicizzato.
- `report2r.keymove.it` — sito pubblico, non ancora caricato.
- `adminreport2r.keymove.it` — pannello per Michele, **non ancora costruito**
  (vedi "Da fare" in fondo). Oggi domande e valori si modificano solo a mano
  nel database.

**Come sta il codice:**
- `db/` — schema SQL (`01_configurazione.sql`, `02_dati.sql`, `03_admin.sql`),
  seed generato da `index.html` (`estrai_seed.js` → `04_seed_configurazione.sql`),
  e `setup_locale.sh` per ricreare tutto sul Mac (MySQL locale via Homebrew,
  già installato). Un solo database con 15 tabelle (non più tre: vedi sotto).
  Password locali in `db/.env.locale`, **gitignored**.
- `server/` — l'applicazione PHP. `index.php` legge la versione del
  questionario dal database (bozza se siamo su `prova` e una bozza esiste,
  altrimenti la pubblicata), inietta `DIMS`/`QUESTIONS`/`DIM_SHORT` dentro
  `app/pagina.html` (= una copia di `index.html`) negli stessi marcatori
  `@@DIMS_START@@` ecc. usati prima dal vecchio pannello Setup, e serve il
  risultato. `api/compilazione.php` riceve il POST finale (onboarding +
  punteggi di sezione) e lo scrive nel database — ricalcola benchmark/target
  lato server, non si fida di quelli mandati dal browser. `app/config.php`
  contiene le credenziali (fuori dal repository, solo in
  `Keymove/export/<ambiente>/app/config.php`). `crea_cartella.sh` compone la
  cartella pronta per FileZilla a partire da questi file + `index.html`.
- **Le risposte dell'utente non si salvano.** Scelta di Michele (2026-09-10):
  il database tiene solo dati onboarding, punteggio complessivo ed esito,
  punteggio per sezione con benchmark/target, consenso, documenti. Non il
  dettaglio domanda-per-risposta. Conseguenza: qualsiasi documento con le
  risposte del cliente si può generare **solo al momento dell'invio**, non
  più dopo — se in futuro serve rigenerarlo, va rivista questa scelta.
- **Punteggio delle risposte**: non più automatico per posizione
  (1,25·2,5·3,75·5 su 4 opzioni). Ogni risposta ha un valore libero scelto da
  chi modifica il questionario (`answerScore()` in `index.html`, divide per
  il massimo della domanda). Il seed carica 1,2,3,4… così i punteggi restano
  identici finché nessuno li cambia dall'admin (che non esiste ancora).
- **Un solo database, non tre.** Il progetto iniziale prevedeva tre database
  separati per isolare i permessi (config/dati/admin); Michele ne ha creato
  uno solo in phpMyAdmin, quindi lo schema è stato riunito. Conseguenza
  importante: **i permessi MySQL ora coprono tutte le tabelle**, quindi che
  il sito pubblico non tocchi il questionario e non legga gli account admin
  lo garantisce solo il codice PHP (`api/compilazione.php` fa **solo**
  INSERT su `compilazioni`/`risultati_sezione`, mai UPDATE su
  `domande`/`sezioni`) — non c'è più una rete di sicurezza a livello di
  database. Da tenere a mente se in futuro si scrive il pannello admin.
- **Verifica fatta:** `php -l` su tutti i file, test via `curl` di
  `api/compilazione.php` (12 casi: salvataggio valido, richieste da altra
  origine, consenso mancante, versione non attuale, punteggi/e-mail/canali
  non validi…), caricamento di `index.php` via `curl` (pagina completa,
  200 OK, `DIMS`/`QUESTIONS` letti dal database, `X-Robots-Tag: noindex` su
  `prova`). **Non verificato:** il flusso completo dentro un vero browser
  (onboarding → domande → risultati → salvataggio) sulla versione con
  database — lo script Playwright preparato per farlo si è bloccato
  all'avvio (probabile conflitto con altri processi Chrome della sessione,
  non un errore del codice) e non è stato rilanciato prima del `/clear`.
  **Da rifare prima di considerare `provareport2r` collaudato.**
- **Pannello Setup rimosso ma non ripulito.** I due ingressi nascosti (`?setup`,
  5 click sul logo RR) sono stati tolti, quindi il pannello non si apre più.
  Il markup e le funzioni JS (`openSetup`, `switchSetupTab`,
  `renderQuestionsEditor`, `addQuestion`, `downloadConfigured`, il tab
  Database con `loadDatabase`/`deleteEntry`…) sono ancora fisicamente nel
  file, morti. Da eliminare quando si è sicuri che non serva tornare
  indietro velocemente.
- **CTA "Contattaci" e link studioguzzetti.it** ancora presenti nel markup:
  quando l'autovalutazione sarà a marchio Keymove puro, questi riferimenti
  vanno rivisti insieme ai contatti reali (vedi anche la mail brandizzata,
  ferma in attesa di link prenotazione/telefono/mittente).

## Note da segnalare
- La copia è uno snapshot: se il collega continua a modificare il file live
  su studioguzzetti.it, questa versione va riallineata (basta ri-scaricare
  l'URL) — ma ormai `index.html` è avanti rispetto a quel file, con logica
  di punteggio diversa e senza pannello Setup.

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

## Sicurezza (passata del 2026-09-11)

Cosa c'è e cosa **non** c'è, per non dare per scontate protezioni che non esistono.

**Difese attive**
- Escaping: `_esc()` per il contenuto HTML e gli attributi, `_escJs()` per i
  valori dentro una stringa JS in un attributo evento. Tutto ciò che arriva dal
  database (`sezioni.nome/descrizione`, `domande.testo`, `risposte.testo`,
  `sezioni.chiave`) passa da una delle due. **Se aggiungi un nuovo punto che
  scrive dati del DB in `innerHTML`, devi usarle.**
- CSP: `<meta>` nella pagina + header in `server/index.php`. Niente origini
  esterne (tutto inline o `data:`), niente `unsafe-eval`.
  `unsafe-inline` è obbligatorio finché esistono gli `onclick=` inline.
- `frame-ancestors 'self'` + `X-Frame-Options: SAMEORIGIN`: la pagina si può
  mettere in un iframe solo dallo stesso dominio. **Per incorporarla su
  `www.keymove.it` va aggiunta quell'origine in `server/index.php`**, altrimenti
  l'iframe resta bianco.
- API: `api/compilazione.php` usa prepared statement, valida ogni campo e
  controlla `Origin`. Benchmark e target li rilegge dal database, non si fida
  del browser.

**Limiti noti, da non confondere con protezioni**
- Il gate password del Setup (`_kh` + `checkPwd()`) è **lato client**: si aggira
  da console con `_openSetupPanel()`. Serve solo a evitare aperture accidentali.
  La protezione vera arriverà con il login di `adminreport2r`.
- Il codice d'accesso `?c=` è lo stesso discorso: `s_locked` è solo una classe
  CSS, le altre schermate sono già nel DOM. In produzione `questionario.php`
  lo azzera comunque (`var ACCESS_CODE = '';`), quindi oggi non protegge nulla.
  La protezione reale del banco di prova è la password di cartella
  sull'hosting (punto 2 di "Da fare").
- `Sec-Fetch-Site` in `api/compilazione.php` ha un fallback permissivo
  (`?? 'same-origin'`). Lasciato così apposta: irrigidirlo bloccherebbe Safari
  sotto la 16.4. Il controllo su `Origin` copre già il caso CSRF vero.

## Da fare prima della consegna

Ordine ragionevole per riprendere:

1. **Rilanciare la verifica browser** del backend su `provareport2r` (vedi
   sopra) — è il passo bloccato prima del `/clear`, va confermato che
   l'intero flusso onboarding → domande (filtrate B2B/B2C) → risultati →
   salvataggio funzioni davvero cliccando, non solo via `curl`.
2. **Attivare la password** sul sottodominio `provareport2r` (protezione
   cartella lato hosting — Michele deve farlo, non è nei file).
3. **Ruotare la password del database** `mobilica_prvadm`: è passata in
   chiaro in chat una volta per i test. Dopo il collaudo, cambiarla e
   aggiornare solo `Keymove/export/provareport2r/app/config.php` (mai nel
   repository).
4. **Cambiare la password del Setup.** Era `KeyMoveMMP`, scritta in chiaro
   (base64) nel file: ora nel sorgente c'è solo l'impronta SHA-256, ma la
   stringa originale resta nella history dei commit di un repo **pubblico**,
   quindi si recupera con `git log -p`. Genera la nuova impronta con
   `printf '%s' 'NUOVA_PASSWORD' | shasum -a 256` e sostituisci il valore di
   `_kh` in `index.html`.
5. **Pannello admin** (`adminreport2r`) — ancora da costruire: login,
   moduli per modificare testi/domande/risposte/benchmark/target/sezioni
   (limiti già discussi: 3–8 sezioni, ogni sezione nuova richiede benchmark
   B2B/B2C + 14 target), flusso bozza → pubblica, storico modifiche con
   ripristino, gestione account (tutti possono creare utenti e pubblicare).
6. **Decidere come `provareport2r` diventa `report2r`** per HTML/CSS/JS:
   Git deploy vs copia file — lasciato aperto apposta, va deciso prima del
   primo rilascio pubblico.
7. **Endpoint PDF + mail brandizzata**: `requestPdf()` è ancora un
   segnaposto (mostra solo un toast). Tre documenti pianificati (PDF
   cliente, PDF interno, presentazione stile slide) via motore Chrome-based
   (PDFMonkey proposto, non confermato) — vedi la memoria di progetto per il
   ragionamento completo. La mail al cliente è ferma in attesa di link
   prenotazione, telefono/WhatsApp e a nome di chi firma.
8. **Iubenda**: da attivare per privacy/T&C + registro consensi; sostituirà
   i testi provvisori in `LEGAL` (JS).
9. **Pulizia**: barra DEV (`?dev`, `initDev/devGo/devFill`, `#devbar`),
   markup e funzioni morte del vecchio pannello Setup (vedi sopra).
10. **Repo GitHub pubblico**: da rendere privato o eliminare quando tutto
   gira sui sottodomini Keymove — oggi è ancora il banco di prova UX/UI e
   resta comodo per il preview automatico.

## Cosa il redesign NON tocca più da solo
Con l'accordo del collega consulente (2026-09-10), `QUESTIONS`/`DIMS`/benchmark/
target sono passati dal codice al database — non è più territorio esclusivo
suo, ma restano **dati**, non markup: il redesign continua a lavorare su
markup + CSS + funzioni di render, senza reinventare punteggi o branching a
mano nel codice.
