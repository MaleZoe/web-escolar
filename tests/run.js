// Crea una base de prueba separada, copia el sitio a una carpeta temporal, corre las pruebas y limpia todo.
// Nunca toca la base web_escolar_db ni los archivos del proyecto.
// Variables opcionales: PHP_BIN, MYSQL_BIN, TEST_PORT.
const {execFileSync,spawn}=require('child_process');const fs=require('fs');const os=require('os');const path=require('path');const crypto=require('crypto');
const ROOT=path.join(__dirname,'..');
const PHP=process.env.PHP_BIN||(fs.existsSync('C:/xampp/php/php.exe')?'C:/xampp/php/php.exe':'php');
const MYSQL=process.env.MYSQL_BIN||(fs.existsSync('C:/xampp/mysql/bin/mysql.exe')?'C:/xampp/mysql/bin/mysql.exe':'mysql');
const PORT=process.env.TEST_PORT||'8098';const DB='web_escolar_autotest';
const site=fs.mkdtempSync(path.join(os.tmpdir(),'eest5-test-'));
const mysql=(args,input)=>execFileSync(MYSQL,['-uroot',...args],{encoding:'utf8',input});
let server;
const cleanup=()=>{if(server)server.kill();try{mysql(['-e','DROP DATABASE IF EXISTS '+DB])}catch(e){}try{fs.rmSync(site,{recursive:true,force:true})}catch(e){}};
(async()=>{
  for(const x of ['index.html','favicon.png','css','js','pages','api','admin','sys-control-98','img'])fs.cpSync(path.join(ROOT,x),path.join(site,x),{recursive:true});
  mysql(['-e',`DROP DATABASE IF EXISTS ${DB}; CREATE DATABASE ${DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;`]);
  mysql(['--default-character-set=utf8mb4',DB],fs.readFileSync(path.join(ROOT,'database.sql'),'utf8'));
  const pass=crypto.randomBytes(12).toString('hex');
  const hash=execFileSync(PHP,['-r',`echo password_hash('${pass}',PASSWORD_BCRYPT,['cost'=>12]);`],{encoding:'utf8'});
  mysql([DB,'-e',`UPDATE usuarios SET password='${hash}' WHERE usuario='gestor_sys98'`]);
  const env={...process.env,DB_HOST:'127.0.0.1',DB_USER:'root',DB_PASS:'',DB_NAME:DB,DB_PORT:'3306'};
  server=spawn(PHP,['-S','127.0.0.1:'+PORT,'-t',site,path.join(__dirname,'router.php')],{env,stdio:'ignore',cwd:site});
  await new Promise(r=>setTimeout(r,1500));
  execFileSync(process.execPath,[path.join(__dirname,'api.test.js')],{stdio:'inherit',env:{...process.env,TEST_BASE:'http://127.0.0.1:'+PORT,TEST_SITE:site,TEST_MYSQL:MYSQL,TEST_DB:DB,TEST_PASS:pass}});
  // Ruta rotativa: otro servidor con la funcion activa y un correo simulado.
  server.kill();
  mysql([DB,'-e','DELETE FROM intentos_login; DELETE FROM panel_acceso;']);
  const ROT=String(Number(PORT)+2),MOCK=String(Number(PORT)+1);
  server=spawn(PHP,['-S','127.0.0.1:'+ROT,'-t',site,path.join(__dirname,'router.php')],{env:{...env,ADMIN_ALERT_EMAIL:'prueba@example.com',PANEL_ROTATE_TEST:'1',PANEL_MAIL_ENDPOINT:'http://127.0.0.1:'+MOCK+'/',PANEL_BASE_URL:'http://127.0.0.1:'+ROT},stdio:'ignore',cwd:site});
  await new Promise(r=>setTimeout(r,1500));
  execFileSync(process.execPath,[path.join(__dirname,'rotation.test.js')],{stdio:'inherit',env:{...process.env,TEST_ROT_BASE:'http://127.0.0.1:'+ROT,TEST_MOCK_PORT:MOCK,TEST_PASS:pass}});
})().catch(e=>{process.exitCode=1;if(!e.status)console.error(e)}).finally(cleanup);
