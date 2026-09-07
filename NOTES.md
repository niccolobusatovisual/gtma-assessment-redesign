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

## Com'è fatto (un solo file, 2280 righe, ~323 KB)
| Righe | Contenuto |
|-------|-----------|
| 8–29 | `<script>` Chart.js 4.4.0 incollato inline (CDN bloccato dalla CSP del server) |
| 30–375 | `<style>` — tutto il CSS inline (~345 righe) |
| 377–573 | markup `<body>` — le schermate |
| 574–2134 | `<script>` — logica applicativa in JS vanilla (~1560 righe): `QUESTIONS`, `DIMS`, scoring, branching per segmento, render schermate, admin |

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

## Note da segnalare
- La **write key `GMA_WRITE_2026` è in chiaro** nel JS lato client, insieme a un pannello admin nello stesso file. Chiunque può leggerla e scrivere sull'API. Bassa gravità ma reale — da girare al referente.
- La copia è uno snapshot: se il collega continua a modificare il file live, questa versione va riallineata (basta ri-scaricare l'URL).

## Cosa NON toccare nel redesign
`QUESTIONS`, `DIMS`, matematica benchmark/target, branching per segmento — è il dominio del collega consulente. Il redesign lavora su markup + CSS + funzioni di render delle schermate.
