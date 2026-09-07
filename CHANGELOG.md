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

### 4. Schermate domande per dimensione (`sectionScreens`) — 🔲
_(da compilare)_

### 5. Risultati — radar + card punteggio + sintesi (`s_results`) — 🔲
_(da compilare)_

### 6. Admin — Setup benchmark / Database / editor domande — 🔲
_(da compilare)_
