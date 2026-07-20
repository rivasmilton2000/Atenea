<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conexion.php';

function urlNotificacionAtenea(?string $url): ?string
{
    $url = trim((string)$url);
    if ($url === '') return null;
    if (preg_match('/^(?:javascript|data|vbscript):/i', $url) || str_contains($url, '..')) return null;
    if (preg_match('#^https?://#i', $url)) {
        $base = parse_url(atenea_url_absoluta(''), PHP_URL_HOST);
        return parse_url($url, PHP_URL_HOST) === $base ? $url : null;
    }
    return preg_match('~^/?[A-Za-z0-9_./?=&%#-]+$~', $url) ? $url : null;
}

function crearNotificacionAtenea(array $datos, ?PDO $pdo = null): void
{
    $pdo ??= obtenerConexion();
    $usuarios = [];
    if (!empty($datos['usuario_id'])) $usuarios[] = (int)$datos['usuario_id'];
    elseif (!empty($datos['rol'])) {
        $q = $pdo->prepare("SELECT id FROM usuarios WHERE rol=:rol AND estado='activo' AND deleted_at IS NULL");
        $q->execute(['rol' => $datos['rol']]);
        $usuarios = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    }
    if (!$usuarios) return;
    $sql = "INSERT INTO admin_notices(user_id,created_by,type,category,level,title,message,target_section,action_url,idempotency_key,pedido_id,correo_envio_id,hilo_id,error_id,priority,status) VALUES(:usuario,:autor,:tipo,:categoria,:nivel,:titulo,:mensaje,:seccion,:url,:clave,:pedido,:correo,:hilo,:error,:prioridad,'pendiente') ON DUPLICATE KEY UPDATE id=id";
    $q = $pdo->prepare($sql);
    foreach ($usuarios as $usuario) {
        $base = substr((string)($datos['idempotency_key'] ?? ''), 0, 160);
        $clave = $base === '' ? null : $base . ':u:' . $usuario;
        $q->execute([
            'usuario'=>$usuario,'autor'=>$datos['created_by']??null,'tipo'=>substr((string)($datos['tipo']??'sistema'),0,50),
            'categoria'=>substr((string)($datos['categoria']??'sistema'),0,50),'nivel'=>in_array($datos['nivel']??'', ['informacion','exito','advertencia','error'],true)?$datos['nivel']:'informacion',
            'titulo'=>substr(trim((string)($datos['titulo']??'Notificacion')),0,180),'mensaje'=>substr(trim((string)($datos['descripcion']??'')),0,2000),
            'seccion'=>substr((string)($datos['seccion']??''),0,100)?:null,'url'=>urlNotificacionAtenea($datos['url']??null),'clave'=>$clave,
            'pedido'=>$datos['pedido_id']??null,'correo'=>$datos['correo_envio_id']??null,'hilo'=>$datos['hilo_id']??null,'error'=>$datos['error_id']??null,
            'prioridad'=>($datos['nivel']??'')==='error'?'alta':(($datos['nivel']??'')==='advertencia'?'media':'normal'),
        ]);
    }
}

function notificacionesAdminResumen(int $usuarioId, int $limite = 5, ?PDO $pdo = null): array
{
    $pdo ??= obtenerConexion(); $limite=max(1,min(10,$limite));
    $q=$pdo->prepare("SELECT COUNT(*) FROM admin_notices WHERE user_id=:u AND status='pendiente'");$q->execute(['u'=>$usuarioId]);$total=(int)$q->fetchColumn();
    $q=$pdo->prepare("SELECT id,title,message,category,level,action_url,status,created_at FROM admin_notices WHERE user_id=:u AND status IN('pendiente','visto') ORDER BY created_at DESC,id DESC LIMIT :lim");
    $q->bindValue(':u',$usuarioId,PDO::PARAM_INT);$q->bindValue(':lim',$limite,PDO::PARAM_INT);$q->execute();
    return ['no_leidas'=>$total,'notificaciones'=>$q->fetchAll()];
}

function notificacionesUsuarioResumen(int $usuarioId, int $limite = 5, ?PDO $pdo = null): array
{
    return notificacionesAdminResumen($usuarioId,$limite,$pdo);
}
