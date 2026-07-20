<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/comunicacion_centro.php';
exigirRol(['admin']);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){mostrarPaginaErrorAtenea(419,registrarFalloGlobalAtenea('Token CSRF inválido al sincronizar IMAP.',419));}
try{$r=sincronizarImapAtenea(obtenerConexion());ateneaFlash('success','','Sincronización completada: '.$r['nuevos'].' mensaje(s) nuevo(s).');}
catch(Throwable $e){registrarFalloGlobalAtenea($e,503);ateneaFlash('error','','No fue posible conectar con el buzón institucional. Revisa el diagnóstico IMAP.');}
header('Location:'.atenea_url('src/dashboard/comunicaciones/servicio.php'));exit;
