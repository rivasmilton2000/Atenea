CREATE DATABASE IF NOT EXISTS db_atenea
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_atenea;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin', 'usuario', 'docente') NOT NULL DEFAULT 'usuario',
  foto VARCHAR(255) NULL,
  estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  ultimo_acceso DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_usuarios_rol_estado (rol, estado)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Los usuarios se crean con src/database/crear_admin.php para generar hashes reales
-- mediante password_hash(). No se almacenan contraseñas en texto plano.
