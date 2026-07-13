-- Solo ejecutar si no existen pedidos ni movimientos. Conserva seguridad antes que comodidad.
USE db_atenea;
DELIMITER //
CREATE PROCEDURE validar_reversion_comercio()
BEGIN
 IF EXISTS(SELECT 1 FROM pedidos LIMIT 1) OR EXISTS(SELECT 1 FROM inventario_movimientos LIMIT 1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Reversión cancelada: existen pedidos o movimientos'; END IF;
END//
DELIMITER ;
CALL validar_reversion_comercio(); DROP PROCEDURE validar_reversion_comercio;
DROP TABLE stripe_eventos,pedido_historial,pagos,inventario_movimientos,pedido_detalles,pedidos,promociones,producto_imagenes,productos,categorias_producto;
