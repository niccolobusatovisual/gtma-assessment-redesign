# Changelog redesign — schermata per schermata

Registro leggibile per la review con l'agenzia. Per ogni schermata: cosa c'era,
cosa è stato cambiato, stato dell'approvazione.

Stati: 🔲 da rivedere · 🟡 in discussione · ✅ approvata · ↩️ da correggere

---

## Round correzioni Nick — 2026-09-09 (3) — 🟡 in discussione

Sette interventi. Nessuna modifica a `QUESTIONS`, `DIMS`, scoring o branching.

**1. Header.** Tolta la scritta "GoToMarket Assessment", al suo posto il **logo RR**
(SVG fornito da Nick) **ricolorato in nero** via `currentColor` (`.rr-logo{color:var(--ink)}`).
Il divisore verticale fra i due loghi resta, alzato a 30px per accompagnare il marchio tondo.

**2. Landing (`s0`).** Rimossa la pill "GoToMarket Assessment · accesso su invito" e il
bottone secondario "Parla con lo studio". Resta **una sola CTA grande** (`.w0-cta-main`:
60px di altezza, pill, ombra rossa). Ritmo verticale compresso (padding, margini, titolo su
2 righe con `max-width:24ch`) → **zero scroll su desktop**: verificato 0px di overflow a
1440×900 **e** 1280×800. In fondo un blocco **privacy / termini**: riga di accettazione con
due link che aprono una **modale** (`openLegal`), più una riga con titolare e finalità.
⚠️ I testi di privacy e T&C sono **provvisori** (segnalati da un badge "Bozza provvisoria"
dentro la modale): servono a vedere l'impaginato, vanno fatti validare.

**3. Onboarding, primo passo.** Tolta la card "Entrambe" → scelta secca **B2B / B2C** su due
colonne, **senza descrizioni**: solo l'icona e la sigla, resa più grande (`.model-cards`).
Il ramo `both` resta nel codice ma non è più raggiungibile, così nulla a valle si rompe.

**4. Messaggio d'errore.** Da box ambra a **outline rossa + testo rosso su fondo rosso
chiaro** (`--brand` / `--brand-wash`), angoli 12px, icona d'allerta e ombra morbida, con una
piccola animazione d'ingresso. L'icona è un `::before` con background-image perché
`onbWarn()` riscrive il `textContent` del box.

**5. Header di sezione (domande).** Via il **box nero**: ora è pulito sullo sfondo chiaro.
L'alternanza nero/bianco resta nel tag di sezione, diventato una **pillola scura con testo
bianco** ("SEZIONE B · 1 DI 6"), titolo nero più grande, descrizione grigia. Di conseguenza
la prima card domanda non è più agganciata all'header: tutti gli angoli tondi.

**6. Risultati — dettaglio rimosso.** Eliminata l'intera lista "Dettaglio per dimensione"
con percentuali, benchmark, target e legenda. Resta il **punteggio complessivo**, ora molto
più grande (`clamp(3.6rem,13vw,5rem)`), con **barra di stato** sotto e badge di
posizionamento. Il numero **conta da 0** al valore finale (`gcount`, 1,4s) e la barra cresce
da 0.

**7. Risultati — panoramica azienda.** La vecchia griglia chiave/valore è sostituita dalla
**stessa carta d'identità del riepilogo onboarding** (`.idc`), in versione `.idc-compact`
(padding, icone e testi ridotti) e **in sola lettura**: nessun pulsante "Modifica", niente
hover cliccabili.

**8. Risultati — grafico interattivo.** Il radar ora è cliccabile: `onClick` sul grafico
apre il **pannello di dettaglio** della dimensione toccata. Sotto al grafico ci sono anche
**6 pastiglie** colorate (`.dchip`) che fanno la stessa cosa — servono su mobile, dove i
punti del radar sono bersagli minuscoli. Il pannello mostra nome, descrizione, punteggio
grande, barra con i marker di benchmark e target, e si chiude con la ✕.
**Animazioni GSAP**: il grafico entra ruotando e ingrandendosi (`back.out`), le pastiglie a
cascata, il pannello sale in `power3.out` con la barra che cresce e la percentuale che conta.

