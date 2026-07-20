# Correcciones 7 — cola segura de correo

## Puesta en marcha

1. Aplicar `src/database/migrations/022_cola_correo_segura.sql` una sola vez.
2. Mantener `MAIL_TEST_MODE=true` en desarrollo y definir los límites indicados en `.env.example`.
3. Dejar `MAIL_TEST_RECIPIENT` vacío salvo cuando exista un buzón controlado y autorizado para una prueba administrativa individual.
4. Programar por cron, cada minuto, `php src/cron/procesar-cola-correos.php` únicamente en el entorno que deba procesar la cola.
5. Antes de habilitar producción, revisar la cola administrativa y cambiar `MAIL_TEST_MODE=false` de forma deliberada.

## Comportamiento de seguridad

- En modo de pruebas, cada mensaje ordinario se conserva en `correo_envios` con estado `cancelado`; no se abre una conexión SMTP.
- La prueba administrativa exige rol administrador, permiso, CSRF, dirección idéntica a `MAIL_TEST_RECIPIENT`, frase de confirmación y casilla de aceptación.
- La clave de idempotencia y el identificador de evento impiden más de un correo por evento.
- El procesador aplica límites globales y por usuario, recupera trabajos interrumpidos y respeta el máximo de reintentos.
- El historial muestra destinatarios enmascarados y errores sanitizados; no expone credenciales ni contenido del mensaje.
- Cada usuario puede desactivar categorías no críticas desde **Mi perfil → Notificaciones**. Los correos de seguridad y cuenta no son opcionales.

## Pruebas sin entrega

Ejecutar:

```text
C:\xampp\php\php.exe tests\integration\correcciones7_correo.php
```

La prueba exige `MAIL_TEST_MODE=true`, usa `example.invalid`, verifica la idempotencia y elimina su registro al finalizar.
