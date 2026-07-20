<?php
declare(strict_types=1);
require_once __DIR__.'/config/services.php';

function configuracionImapAtenea(): array { return ImapConfig::toArray(); }
function imapAteneaConfigurado(): bool { return ImapConfig::isAvailable(); }

function buzonImapAtenea(): string
{
    $c=ImapConfig::toArray();
    $flags=$c['encryption']==='none'?'/novalidate-cert':'/'.$c['encryption'];
    return '{'.$c['host'].':'.$c['port'].'/imap'.$flags.'}'.$c['folder'];
}

function abrirImapAtenea(): mixed
{
    if(!ImapConfig::extensionAvailable())throw new RuntimeException('La extensión IMAP de PHP no está disponible.');
    $faltantes=ImapConfig::missing();
    if($faltantes)throw new RuntimeException('Falta configuración IMAP: '.implode(', ',$faltantes).'.');
    if(function_exists('imap_timeout')){@imap_timeout(IMAP_OPENTIMEOUT,5);@imap_timeout(IMAP_READTIMEOUT,10);}
    $c=ImapConfig::toArray();
    $imap=@imap_open(buzonImapAtenea(),$c['user'],$c['password'],OP_READONLY,1);
    if(!$imap){if(function_exists('imap_errors'))@imap_errors();throw new RuntimeException('No fue posible autenticar la conexión IMAP.');}
    return $imap;
}

function diagnosticoImapAtenea(PDO $pdo,bool $probarConexion=true): array
{
    $conexion=false;
    if($probarConexion&&ImapConfig::isAvailable()){
        try{$imap=abrirImapAtenea();$conexion=true;imap_close($imap);}
        catch(Throwable){$conexion=false;}
    }
    $q=$pdo->prepare('SELECT ultima_sincronizacion_at FROM correo_imap_estado WHERE carpeta=:carpeta LIMIT 1');
    $q->execute(['carpeta'=>ImapConfig::folder()?:'INBOX']);
    $ultima=$q->fetchColumn()?:null;
    $cantidad=(int)$pdo->query("SELECT COUNT(*) FROM correo_centro_mensajes WHERE direccion='entrada' AND uid_imap IS NOT NULL")->fetchColumn();
    return ['extension'=>ImapConfig::extensionAvailable(),'faltantes'=>ImapConfig::missing(),'disponible'=>ImapConfig::isAvailable(),'conexion'=>$conexion,'ultima_sincronizacion'=>$ultima,'mensajes_sincronizados'=>$cantidad];
}