Mobile-first: verificato via CDP a **390px** (landing, onboarding, errore, domande,
risultati) — **zero scroll orizzontale**; e a 1280 / 1440px. Nessun errore in console.

Da decidere con Michele: (1) i testi legali provvisori; (2) l'interpretazione di "testo
alternato nero/bianco" nell'header di sezione (ora: pillola scura + titolo nero).

---

## Favicon Keymove — 2026-09-09 — 🟡 in discussione

Aggiunta la favicon: la "k" del logo Keymove (rosso brand `#f71e44`), fornita da
Nick come SVG. Incollata **inline come data URI** in `<head>` (`<link rel="icon"
type="image/svg+xml">`) — nessun file esterno, compatibile con la CSP del server
e con l'hosting a file singolo. `viewBox` reso quadrato con padding uniforme così
non viene tagliata nella tab. Sorgente anche come `favicon.svg` (non referenziato,
solo archivio). Supportata da Chrome/Firefox/Edge e Safari 16.4+.

---

## GSAP — logo che si disegna + tendina a curva SVG — 2026-09-09 — 🟡 in discussione

**Agg. 2026-09-09 (2): plugin installati.** Core GSAP portato da 3.12.5 a **3.13.0**
e aggiunti inline **DrawSVGPlugin** + **MorphSVGPlugin** 3.13.0 (dal 2025 gratuiti).
Gli effetti sono ora **attivi**: il logo si disegna davvero all'avvio, la tendina
usa il morph "vero" (curva organica, non la sola interpolazione del `d`).
Verificato via CDP a 1280px: nessun errore in console, logo che chiude pulito
(fill pieno, stroke a 0), tendina che copre e atterra su `s1` con overlay
nascosto. Il resto della descrizione qui sotto resta valido.



Cablati due effetti richiesti da Michele/Nick, **con fallback**: girano solo se
i file dei plugin sono presenti, altrimenti il sito è identico a prima.

- **`registerPlugin` + flag** `HAS_DRAWSVG` / `HAS_MORPHSVG` nel blocco helper
  GSAP: registrano DrawSVG / MorphSVG **se** i rispettivi file inline ci sono.
- **`animateLogoDraw()`** — il wordmark `#kmLogo` (7 path *pieni*, niente
  stroke) all'avvio prende un contorno rosso, si **disegna** (`drawSVG`,
  stagger .1s), poi compare il riempimento e lo stroke sparisce. Chiamata in
  init. Senza DrawSVG: **logo invariato** (no-op).
- **`wipeTransition(cb)` + overlay `#wipe`** — tendina rossa a tutto schermo
  (`<svg preserveAspectRatio="none">` + un `<path>` `--brand`): la curva sale →
  copre → `cb()` cambia schermata → esce verso l'alto. Con MorphSVG il morph è
  "vero"; senza, GSAP core interpola il `d` (4 forme, stessa struttura). Senza
  GSAP / `prefers-reduced-motion`: cambio schermata immediato.
- **`startAssessment()`** — il bottone "Inizia l'assessment" della welcome (`s0`)
  ora fa `wipeTransition(() => goTo(1))` invece di `goTo(1)` diretto.
- CSS: `.wipe` (fixed, `z-index:9999`, `visibility:hidden` a riposo).

**Per accendere gli effetti** servono 3 passi manuali (in `NOTES.md` →
"Plugin bonus GSAP"): aggiornare il core GSAP a 3.13.5 e incollare inline
`DrawSVGPlugin.min.js` + `MorphSVGPlugin.min.js`. Dal 2025 sono gratuiti.

