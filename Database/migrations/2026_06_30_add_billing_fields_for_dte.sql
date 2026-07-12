-- Ejecutar una sola vez en la base de datos `atenea`
-- Agrega campos fiscales para capturar datos DTE por compra sin perder compras existentes

ALTER TABLE `ordenes`
    ADD COLUMN IF NOT EXISTS `billing_tipo_documento` VARCHAR(10) NOT NULL DEFAULT '' AFTER `billing_address`,
    ADD COLUMN IF NOT EXISTS `billing_numero_documento` VARCHAR(25) NOT NULL DEFAULT '' AFTER `billing_tipo_documento`,
    ADD COLUMN IF NOT EXISTS `billing_telefono` VARCHAR(20) NULL AFTER `billing_numero_documento`,
    ADD COLUMN IF NOT EXISTS `billing_departamento` VARCHAR(100) NULL AFTER `billing_telefono`,
    ADD COLUMN IF NOT EXISTS `billing_municipio` VARCHAR(100) NULL AFTER `billing_departamento`,
    ADD COLUMN IF NOT EXISTS `billing_distrito` VARCHAR(100) NULL AFTER `billing_municipio`,
    ADD COLUMN IF NOT EXISTS `billing_nrc` VARCHAR(20) NULL AFTER `billing_distrito`;
