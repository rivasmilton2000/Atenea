-- Ejecutar en la base de datos `atenea`
-- Esquema DTE para Atenea

CREATE TABLE IF NOT EXISTS dte_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mode ENUM('simulation','test','production') NOT NULL DEFAULT 'simulation',
    ambiente VARCHAR(10) NOT NULL DEFAULT '00',
    emisor_nit VARCHAR(20) NOT NULL,
    emisor_nrc VARCHAR(20) NOT NULL,
    emisor_nombre VARCHAR(255) NOT NULL,
    emisor_nombre_comercial VARCHAR(255) NOT NULL,
    emisor_cod_actividad VARCHAR(20) NOT NULL,
    emisor_desc_actividad VARCHAR(255) NOT NULL,
    emisor_tipo_establecimiento VARCHAR(50) NOT NULL,
    emisor_departamento VARCHAR(100) NOT NULL,
    emisor_municipio VARCHAR(100) NOT NULL,
    emisor_direccion VARCHAR(255) NOT NULL,
    emisor_telefono VARCHAR(30) NOT NULL,
    emisor_correo VARCHAR(150) NOT NULL,
    cod_estable_mh VARCHAR(20) NOT NULL,
    cod_estable VARCHAR(20) NOT NULL,
    cod_punto_venta_mh VARCHAR(20) NOT NULL,
    cod_punto_venta VARCHAR(20) NOT NULL,
    api_user VARCHAR(150) DEFAULT NULL,
    api_password_encrypted TEXT DEFAULT NULL,
    certificate_path VARCHAR(255) DEFAULT NULL,
    certificate_password_encrypted TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dte_settings_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dte_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    tipo_dte VARCHAR(10) NOT NULL DEFAULT '01',
    numero_control VARCHAR(50) NOT NULL,
    codigo_generacion VARCHAR(50) NOT NULL,
    sello_recibido VARCHAR(120) DEFAULT NULL,
    modelo_facturacion VARCHAR(50) NOT NULL DEFAULT 'Previo',
    tipo_transmision VARCHAR(50) NOT NULL DEFAULT 'Normal',
    version_json INT NOT NULL DEFAULT 1,
    ambiente VARCHAR(10) NOT NULL DEFAULT '00',
    estado VARCHAR(50) NOT NULL DEFAULT 'PENDIENTE',
    codigo_msg VARCHAR(20) DEFAULT NULL,
    descripcion_msg VARCHAR(255) DEFAULT NULL,
    fecha_emision DATE NOT NULL,
    hora_emision TIME NOT NULL,
    total_pagar DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    json_path VARCHAR(255) DEFAULT NULL,
    pdf_path VARCHAR(255) DEFAULT NULL,
    response_path VARCHAR(255) DEFAULT NULL,
    modo ENUM('simulation','test','production') NOT NULL DEFAULT 'simulation',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dte_documents_order_id (order_id),
    UNIQUE KEY uq_dte_documents_numero_control (numero_control),
    UNIQUE KEY uq_dte_documents_codigo_generacion (codigo_generacion),
    INDEX idx_dte_documents_estado (estado),
    INDEX idx_dte_documents_modo (modo),
    INDEX idx_dte_documents_fecha (fecha_emision),
    CONSTRAINT fk_dte_documents_order
        FOREIGN KEY (order_id) REFERENCES ordenes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dte_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_dte VARCHAR(10) NOT NULL,
    cod_estable VARCHAR(20) NOT NULL,
    cod_punto_venta VARCHAR(20) NOT NULL,
    current_number BIGINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dte_sequences_scope (tipo_dte, cod_estable, cod_punto_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