Verificato via CDP (senza plugin, solo fallback core): welcome → click → la curva
rossa copre → atterra su `s1`, overlay tornato nascosto, logo intatto. Mobile
390px: la tendina copre tutto il viewport, nessun gap. Nessun errore in console.

Da valutare con Michele: durata tendina (ora ~1,1s), se applicarla anche al
passaggio riepilogo → prima sezione domande (`onbNext` ultimo step).

---

## Sezione domande → stile reference "MCT Mock Tests" — 2026-09-09 — 🟡 in discussione

Restyle della sola **sezione domande** (`.q-row` / `.q-label` / `.a-cell`) sul
riferimento mandato da Nick. **Progress bar, palette e logica (scoring /
branching / rivelazione progressiva) invariate.** Solo CSS + due `innerHTML` in
`renderQuestions`.

- **Card unica per domanda**: da card bianca con ombra netta a **superficie
  bianca con ombra morbida** (`0 12px 28px -14px`), angoli 14px. Il primo blocco
  resta agganciato all'header scuro `.sec-head` (angoli alti piatti) — invariato.
- **Eyebrow**: da `1 / 3` minuscolo maiuscolo tracciato a **"Domanda 1 di 3"**,
  peso 500, colore `--text-light` (come il "Question 1" del reference).
- **Testo domanda**: peso 700 → **600**, size fluida `clamp(.98rem,2.4vw,1.06rem)`,
  colore `--ink`.
- **Divisore** fra domanda e opzioni: hairline `--gray` con più aria sopra/sotto.
- **Opzioni a riga piena**: radio **spostato a destra** (era a sinistra),
  `justify-content:space-between`; riga non selezionata = fondo `#f6f4f5`, testo
  **muted** `--text-light`, radio vuoto Ø19; hover = fondo bianco + bordo +
  ombrina; **selezionata = rosso del logo** (richiesta Michele 08/09, invariata)
  + alone rosso morbido + radio bianco pieno. Tap target ≥ 52px.
- Regole mobile duplicate rimosse: le regole base ora sono fluide (`clamp`) e
  mobile-first da sole.

Mobile-first: verificato via CDP a **390px** (stato vuoto, stato con risposta +
domanda successiva rivelata) e a **1280px** — zero scroll orizzontale, radio
allineato, prima card agganciata all'header.

Da valutare con Michele: (1) testo opzioni non selezionate muted vs. pieno;
(2) se la selezionata deve restare rosso pieno o passare a bianco + barra
d'accento a sinistra (come da reference letterale); (3) header scuro `.sec-head`
lasciato com'è — nel reference è testo chiaro.

---

## Riepilogo → "carta d'identità" azienda — 2026-09-08 — 🟡 in discussione

Il passo finale `review` non è più un elenco chiave/valore ma una **card
identità** (ispirazione: profile card tipo Dribbble mandata da Nick). Solo
`onbRenderReview()` + CSS `.idc*`; struttura dati e flusso (`onbEditStep` /
`onbEditReturn`) invariati.

- **Header**: emblema con l'**icona del settore** (riquadro arrotondato brand,
  `sectorIco()`) in alto a sx; **badge scuro** col modello (`B2B` / `B2C` /
  `B2B · B2C`) in alto a dx; **nome azienda** grande bold corsivo + riga
  ruolo · settore sotto.
- **Barra split B2B/B2C** bicolore (inchiostro / rosso) sotto l'header, solo se
  modello = "Entrambe", con le percentuali.
- **3 stat-tile** icona + numero + label: Fatturato · Dipendenti · Clienti/anno
  (dal passo Dimensione). Stato vuoto = "—". Su < 400px vanno a 2 colonne e la
  terza diventa una riga piena.
- **Righe dettaglio** con chip-icona brand: Referente · Canali (come pill) ·
  Obiettivo · Descrizione.
- **Ogni elemento è modificabile**: emblema → Settore, badge → Modello, tile →
  Dimensione, righe → passo relativo. Le tile mostrano una matita (sempre
  visibile su touch, in hover su desktop); le righe tengono il bottone
  "Modifica". `onbEditStep(i)` → correggi → torni dritto al riepilogo.

