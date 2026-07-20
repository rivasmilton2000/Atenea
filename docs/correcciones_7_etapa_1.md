# Correcciones 7 — Etapa 1

## Configuración IMAP

La configuración se carga una sola vez desde `.env` mediante `includes/env.php` y se valida con `ImapConfig` en `includes/config/services.php`. Los módulos consumen `includes/imap_config.php`; ningún archivo contiene credenciales propias.

La extensión `imap` fue habilitada en `C:\xampp\php\php.ini`. Después de modificar ese archivo es obligatorio reiniciar Apache. El diagnóstico administrativo está en **Comunicaciones → Estado del servicio** y no muestra host, usuario ni contraseña.

La bandeja de estudiantes y docentes nunca muestra variables faltantes ni instrucciones técnicas. Esa información está reservada al diagnóstico administrativo.

## Errores e incidentes

Las rutas compartidas están en `src/errors`: 403, 404, 419, 500, 503 y `database.php`. Cada respuesta tiene un identificador de incidente, registra internamente código, usuario y URL, y evita mostrar excepciones, consultas o rutas físicas.

## Alertas y experiencia global

- SweetAlert queda reservado para confirmaciones delicadas.
- Los resultados breves usan toast y los errores de validación permanecen junto al formulario.
- Los formularios con carga impiden envíos duplicados.
- Las tablas, modales, foco de teclado y botones académicos comparten ajustes responsivos en `src/shared/assets/css/atenea-theme.css`.

## Notificaciones

Administrador, docente y estudiante disponen de campana, contador, historial, lectura individual, lectura total y enlaces relacionados. El contador se actualiza cada 30 segundos. La tabla `admin_notices` mantiene la prevención de duplicados mediante su clave de idempotencia.

Los cambios de cuenta y los intentos sospechosos generan notificación interna. El correo se reserva para eventos importantes y continúa protegido por la cola y `MAIL_TEST_MODE`.
