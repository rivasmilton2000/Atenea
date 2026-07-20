<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/carrito.php';
require_once dirname(__DIR__, 2) . '/includes/alerts.php';

$destino = atenea_url('src/carrito/index.php');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarTokenCsrf((string)($_POST['csrf_token'] ?? ''))) {
    ateneaFlash('error', 'Solicitud vencida', 'Recarga la página e inténtalo nuevamente.');
    header('Location: ' . $destino);
    exit;
}

$accion = (string)($_POST['accion'] ?? '');
$productoId = filter_var($_POST['producto_id'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 0;
$cantidadSolicitada = filter_var($_POST['cantidad'] ?? 0, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>99]]) ?: 0;
$usuarioActual = obtenerUsuarioActual();
$autenticado = $usuarioActual !== null && $usuarioActual['rol'] === 'usuario';
$pdo = obtenerConexion();

try {
    if ($autenticado) {
        $pdo->beginTransaction();
        $carrito = carritoActivo($pdo, (int)$_SESSION['usuario_id'], true, true);
        if ($accion === 'vaciar') {
            $pdo->prepare('DELETE FROM carrito_items WHERE carrito_id=:id')->execute(['id'=>$carrito['id']]);
        } elseif ($productoId && in_array($accion, ['agregar','actualizar','incrementar','disminuir','eliminar'], true)) {
            $actualQ = $pdo->prepare('SELECT cantidad FROM carrito_items WHERE carrito_id=:carrito AND producto_id=:producto FOR UPDATE');
            $actualQ->execute(['carrito'=>$carrito['id'],'producto'=>$productoId]);
            $actual = (int)($actualQ->fetchColumn() ?: 0);
            if ($accion === 'eliminar' || ($accion === 'disminuir' && $actual <= 1)) {
                $pdo->prepare('DELETE FROM carrito_items WHERE carrito_id=:carrito AND producto_id=:producto')->execute(['carrito'=>$carrito['id'],'producto'=>$productoId]);
            } else {
                if (in_array($accion, ['agregar','actualizar'], true) && !$cantidadSolicitada) throw new DomainException('Indica una cantidad válida.');
                $q = $pdo->prepare('SELECT stock,stock_reservado FROM productos WHERE id=:id AND activo=1 AND disponible=1 AND eliminado_at IS NULL FOR UPDATE');
                $q->execute(['id'=>$productoId]);
                $producto = $q->fetch();
                if (!$producto) throw new DomainException('El producto ya no está disponible.');
                $disponible = max(0, (int)$producto['stock'] - (int)$producto['stock_reservado']);
                $cantidad = match ($accion) {
                    'agregar' => $actual + $cantidadSolicitada,
                    'actualizar' => $cantidadSolicitada,
                    'incrementar' => $actual + 1,
                    'disminuir' => $actual - 1,
                    default => 0,
                };
                if ($cantidad < 1 || $cantidad > 99) throw new DomainException('Selecciona una cantidad entre 1 y 99.');
                if ($cantidad > $disponible) throw new DomainException('Solo hay ' . $disponible . ' unidad(es) disponibles.');
                $pdo->prepare('INSERT INTO carrito_items(carrito_id,producto_id,cantidad) VALUES(:carrito,:producto,:cantidad) ON DUPLICATE KEY UPDATE cantidad=:cantidad2')
                    ->execute(['carrito'=>$carrito['id'],'producto'=>$productoId,'cantidad'=>$cantidad,'cantidad2'=>$cantidad]);
            }
        } else {
            throw new DomainException('La acción solicitada no es válida.');
        }
        $pdo->prepare('UPDATE carritos SET version=version+1 WHERE id=:id')->execute(['id'=>$carrito['id']]);
        $pdo->commit();
    } else {
        $items = carritoInvitadoAtenea();
        if ($accion === 'vaciar') {
            $items = [];
        } elseif ($productoId && in_array($accion, ['agregar','actualizar','incrementar','disminuir','eliminar'], true)) {
            $actual = (int)($items[$productoId] ?? 0);
            if ($accion === 'eliminar' || ($accion === 'disminuir' && $actual <= 1)) {
                unset($items[$productoId]);
            } else {
                if (in_array($accion, ['agregar','actualizar'], true) && !$cantidadSolicitada) throw new DomainException('Indica una cantidad válida.');
                $q = $pdo->prepare('SELECT stock,stock_reservado FROM productos WHERE id=:id AND activo=1 AND disponible=1 AND eliminado_at IS NULL');
                $q->execute(['id'=>$productoId]);
                $producto = $q->fetch();
                if (!$producto) throw new DomainException('El producto ya no está disponible.');
                $disponible = max(0, (int)$producto['stock'] - (int)$producto['stock_reservado']);
                $cantidad = match ($accion) {
                    'agregar' => $actual + $cantidadSolicitada,
                    'actualizar' => $cantidadSolicitada,
                    'incrementar' => $actual + 1,
                    'disminuir' => $actual - 1,
                    default => 0,
                };
                if ($cantidad < 1 || $cantidad > 99) throw new DomainException('Selecciona una cantidad entre 1 y 99.');
                if ($cantidad > $disponible) throw new DomainException('Solo hay ' . $disponible . ' unidad(es) disponibles.');
                $items[$productoId] = $cantidad;
            }
        } else {
            throw new DomainException('La acción solicitada no es válida.');
        }
        guardarCarritoInvitadoAtenea($items);
    }
    ateneaFlash('success', 'Carrito actualizado', $accion === 'agregar' ? 'El producto se añadió correctamente.' : 'Los cambios se guardaron correctamente.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    ateneaFlash('warning', 'No fue posible actualizar el carrito', $e instanceof DomainException ? $e->getMessage() : 'Inténtalo nuevamente.');
}

header('Location: ' . $destino);
exit;
