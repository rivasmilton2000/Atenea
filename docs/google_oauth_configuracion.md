# Configuración exacta de Google OAuth

## Callback real y fuente única

El único callback ejecutado por la aplicación es:

`src/auth/google-callback.php`

`includes/config/services.php` concatena esa ruta fija a la URL base del entorno. `GOOGLE_REDIRECT_URI` ya no se lee: esto elimina rutas relativas, carpetas duplicadas y divergencias entre autorización e intercambio del código.

## Entorno local inspeccionado

La configuración activa auditada el 2026-07-16 es `APP_ENV=local` y `APP_URL=http://localhost/Atenea` (compatibilidad). Por tanto, la aplicación envía exactamente:

- `redirect_uri`: `http://localhost/Atenea/src/auth/google-callback.php`
- Authorized redirect URIs: `http://localhost/Atenea/src/auth/google-callback.php`
- Authorized JavaScript origins: `http://localhost`

No agregar como sustitutos `http://127.0.0.1`, `https://localhost`, `/atenea`, una barra final, otro callback ni una segunda carpeta `Atenea`. Si se desea usar alguna variante, primero debe cambiarse conscientemente `APP_URL_LOCAL` y después registrarse el nuevo valor exacto en Google Cloud.

## Entorno de producción

No hay un dominio de producción real en el proyecto, `.env`, base de datos o documentación inspeccionados. Por esa razón no existe todavía una URI de producción exacta que pueda registrarse sin inventar datos. La aplicación no enviará una autorización OAuth válida en `APP_ENV=production` hasta configurar una base HTTPS real.

Cuando se conozca el despliegue, configure en el entorno del servidor:

```dotenv
APP_ENV=production
APP_URL_PRODUCTION=https://<dominio-y-subcarpeta-reales>
GOOGLE_CLIENT_ID=<client-id-del-cliente-web>
GOOGLE_CLIENT_SECRET=<secreto-en-el-servidor>
```

La URI que enviará será el valor exacto de `APP_URL_PRODUCTION`, sin barra final, seguido de `/src/auth/google-callback.php`. El origen JavaScript será únicamente esquema, host y puerto de esa misma base. No se debe agregar a Google Cloud el texto de ejemplo con `<...>`; primero hay que sustituirlo por el dato real y ejecutar la comprobación indicada abajo.

## Qué registrar en Google Cloud

En **Google Cloud Console → APIs y servicios → Credenciales → cliente OAuth 2.0 de tipo Aplicación web**:

1. En **Authorized redirect URIs**, agregar ahora para local: `http://localhost/Atenea/src/auth/google-callback.php`.
2. En **Authorized JavaScript origins**, agregar ahora para local: `http://localhost`.
3. Para producción, agregar los dos valores calculados por la aplicación solo después de configurar el dominio real.
4. Guardar `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET` en `.env` local o en secretos/variables del servidor. Nunca poner el secreto en PHP versionado, HTML o JavaScript.

## Comprobación carácter por carácter

Desde la raíz del proyecto, este comando imprime solo datos no secretos:

```powershell
C:\xampp\php\php.exe -r "require 'includes/google_oauth.php'; `$c=obtenerConfiguracionGoogle(); echo 'origin='.`$c['javascript_origin'].PHP_EOL.'redirect_uri='.`$c['redirect_uri'].PHP_EOL;"
```

Para local debe producir exactamente:

```text
origin=http://localhost
redirect_uri=http://localhost/Atenea/src/auth/google-callback.php
```

Copie esos valores directamente a Google Cloud. Compare especialmente esquema (`http`/`https`), host (`localhost`/`127.0.0.1`), puerto, mayúscula de `Atenea`, subcarpeta, guiones, extensión `.php` y ausencia de barra final. También puede abrir la pestaña Red del navegador, pulsar cualquiera de los dos botones Google y verificar el parámetro `redirect_uri` de la solicitud a `accounts.google.com`.

## Configuración externa pendiente

- Registrar la URI y el origen local exactos en el mismo cliente web al que pertenece `GOOGLE_CLIENT_ID`.
- Confirmar y configurar el dominio/subcarpeta HTTPS reales de producción; después obtener los valores exactos con el comando anterior ejecutado en ese entorno y registrarlos.
- Mantener habilitados los scopes OpenID `openid email profile` y la pantalla de consentimiento correspondiente.

Google puede tardar unos minutos en propagar cambios. El código nunca muestra `client_secret`, tokens ni respuestas internas de Google al usuario.
