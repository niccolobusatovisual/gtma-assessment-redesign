// Genera 04_seed_configurazione.sql dal questionario attuale in index.html.
// Da rilanciare se in index.html cambiano domande o valori prima della migrazione.
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const src = fs.readFileSync(path.join(__dirname, '..', 'index.html'), 'utf8');

function block(start, end) {
  const a = src.indexOf(start);
  const b = src.indexOf(end);
  if (a < 0 || b < 0) throw new Error('Marcatore non trovato: ' + start);
  return src.slice(a + start.length, b);
}

const DIMS = new Function(block('// @@DIMS_START@@', '// @@DIMS_END@@') + '; return DIMS;')();
const QUESTIONS = new Function(block('// @@QUESTIONS_START@@', '// @@QUESTIONS_END@@') + '; return QUESTIONS;')();

const shortMatch = src.match(/var DIM_SHORT = (\{[^}]*\});/);
if (!shortMatch) throw new Error('DIM_SHORT non trovato');
const DIM_SHORT = new Function('return ' + shortMatch[1])();

const selectMatch = src.match(/<select id="f_obiettivo">([\s\S]*?)<\/select>/);
if (!selectMatch) throw new Error('Elenco obiettivi non trovato');
const OBIETTIVI = [...selectMatch[1].matchAll(/<option>(\d+)\.\s*([^<]+)<\/option>/g)]
  .map(m => ({ ordine: Number(m[1]), testo: m[2].trim().replace(/&amp;/g, '&') }));

const soglie = src.match(/overallPct >= (\d+) \? 'positivo' : overallPct >= (\d+) \? 'in sviluppo'/);
if (!soglie) throw new Error('Soglie dell\'esito non trovate');

DIMS.forEach(d => {
  if (d.tg_b2b.length !== OBIETTIVI.length || d.tg_b2c.length !== OBIETTIVI.length) {
    throw new Error(`Target di "${d.id}" non allineati ai ${OBIETTIVI.length} obiettivi`);
  }
  ['b2b', 'b2c'].forEach(seg => {
    if (!(QUESTIONS[d.id] && QUESTIONS[d.id][seg] && QUESTIONS[d.id][seg].length)) {
      throw new Error(`Nessuna domanda ${seg} per "${d.id}"`);
    }
  });
});

const sql = s => "'" + String(s).replace(/\\/g, '\\\\').replace(/'/g, "''") + "'";
const dec = n => Number(n).toFixed(3);
// derivati dalla posizione, così rilanciando lo script gli identificativi non cambiano
const uid = key => {
  const h = crypto.createHash('sha256').update('km2r|' + key).digest('hex');
  return `${h.slice(0, 8)}-${h.slice(8, 12)}-5${h.slice(13, 16)}-8${h.slice(17, 20)}-${h.slice(20, 32)}`;
};

const out = [
  '-- Generato da estrai_seed.js a partire da index.html: non modificare a mano.',
  'SET NAMES utf8mb4;',
  'START TRANSACTION;',
  "INSERT INTO versioni (id, stato, nota, pubblicata_il) VALUES (1, 'pubblicata', 'Importazione iniziale da index.html', NOW());",
  `INSERT INTO impostazioni (versione_id, soglia_positivo, soglia_sviluppo) VALUES (1, ${dec(soglie[1] / 100)}, ${dec(soglie[2] / 100)});`,
  'INSERT INTO obiettivi (id, versione_id, testo, ordine) VALUES\n' +
    OBIETTIVI.map((o, i) => `  (${i + 1}, 1, ${sql(o.testo)}, ${o.ordine})`).join(',\n') + ';',
  'INSERT INTO sezioni (id, versione_id, chiave, nome, nome_breve, descrizione, colore_indice, ordine, benchmark_b2b, benchmark_b2c) VALUES\n' +
    DIMS.map((d, i) => `  (${i + 1}, 1, ${sql(d.id)}, ${sql(d.label)}, ${sql(DIM_SHORT[d.id] || d.label)}, ${sql(d.desc)}, ${i}, ${i + 1}, ${dec(d.bm_b2b)}, ${dec(d.bm_b2c)})`).join(',\n') + ';',
];

const target = [];
DIMS.forEach((d, i) => OBIETTIVI.forEach((o, j) => {
  target.push(`  (${i + 1}, ${j + 1}, 'b2b', ${dec(d.tg_b2b[j])})`);
  target.push(`  (${i + 1}, ${j + 1}, 'b2c', ${dec(d.tg_b2c[j])})`);
}));
out.push('INSERT INTO target_sezione (sezione_id, obiettivo_id, segmento, valore) VALUES\n' + target.join(',\n') + ';');

const domande = [];
const risposte = [];
let domandaId = 0;
let rispostaId = 0;
DIMS.forEach((d, i) => ['b2b', 'b2c'].forEach(seg => {
  QUESTIONS[d.id][seg].forEach((q, qi) => {
    domandaId++;
    domande.push(`  (${domandaId}, ${i + 1}, '${seg}', '${uid(`${d.id}|${seg}|${qi}`)}', ${sql(q.text)}, ${qi + 1})`);
    // valori 1..n: diviso per il massimo della domanda dà lo stesso punteggio della formula attuale
    q.opts.forEach((opt, oi) => {
      rispostaId++;
      risposte.push(`  (${rispostaId}, ${domandaId}, '${uid(`${d.id}|${seg}|${qi}|${oi}`)}', ${sql(opt)}, ${dec(oi + 1)}, ${oi + 1})`);
    });
  });
}));
out.push('INSERT INTO domande (id, sezione_id, segmento, uid, testo, ordine) VALUES\n' + domande.join(',\n') + ';');
out.push('INSERT INTO risposte (id, domanda_id, uid, testo, valore, ordine) VALUES\n' + risposte.join(',\n') + ';');
out.push('COMMIT;', '');

fs.writeFileSync(path.join(__dirname, '04_seed_configurazione.sql'), out.join('\n\n'));
console.log(`Seed scritto: ${DIMS.length} sezioni, ${OBIETTIVI.length} obiettivi, ${domandaId} domande, ${rispostaId} risposte.`);
