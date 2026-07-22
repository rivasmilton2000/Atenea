<?php
declare(strict_types=1);

require_once __DIR__.'/conexion.php';
require_once __DIR__.'/config.php';
require_once __DIR__.'/mailer.php';
require_once __DIR__.'/audit.php';

function normalizarCorreoNewsletterAtenea(string $correo): string
{
    return mb_strtolower(trim($correo));
}

function tokenNewsletterAtenea(): string
{
    return bin2hex(random_bytes(32));
}

function suscribirNewsletterAtenea(string $correo, ?string $nombre=null, string $origen='website_footer', ?string $ip=null, ?PDO $pdo=null): array
{
    $correo=normalizarCorreoNewsletterAtenea($correo);$nombre=trim((string)$nombre)?:null;$origen=trim($origen)?:'website_footer';
    if(!filter_var($correo,FILTER_VALIDATE_EMAIL)||mb_strlen($correo)>190)throw new DomainException('Ingresa un correo electrónico válido.');
    if($nombre!==null&&mb_strlen($nombre)>150)throw new DomainException('El nombre es demasiado largo.');
    if(mb_strlen($origen)>80)$origen=mb_substr($origen,0,80);
    $pdo??=obtenerConexion();$propia=!$pdo->inTransaction();if($propia)$pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT id,estado FROM newsletter_suscriptores WHERE email=:email FOR UPDATE');$q->execute(['email'=>$correo]);$actual=$q->fetch();
        if($actual&&$actual['estado']==='activo'){if($propia)$pdo->commit();return['estado'=>'existente','id'=>(int)$actual['id']];}
        $token=tokenNewsletterAtenea();$ipHash=$ip?hash('sha256',$ip):null;
        if($actual){$pdo->prepare("UPDATE newsletter_suscriptores SET nombre=COALESCE(:nombre,nombre),estado='activo',token_baja=:token,origen=:origen,ip_hash=:ip,fecha_suscripcion=NOW(),fecha_baja=NULL WHERE id=:id")->execute(['nombre'=>$nombre,'token'=>$token,'origen'=>$origen,'ip'=>$ipHash,'id'=>$actual['id']]);$id=(int)$actual['id'];$estado='reactivado';}
        else{$q=$pdo->prepare("INSERT INTO newsletter_suscriptores(email,nombre,estado,token_baja,origen,ip_hash) VALUES(:email,:nombre,'activo',:token,:origen,:ip)");$q->execute(['email'=>$correo,'nombre'=>$nombre,'token'=>$token,'origen'=>$origen,'ip'=>$ipHash]);$id=(int)$pdo->lastInsertId();$estado='creado';}
        if($propia)$pdo->commit();return['estado'=>$estado,'id'=>$id,'token'=>$token];
    }catch(Throwable$e){if($propia&&$pdo->inTransaction())$pdo->rollBack();throw$e;}
}

function suscriptorNewsletterPorTokenAtenea(string $token, ?PDO $pdo=null): ?array
{
    if(!preg_match('/^[a-f0-9]{64}$/',$token))return null;$pdo??=obtenerConexion();$q=$pdo->prepare('SELECT id,estado,fecha_baja FROM newsletter_suscriptores WHERE token_baja=:token LIMIT 1');$q->execute(['token'=>$token]);return$q->fetch()?:null;
}

function cambiarEstadoNewsletterPorTokenAtenea(string $token, bool $activar, ?PDO $pdo=null): bool
{
    if(!preg_match('/^[a-f0-9]{64}$/',$token))return false;$pdo??=obtenerConexion();
    if($activar)$q=$pdo->prepare("UPDATE newsletter_suscriptores SET estado='activo',fecha_baja=NULL,fecha_suscripcion=NOW(),token_baja=:nuevo WHERE token_baja=:token");
    else$q=$pdo->prepare("UPDATE newsletter_suscriptores SET estado='inactivo',fecha_baja=NOW() WHERE token_baja=:token");
    $params=['token'=>$token];if($activar)$params['nuevo']=tokenNewsletterAtenea();$q->execute($params);return$q->rowCount()===1;
}

function urlBajaNewsletterAtenea(string $token): string
{
    return atenea_url_absoluta('src/website/newsletter-baja.php?token='.rawurlencode($token));
}

