# Changelog redesign — schermata per schermata

Registro leggibile per la review con l'agenzia. Per ogni schermata: cosa c'era,
cosa è stato cambiato, stato dell'approvazione.

Stati: 🔲 da rivedere · 🟡 in discussione · ✅ approvata · ↩️ da correggere

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
