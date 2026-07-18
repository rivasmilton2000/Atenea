USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_conversaciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tipo ENUM('individual') NOT NULL DEFAULT 'individual',
 clave_individual CHAR(64) NOT NULL,
 creado_por INT UNSIGNED NOT NULL,
 estado ENUM('activa','cerrada') NOT NULL DEFAULT 'activa',
 ultimo_mensaje_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_chat_individual(clave_individual),
 KEY idx_chat_actividad(estado,ultimo_mensaje_at),
 CONSTRAINT fk_chat_creador FOREIGN KEY(creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_participantes (
 conversacion_id BIGINT UNSIGNED NOT NULL,
 usuario_id INT UNSIGNED NOT NULL,
 ultimo_leido_mensaje_id BIGINT UNSIGNED NULL,
 unido_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 archivado_at DATETIME NULL,
 PRIMARY KEY(conversacion_id,usuario_id),
 KEY idx_chat_participante_usuario(usuario_id,archivado_at),
 CONSTRAINT fk_chat_part_conversacion FOREIGN KEY(conversacion_id) REFERENCES chat_conversaciones(id) ON DELETE CASCADE,
 CONSTRAINT fk_chat_part_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_mensajes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 conversacion_id BIGINT UNSIGNED NOT NULL,
 remitente_id INT UNSIGNED NOT NULL,
 contenido TEXT NOT NULL,
 estado ENUM('activo','moderado','eliminado') NOT NULL DEFAULT 'activo',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_chat_mensaje_conversacion(conversacion_id,id),
 KEY idx_chat_mensaje_remitente(remitente_id,created_at),
 CONSTRAINT fk_chat_mensaje_conversacion FOREIGN KEY(conversacion_id) REFERENCES chat_conversaciones(id) ON DELETE CASCADE,
 CONSTRAINT fk_chat_mensaje_remitente FOREIGN KEY(remitente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_lecturas (
 mensaje_id BIGINT UNSIGNED NOT NULL,
 usuario_id INT UNSIGNED NOT NULL,
 leido_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(mensaje_id,usuario_id),
 KEY idx_chat_lectura_usuario(usuario_id,leido_at),
 CONSTRAINT fk_chat_lectura_mensaje FOREIGN KEY(mensaje_id) REFERENCES chat_mensajes(id) ON DELETE CASCADE,
 CONSTRAINT fk_chat_lectura_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_adjuntos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 mensaje_id BIGINT UNSIGNED NOT NULL,
 archivo_relpath VARCHAR(255) NOT NULL,
 archivo_nombre VARCHAR(190) NOT NULL,
 archivo_mime VARCHAR(100) NOT NULL,
 archivo_tamano INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY idx_chat_adjunto_mensaje(mensaje_id),
 CONSTRAINT fk_chat_adjunto_mensaje FOREIGN KEY(mensaje_id) REFERENCES chat_mensajes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_reportes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 mensaje_id BIGINT UNSIGNED NOT NULL,
 reportado_por INT UNSIGNED NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 estado ENUM('pendiente','revisado','descartado','moderado') NOT NULL DEFAULT 'pendiente',
 revisado_por INT UNSIGNED NULL,
 revisado_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_chat_reporte_usuario(mensaje_id,reportado_por),
 KEY idx_chat_reporte_estado(estado,created_at),
 CONSTRAINT fk_chat_reporte_mensaje FOREIGN KEY(mensaje_id) REFERENCES chat_mensajes(id),
 CONSTRAINT fk_chat_reporte_usuario FOREIGN KEY(reportado_por) REFERENCES usuarios(id),
 CONSTRAINT fk_chat_reporte_admin FOREIGN KEY(revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_bloqueos (
 usuario_id INT UNSIGNED PRIMARY KEY,
 bloqueado_por INT UNSIGNED NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 bloqueado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 desbloqueado_at DATETIME NULL,
 CONSTRAINT fk_chat_bloqueo_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
 CONSTRAINT fk_chat_bloqueo_admin FOREIGN KEY(bloqueado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS correo_centro_hilos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 asunto VARCHAR(190) NOT NULL,
 usuario_relacionado_id INT UNSIGNED NULL,
 ultimo_mensaje_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_correo_centro_actividad(ultimo_mensaje_at),
 KEY idx_correo_centro_usuario(usuario_relacionado_id,ultimo_mensaje_at),
 CONSTRAINT fk_correo_centro_usuario FOREIGN KEY(usuario_relacionado_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS correo_centro_mensajes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 hilo_id BIGINT UNSIGNED NOT NULL,
 direccion ENUM('entrada','salida') NOT NULL,
 autor_usuario_id INT UNSIGNED NULL,
 remitente VARCHAR(190) NOT NULL,
 destinatario VARCHAR(190) NOT NULL,
 reply_to VARCHAR(190) NULL,
 asunto VARCHAR(190) NOT NULL,
 contenido_texto MEDIUMTEXT NOT NULL,
 message_id_servidor VARCHAR(255) NULL,
 uid_imap BIGINT UNSIGNED NULL,
 carpeta_imap VARCHAR(120) NULL,
 in_reply_to VARCHAR(255) NULL,
 estado ENUM('pendiente','enviado','recibido','fallido') NOT NULL,
 error_sanitizado VARCHAR(500) NULL,
 enviado_recibido_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_correo_message_id(message_id_servidor),
 UNIQUE KEY uq_correo_imap(carpeta_imap,uid_imap),
 KEY idx_correo_hilo_fecha(hilo_id,enviado_recibido_at),
 KEY idx_correo_autor(autor_usuario_id,enviado_recibido_at),
 KEY idx_correo_estado(estado,enviado_recibido_at),
 CONSTRAINT fk_correo_mensaje_hilo FOREIGN KEY(hilo_id) REFERENCES correo_centro_hilos(id) ON DELETE CASCADE,
 CONSTRAINT fk_correo_mensaje_autor FOREIGN KEY(autor_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS correo_centro_adjuntos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 mensaje_id BIGINT UNSIGNED NOT NULL,
 archivo_relpath VARCHAR(255) NOT NULL,
 archivo_nombre VARCHAR(190) NOT NULL,
 archivo_mime VARCHAR(100) NOT NULL,
 archivo_tamano INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY idx_correo_adjunto_mensaje(mensaje_id),
 CONSTRAINT fk_correo_adjunto_mensaje FOREIGN KEY(mensaje_id) REFERENCES correo_centro_mensajes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS correo_imap_estado (
 carpeta VARCHAR(120) PRIMARY KEY,
 ultimo_uid BIGINT UNSIGNED NOT NULL DEFAULT 0,
 ultima_sincronizacion_at DATETIME NULL,
 ultimo_error VARCHAR(500) NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE correo_centro_mensajes
 ADD COLUMN IF NOT EXISTS leido_at DATETIME NULL AFTER estado,
 ADD KEY IF NOT EXISTS idx_correo_no_leido (direccion,leido_at,enviado_recibido_at);