function renderizarCampanaNewsletterAtenea(array $campana, array $suscriptor): array
{
    require_once __DIR__.'/templates/email/layout.php';
    $contenido=nl2br(atenea_e((string)$campana['contenido']));$url=trim((string)($campana['url_destino']??''));$boton=trim((string)($campana['texto_boton']??''));
    if($url!==''&&$boton!=='')$contenido.=botonCorreoAtenea($boton,$url);
    $baja=urlBajaNewsletterAtenea((string)$suscriptor['token_baja']);
    $contenido.='<p style="margin:28px 0 0;padding-top:18px;border-top:1px solid #e2dccd;color:#687068;font-size:12px;line-height:1.6;">Recibes este mensaje porque te suscribiste al boletín de Atenea. <a href="'.atenea_e($baja).'">Cancelar suscripción</a>.</p>';
    $texto=(string)$campana['contenido'].($url!==''?"\n\n".$url:'')."\n\nCancelar suscripción: ".$baja;
    $layout=renderizarLayoutCorreoAtenea((string)$campana['asunto'],(string)($campana['preencabezado']?:$campana['asunto']),$contenido,$texto);
    return['subject'=>(string)$campana['asunto'],'html'=>$layout['html'],'text'=>$layout['text']];
}

function encolarDestinatariosCampanaNewsletterAtenea(int $campanaId, ?PDO $pdo=null): int
{
    $pdo??=obtenerConexion();$propia=!$pdo->inTransaction();if($propia)$pdo->beginTransaction();
    try{
        $q=$pdo->prepare("SELECT * FROM newsletter_campanas WHERE id=:id FOR UPDATE");$q->execute(['id'=>$campanaId]);$c=$q->fetch();
        if(!$c||!in_array($c['estado'],['programada','encolada','procesando'],true)||!$c['aprobada_at']||trim((string)$c['asunto'])===''||trim((string)$c['contenido'])==='')throw new DomainException('La campaña no está aprobada o no tiene contenido completo.');
        $q=$pdo->prepare("INSERT IGNORE INTO newsletter_envios(campana_id,suscriptor_id,estado) SELECT :campana,id,'pendiente' FROM newsletter_suscriptores WHERE estado='activo'");$q->execute(['campana'=>$campanaId]);
        $q=$pdo->prepare('SELECT COUNT(*) FROM newsletter_envios WHERE campana_id=:id');$q->execute(['id'=>$campanaId]);$total=(int)$q->fetchColumn();
        $pdo->prepare("UPDATE newsletter_campanas SET estado=IF(:total_condicion>0,'encolada','enviada'),destinatarios_total=:total WHERE id=:id")->execute(['total_condicion'=>$total,'total'=>$total,'id'=>$campanaId]);
        if($propia)$pdo->commit();return$total;
    }catch(Throwable$e){if($propia&&$pdo->inTransaction())$pdo->rollBack();throw$e;}
}

function sincronizarEstadoCampanaNewsletterAtenea(int $campanaId, ?PDO $pdo=null): void
{
    $pdo??=obtenerConexion();
    $pdo->prepare("UPDATE newsletter_envios ne JOIN correo_envios ce ON ce.id=ne.correo_envio_id SET ne.estado=CASE ce.estado WHEN 'enviado' THEN 'enviado' WHEN 'fallido' THEN 'fallido' WHEN 'cancelado' THEN 'cancelado' ELSE ne.estado END,ne.error_sanitizado=ce.error_sanitizado,ne.enviado_at=ce.enviado_at WHERE ne.campana_id=:id AND ne.estado IN('procesando','fallido')")->execute(['id'=>$campanaId]);
    $q=$pdo->prepare("SELECT COUNT(*) total,SUM(estado='pendiente') pendientes,SUM(estado='procesando') procesando,SUM(estado='enviado') enviados,SUM(estado='fallido') fallidos,SUM(estado='cancelado') cancelados FROM newsletter_envios WHERE campana_id=:id");$q->execute(['id'=>$campanaId]);$r=$q->fetch();if(!$r)return;
    $terminada=(int)$r['total']>0&&(int)$r['pendientes']===0&&(int)$r['procesando']===0;$estado=$terminada?'enviada':((int)$r['procesando']>0?'procesando':'encolada');
    $pdo->prepare("UPDATE newsletter_campanas SET estado=IF(estado='cancelada','cancelada',:estado),destinatarios_total=:total,enviados_total=:enviados,fallidos_total=:fallidos,cancelados_total=:cancelados WHERE id=:id")->execute(['estado'=>$estado,'total'=>(int)$r['total'],'enviados'=>(int)$r['enviados'],'fallidos'=>(int)$r['fallidos'],'cancelados'=>(int)$r['cancelados'],'id'=>$campanaId]);
}

