# Changelog redesign — schermata per schermata

Registro leggibile per la review con l'agenzia. Per ogni schermata: cosa c'era,
cosa è stato cambiato, stato dell'approvazione.

Stati: 🔲 da rivedere · 🟡 in discussione · ✅ approvata · ↩️ da correggere

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
  destra della sezione completata (`stepperSweep`, ~520ms) prima di avanzare.
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
