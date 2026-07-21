<?php
declare(strict_types=1);

const ATENEA_REMEMBER_COOKIE = 'ATENEA_REMEMBER';
const ATENEA_REMEMBER_LIFETIME = 2592000;

function solicitudHttpsAtenea(): bool
{
    return (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function opcionesCookieRecuerdoAtenea(int $expira): array
{
    return ['expires'=>$expira,'path'=>'/','secure'=>solicitudHttpsAtenea(),'httponly'=>true,'samesite'=>'Lax'];
}

function borrarCookieRecuerdoAtenea(): void
{
    setcookie(ATENEA_REMEMBER_COOKIE, '', opcionesCookieRecuerdoAtenea(time()-42000));
    unset($_COOKIE[ATENEA_REMEMBER_COOKIE]);
}

function partesCookieRecuerdoAtenea(?string $cookie=null): ?array
{
    $cookie ??= (string)($_COOKIE[ATENEA_REMEMBER_COOKIE] ?? '');
    if (!preg_match('/^([a-f0-9]{24})\.([a-f0-9]{64})$/', $cookie, $m)) return null;
    return ['selector'=>$m[1],'verificador'=>$m[2]];
}

function huellaAgenteRecuerdoAtenea(): string
{
    return hash('sha256', mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'), 0, 500));
}

function huellaIpRecuerdoAtenea(): ?string
{
    $ip=filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP);
    return $ip ? hash('sha256', (string)$ip) : null;
}

function crearTokenRecuerdoAtenea(PDO $pdo,int $usuarioId,int $versionSesion): void
{
    $selector=bin2hex(random_bytes(12));$verificador=bin2hex(random_bytes(32));$expira=time()+ATENEA_REMEMBER_LIFETIME;
    $pdo->prepare('DELETE FROM auth_remember_tokens WHERE usuario_id=:u AND (revoked_at IS NOT NULL OR expires_at<=NOW())')->execute(['u'=>$usuarioId]);
    $q=$pdo->prepare('INSERT INTO auth_remember_tokens(usuario_id,selector,token_hash,session_version,user_agent_hash,ip_hash,expires_at) VALUES(:u,:s,:h,:v,:a,:ip,FROM_UNIXTIME(:e))');
    $q->execute(['u'=>$usuarioId,'s'=>$selector,'h'=>hash('sha256',$verificador),'v'=>$versionSesion,'a'=>huellaAgenteRecuerdoAtenea(),'ip'=>huellaIpRecuerdoAtenea(),'e'=>$expira]);
    setcookie(ATENEA_REMEMBER_COOKIE,$selector.'.'.$verificador,opcionesCookieRecuerdoAtenea($expira));
    $_COOKIE[ATENEA_REMEMBER_COOKIE]=$selector.'.'.$verificador;
}

function revocarTokenRecuerdoActualAtenea(?PDO $pdo=null): void
{
    $partes=partesCookieRecuerdoAtenea();
    if($partes){try{$pdo??=obtenerConexion();$pdo->prepare('UPDATE auth_remember_tokens SET revoked_at=COALESCE(revoked_at,NOW()) WHERE selector=:s')->execute(['s'=>$partes['selector']]);}catch(Throwable $e){error_log('Revocación de acceso recordado Atenea: '.$e->getMessage());}}
    borrarCookieRecuerdoAtenea();
}

function revocarTokensRecuerdoUsuarioAtenea(PDO $pdo,int $usuarioId): void
{
    $pdo->prepare('UPDATE auth_remember_tokens SET revoked_at=COALESCE(revoked_at,NOW()) WHERE usuario_id=:u AND revoked_at IS NULL')->execute(['u'=>$usuarioId]);
    $actual=partesCookieRecuerdoAtenea();if($actual)borrarCookieRecuerdoAtenea();
}

function usuarioDesdeTokenRecuerdoAtenea(PDO $pdo): ?array
{
    $partes=partesCookieRecuerdoAtenea();if(!$partes)return null;
    $q=$pdo->prepare("SELECT t.id token_id,t.token_hash,t.session_version token_version,t.user_agent_hash,t.expires_at,u.* FROM auth_remember_tokens t JOIN usuarios u ON u.id=t.usuario_id WHERE t.selector=:s AND t.revoked_at IS NULL AND t.expires_at>NOW() AND u.estado='activo' AND u.deleted_at IS NULL LIMIT 1");
    $q->execute(['s'=>$partes['selector']]);$fila=$q->fetch();
    $valida=is_array($fila)&&hash_equals((string)$fila['token_hash'],hash('sha256',$partes['verificador']))&&hash_equals((string)$fila['user_agent_hash'],huellaAgenteRecuerdoAtenea())&&(int)$fila['token_version']===(int)$fila['session_version'];
    if(!$valida){if(is_array($fila))$pdo->prepare('UPDATE auth_remember_tokens SET revoked_at=NOW() WHERE id=:id')->execute(['id'=>$fila['token_id']]);borrarCookieRecuerdoAtenea();return null;}
    $nuevo=bin2hex(random_bytes(32));$q=$pdo->prepare('UPDATE auth_remember_tokens SET token_hash=:h,last_used_at=NOW() WHERE id=:id AND revoked_at IS NULL');$q->execute(['h'=>hash('sha256',$nuevo),'id'=>$fila['token_id']]);
    setcookie(ATENEA_REMEMBER_COOKIE,$partes['selector'].'.'.$nuevo,opcionesCookieRecuerdoAtenea(strtotime((string)$fila['expires_at'])));$_COOKIE[ATENEA_REMEMBER_COOKIE]=$partes['selector'].'.'.$nuevo;
    unset($fila['token_id'],$fila['token_hash'],$fila['token_version'],$fila['user_agent_hash'],$fila['expires_at']);return $fila;
}

