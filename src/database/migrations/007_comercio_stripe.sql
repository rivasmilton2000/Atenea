USE db_atenea;

CREATE TABLE IF NOT EXISTS categorias_producto (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,nombre VARCHAR(120) NOT NULL,slug VARCHAR(140) NOT NULL UNIQUE,descripcion VARCHAR(500) NULL,activo TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,categoria_id INT UNSIGNED NULL,sku VARCHAR(80) NULL UNIQUE,nombre VARCHAR(180) NOT NULL,slug VARCHAR(200) NOT NULL UNIQUE,descripcion_corta VARCHAR(500) NOT NULL,descripcion TEXT NOT NULL,precio DECIMAL(12,2) NOT NULL,stock INT UNSIGNED NOT NULL DEFAULT 0,stock_reservado INT UNSIGNED NOT NULL DEFAULT 0,stock_minimo INT UNSIGNED NOT NULL DEFAULT 0,disponible TINYINT(1) NOT NULL DEFAULT 1,activo TINYINT(1) NOT NULL DEFAULT 1,imagen_principal VARCHAR(500) NULL,eliminado_at DATETIME NULL,creado_por INT UNSIGNED NULL,actualizado_por INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_productos_publicos(activo,disponible,eliminado_at),INDEX idx_productos_stock(stock,stock_minimo),CONSTRAINT fk_productos_categoria FOREIGN KEY(categoria_id) REFERENCES categorias_producto(id) ON DELETE SET NULL,CONSTRAINT fk_productos_creado FOREIGN KEY(creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,CONSTRAINT fk_productos_actualizado FOREIGN KEY(actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,CONSTRAINT chk_producto_precio CHECK(precio>=0),CONSTRAINT chk_producto_reserva CHECK(stock_reservado<=stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS producto_imagenes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,producto_id INT UNSIGNED NOT NULL,ruta VARCHAR(500) NOT NULL,orden INT NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_imagen_producto(producto_id,orden),CONSTRAINT fk_imagen_producto FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promociones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,producto_id INT UNSIGNED NOT NULL,etiqueta VARCHAR(100) NULL,precio_promocional DECIMAL(12,2) NOT NULL,porcentaje_descuento DECIMAL(5,2) NULL,fecha_inicio DATETIME NOT NULL,fecha_fin DATETIME NOT NULL,activa TINYINT(1) NOT NULL DEFAULT 1,creado_por INT UNSIGNED NULL,actualizado_por INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_promocion_vigente(producto_id,activa,fecha_inicio,fecha_fin),CONSTRAINT fk_promocion_producto FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE,CONSTRAINT fk_promocion_creado FOREIGN KEY(creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,CONSTRAINT fk_promocion_actualizado FOREIGN KEY(actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,CONSTRAINT chk_promocion_fechas CHECK(fecha_fin>fecha_inicio),CONSTRAINT chk_promocion_precio CHECK(precio_promocional>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,numero VARCHAR(32) NOT NULL UNIQUE,usuario_id INT UNSIGNED NOT NULL,subtotal DECIMAL(12,2) NOT NULL,descuento DECIMAL(12,2) NOT NULL DEFAULT 0,total DECIMAL(12,2) NOT NULL,moneda CHAR(3) NOT NULL DEFAULT 'usd',estado ENUM('pendiente','esperando_pago','pagado','fallido','cancelado','reembolsado') NOT NULL DEFAULT 'pendiente',stripe_checkout_session_id VARCHAR(255) NULL UNIQUE,stripe_payment_intent_id VARCHAR(255) NULL UNIQUE,stock_procesado TINYINT(1) NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_pedido_usuario_fecha(usuario_id,created_at),INDEX idx_pedido_estado(estado),CONSTRAINT fk_pedido_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id),CONSTRAINT chk_pedido_total CHECK(total>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_detalles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,pedido_id BIGINT UNSIGNED NOT NULL,producto_id INT UNSIGNED NOT NULL,nombre_producto VARCHAR(180) NOT NULL,sku VARCHAR(80) NULL,cantidad INT UNSIGNED NOT NULL,precio_normal DECIMAL(12,2) NOT NULL,precio_unitario DECIMAL(12,2) NOT NULL,descuento_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,subtotal DECIMAL(12,2) NOT NULL,promocion_id INT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_detalle_pedido(pedido_id),CONSTRAINT fk_detalle_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id),CONSTRAINT fk_detalle_producto FOREIGN KEY(producto_id) REFERENCES productos(id),CONSTRAINT fk_detalle_promocion FOREIGN KEY(promocion_id) REFERENCES promociones(id) ON DELETE SET NULL,CONSTRAINT chk_detalle_cantidad CHECK(cantidad>0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventario_movimientos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,producto_id INT UNSIGNED NOT NULL,pedido_id BIGINT UNSIGNED NULL,usuario_admin_id INT UNSIGNED NULL,tipo ENUM('entrada','salida','ajuste','venta') NOT NULL,cantidad INT NOT NULL,stock_anterior INT UNSIGNED NOT NULL,stock_nuevo INT UNSIGNED NOT NULL,nota VARCHAR(500) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_mov_producto_fecha(producto_id,created_at),CONSTRAINT fk_mov_producto FOREIGN KEY(producto_id) REFERENCES productos(id),CONSTRAINT fk_mov_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,CONSTRAINT fk_mov_admin FOREIGN KEY(usuario_admin_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,pedido_id BIGINT UNSIGNED NOT NULL,proveedor VARCHAR(30) NOT NULL DEFAULT 'stripe',stripe_payment_intent_id VARCHAR(255) NULL,importe DECIMAL(12,2) NOT NULL,moneda CHAR(3) NOT NULL,estado VARCHAR(40) NOT NULL,datos_referencia JSON NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_pago_pedido_proveedor(pedido_id,proveedor),CONSTRAINT fk_pago_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_historial (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,pedido_id BIGINT UNSIGNED NOT NULL,estado_anterior VARCHAR(30) NULL,estado_nuevo VARCHAR(30) NOT NULL,origen ENUM('sistema','stripe','admin','usuario') NOT NULL,usuario_id INT UNSIGNED NULL,nota VARCHAR(500) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_historial_pedido(pedido_id,created_at),CONSTRAINT fk_historial_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id),CONSTRAINT fk_historial_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stripe_eventos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,stripe_event_id VARCHAR(255) NOT NULL UNIQUE,tipo VARCHAR(100) NOT NULL,procesado TINYINT(1) NOT NULL DEFAULT 0,error_mensaje VARCHAR(500) NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,procesado_at DATETIME NULL,INDEX idx_stripe_evento_estado(procesado,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
