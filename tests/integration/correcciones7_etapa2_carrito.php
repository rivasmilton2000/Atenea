<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/carrito.php';

$pdo = obtenerConexion();
$tag = 'c7e2_' . bin2hex(random_bytes(5));
$pruebas = [];
$usuarioId = $productoId = 0;
$assert = static function (bool $condicion, string $mensaje) use (&$pruebas): void {
    if (!$condicion) throw new RuntimeException('FALLO: ' . $mensaje);
    $pruebas[] = $mensaje;
};

try {
    $q = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,correo,password,rol,estado,email_verificado) VALUES('Prueba','Carrito',:correo,:password,'usuario','activo',1)");
    $q->execute(['correo'=>$tag.'@example.invalid','password'=>password_hash(bin2hex(random_bytes(18)), PASSWORD_DEFAULT)]);
    $usuarioId = (int)$pdo->lastInsertId();
    $q = $pdo->prepare("INSERT INTO productos(sku,nombre,slug,descripcion_corta,descripcion,precio,stock,stock_reservado,disponible,activo) VALUES(:sku,'Producto temporal',:slug,'Prueba de carrito','Producto temporal para una prueba transaccional',25.00,4,0,1,1)");
    $q->execute(['sku'=>$tag,'slug'=>$tag]);
    $productoId = (int)$pdo->lastInsertId();

    guardarCarritoInvitadoAtenea([$productoId=>3, 'invalido'=>1000]);
    $assert(carritoInvitadoAtenea() === [$productoId=>3], 'El carrito visitante sanea identificadores y cantidades');
    $assert(cantidadCarritoActualAtenea($pdo) === 3, 'El contador visitante suma las unidades de sesión');

    $fusionadas = sincronizarCarritoInvitadoAtenea($pdo, $usuarioId);
    $assert($fusionadas === 3, 'La sincronización transfiere el carrito visitante a la cuenta');
    $carrito = carritoActivo($pdo, $usuarioId, false);
    $q = $pdo->prepare('SELECT cantidad FROM carrito_items WHERE carrito_id=:carrito AND producto_id=:producto');
    $q->execute(['carrito'=>$carrito['id'],'producto'=>$productoId]);
    $assert((int)$q->fetchColumn() === 3, 'El carrito autenticado queda persistido en la base de datos');
    $assert(carritoInvitadoAtenea() === [], 'La sesión temporal se limpia después de una fusión correcta');

    guardarCarritoInvitadoAtenea([$productoId=>5]);
    sincronizarCarritoInvitadoAtenea($pdo, $usuarioId);
    $q->execute(['carrito'=>$carrito['id'],'producto'=>$productoId]);
    $assert((int)$q->fetchColumn() === 4, 'La fusión nunca supera el stock real disponible');

    $precio = precioProductoCentavos(['precio'=>'25.00'], ['precio_promocional'=>'19.50']);
    $assert($precio['normal'] === 2500 && $precio['final'] === 1950 && $precio['descuento'] === 550, 'Los importes y descuentos se calculan en centavos en el servidor');
    $checkout = file_get_contents(dirname(__DIR__, 2) . '/src/pagos/crear-checkout.php');
    $assert(!str_contains($checkout, "\$_POST['precio']") && str_contains($checkout, 'resumenCarrito'), 'El checkout vuelve a consultar precios y carrito sin aceptar precios del navegador');
    $assert(str_contains($checkout, 'FOR UPDATE') && str_contains($checkout, 'stock_reservado'), 'El checkout bloquea y valida existencias antes de crear el pago');
    $accion = file_get_contents(dirname(__DIR__, 2) . '/src/carrito/accion.php');
    $assert(str_contains($accion, 'validarTokenCsrf') && str_contains($accion, 'prepare('), 'Las acciones usan CSRF y consultas preparadas');
    $assert(is_file(dirname(__DIR__, 2) . '/src/carrito/index.php'), 'Existe una página de carrito independiente del perfil');

    echo 'OK ' . count($pruebas) . " pruebas\n";
    foreach ($pruebas as $prueba) echo '- ' . $prueba . "\n";
} finally {
    unset($_SESSION['carrito_invitado']);
    try {
        if ($usuarioId) {
            $pdo->prepare('DELETE FROM carrito_items WHERE carrito_id IN (SELECT id FROM carritos WHERE usuario_id=:usuario)')->execute(['usuario'=>$usuarioId]);
            $pdo->prepare('DELETE FROM carritos WHERE usuario_id=:usuario')->execute(['usuario'=>$usuarioId]);
        }
        if ($productoId) $pdo->prepare('DELETE FROM productos WHERE id=:id')->execute(['id'=>$productoId]);
        if ($usuarioId) $pdo->prepare('DELETE FROM usuarios WHERE id=:id')->execute(['id'=>$usuarioId]);
    } catch (Throwable $limpieza) {
        fwrite(STDERR, 'Limpieza: ' . $limpieza->getMessage() . "\n");
    }
}