Mobile-first: verificato via CDP a 360 / 390 / 1280px su card piena e card con
soli campi obbligatori (stati vuoti) + round-trip di modifica di una tile →
zero scroll orizzontale, ritorno al riepilogo OK.

---

## Round feedback Michele — 2026-09-08 — 🟡 in discussione

Sette richieste dalla review vocale. Nessuna modifica a `QUESTIONS`, `DIMS`,
scoring o branching: solo markup + CSS + funzioni di render.

1. **Colore della risposta selezionata = rosso del logo.** Le celle risposta
   (`.a-cell.sel`) ora hanno sfondo `--brand` (`#f71e44`, il rosso del logo)
   invece di `--brand-dark`. Tolto lo stile inline `background:var(--brand-dark)`
   da `renderQuestions`/`selectOpt`: il colore è tutto in CSS.

2. **Icone sui settori.** Il passo "Settore" dell'onboarding: le chip di
   suggerimento ora hanno un'icona lineare dedicata per settore
   (`SECTOR_ICO` + `sectorIco()`), stile coerente col resto (`_ic`). La chip
   che corrisponde al valore digitato si evidenzia (`.onb-chip.on`).

3. **Niente più "scatto" alla selezione.** Le selezioni dentro un passo
   dell'onboarding (`onbSetModel`, `onbToggleCb`, `onbSetGoal`, `onbChip`)
   aggiornavano l'interfaccia ri-eseguendo `renderOnbStep()` → l'intero passo
   veniva ricostruito e l'animazione `onbIn` ripartiva (sembrava un reload).
   Ora aggiornano **solo** la card/chip toccata (via `data-*` attributi).
   L'animazione CSS `onbIn` è stata spostata su `html.no-gsap .onb-step`
   (fallback); con GSAP la transizione fra passi è esplicita.

4. **Domande una alla volta.** Le sezioni mostravano tutte le domande insieme.
   Ora `syncQuestionReveal()` mostra solo le domande già risposte + la prima
   ancora senza risposta; quando ne rispondi una, la card successiva compare
   (fade GSAP) e viene portata nel viewport. "Continua" resta bloccato finché
   non sono tutte compilate (invariato).

5. **Schermata risultati riorganizzata.** Layout a due colonne su desktop:
   **grafico grande a sinistra** (`.chart-box` alto fino a 620px,
   `maintainAspectRatio:false`, sticky durante lo scroll) e **statistiche a
   destra** (tessera punteggio complessivo + dettaglio per dimensione, più
   compatto). Su < 900px torna a colonna singola. **CTA resa prominente**:
   blocco rosso pieno `.res-cta` subito sotto l'hero, titolo grande + bottone
   bianco grande "Prenota il confronto"; la CTA in fondo (`nav`) resta.
   Stampa/PDF: il layout torna a colonna singola (regole `@media print`
   aggiornate, + fix `#s9` → `#s_results`).

6. **Riepilogo di fine onboarding.** Nuovo passo finale `review`: card con
   tutti i dati inseriti, ognuno con "Modifica" che porta **dritto** a quel
   passo (`onbEditStep`); al primo "Continua"/"Indietro" valido si torna al
   Riepilogo (`onbEditReturn`), senza indietro-indietro. "Inizia l'assessment"
   parte da qui.

7. **GSAP.** Libreria `gsap` 3.12.5 **incorporata inline** nell'`<head>`
   (stesso motivo di Chart.js: la CSP `script-src` del server blocca i CDN).
   Usata per: transizione fra i passi dell'onboarding, comparsa progressiva
   delle domande, ingresso e conta-su dei numeri nella schermata risultati,
   pulse della CTA. Tutti gli helper (`gfrom` / `gto` / `gcount`) degradano
   senza errori se GSAP non è caricato e rispettano `prefers-reduced-motion`.

