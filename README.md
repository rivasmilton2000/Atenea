# Atenea

Atenea es una plataforma web para la gestión académica, administrativa y comercial de una escuela de naturopatía holística. Reúne el sitio institucional, la oferta de capacitaciones, el comercio electrónico y portales diferenciados para administración, docentes y estudiantes.

## Descripción

El sistema centraliza la publicación de contenido institucional y noticias, la administración de usuarios y permisos, la operación de capacitaciones y el seguimiento académico. También incorpora catálogo, carrito, pedidos y pagos mediante Stripe, comprobantes de compra y un módulo de Documentos Tributarios Electrónicos (DTE).

El proveedor DTE incluye un modo de simulación para desarrollo y una integración preparada para los ambientes de prueba y producción del Ministerio de Hacienda de El Salvador. La activación real requiere credenciales, certificado, servicio de firma y autorización del emisor; el repositorio no constituye por sí solo una certificación fiscal.

## Funcionalidades principales

### Sitio institucional

- Página de inicio y secciones administrables.
- Publicación de capacitaciones, noticias y contenido institucional.
- Formularios de contacto y suscripción al boletín.
- Catálogo de productos, carrito y consulta de certificados.
- Diseño adaptable basado en Bootstrap.

### Gestión académica

- Portales separados para estudiantes y docentes.
- Asignación de docentes, clases y capacitaciones.
- Contenido de clase, tareas, evaluaciones, entregas y calificaciones.
- Seguimiento del progreso, expediente académico y certificados.
- Almacenamiento privado configurable para documentos, evidencias y videos.

### Panel administrativo

- Gestión de usuarios, categorías, productos, pedidos y capacitaciones.
- Administración de contenido, navegación, noticias y configuración visual.
- Métricas, notificaciones, bitácora, auditoría y registro de errores.
- Editor con borradores, vista previa, publicación y versionado del sitio.
- Copias de seguridad de base de datos almacenadas fuera del directorio público.

### Usuarios y seguridad

- Autenticación local y acceso con Google OAuth.
- Recuperación de contraseña, sesiones persistentes y cierre por inactividad.
- Roles de administración, docente, estudiante/usuario y administración-docente.
- Permisos individuales para el rol híbrido y controles adicionales para operaciones críticas.
- Protección CSRF, contraseñas cifradas, auditoría y ciclo de vida de cuentas.

### Comercio, pagos y comprobantes

- Carrito, control de pedidos e inventario.
- Checkout y confirmación asíncrona de pagos con Stripe mediante webhook.
- Inscripciones pagadas a capacitaciones.
- Generación de comprobantes internos en PDF.
- Reintentos idempotentes para comprobantes, correo y procesos posteriores al pago.

### DTE y comunicaciones

- Factura Electrónica v1 en modo de simulación.
- Generación de JSON, PDF, código QR, UUID y sello marcado como simulado.
- Proveedor separado para integración con Hacienda en pruebas o producción.
- Correo saliente por SMTP, buzón entrante por IMAP y cola segura.
- Campañas de newsletter, notificaciones y centro de comunicaciones.

## Tecnologías utilizadas

- PHP 8 y PDO.
- MySQL o MariaDB.
- Apache con `.htaccess`.
- HTML5, CSS3 y JavaScript.
- Bootstrap y Bootstrap Icons.
- Composer.
- Dompdf para documentos PDF.
- Endroid QR Code.
- Opis JSON Schema.
- Stripe y Google OAuth como integraciones externas.
- Playwright para pruebas visuales.

## Requisitos del sistema

- Windows, Linux o macOS con un entorno web compatible; XAMPP es una opción para desarrollo local.
- Apache 2.4 o equivalente, con `mod_rewrite`; HTTPS es obligatorio en producción.
- PHP 8.0 o posterior.
- MySQL 8 o MariaDB compatible con InnoDB, JSON y restricciones `CHECK`.
- Composer 2.
- Git.
- Node.js y Playwright únicamente para ejecutar pruebas visuales.

Extensiones PHP utilizadas o requeridas por la aplicación y sus dependencias:

- `pdo_mysql`
- `mysqli`
- `mbstring`
- `json`
- `curl`
- `openssl`
- `fileinfo`
- `gd`
- `imap` para la sincronización del buzón institucional

## Instalación local

1. Clone el repositorio y entre en el directorio:

   ```bash
   git clone https://github.com/rivasmilton2000/Atenea.git
   cd Atenea
   ```

2. Instale las dependencias PHP:

   ```bash
   composer install
   ```

3. Cree una base de datos con juego de caracteres `utf8mb4`:

   ```sql
   CREATE DATABASE db_atenea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. Importe la base inicial:

   ```bash
   mysql -u root -p db_atenea < src/database/db_atenea.sql
   ```

5. Aplique en orden las migraciones pendientes de `src/database/migrations/`. Omita los archivos `*_rollback.sql` durante una instalación o actualización normal.

6. Cree la configuración local:

   ```bash
   cp .env.example .env
   ```

   En Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

7. Complete las variables necesarias en `.env`. No confirme este archivo en Git.

8. Cree fuera del directorio público las carpetas configuradas para archivos académicos, comunicaciones, DTE, comprobantes y respaldos. Conceda escritura únicamente al proceso de PHP/Apache.

9. Configure Apache para servir la raíz del proyecto. En XAMPP, una ubicación habitual es `C:\xampp\htdocs\Atenea`; si usa otra ruta, ajuste las URL de la aplicación.

10. Inicie Apache y MySQL y abra `http://localhost/Atenea/`.

