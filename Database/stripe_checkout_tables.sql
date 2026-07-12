-- Ejecutar en la base de datos `atenea`
-- Tablas para checkout con Stripe

CREATE TABLE IF NOT EXISTS ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    stripe_session_id VARCHAR(255) DEFAULT NULL,
    stripe_payment_intent VARCHAR(255) DEFAULT NULL,
    billing_name VARCHAR(120) NOT NULL,
    billing_email VARCHAR(150) NOT NULL,
    billing_address VARCHAR(255) NOT NULL,
    billing_tipo_documento VARCHAR(10) NOT NULL DEFAULT '',
    billing_numero_documento VARCHAR(25) NOT NULL DEFAULT '',
    billing_telefono VARCHAR(20) DEFAULT NULL,
    billing_departamento VARCHAR(100) DEFAULT NULL,
    billing_municipio VARCHAR(100) DEFAULT NULL,
    billing_distrito VARCHAR(100) DEFAULT NULL,
    billing_nrc VARCHAR(20) DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('pending_payment','paid','cancelled','failed') NOT NULL DEFAULT 'pending_payment',
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ordenes_session_id (session_id),
    INDEX idx_ordenes_stripe_session_id (stripe_session_id),
    INDEX idx_ordenes_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS orden_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    producto_id INT NOT NULL,
    producto_nombre VARCHAR(255) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orden_detalles_orden_id (orden_id),
    INDEX idx_orden_detalles_producto_id (producto_id),
    CONSTRAINT fk_orden_detalles_orden
        FOREIGN KEY (orden_id) REFERENCES ordenes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS orden_facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    billing_email VARCHAR(150) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    email_status ENUM('sent','failed') NOT NULL DEFAULT 'failed',
    error_message VARCHAR(500) DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_orden_facturas_orden_id (orden_id),
    INDEX idx_orden_facturas_email_status (email_status),
    CONSTRAINT fk_orden_facturas_orden
        FOREIGN KEY (orden_id) REFERENCES ordenes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
