USE db_atenea;
ALTER TABLE usuarios MODIFY password VARCHAR(255) NULL;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) NULL AFTER password;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS proveedor ENUM('local','google','mixto') NOT NULL DEFAULT 'local' AFTER google_id;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email_verificado TINYINT(1) NOT NULL DEFAULT 0 AFTER proveedor;
ALTER TABLE usuarios MODIFY foto VARCHAR(500) NULL;
CREATE UNIQUE INDEX IF NOT EXISTS uq_usuarios_google_id ON usuarios (google_id);
