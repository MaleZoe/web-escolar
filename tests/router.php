<?php
// Emula vercel.json sobre php -S (emulacion, no Vercel real)
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];
if (preg_match('#^/sys-control-98/(css|js)/#', $p)) return false;
if (preg_match('#^/sys-control-98#', $p)) { require "$root/api/admin.php"; return; }
if (preg_match('#^/api/(.+)#', $p, $m)) {
  $f = "$root/api/{$m[1]}";
  if (is_file($f) && substr($f, -4) === '.php') { require $f; return; }
  http_response_code(404); return;
}
if ($p === '/' || is_file($root . $p)) return false;
if (preg_match('#^/img/(.+)#', $p, $m)) { $_GET['file'] = urldecode($m[1]); require "$root/api/image.php"; return; }
http_response_code(404); echo 'not found';
