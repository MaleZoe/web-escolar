// Comprueba que el crédito fijo del pie siga intacto en todas las páginas.
// Si esta prueba falla, alguien cambió o quitó el crédito "Desarrollado por".
const fs = require('fs');
const path = require('path');
const ROOT = path.join(__dirname, '..');

const CREDITO = 'Desarrollado por: <a href="https://portfolio-mauve-one-s4i0h3h691.vercel.app/" target="_blank" rel="noopener noreferrer">Malena Salvia ↗</a>';
const PAGINAS = ['index.html', 'pages/contacto.html', 'pages/escuela.html', 'pages/expotec.html', 'pages/institucionales.html'];
const SIN_ACCESO = ['js/dynamic_content.js', 'js/script.js', 'sys-control-98/js/admin.js', 'sys-control-98/index.php', 'sys-control-98/api/manage.php'];

let fallas = 0;
const ok = (nombre, paso) => { if (!paso) fallas++; console.log(`${paso ? 'PASS' : 'FAIL'} CRED ${nombre}`); };
const leer = (f) => fs.readFileSync(path.join(ROOT, f), 'utf8');

for (const f of PAGINAS) {
  const html = leer(f);
  ok(`crédito intacto en ${f}`, html.includes(CREDITO));
  ok(`crédito una sola vez en ${f}`, html.split('Desarrollado por:').length === 2);
}
for (const f of SIN_ACCESO) {
  ok(`${f} no toca el crédito`, !leer(f).includes('footer-dev'));
}

console.log(`\nCREDITO ${fallas === 0 ? 'OK' : fallas + ' FALLAS'}`);
if (fallas) process.exitCode = 1;
