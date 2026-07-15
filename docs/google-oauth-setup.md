# Configuracion de Google OAuth para Atenea

## Ruta real usada por el proyecto

Atenea inicia ambos casos (inicio de sesion y registro) en `src/auth/google.php` y procesa ambos en un unico callback:

`src/auth/google-callback.php`

La URL se obtiene exclusivamente de `GOOGLE_REDIRECT_URI`. Si no se define, se construye con `APP_URL` y la ruta anterior. El valor enviado al intercambio del codigo es exactamente el mismo valor enviado en la solicitud de autorizacion.

## Desarrollo local verificado

- Origen JavaScript autorizado: `http://localhost`
- URI de redireccion autorizada: `http://localhost/Atenea/src/auth/google-callback.php`
- `APP_ENV`: `local`
- `APP_URL`: `http://localhost/Atenea`
- `GOOGLE_REDIRECT_URI`: `http://localhost/Atenea/src/auth/google-callback.php`

No registre variantes con `127.0.0.1`, otra capitalizacion de `/Atenea`, una barra final ni otro archivo callback, salvo que cambie de forma consciente toda la configuracion local.

## Produccion

El repositorio no contiene un dominio de produccion verificable y no se inventa uno. Cuando se conozca el dominio, sustituya `https://DOMINIO_REAL` por el origen HTTPS exacto del despliegue:

- Origen JavaScript autorizado: `https://DOMINIO_REAL`
- URI de redireccion autorizada: `https://DOMINIO_REAL/src/auth/google-callback.php`
- `APP_ENV`: `production`
- `APP_URL`: `https://DOMINIO_REAL`
- `GOOGLE_REDIRECT_URI`: `https://DOMINIO_REAL/src/auth/google-callback.php`

Si Atenea se publica dentro de un subdirectorio, este debe aparecer tanto en `APP_URL` como en la URI. Por ejemplo, si `APP_URL` termina en `/Atenea`, el callback termina en `/Atenea/src/auth/google-callback.php`. No registre una URI de produccion hasta confirmar el `APP_URL` real del servidor.

## Registro en Google Cloud

1. Abra Google Cloud Console y seleccione el proyecto que contiene el OAuth Client de Atenea.
2. Configure la pantalla de consentimiento OAuth y habilite los alcances `openid`, `email` y `profile`.
3. Entre a **APIs y servicios > Credenciales** y abra el cliente OAuth 2.0 de tipo **Aplicacion web**.
4. En **Origenes de JavaScript autorizados**, agregue el origen correspondiente, sin ruta ni barra final.
5. En **URI de redireccion autorizados**, agregue el callback completo y exacto indicado arriba.
6. Guarde los cambios y espere unos minutos si Google aun presenta una configuracion anterior.
7. Guarde `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET` solo en `.env` o en secretos del servidor. Nunca los agregue al repositorio.

## Comprobar el `redirect_uri` que envia Atenea

1. Abra las herramientas de desarrollo del navegador, pestaña **Network/Red**.
2. Pulse **Continuar con Google** desde inicio de sesion o registro.
3. Abra la solicitud a `accounts.google.com/o/oauth2/v2/auth`.
4. Revise el parametro `redirect_uri`. Debe coincidir caracter por caracter con la URI registrada en Google Cloud.

Tambien puede inspeccionarlo sin exponer secretos desde PHP:

```php
require 'includes/google_oauth.php';
echo obtenerConfiguracionGoogle()['redirect_uri'];
```

El error `redirect_uri_mismatch` solo quedara completamente resuelto despues de registrar esa URI exacta en el cliente OAuth correcto de Google Cloud.
