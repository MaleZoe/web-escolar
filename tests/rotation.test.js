// Prueba la ruta rotativa del panel con un servicio de correo simulado. No envía correos reales.
const http = require('http');
const B = process.env.TEST_ROT_BASE;
const USER = 'gestor_sys98', PASS = process.env.TEST_PASS;
const state = { mails: [], fail: false };
const mock = http.createServer((req, res) => {
  let body = '';
  req.on('data', d => { body += d; });
  req.on('end', () => {
    const resend = !!process.env.TEST_RESEND_KEY;
    if (state.fail) { res.writeHead(resend ? 403 : 200, { 'content-type': 'application/json' }); return res.end(JSON.stringify({ success: 'false', message: 'servicio no disponible' })); }
    const mail = JSON.parse(body);
    if (resend) {
      state.authOk = req.headers.authorization === 'Bearer ' + process.env.TEST_RESEND_KEY;
      state.toOk = Array.isArray(mail.to) && mail.to[0] === 'prueba@example.com' && !!mail.from;
      mail['Nuevo enlace'] = (String(mail.text).match(/Nuevo enlace: (\S+)/) || [])[1];
      res.writeHead(200, { 'content-type': 'application/json' });
      res.end(JSON.stringify({ id: 'mock-id' }));
    } else {
      res.writeHead(200, { 'content-type': 'application/json' });
      res.end(JSON.stringify({ success: 'true' }));
    }
    state.mails.push(mail);
  });
});
const R = [];
const ok = (id, name, pass, info = '') => { R.push(!!pass); console.log(`${pass ? 'PASS' : 'FAIL'} ${id} ${name}${info !== '' ? ' :: ' + info : ''}`); };
async function req(jar, p, o = {}) {
  const headers = { ...(o.headers || {}) };
  if (jar.c) headers.cookie = jar.c;
  const r = await fetch(B + p, { redirect: 'manual', ...o, headers });
  for (const s of r.headers.getSetCookie()) jar.c = s.split(';')[0];
  r.txt = await r.text();
  return r;
}
async function login(jar, dir) {
  const g = await req(jar, dir + '/login.php');
  const tok = (g.txt.match(/name="csrf_token" value="([^"]+)"/) || [])[1] || '';
  return req(jar, dir + '/login.php', { method: 'POST', headers: { 'content-type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ csrf_token: tok, usuario: USER, password: PASS }) });
}
const dirOf = (u) => u.replace(/^https?:\/\/[^/]+/, '').replace(/\/[^/]*$/, '');

(async () => {
  await new Promise(r => mock.listen(Number(process.env.TEST_MOCK_PORT), '127.0.0.1', r));

  // Correo caido: la ruta no cambia y nadie queda afuera
  state.fail = true;
  let a = {};
  let r = await login(a, '/sys-control-98');
  ok('ROT-1', 'si el correo falla, entra por la ruta actual', r.status === 302 && r.headers.get('location') === 'index.php', r.headers.get('location'));
  r = await req(a, '/sys-control-98/index.php');
  ok('ROT-2', 'el panel avisa que el correo fallo', r.status === 200 && r.txt.includes('No se pudo enviar el correo'));
  r = await req({}, '/sys-control-98/login.php');
  ok('ROT-3', 'la ruta inicial sigue activa', r.status === 200);

  // Correo disponible: rota
  state.fail = false;
  a = {};
  r = await login(a, '/sys-control-98');
  const loc = r.headers.get('location') || '';
  ok('ROT-4', 'login exitoso redirige a ruta nueva', /^\/sys-control-[a-f0-9]{32}\/index\.php$/.test(loc), loc.slice(0, 22));
  ok('ROT-5', 'se envia un correo', state.mails.length === 1);
  const link = state.mails[0]?.['Nuevo enlace'] || '';
  ok('ROT-6', 'el correo trae el enlace de la ruta nueva', dirOf(link) === dirOf(loc));
  ok('ROT-7', 'el correo no trae la contrasena', !JSON.stringify(state.mails[0] || {}).includes(PASS));
  const d1 = dirOf(loc);
  r = await req(a, loc);
  ok('ROT-8', 'el panel carga en la ruta nueva', r.status === 200 && r.txt.includes('CSRF_TOKEN'));
  const tok = (r.txt.match(/CSRF_TOKEN = "([^"]+)"/) || [])[1];
  const f = new FormData(); f.append('action', 'get_x'); f.append('csrf_token', tok);
  r = await req(a, d1 + '/api/manage.php', { method: 'POST', body: f });
  ok('ROT-9', 'la API responde en la ruta nueva', r.status === 400 && r.txt.includes('no v'), r.status);
  r = await req({}, d1 + '/css/admin.css');
  ok('ROT-10', 'el CSS responde en la ruta nueva', r.status === 200);
  r = await req({}, d1 + '/js/admin.js');
  ok('ROT-11', 'el JS responde en la ruta nueva', r.status === 200);
  for (const p of ['/sys-control-98/login.php', '/sys-control-98/index.php', '/sys-control-98/api/manage.php', '/sys-control-98/', '/sys-control-abc123/login.php']) {
    r = await req({}, p);
    ok('ROT-12', 'ruta no vigente = 404: ' + p, r.status === 404, r.status);
  }

  // Segundo ingreso: el enlace anterior muere
  a = {};
  r = await login(a, d1);
  const d2 = dirOf(r.headers.get('location') || '');
  ok('ROT-13', 'cada ingreso genera otra ruta', d2 && d2 !== d1);
  ok('ROT-14', 'cada ingreso envia otro correo', state.mails.length === 2);
  r = await req({}, d1 + '/login.php');
  ok('ROT-15', 'el enlace anterior deja de funcionar', r.status === 404, r.status);
  r = await req({}, d2 + '/login.php');
  ok('ROT-16', 'el enlace del correo abre el login', r.status === 200 && r.txt.includes('csrf_token'));

  // Login fallido: no rota ni envia correo
  const n = state.mails.length;
  const g = await req({}, d2 + '/login.php');
  ok('ROT-17', 'sin login exitoso no hay correo', state.mails.length === n && g.status === 200);

  // Correo caido con ruta rotada: sigue la vigente
  state.fail = true;
  a = {};
  r = await login(a, d2);
  ok('ROT-18', 'con correo caido sigue la ruta vigente', r.status === 302 && r.headers.get('location') === 'index.php');
  r = await req({}, d2 + '/login.php');
  ok('ROT-19', 'la ruta vigente sigue abierta', r.status === 200);

  // Logout dentro de la ruta nueva
  r = await req(a, d2 + '/index.php');
  const t2 = (r.txt.match(/CSRF_TOKEN = "([^"]+)"/) || [])[1];
  r = await req(a, d2 + '/logout.php', { method: 'POST', headers: { 'content-type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ csrf_token: t2 }) });
  ok('ROT-20', 'logout vuelve al login de la misma ruta', r.status === 302 && r.headers.get('location') === 'login.php?msg=logout');

  if (process.env.TEST_RESEND_KEY) {
    ok('RES-1', 'Resend recibe la clave por cabecera', state.authOk);
    ok('RES-2', 'Resend recibe remitente y destinatario', state.toOk);
    ok('RES-3', 'la clave no viaja en el cuerpo', !JSON.stringify(state.mails).includes(process.env.TEST_RESEND_KEY));
  }
  const pass = R.filter(Boolean).length;
  console.log(`\nROTACION ${process.env.TEST_RESEND_KEY ? '(Resend)' : '(FormSubmit)'} ${pass}/${R.length} PASS`);
  if (pass !== R.length) process.exitCode = 1;
  mock.close();
})().catch(e => { console.error(e); process.exitCode = 1; mock.close(); });
