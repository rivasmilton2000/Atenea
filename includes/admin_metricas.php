<?php
declare(strict_types=1);
require_once __DIR__.'/conexion.php';

function periodoMetricasAtenea(?string $desde,?string $hasta): array
{
    $fin=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$hasta)?:new DateTimeImmutable('today');
    $inicio=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$desde)?:$fin->modify('-29 days');
    if($inicio>$fin)$inicio=$fin->modify('-29 days');if($inicio<$fin->modify('-365 days'))$inicio=$fin->modify('-365 days');
    $dias=(int)$inicio->diff($fin)->days+1;$anteriorFin=$inicio->modify('-1 day');$anteriorInicio=$anteriorFin->modify('-'.($dias-1).' days');
    return ['desde'=>$inicio->format('Y-m-d'),'hasta'=>$fin->format('Y-m-d'),'inicio'=>$inicio->format('Y-m-d 00:00:00'),'fin'=>$fin->modify('+1 day')->format('Y-m-d 00:00:00'),'anterior_inicio'=>$anteriorInicio->format('Y-m-d 00:00:00'),'anterior_fin'=>$inicio->format('Y-m-d 00:00:00')];
}

function metricasAdministrativasAtenea(PDO $pdo,array $p): array
{
    $r=['kpi'=>[],'ventas'=>[],'pedidos_estado'=>[],'productos'=>[],'usuarios_nuevos'=>[],'roles'=>[]];
    $q=$pdo->prepare("SELECT COUNT(*) pedidos,SUM(payment_status='paid' AND estado IN('pagado','preparando','enviado','entregado')) pagados,0 pendientes,SUM(estado='entregado') entregados FROM pedidos WHERE created_at>=:d AND created_at<:h AND es_intencion_checkout=0");$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$r['kpi']=$q->fetch();
    $q=$pdo->prepare("SELECT COALESCE(SUM(total),0) ingresos,COALESCE(AVG(total),0) promedio FROM pedidos WHERE created_at>=:d AND created_at<:h AND estado IN('pagado','preparando','enviado','entregado') AND payment_status='paid'");$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$dinero=$q->fetch();$r['kpi']['ingresos']=$dinero['ingresos'];$r['kpi']['promedio']=$dinero['promedio'];
    $q=$pdo->prepare("SELECT COALESCE(SUM(total),0) ingresos FROM pedidos WHERE created_at>=:d AND created_at<:h AND estado IN('pagado','preparando','enviado','entregado') AND payment_status='paid'");$q->execute(['d'=>$p['anterior_inicio'],'h'=>$p['anterior_fin']]);$r['kpi']['ingresos_anterior']=$q->fetchColumn();
    $q=$pdo->prepare("SELECT DATE(paid_at) etiqueta,COUNT(*) pedidos,CAST(SUM(total) AS DECIMAL(14,2)) ingresos FROM pedidos WHERE paid_at>=:d AND paid_at<:h AND estado IN('pagado','preparando','enviado','entregado') AND payment_status='paid' GROUP BY DATE(paid_at) ORDER BY etiqueta");$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$r['ventas']=$q->fetchAll();
    $q=$pdo->prepare('SELECT estado etiqueta,COUNT(*) total FROM pedidos WHERE created_at>=:d AND created_at<:h AND es_intencion_checkout=0 GROUP BY estado ORDER BY total DESC');$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$r['pedidos_estado']=$q->fetchAll();
    $q=$pdo->prepare("SELECT pd.nombre_producto etiqueta,SUM(pd.cantidad) total FROM pedido_detalles pd JOIN pedidos p ON p.id=pd.pedido_id WHERE p.paid_at>=:d AND p.paid_at<:h AND p.estado IN('pagado','preparando','enviado','entregado') AND p.payment_status='paid' GROUP BY pd.producto_id,pd.nombre_producto ORDER BY total DESC LIMIT 8");$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$r['productos']=$q->fetchAll();
    $q=$pdo->prepare('SELECT DATE(created_at) etiqueta,COUNT(*) total FROM usuarios WHERE created_at>=:d AND created_at<:h GROUP BY DATE(created_at) ORDER BY etiqueta');$q->execute(['d'=>$p['inicio'],'h'=>$p['fin']]);$r['usuarios_nuevos']=$q->fetchAll();
    $r['roles']=$pdo->query('SELECT rol etiqueta,COUNT(*) total FROM usuarios WHERE deleted_at IS NULL GROUP BY rol ORDER BY rol')->fetchAll();
    $r['usuarios']=['total'=>(int)$pdo->query('SELECT COUNT(*) FROM usuarios WHERE deleted_at IS NULL')->fetchColumn(),'activos'=>(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado='activo' AND deleted_at IS NULL")->fetchColumn(),'inactivos'=>(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado='inactivo' OR deleted_at IS NOT NULL")->fetchColumn(),'recientes'=>$pdo->query('SELECT id,nombre,apellido,rol,estado,created_at FROM usuarios ORDER BY created_at DESC LIMIT 5')->fetchAll(),'accesos'=>$pdo->query('SELECT id,nombre,apellido,ultimo_acceso FROM usuarios WHERE ultimo_acceso IS NOT NULL ORDER BY ultimo_acceso DESC LIMIT 5')->fetchAll()];
    $r['contenido']=['capacitaciones'=>(int)$pdo->query("SELECT COUNT(*) FROM asignaturas WHERE estado_capacitacion='publicada' AND activo=1 AND deleted_at IS NULL")->fetchColumn(),'productos'=>(int)$pdo->query('SELECT COUNT(*) FROM productos WHERE activo=1 AND disponible=1 AND eliminado_at IS NULL')->fetchColumn(),'pendientes'=>(int)$pdo->query('SELECT COUNT(*) FROM elementos_seccion WHERE activo=0')->fetchColumn(),'stock_bajo'=>$pdo->query('SELECT id,nombre,stock,stock_reservado,stock_minimo FROM productos WHERE activo=1 AND eliminado_at IS NULL AND GREATEST(stock-stock_reservado,0)<=stock_minimo ORDER BY (stock-stock_reservado) ASC LIMIT 10')->fetchAll()];
    $r['alertas']=['pedidos_pendientes'=>0,'errores_nuevos'=>(int)$pdo->query("SELECT COUNT(*) FROM errores_sistema WHERE estado='nuevo'")->fetchColumn(),'correos_fallidos'=>(int)$pdo->query("SELECT COUNT(*) FROM correo_envios WHERE estado='fallido'")->fetchColumn()];
    return $r;
}
