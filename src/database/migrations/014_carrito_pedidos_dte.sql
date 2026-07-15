USE db_atenea;

CREATE TABLE IF NOT EXISTS carritos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL,
 estado ENUM('activo','convertido','abandonado') NOT NULL DEFAULT 'activo', version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_carrito_usuario_estado(usuario_id,estado), CONSTRAINT fk_carrito_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS carrito_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, carrito_id BIGINT UNSIGNED NOT NULL, producto_id INT UNSIGNED NOT NULL,
 cantidad INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_carrito_producto(carrito_id,producto_id), INDEX idx_carrito_item(carrito_id),
 CONSTRAINT fk_item_carrito FOREIGN KEY(carrito_id) REFERENCES carritos(id) ON DELETE CASCADE,
 CONSTRAINT fk_item_producto FOREIGN KEY(producto_id) REFERENCES productos(id), CONSTRAINT chk_item_cantidad CHECK(cantidad>0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS direcciones_usuario (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL,
 etiqueta ENUM('casa','oficina','otra') NOT NULL, etiqueta_personalizada VARCHAR(60) NULL, etiqueta_normalizada VARCHAR(80) NOT NULL,
 receptor VARCHAR(160) NOT NULL, telefono VARCHAR(30) NOT NULL, departamento_id SMALLINT UNSIGNED NOT NULL,
 municipio_id SMALLINT UNSIGNED NOT NULL, distrito_id SMALLINT UNSIGNED NULL, direccion_detallada VARCHAR(500) NOT NULL,
 referencias VARCHAR(500) NULL, predeterminada TINYINT(1) NOT NULL DEFAULT 0, activa TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_direccion_etiqueta(usuario_id,etiqueta_normalizada), INDEX idx_direccion_usuario(usuario_id,activa,predeterminada),
 CONSTRAINT fk_direccion_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
 CONSTRAINT fk_direccion_departamento FOREIGN KEY(departamento_id) REFERENCES departamentos(id),
 CONSTRAINT fk_direccion_municipio FOREIGN KEY(municipio_id) REFERENCES municipios(id),
 CONSTRAINT fk_direccion_distrito FOREIGN KEY(distrito_id) REFERENCES distritos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pedidos
 MODIFY estado ENUM('pendiente','esperando_pago','fallido','carrito','pendiente_pago','pagado','preparando','enviado','entregado','cancelado','pago_fallido','reembolsado') NOT NULL DEFAULT 'pendiente_pago',
 ADD COLUMN carrito_id BIGINT UNSIGNED NULL AFTER usuario_id,
 ADD COLUMN direccion_id BIGINT UNSIGNED NULL AFTER carrito_id,
 ADD COLUMN direccion_snapshot JSON NULL AFTER direccion_id,
 ADD COLUMN envio DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER descuento,
 ADD COLUMN impuestos DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER envio,
 ADD COLUMN checkout_key CHAR(64) NULL AFTER moneda,
 ADD UNIQUE KEY uq_pedido_checkout_key(checkout_key),
 ADD INDEX idx_pedido_carrito(carrito_id),
 ADD CONSTRAINT fk_pedido_carrito FOREIGN KEY(carrito_id) REFERENCES carritos(id) ON DELETE SET NULL,
 ADD CONSTRAINT fk_pedido_direccion FOREIGN KEY(direccion_id) REFERENCES direcciones_usuario(id) ON DELETE SET NULL;

UPDATE pedidos SET estado='pendiente_pago' WHERE estado IN('pendiente','esperando_pago');
UPDATE pedidos SET estado='pago_fallido' WHERE estado='fallido';
ALTER TABLE pedidos MODIFY estado ENUM('carrito','pendiente_pago','pagado','preparando','enviado','entregado','cancelado','pago_fallido','reembolsado') NOT NULL DEFAULT 'pendiente_pago';

ALTER TABLE pedido_historial
 MODIFY origen ENUM('sistema','stripe','admin','usuario','dte') NOT NULL,
 ADD COLUMN pago_id BIGINT UNSIGNED NULL AFTER usuario_id,
 ADD INDEX idx_historial_estado_fecha(estado_nuevo,created_at),
 ADD CONSTRAINT fk_historial_pago FOREIGN KEY(pago_id) REFERENCES pagos(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS dte_configuracion (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ambiente ENUM('simulation','test','production') NOT NULL DEFAULT 'simulation',
 nit VARCHAR(20) NOT NULL, nrc VARCHAR(20) NOT NULL, nombre_comercial VARCHAR(180) NOT NULL, razon_social VARCHAR(220) NOT NULL,
 actividad_codigo VARCHAR(10) NOT NULL, actividad_descripcion VARCHAR(250) NOT NULL, direccion VARCHAR(500) NOT NULL,
 departamento_codigo CHAR(2) NOT NULL DEFAULT '06', municipio_codigo CHAR(2) NOT NULL DEFAULT '14', telefono VARCHAR(30) NOT NULL,
 correo VARCHAR(180) NOT NULL, codigo_establecimiento CHAR(4) NOT NULL DEFAULT 'M001', punto_venta CHAR(4) NOT NULL DEFAULT 'P001',
 schema_version VARCHAR(20) NOT NULL DEFAULT '1', activo TINYINT(1) NOT NULL DEFAULT 1, actualizado_por INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_dte_config_activa(activo,ambiente), CONSTRAINT fk_dte_config_admin FOREIGN KEY(actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dte_correlativos (
 tipo_dte CHAR(2) NOT NULL, establecimiento CHAR(4) NOT NULL, punto_venta CHAR(4) NOT NULL, ultimo BIGINT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(tipo_dte,establecimiento,punto_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dte_documentos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, pedido_id BIGINT UNSIGNED NOT NULL, tipo_dte CHAR(2) NOT NULL DEFAULT '01',
 ambiente ENUM('simulation','test','production') NOT NULL, codigo_generacion CHAR(36) NOT NULL, numero_control VARCHAR(31) NOT NULL,
 sello_recepcion VARCHAR(255) NULL, estado VARCHAR(40) NOT NULL, schema_version VARCHAR(20) NOT NULL,
 json_documento JSON NOT NULL, json_sha256 CHAR(64) NOT NULL, pdf_relpath VARCHAR(255) NULL, pdf_sha256 CHAR(64) NULL,
 observaciones VARCHAR(1000) NULL, emitido_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_dte_pedido(pedido_id), UNIQUE KEY uq_dte_codigo(codigo_generacion), UNIQUE KEY uq_dte_control(numero_control),
 INDEX idx_dte_estado_fecha(estado,emitido_at), CONSTRAINT fk_dte_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dte_eventos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, dte_id BIGINT UNSIGNED NULL, pedido_id BIGINT UNSIGNED NOT NULL,
 operacion VARCHAR(40) NOT NULL, ambiente VARCHAR(20) NOT NULL, resultado VARCHAR(40) NOT NULL,
 request_sanitizado JSON NULL, response_sanitizado JSON NULL, codigo VARCHAR(80) NULL, observaciones VARCHAR(1000) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_dte_evento(dte_id,created_at), INDEX idx_dte_pedido_evento(pedido_id,created_at),
 CONSTRAINT fk_dte_evento_doc FOREIGN KEY(dte_id) REFERENCES dte_documentos(id) ON DELETE SET NULL,
 CONSTRAINT fk_dte_evento_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