function cancelarCampanaNewsletterAtenea(int $campanaId, int $actorId, ?PDO $pdo=null): void
{
    $pdo??=obtenerConexion();$pdo->beginTransaction();try{
        $q=$pdo->prepare("SELECT estado FROM newsletter_campanas WHERE id=:id FOR UPDATE");$q->execute(['id'=>$campanaId]);$estado=$q->fetchColumn();if(!$estado||in_array($estado,['enviada','cancelada'],true))throw new DomainException('La campaña ya no puede cancelarse.');
        $pdo->prepare("UPDATE correo_envios ce JOIN newsletter_envios ne ON ne.correo_envio_id=ce.id SET ce.estado='cancelado',ce.cancelado_at=NOW(),ce.cancelado_motivo='Campaña cancelada por administración.' WHERE ne.campana_id=:id AND ce.estado IN('pendiente','fallido')")->execute(['id'=>$campanaId]);
        $pdo->prepare("UPDATE newsletter_envios SET estado='cancelado',procesando_desde=NULL WHERE campana_id=:id AND estado IN('pendiente','procesando','fallido')")->execute(['id'=>$campanaId]);
        $pdo->prepare("UPDATE newsletter_campanas SET estado='cancelada',actualizada_por=:actor WHERE id=:id")->execute(['actor'=>$actorId,'id'=>$campanaId]);
        registrarAuditoria(['actor_user_id'=>$actorId,'event_type'=>'newsletter.campaign.cancelled','module'=>'newsletter','entity_type'=>'newsletter_campaign','entity_id'=>$campanaId,'action'=>'cancel','result'=>'success','description'=>'Campaña de boletín cancelada.'],$pdo);$pdo->commit();
    }catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
}

