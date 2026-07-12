<?php

class DteSchema
{
    public static function ensure(mysqli $db): void
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        $statements = [
            "CREATE TABLE IF NOT EXISTS dte_settings (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS dte_documents (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS dte_sequences (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo_dte VARCHAR(10) NOT NULL,
                cod_estable VARCHAR(20) NOT NULL,
                cod_punto_venta VARCHAR(20) NOT NULL,
                current_number BIGINT NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_dte_sequences_scope (tipo_dte, cod_estable, cod_punto_venta)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $sql) {
            if ($db->query($sql) !== true) {
                throw new RuntimeException('No se pudo preparar el esquema DTE: ' . $db->error);
            }
        }

        self::ensureOrderBillingColumns($db);
        $ensured = true;
    }

    public static function ensureOrderBillingColumns(mysqli $db): void
    {
        static $orderColumnsEnsured = false;

        if ($orderColumnsEnsured) {
            return;
        }

        if (!self::tableExists($db, 'ordenes')) {
            return;
        }

        $columns = [
            'billing_tipo_documento' => "VARCHAR(10) NOT NULL DEFAULT '' AFTER billing_address",
            'billing_numero_documento' => "VARCHAR(25) NOT NULL DEFAULT '' AFTER billing_tipo_documento",
            'billing_telefono' => "VARCHAR(20) DEFAULT NULL AFTER billing_numero_documento",
            'billing_departamento' => "VARCHAR(100) DEFAULT NULL AFTER billing_telefono",
            'billing_municipio' => "VARCHAR(100) DEFAULT NULL AFTER billing_departamento",
            'billing_distrito' => "VARCHAR(100) DEFAULT NULL AFTER billing_municipio",
            'billing_nrc' => "VARCHAR(20) DEFAULT NULL AFTER billing_distrito",
        ];

        foreach ($columns as $column => $definition) {
            if (self::columnExists($db, 'ordenes', $column)) {
                continue;
            }

            $sql = sprintf(
                'ALTER TABLE `ordenes` ADD COLUMN `%s` %s',
                str_replace('`', '``', $column),
                $definition
            );

            if ($db->query($sql) !== true) {
                throw new RuntimeException('No se pudo actualizar la tabla de ordenes para datos DTE: ' . $db->error);
            }
        }

        $orderColumnsEnsured = true;
    }

    private static function columnExists(mysqli $db, string $table, string $column): bool
    {
        $tableName = str_replace('`', '``', $table);
        $columnName = str_replace('`', '``', $column);
        $result = $db->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
        $exists = $result instanceof mysqli_result && $result->num_rows > 0;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $exists;
    }

    private static function tableExists(mysqli $db, string $table): bool
    {
        $tableName = str_replace('`', '``', $table);
        $result = $db->query("SHOW TABLES LIKE '{$tableName}'");
        $exists = $result instanceof mysqli_result && $result->num_rows > 0;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $exists;
    }
}