La guía ampliada de instalación y actualización está en [`docs/instalacion_actualizacion.md`](docs/instalacion_actualizacion.md).

## Configuración del entorno

Use `.env.example` como referencia y mantenga los valores reales fuera del repositorio.

### Aplicación

- `APP_ENV`
- `APP_KEY`
- `APP_URL_LOCAL`
- `APP_URL_PRODUCTION`
- `ATENEA_ADMIN_PASSWORD`

### Base de datos

- `ATENEA_DB_HOST`
- `ATENEA_DB_NAME`
- `ATENEA_DB_USER`
- `ATENEA_DB_PASSWORD`

### Google OAuth

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`

### Stripe

- `STRIPE_PUBLISHABLE_KEY`
- `STRIPE_SECRET_KEY`
- `STRIPE_WEBHOOK_SECRET`
- `STRIPE_CURRENCY`

### Correo y buzón institucional

- `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`
- `SMTP_USERNAME`, `SMTP_PASSWORD`
- `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`
- `CONTACT_RECIPIENT`
- `IMAP_HOST`, `IMAP_PORT`, `IMAP_ENCRYPTION`
- `IMAP_USERNAME`, `IMAP_PASSWORD`, `IMAP_FOLDER`
- Variables `MAIL_*` de modo de prueba, límites y reintentos

### Almacenamiento privado

- `ACADEMIC_STORAGE_PATH`
- `COMMUNICATION_STORAGE_PATH`
- `BACKUP_STORAGE_PATH`
- `COMPROBANTES_STORAGE_PATH`
- `DTE_STORAGE_PATH`
- Límites `ACADEMIC_*_MAX_MB` y políticas `BACKUP_*`

### DTE

- `DTE_ENV`
- `DTE_NIT`, `DTE_NRC`
- `DTE_HACIENDA_USER`, `DTE_HACIENDA_PASSWORD`
- `DTE_CERTIFICATE_PATH`, `DTE_CERTIFICATE_SECRET`
- `DTE_SIGN_URL`, `DTE_ACCESS_TOKEN`
- URL `DTE_*_URL_TEST` y `DTE_*_URL_PRODUCTION`

Consulte [`docs/dte-setup.md`](docs/dte-setup.md) antes de habilitar un ambiente distinto de `simulation`.

## Base de datos

La aplicación usa MySQL/MariaDB mediante PDO. La base inicial está en `src/database/db_atenea.sql` y las actualizaciones incrementales en `src/database/migrations/`.

Antes de aplicar migraciones:

- realice un respaldo fuera del directorio público;
- ejecute solo las migraciones pendientes y en orden numérico;
- no ejecute archivos de rollback durante una actualización normal;
- pruebe primero en un ambiente de staging.

No almacene en Git exportaciones o respaldos que contengan usuarios, correos, contraseñas cifradas, pedidos u otros datos reales.

## Estructura del proyecto

```text
.
├── config/                 # Configuración de integraciones sin secretos
├── docs/                   # Guías técnicas y operativas
├── includes/               # Servicios, autenticación y lógica compartida
├── resources/dte/          # Esquemas para Documentos Tributarios Electrónicos
├── src/
│   ├── dashboard/          # Panel administrativo
│   ├── docente/            # Portal docente
│   ├── estudiantes/        # Portal estudiantil
│   ├── website/            # Sitio institucional
│   ├── pagos/              # Checkout y webhooks
│   ├── dte/                # Acceso seguro a documentos DTE
│   ├── cron/               # Procesos programables
│   └── database/           # Base inicial y migraciones
├── tests/                  # Pruebas de integración y visuales
├── uploads/                # Contenido público controlado
├── composer.json
├── .env.example
└── index.php               # Punto de entrada público
```

## Verificaciones

Valide dependencias y sintaxis antes de publicar:

```bash
composer validate --strict
composer audit
php -l index.php
```

Para revisar todos los archivos PHP en PowerShell:

```powershell
Get-ChildItem -Recurse -Filter *.php |
  ForEach-Object { php -l $_.FullName }
```

Las pruebas de integración son scripts PHP bajo `tests/integration/`. Las pruebas visuales requieren Playwright y una instancia local disponible en `http://localhost/Atenea`.

## Seguridad y producción

- Mantenga `.env`, certificados, claves, respaldos y almacenamiento privado fuera del repositorio y del directorio público.
- Use HTTPS, cookies seguras y credenciales de mínimo privilegio.
- Deshabilite `display_errors` en producción y almacene logs fuera del webroot.
- Proteja los endpoints cron y el webhook de Stripe según la documentación del proveedor.
- Pruebe Google OAuth, Stripe y DTE en sus ambientes de prueba antes de activar producción.
- Rote inmediatamente cualquier credencial que haya sido expuesta en un commit o compartida por un canal no seguro.

## Documentación adicional

- [`docs/instalacion_actualizacion.md`](docs/instalacion_actualizacion.md)
- [`docs/google-oauth-setup.md`](docs/google-oauth-setup.md)
- [`docs/dte-setup.md`](docs/dte-setup.md)
- [`docs/configuracion_correo_smtp_imap.md`](docs/configuracion_correo_smtp_imap.md)