Mobile-first: verificato a 390px (viewport reale via CDP) su onboarding,
riepilogo, domande e risultati — **zero scroll orizzontale**. Ricontrollato a
1120 / 1440.

---

## Baseline
- **2026-09-07** — importato snapshot del file live (`last-modified` server: 2026-09-03)
  come `original-2026-09-03.html`. `index.html` parte identico.

## Palette Keymove — v1 (2026-09-07) — 🟡 in discussione
Sostituito il tema navy/oro/blu di default con i colori del brand Keymove.
Nessuna modifica a domande, scoring o branching.

- **Sistema di token** rifatto in `:root`: `--brand #f71e44`, `--brand-dark #d4123a`,
  `--brand-wash #fdeaee`; `--ink #1c1c22` (ex navy, ora near-black per header/struttura/titoli);
  neutri caldi (`--gray #e6e3e7`, sfondo `#faf8f8`, testo sec. `#5c5c68`); alias legacy
  (`--navy`, `--gold`, `--blue`) rimappati sul nuovo sistema così le ~80 chiamate esistenti
  si aggiornano da sole.
- **CTA primario** → rosso Keymove (era navy). Secondario → outline inchiostro.
  "Condividi" (btn-gold) → fill inchiostro. Focus dei campi → bordo rosso.
- **Hero** (welcome + risultati) → gradiente carbone→bordeaux scuro (era blu navy).
- **Header / progress / strip dimensioni / sum-card** → near-black con accenti rossi.
- **Grafico radar**: serie "Azienda" = rosso brand (era navy, così il dato dell'utente
  risalta); "Benchmark" = grigio `#8a8a94` (era arancio); "Target" = inchiostro (era `#777`).
- **Stati risultati** (badge + legenda): trio semantico desaturato per non confliggere col
  rosso brand — sotto benchmark terracotta `#a83a22`, sopra benchmark ambra `#8a5f12`,
  sopra target verde `#188a56`.
- **Colori delle 6 dimensioni**: invariati, tranne "Organizzazione" da rosso `#ef4444`
  (troppo simile al brand) a azzurro `#0ea5e9`.

Da valutare insieme: (1) hero rosso pieno vs carbone; (2) scala risultati semantica vs
monocromatica sul rosso; (3) rivedere le 6 tinte categoriali come set coerente;
(4) font del brand (ora ancora `Segoe UI`/sistema).

---

## Schermate

### 1. Gate codice d'accesso (`s_locked`) — 🔲
_(da compilare)_

### 2. Welcome / presentazione (`s0`) — 🟡 in discussione

**Rifatta stile landing (2026-09-07)** — ispirazione quso.ai + ticketapp.
Da hero scuro a **layout chiaro e centrato**, molto più arioso:
- badge pill in alto ("GoToMarket Assessment · accesso su invito")
- **titolo grande** a due toni con parola-chiave evidenziata in box arrotondato rosso
  ("…la tua `organizzazione commerciale?`")
