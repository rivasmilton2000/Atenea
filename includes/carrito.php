<?php
declare(strict_types=1);

require_once __DIR__ . '/comercio.php';

function dineroCentavos(string $valor): int
{
    $valor = trim($valor);
    if (!preg_match('/^-?\d+(?:\.\d{1,2})?$/', $valor)) throw new InvalidArgumentException('Importe decimal no válido.');
    $negativo = str_starts_with($valor, '-');
    $partes = explode('.', ltrim($valor, '-'), 2);
    return ($negativo ? -1 : 1) * ((int)$partes[0] * 100 + (int)str_pad($partes[1] ?? '', 2, '0'));
}

function centavosDinero(int $centavos): string
{
    $signo = $centavos < 0 ? '-' : '';
    $centavos = abs($centavos);
    return $signo . intdiv($centavos, 100) . '.' . str_pad((string)($centavos % 100), 2, '0', STR_PAD_LEFT);
}

function configuracionComercio(): array
{
    $envio = getenv('SHOP_SHIPPING_AMOUNT') ?: '0.00';
    $tasa = getenv('SHOP_TAX_RATE') ?: '0.00';
    return ['envio' => dineroCentavos($envio), 'tasa' => dineroCentavos($tasa), 'incluido' => filter_var(getenv('SHOP_TAX_INCLUDED') ?: 'true', FILTER_VALIDATE_BOOL)];
}

function carritoActivo(PDO $pdo, int $usuarioId, bool $crear = true, bool $bloquear = false): ?array
{
    $sql = "SELECT * FROM carritos WHERE usuario_id=:usuario AND estado='activo' ORDER BY id DESC LIMIT 1" . ($bloquear ? ' FOR UPDATE' : '');
    $q = $pdo->prepare($sql); $q->execute(['usuario'=>$usuarioId]);
    $carrito = $q->fetch();
    if ($carrito || !$crear) return $carrito ?: null;
    $pdo->prepare("INSERT INTO carritos(usuario_id,estado) VALUES(:usuario,'activo')")->execute(['usuario'=>$usuarioId]);
    return ['id'=>(int)$pdo->lastInsertId(),'usuario_id'=>$usuarioId,'estado'=>'activo','version'=>1];
}

function precioProductoCentavos(array $producto, ?array $promocion): array
{
    $normal = dineroCentavos((string)$producto['precio']);
    $final = $promocion ? dineroCentavos((string)$promocion['precio_promocional']) : $normal;
    if ($final > $normal) $final = $normal;
    return ['normal'=>$normal,'final'=>$final,'descuento'=>$normal-$final,'promocion'=>$promocion];
}

function resumenCarrito(PDO $pdo, int $usuarioId, bool $bloquear = false): array
{
    $carrito = carritoActivo($pdo, $usuarioId, true, $bloquear);
    $sql = 'SELECT ci.id item_id,ci.cantidad,p.*,pr.id promo_id,pr.precio_promocional promo_precio,pr.etiqueta promo_etiqueta FROM carrito_items ci JOIN productos p ON p.id=ci.producto_id LEFT JOIN promociones pr ON pr.id=(SELECT pr2.id FROM promociones pr2 WHERE pr2.producto_id=p.id AND pr2.activa=1 AND NOW() BETWEEN pr2.fecha_inicio AND pr2.fecha_fin ORDER BY pr2.precio_promocional,pr2.id LIMIT 1) WHERE ci.carrito_id=:carrito ORDER BY ci.created_at';
    if ($bloquear) $sql .= ' FOR UPDATE';
    $q=$pdo->prepare($sql); $q->execute(['carrito'=>$carrito['id']]); $items=[]; $subtotal=0; $descuento=0; $cantidad=0;
    foreach($q->fetchAll() as $producto){
        $publicado=productoPublico((int)$producto['id']);
        if(!$publicado)continue;
        foreach(['nombre','descripcion_corta','precio','imagen_principal'] as$campo)$producto[$campo]=$publicado[$campo]??$producto[$campo];
        $promo=$producto['promo_id']?['id'=>$producto['promo_id'],'precio_promocional'=>$producto['promo_precio'],'etiqueta'=>$producto['promo_etiqueta']]:null;
        $precio=precioProductoCentavos($producto,$promo);
        $producto['disponible_real']=max(0,(int)$producto['stock']-(int)$producto['stock_reservado']);
        $producto['precio_normal_centavos']=$precio['normal']; $producto['precio_centavos']=$precio['final'];
        $producto['descuento_centavos']=$precio['descuento']; $producto['linea_centavos']=$precio['final']*(int)$producto['cantidad'];
        $subtotal += $precio['normal']*(int)$producto['cantidad']; $descuento += $precio['descuento']*(int)$producto['cantidad'];
        $cantidad += (int)$producto['cantidad']; $items[]=$producto;
    }
    $cfg=configuracionComercio(); $base=$subtotal-$descuento; $impuesto=$cfg['incluido'] ? 0 : intdiv($base*$cfg['tasa']+5000,10000);
    $envio=$items ? $cfg['envio'] : 0;
    return compact('carrito','items','subtotal','descuento','envio','impuesto','cantidad')+['total'=>$base+$envio+$impuesto];
}

function cantidadCarrito(PDO $pdo, int $usuarioId): int
{
    $q=$pdo->prepare("SELECT COALESCE(SUM(ci.cantidad),0) FROM carritos c JOIN carrito_items ci ON ci.carrito_id=c.id WHERE c.usuario_id=:usuario AND c.estado='activo'");
    $q->execute(['usuario'=>$usuarioId]); return (int)$q->fetchColumn();
}

function etiquetaDireccion(array $d): string
{
    return $d['etiqueta']==='otra' ? (string)$d['etiqueta_personalizada'] : ucfirst((string)$d['etiqueta']);
}

function direccionesUsuario(PDO $pdo, int $usuarioId): array
{
    $q=$pdo->prepare('SELECT d.*,de.nombre departamento,m.nombre municipio,di.nombre distrito FROM direcciones_usuario d JOIN departamentos de ON de.id=d.departamento_id JOIN municipios m ON m.id=d.municipio_id LEFT JOIN distritos di ON di.id=d.distrito_id WHERE d.usuario_id=:usuario AND d.activa=1 ORDER BY d.predeterminada DESC,d.updated_at DESC');
    $q->execute(['usuario'=>$usuarioId]); return $q->fetchAll();
}

function direccionPropia(PDO $pdo, int $direccionId, int $usuarioId, bool $bloquear=false): ?array
{
    $q=$pdo->prepare('SELECT d.*,de.nombre departamento,m.nombre municipio,di.nombre distrito FROM direcciones_usuario d JOIN departamentos de ON de.id=d.departamento_id JOIN municipios m ON m.id=d.municipio_id LEFT JOIN distritos di ON di.id=d.distrito_id WHERE d.id=:id AND d.usuario_id=:usuario AND d.activa=1'.($bloquear?' FOR UPDATE':''));
    $q->execute(['id'=>$direccionId,'usuario'=>$usuarioId]); return $q->fetch() ?: null;
}

function snapshotDireccion(array $d): array
{
    return array_intersect_key($d,array_flip(['id','etiqueta','etiqueta_personalizada','receptor','telefono','departamento_id','municipio_id','distrito_id','departamento','municipio','distrito','direccion_detallada','referencias']));
}