function procesarColaNewsletterAtenea(int $limite=25): array
{
    $pdo=obtenerConexion();$limite=max(1,min(50,$limite));$resultado=['campanas_encoladas'=>0,'revisados'=>0,'encolados'=>0,'cancelados'=>0,'fallidos'=>0];
    $pdo->exec("UPDATE newsletter_envios SET estado=IF(intentos>=3,'fallido','pendiente'),procesando_desde=NULL,error_sanitizado='El procesamiento anterior no concluyó.' WHERE estado='procesando' AND correo_envio_id IS NULL AND procesando_desde<DATE_SUB(NOW(),INTERVAL 10 MINUTE)");
    $idsCampana=$pdo->query("SELECT id FROM newsletter_campanas WHERE estado='programada' AND programada_at<=NOW() ORDER BY programada_at,id LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
    foreach($idsCampana as$id){try{encolarDestinatariosCampanaNewsletterAtenea((int)$id,$pdo);$resultado['campanas_encoladas']++;}catch(Throwable$e){error_log('Newsletter campaña '.(int)$id.': '.sanitizarErrorCorreoAtenea($e));}}
    $ids=$pdo->query("SELECT id FROM newsletter_envios WHERE estado='pendiente' ORDER BY id LIMIT {$limite}")->fetchAll(PDO::FETCH_COLUMN);
    foreach($ids as$id){$resultado['revisados']++;try{
        $pdo->beginTransaction();$q=$pdo->prepare("SELECT ne.*,s.email,s.nombre,s.token_baja,s.estado suscriptor_estado,c.asunto,c.preencabezado,c.contenido,c.texto_boton,c.url_destino,c.estado campana_estado FROM newsletter_envios ne JOIN newsletter_suscriptores s ON s.id=ne.suscriptor_id JOIN newsletter_campanas c ON c.id=ne.campana_id WHERE ne.id=:id AND ne.estado='pendiente' FOR UPDATE");$q->execute(['id'=>$id]);$r=$q->fetch();
        if(!$r){$pdo->commit();continue;}if($r['suscriptor_estado']!=='activo'||$r['campana_estado']==='cancelada'){$pdo->prepare("UPDATE newsletter_envios SET estado='cancelado' WHERE id=:id")->execute(['id'=>$id]);$pdo->commit();$resultado['cancelados']++;continue;}
        $pdo->prepare("UPDATE newsletter_envios SET estado='procesando',intentos=intentos+1,procesando_desde=NOW() WHERE id=:id")->execute(['id'=>$id]);$pdo->commit();
        $plantilla=renderizarCampanaNewsletterAtenea($r,$r);$correoId=encolarCorreoAtenea((string)$r['email'],(string)($r['nombre']?:'Suscriptor Atenea'),$plantilla['subject'],$plantilla['html'],$plantilla['text'],['tipo'=>'newsletter_campaign','categoria'=>'novedades','evento_id'=>'newsletter:campana:'.$r['campana_id'],'idempotency_key'=>'newsletter:campana:'.$r['campana_id'].':suscriptor:'.$r['suscriptor_id']]);
        $q=$pdo->prepare('SELECT estado,error_sanitizado,enviado_at FROM correo_envios WHERE id=:id');$q->execute(['id'=>$correoId]);$correo=$q->fetch();$estado=match($correo['estado']??''){ 'enviado'=>'enviado','fallido'=>'fallido','cancelado'=>'cancelado',default=>'procesando'};
        $pdo->prepare('UPDATE newsletter_envios SET correo_envio_id=:correo,estado=:estado,error_sanitizado=:error,enviado_at=:enviado,procesando_desde=NULL WHERE id=:id')->execute(['correo'=>$correoId,'estado'=>$estado,'error'=>$correo['error_sanitizado']??null,'enviado'=>$correo['enviado_at']??null,'id'=>$id]);$resultado[$estado==='procesando'?'encolados':($estado==='cancelado'?'cancelados':'fallidos')]++;
    }catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();$pdo->prepare("UPDATE newsletter_envios SET estado=IF(intentos>=3,'fallido','pendiente'),procesando_desde=NULL,error_sanitizado=:error WHERE id=:id")->execute(['error'=>sanitizarErrorCorreoAtenea($e),'id'=>$id]);$resultado['fallidos']++;}}
    $campanas=$pdo->query("SELECT DISTINCT campana_id FROM newsletter_envios WHERE estado IN('procesando','enviado','fallido','cancelado')")->fetchAll(PDO::FETCH_COLUMN);foreach($campanas as$id)sincronizarEstadoCampanaNewsletterAtenea((int)$id,$pdo);return$resultado;
}

function crearCampanaEventoNewsletterAtenea(array $datos, int $actorId, ?PDO $pdo=null): ?int
{
    $pdo??=obtenerConexion();$evento=mb_substr(trim((string)($datos['evento_clave']??'')),0,190);if($evento==='')throw new InvalidArgumentException('El evento publicitario requiere una clave.');
    $q=$pdo->prepare("INSERT IGNORE INTO newsletter_campanas(nombre,asunto,preencabezado,contenido,texto_boton,url_destino,tipo,estado,evento_clave,programada_at,aprobada_at,aprobada_por,creada_por,actualizada_por) VALUES(:nombre,:asunto,:pre,:contenido,:boton,:url,:tipo,'programada',:evento,NOW(),NOW(),:actor,:actor2,:actor3)");
    $q->execute(['nombre'=>mb_substr((string)$datos['nombre'],0,180),'asunto'=>mb_substr((string)$datos['asunto'],0,190),'pre'=>mb_substr((string)($datos['preencabezado']??''),0,255)?:null,'contenido'=>(string)$datos['contenido'],'boton'=>mb_substr((string)($datos['texto_boton']??''),0,80)?:null,'url'=>mb_substr((string)($datos['url_destino']??''),0,500)?:null,'tipo'=>$datos['tipo']??'actualizacion','evento'=>$evento,'actor'=>$actorId,'actor2'=>$actorId,'actor3'=>$actorId]);
    if($q->rowCount()===0)return null;return(int)$pdo->lastInsertId();
}
