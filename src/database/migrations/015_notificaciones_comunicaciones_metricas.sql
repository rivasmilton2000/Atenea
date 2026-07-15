USE db_atenea;

-- Etapa 3. Migracion aditiva: no elimina tablas, columnas ni datos existentes.
ALTER TABLE admin_notices
 MODIFY created_by INT UNSIGNED NULL,
 ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'sistema' AFTER type,
 ADD COLUMN level ENUM('informacion','exito','advertencia','error') NOT NULL DEFAULT 'informacion' AFTER category,
 ADD COLUMN action_url VARCHAR(500) NULL AFTER target_section,
 ADD COLUMN idempotency_key VARCHAR(190) NULL AFTER action_url,
 ADD COLUMN pedido_id BIGINT UNSIGNED NULL AFTER idempotency_key,
 ADD COLUMN correo_envio_id BIGINT UNSIGNED NULL AFTER pedido_id,
 ADD COLUMN hilo_id BIGINT UNSIGNED NULL AFTER correo_envio_id,
 ADD COLUMN error_id BIGINT UNSIGNED NULL AFTER hilo_id,
 ADD UNIQUE KEY uq_admin_notice_idempotencia(idempotency_key),
 ADD INDEX idx_notice_campana(user_id,status,created_at),
 ADD INDEX idx_notice_categoria_fecha(category,created_at),
 ADD INDEX idx_notice_pedido(pedido_id),
 ADD CONSTRAINT fk_notice_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
 ADD CONSTRAINT fk_notice_correo FOREIGN KEY(correo_envio_id) REFERENCES correo_envios(id) ON DELETE SET NULL;

CREATE TABLE comunicacion_hilos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 canal ENUM('contacto','soporte','pedido','plataforma') NOT NULL,
 asunto VARCHAR(190) NOT NULL,
 usuario_id INT UNSIGNED NULL,
 nombre_contacto VARCHAR(180) NULL,
 correo_contacto VARCHAR(190) NULL,
 pedido_id BIGINT UNSIGNED NULL,
 estado ENUM('recibido','pendiente','respondido','cerrado') NOT NULL DEFAULT 'recibido',
 ultimo_mensaje_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_hilo_estado_fecha(estado,ultimo_mensaje_at),
 INDEX idx_hilo_usuario_fecha(usuario_id,ultimo_mensaje_at),
 INDEX idx_hilo_pedido(pedido_id),
 CONSTRAINT fk_hilo_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
 CONSTRAINT fk_hilo_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comunicacion_mensajes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 hilo_id BIGINT UNSIGNED NOT NULL,
 direccion ENUM('entrada','salida','interno') NOT NULL,
 autor_usuario_id INT UNSIGNED NULL,
 autor_nombre VARCHAR(180) NULL,
 autor_correo VARCHAR(190) NULL,
 contenido TEXT NOT NULL,
 correo_envio_id BIGINT UNSIGNED NULL,
 resultado_envio ENUM('no_aplica','pendiente','enviado','fallido') NOT NULL DEFAULT 'no_aplica',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_mensaje_hilo_fecha(hilo_id,created_at),
 INDEX idx_mensaje_correo(correo_envio_id),
 CONSTRAINT fk_mensaje_hilo FOREIGN KEY(hilo_id) REFERENCES comunicacion_hilos(id),
 CONSTRAINT fk_mensaje_autor FOREIGN KEY(autor_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
 CONSTRAINT fk_mensaje_correo FOREIGN KEY(correo_envio_id) REFERENCES correo_envios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE errores_sistema (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 fingerprint CHAR(64) NOT NULL,
 categoria ENUM('pago','webhook','dte','correo','stock','base_datos','sistema') NOT NULL,
 modulo VARCHAR(80) NOT NULL,
 nivel ENUM('advertencia','error','critico') NOT NULL DEFAULT 'error',
 mensaje VARCHAR(500) NOT NULL,
 contexto_sanitizado JSON NULL,
 ocurrencias INT UNSIGNED NOT NULL DEFAULT 1,
 estado ENUM('nuevo','revisando','resuelto') NOT NULL DEFAULT 'nuevo',
 pedido_id BIGINT UNSIGNED NULL,
 usuario_id INT UNSIGNED NULL,
 observacion_resolucion VARCHAR(1000) NULL,
 actualizado_por INT UNSIGNED NULL,
 primera_ocurrencia_at DATETIME NOT NULL,
 ultima_ocurrencia_at DATETIME NOT NULL,
 resuelto_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_error_fingerprint(fingerprint),
 INDEX idx_error_estado_fecha(estado,ultima_ocurrencia_at),
 INDEX idx_error_categoria_fecha(categoria,ultima_ocurrencia_at),
 INDEX idx_error_pedido(pedido_id),
 CONSTRAINT fk_error_pedido FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
 CONSTRAINT fk_error_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
 CONSTRAINT fk_error_admin FOREIGN KEY(actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE admin_notices
 ADD CONSTRAINT fk_notice_hilo FOREIGN KEY(hilo_id) REFERENCES comunicacion_hilos(id) ON DELETE SET NULL,
 ADD CONSTRAINT fk_notice_error FOREIGN KEY(error_id) REFERENCES errores_sistema(id) ON DELETE SET NULL;

ALTER TABLE correo_envios
 ADD COLUMN asunto VARCHAR(190) NULL AFTER tipo,
 ADD COLUMN hilo_id BIGINT UNSIGNED NULL AFTER pedido_id,
 ADD INDEX idx_correo_estado_fecha(estado,created_at),
 ADD INDEX idx_correo_hilo(hilo_id),
 ADD CONSTRAINT fk_correo_hilo FOREIGN KEY(hilo_id) REFERENCES comunicacion_hilos(id) ON DELETE SET NULL;

ALTER TABLE pedidos ADD INDEX idx_pedido_estado_fecha(estado,created_at);
ALTER TABLE pagos ADD INDEX idx_pago_estado_fecha(estado,created_at);
ALTER TABLE pedido_detalles ADD INDEX idx_detalle_producto_pedido(producto_id,pedido_id);