- sottotitolo sintetico con i numeri reali (6 aree, N domande, ~20 min) iniettati da `buildWelcome`
- doppia CTA: **Inizia l'assessment** (primaria rossa) + **Parla con lo studio**
  (secondaria, link a studioguzzetti.it/#contatti)
- 3 card informative (Come funziona / Cosa ottieni / Il passo dopo) — eyebrow + frase
- strip delle 6 aree analizzate (pill), texture a puntini sfumata dietro il titolo
- Rimossi `.welcome-hero`, `.welcome-stats`, `.ws`; `.pill` ristilizzata per tema chiaro.
  Gli id `#welcomePills` / `#welcomeDims` / `#welcomeQs` conservati → `buildWelcome` invariato.

Mobile-first: verificato a 320 / 390 / 768 / 834 / 1280px, zero scroll orizzontale,
CTA full-width < 620px, card a 1 colonna, type fluida `clamp()`.

Da valutare: copy del titolo, testo del badge, aggiungere elementi decorativi
(mini-preview del report) come nelle reference, font display.

### 3. Sezione A — dati compilatore + azienda (`s1`) — 🟡 in discussione

**Onboarding a schermate (2026-09-07).** Il form unico è diventato un onboarding
"una schermata alla volta" (ispirazione Lemonade / Typeform), minimal, con icona +
domanda + card grandi selezionabili. Mobile-first: card a piena larghezza < 560px,
CTA full-width, tap target ≥ 50px, type fluida `clamp()`, verificato a 390px e desktop.

8 passi con indicatore a pallini in alto:
1. **Modello di business** — 3 card grandi B2B / B2C / Entrambe. Se "Entrambe" →
   slider quota B2B/B2C. È il primo passo perché da qui il questionario si adatta al segmento.
2. I tuoi dati (nome + e-mail compilatore, con validazione e-mail)
3. Azienda + ruolo
4. Settore (con chip di suggerimento rapido)
5. Dimensione — fatturato / dipendenti / clienti nuovi (tutti facoltativi)
6. Canali commerciali — card multi-selezione
7. Obiettivo commerciale — card singola scelta
8. Descrizione libera (facoltativa, con "Salta")

**Implementazione senza rischi a valle:** i campi originali `#f_*` e `#cb_*` restano
nel DOM come hidden; l'onboarding li popola. `nextFromA`, `resolveSegment`,
`saveAssessment`, `resetAll` invariati. Nessuna modifica a domande/scoring/branching.
`goTo(1)` renderizza l'onboarding; `resetAll` lo riazzera.

Da valutare: numero di passi (8 — accorpare?), copy delle domande, icone (Rivenditori
e Punti vendita ora condividono l'icona), se il passo "Dimensione" va spezzato.

### Progress bar — 🟡 in discussione (2026-09-07)

Sostituita la barra unica continua con una **barra segmentata stile "1-A"**
(segmenti con titolo di sezione sopra, riempimento per sezione, pallino sul punto
corrente). **Due istanze distinte**, stessa componente `#stepper`:
- **Onboarding** (screen 1): 8 segmenti = i passi (`Modello · Contatti · Azienda ·
  Settore · Dimensione · Canali · Obiettivo · Note`). Passo corrente al 50%.
- **Assessment** (screen 2–7): 6 segmenti = le dimensioni. Il segmento corrente si
  riempie in base alle risposte date (`ans/tot`), aggiornato a ogni click.
- Welcome: nessuna barra. Risultati: tutti i segmenti pieni + "Assessment completato".

Rimossi: la vecchia `#progFill/#progLabel`, i pallini dentro la card onboarding
(`.onb-dots`) e la vecchia strip dimensioni (`#dimStrip`, ora `display:none`) —
tutti ridondanti con lo stepper.

Mobile-first (< 720px): i titoli spariscono, restano i mini-segmenti + una riga
"Sezione X di Y · Nome — n/tot risposte". Verificato a 390 / 1280px.

**Rev. 2026-09-07 (2):**
- Lo stepper ora **aggiorna gli stili invece di ricostruire l'HTML** → le
  transizioni CSS animano (dot e riempimenti con `transition`).
- **Assessment**: una sola dicitura (nome sezione, forma breve) sopra il puntino,
  che lo **segue** mentre il segmento si riempie con le risposte. Posizione del
  flag clampata al 6–94% per non uscire dalla barra.
- **Onboarding**: su *Continua* il puntino fa uno **sweep animato** da sinistra a
  destra della sezione completata.
  - **Fix 2026-09-07**: rimosso `stepperSweep` + il `setTimeout(520ms)` che
    ritardava il cambio contenuto. Ora `onbNext` avanza subito: il contenuto
    cambia all'istante e il puntino (elemento persistente) scorre da solo con la
    transizione CSS su `left` (0.33s, ease-out). Sweep che parte nell'istante del
    click, zero delay.
- **Mobile**: la progress bar è **sticky in alto** (`position:sticky;top:0`),
  l'header diventa statico e scorre via. Verificato: dopo scroll la barra resta a
  `top:0`.

### 5. Risultati — radar + card punteggio + sintesi (`s_results`) — 🔲
_(da compilare)_

### Header + accesso Setup — 🟡 in discussione (2026-09-07)

**Header rifatto** (ispirazione "Simple LinkedIn Cover"): **logo Keymove** (wordmark
rosso, SVG inline) + divisore + kicker "GOTOMARKET" / titolo "Assessment" + a destra
un descrittore muted ("Analisi dell'organizzazione commerciale", nascosto su mobile).
Rimossi la sigla "GMA" e il pulsante ⚙️ Setup.

**Accesso Setup nascosto ai clienti** — nessun pulsante visibile (né header né
schermata locked). Ingressi interni: `?setup` nell'URL, oppure **5 click rapidi sul
logo**. Resta il modal password come gate. Vedi `NOTES.md`.

Schermata `s_locked` ripulita (usava `.welcome-hero` rimossa) → card chiara `.locked-box`.

Mobile: header compatto (logo + "Assessment"), verificato a 390px.

**Rev. 2026-09-07 (4) — set icone unico:**
- Tutte le icone su un unico stile (reference allegata): linea singola, angoli e
  terminazioni arrotondati, griglia 24, stroke 1.8 (medie/grandi) / 2 (pulsanti).
- Helper JS: `_ic`/`_bi` (generatori) + mappe `ICO` (step onboarding), `BI`
  (frecce/check pulsanti), `ICN` (lock, eye, sliders, chart, edit, database,
  target, key, info, trash, x, chevron, refresh, copy, check, alert, share).
- **Emoji rimosse ovunque** → SVG in stile: schermata locked (🔒), toast
  (`showToast(msg, type)` ora antepone un'icona check/alert/x + variante colore),
  modale password (🔒 + toggle 👁/👁‍🗨), pannello Setup (⚙️ + tab 📊📝🗄 + header
  sezione 📊🎯🔐💡), modale condivisione (📤), editor domande (🗑 ✕ ▶▼), pulsanti
  admin (🔄 📋). Toast e locked-icon ora a pillola / tinta brand.

**Rev. 2026-09-07 (3):**
- **Sistema pulsanti** rifatto sulla reference (3 varianti a pillola, icona + testo,
  hover): `btn-primary` = solid rosso; `btn-secondary` = outline rosso; `btn-ghost`
  (ex `btn-gold`) = pill chiaro con ombra morbida e icona rossa. Icone SVG su tutti
  i pulsanti (frecce, check, mail, share, stampa, x, refresh…). Risultati: "Richiedi
  informazioni" ora solid (CTA principale), "Stampa/PDF" outline, "Condividi" ghost.
- **Progress bar**: track dei segmenti non ancora fatti reso visibile su bianco
  (`#d5cfda`, prima `--gray` quasi invisibile).
- **Header**: tutto **centrato** (logo + "GoToMarket Assessment" su una riga), su
  desktop **più grande** (logo 25px, titolo 1.2rem); su mobile resta compatto.
  Rimosso il descrittore di destra.

**Rev. 2026-09-07 (2):**
- **Header bianco** (era scuro): il logo Keymove rosso risalta; titolo "Assessment"
  in nero, kicker + descrittore in grigio, divisore chiaro, hairline in basso.
- **Progress bar bianca** anch'essa (formano un blocco chrome unico): track grigi,
  fill/dot rossi, label sezione corrente in rosso, "done" in nero.
- **Progress bar sempre fissa in alto anche su desktop** (`position:sticky;top:0`):
  l'header scorre via, la barra resta. Verificato con scroll a 1400px → barra a
  `top:0`. Stesso comportamento su mobile.
